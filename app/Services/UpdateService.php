<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class UpdateService
{
    protected string $backupPath;
    protected string $updatePath;
    protected string $rollbackPath;

    // Güncelleme sunucusu URL (config'den alınır)
    protected ?string $updateServerUrl;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        $this->updatePath = storage_path('app/updates');
        $this->rollbackPath = storage_path('app/rollback');
        $this->updateServerUrl = config('app.update_server_url');
        
        // Klasörleri oluştur
        foreach ([$this->backupPath, $this->updatePath, $this->rollbackPath] as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }
    }

    /**
     * Mevcut versiyonu al
     */
    public function getCurrentVersion(): string
    {
        return config('app.version', '1.0.0');
    }

    /**
     * Versiyon bilgilerini al
     */
    public function getVersionInfo(): array
    {
        $version = $this->getCurrentVersion();
        $updateInfo = $this->checkForUpdates();
        
        return [
            'current' => $version,
            'formatted' => 'v' . $version,
            'last_update' => $this->getLastUpdateDate(),
            'update_available' => $updateInfo['available'] ?? false,
            'latest_version' => $updateInfo['version'] ?? null,
            'changelog' => $updateInfo['changelog'] ?? null,
            'download_url' => $updateInfo['download_url'] ?? null,
        ];
    }

    // ==========================================
    // YENİ VERSİYON KONTROLÜ
    // ==========================================

    /**
     * Güncelleme sunucusundan yeni versiyon kontrolü
     */
    public function checkForUpdates(): array
    {
        // Cache'den kontrol (1 saat geçerli)
        $cacheKey = 'update_check_result';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Demo mod: Güncelleme sunucusu yoksa simüle et
        if (!$this->updateServerUrl) {
            $result = $this->simulateUpdateCheck();
            Cache::put($cacheKey, $result, 3600);
            return $result;
        }

        try {
            $response = Http::timeout(10)->get($this->updateServerUrl . '/api/updates/check', [
                'product' => 'castbook',
                'version' => $this->getCurrentVersion(),
                'php_version' => PHP_VERSION,
                'domain' => request()->getHost(),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Cache::put($cacheKey, $result, 3600);
                return $result;
            }
        } catch (\Exception $e) {
            Log::warning('Update check failed: ' . $e->getMessage());
        }

        return ['available' => false, 'message' => 'Güncelleme kontrolü başarısız.'];
    }

    /**
     * Demo mod için güncelleme simülasyonu
     */
    protected function simulateUpdateCheck(): array
    {
        $currentVersion = $this->getCurrentVersion();
        
        // Simüle edilmiş yeni versiyon (minor +1)
        $parts = explode('.', $currentVersion);
        $parts[1] = (int)$parts[1] + 1;
        $parts[2] = 0;
        $simulatedVersion = implode('.', $parts);

        return [
            'available' => false, // Demo modda güncelleme yok
            'version' => $simulatedVersion,
            'changelog' => "## v{$simulatedVersion}\n- Performans iyileştirmeleri\n- Hata düzeltmeleri",
            'download_url' => null,
            'message' => 'Sisteminiz güncel. (Demo mod)',
        ];
    }

    /**
     * Cache'i temizle ve tekrar kontrol et
     */
    public function forceCheckForUpdates(): array
    {
        Cache::forget('update_check_result');
        return $this->checkForUpdates();
    }

    // ==========================================
    // GÜNCELLEMEYİ İNDİR
    // ==========================================

    /**
     * Güncelleme paketini indir
     */
    public function downloadUpdate(string $downloadUrl): array
    {
        try {
            $filename = 'update_' . date('Y-m-d_H-i-s') . '.zip';
            $filepath = $this->updatePath . '/' . $filename;

            // Dosyayı indir
            $response = Http::timeout(300)->get($downloadUrl);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'İndirme başarısız: HTTP ' . $response->status(),
                ];
            }

            File::put($filepath, $response->body());

            // ZIP geçerlilik kontrolü
            $zip = new ZipArchive();
            if ($zip->open($filepath) !== true) {
                File::delete($filepath);
                return [
                    'success' => false,
                    'message' => 'Geçersiz ZIP dosyası.',
                ];
            }
            $zip->close();

            return [
                'success' => true,
                'message' => 'Güncelleme indirildi.',
                'filename' => $filename,
                'path' => $filepath,
                'size' => $this->formatBytes(File::size($filepath)),
            ];
        } catch (\Exception $e) {
            Log::error('Update download failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'İndirme hatası: ' . $e->getMessage(),
            ];
        }
    }

    // ==========================================
    // GÜNCELLEMEYİ UYGULA
    // ==========================================

    /**
     * Güncellemeyi uygula (tam süreç)
     */
    public function applyUpdate(string $updateFilePath): array
    {
        $steps = [];

        try {
            // 1. Rollback noktası oluştur
            $steps[] = 'Rollback noktası oluşturuluyor...';
            $rollbackResult = $this->createRollbackPoint();
            if (!$rollbackResult['success']) {
                throw new \Exception('Rollback noktası oluşturulamadı: ' . $rollbackResult['message']);
            }
            $steps[] = '✓ Rollback noktası oluşturuldu';

            // 2. Bakım modunu aç
            $steps[] = 'Bakım modu açılıyor...';
            Artisan::call('down', ['--secret' => 'castbook-update']);
            $steps[] = '✓ Bakım modu açıldı';

            // 3. ZIP'i aç ve dosyaları kopyala
            $steps[] = 'Dosyalar güncelleniyor...';
            $extractResult = $this->extractAndApply($updateFilePath);
            if (!$extractResult['success']) {
                throw new \Exception('Dosya güncelleme hatası: ' . $extractResult['message']);
            }
            $steps[] = '✓ Dosyalar güncellendi';

            // 4. Composer autoload
            $steps[] = 'Autoload güncelleniyor...';
            exec('composer dump-autoload --optimize 2>&1', $output, $returnCode);
            $steps[] = $returnCode === 0 ? '✓ Autoload güncellendi' : '⚠ Autoload güncellenemedi (manuel çalıştırın)';

            // 5. Migration çalıştır
            $steps[] = 'Migration çalıştırılıyor...';
            $migrationResult = $this->runMigrations();
            if ($migrationResult['success']) {
                $steps[] = '✓ Migration tamamlandı';
            } else {
                $steps[] = '⚠ Migration atlandı: ' . $migrationResult['message'];
            }

            // 6. Cache temizle
            $steps[] = 'Önbellek temizleniyor...';
            $this->clearCache();
            $steps[] = '✓ Önbellek temizlendi';

            // 7. Bakım modunu kapat
            Artisan::call('up');
            $steps[] = '✓ Bakım modu kapatıldı';

            // 8. Güncelleme dosyasını sil
            File::delete($updateFilePath);
            $steps[] = '✓ Güncelleme tamamlandı!';

            return [
                'success' => true,
                'message' => 'Güncelleme başarıyla tamamlandı.',
                'steps' => $steps,
            ];

        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            
            // Hata durumunda bakım modunu kapat
            Artisan::call('up');
            
            $steps[] = '✗ HATA: ' . $e->getMessage();
            $steps[] = 'Rollback yapabilirsiniz.';

            return [
                'success' => false,
                'message' => 'Güncelleme başarısız: ' . $e->getMessage(),
                'steps' => $steps,
                'can_rollback' => true,
            ];
        }
    }

    /**
     * ZIP dosyasını aç ve dosyaları kopyala
     */
    protected function extractAndApply(string $zipPath): array
    {
        $extractPath = $this->updatePath . '/extracted_' . time();
        
        try {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                return ['success' => false, 'message' => 'ZIP açılamadı.'];
            }

            $zip->extractTo($extractPath);
            $zip->close();

            // Dosyaları kopyala (vendor ve .env hariç)
            $excludeDirs = ['vendor', 'node_modules', 'storage', '.git'];
            $excludeFiles = ['.env', '.env.local', '.env.production'];

            $this->copyDirectory($extractPath, base_path(), $excludeDirs, $excludeFiles);

            // Temizlik
            File::deleteDirectory($extractPath);

            return ['success' => true, 'message' => 'Dosyalar başarıyla kopyalandı.'];

        } catch (\Exception $e) {
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Dizin kopyala (exclude listesiyle)
     */
    protected function copyDirectory(string $source, string $dest, array $excludeDirs = [], array $excludeFiles = []): void
    {
        $items = File::allFiles($source, true);
        
        foreach ($items as $item) {
            $relativePath = $item->getRelativePathname();
            
            // Exclude kontrolü
            foreach ($excludeDirs as $dir) {
                if (str_starts_with($relativePath, $dir . '/') || $relativePath === $dir) {
                    continue 2;
                }
            }
            
            if (in_array(basename($relativePath), $excludeFiles)) {
                continue;
            }

            $destPath = $dest . '/' . $relativePath;
            $destDir = dirname($destPath);
            
            if (!File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }
            
            File::copy($item->getRealPath(), $destPath);
        }
    }

    // ==========================================
    // ROLLBACK MEKANİZMASI
    // ==========================================

    /**
     * Rollback noktası oluştur
     */
    public function createRollbackPoint(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $version = $this->getCurrentVersion();
        $filename = "rollback_{$version}_{$timestamp}.zip";
        $filepath = $this->rollbackPath . '/' . $filename;

        try {
            $zip = new ZipArchive();
            if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Rollback ZIP oluşturulamadı.');
            }

            // Kritik dosyaları yedekle
            $foldersToBackup = ['app', 'config', 'database', 'routes', 'resources'];
            
            foreach ($foldersToBackup as $folder) {
                $fullPath = base_path($folder);
                if (File::exists($fullPath)) {
                    $this->addFolderToZip($zip, $fullPath, $folder);
                }
            }

            // composer.json ve composer.lock
            foreach (['composer.json', 'composer.lock'] as $file) {
                if (File::exists(base_path($file))) {
                    $zip->addFile(base_path($file), $file);
                }
            }

            $zip->close();

            // Eski rollback noktalarını temizle (son 3'ü tut)
            $this->cleanupOldRollbacks(3);

            return [
                'success' => true,
                'message' => 'Rollback noktası oluşturuldu.',
                'filename' => $filename,
                'path' => $filepath,
            ];

        } catch (\Exception $e) {
            Log::error('Rollback point creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Rollback noktası oluşturulamadı: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Rollback uygula
     */
    public function rollback(string $rollbackFile = null): array
    {
        try {
            // En son rollback noktasını bul
            if (!$rollbackFile) {
                $rollbacks = $this->getRollbackList();
                if (empty($rollbacks)) {
                    return ['success' => false, 'message' => 'Rollback noktası bulunamadı.'];
                }
                $rollbackFile = $rollbacks[0]['path'];
            }

            if (!File::exists($rollbackFile)) {
                return ['success' => false, 'message' => 'Rollback dosyası bulunamadı.'];
            }

            // Bakım modunu aç
            Artisan::call('down', ['--secret' => 'castbook-rollback']);

            // ZIP'i aç ve dosyaları geri yükle
            $zip = new ZipArchive();
            if ($zip->open($rollbackFile) !== true) {
                Artisan::call('up');
                return ['success' => false, 'message' => 'Rollback ZIP açılamadı.'];
            }

            $extractPath = $this->rollbackPath . '/restore_' . time();
            $zip->extractTo($extractPath);
            $zip->close();

            // Dosyaları geri yükle
            $this->copyDirectory($extractPath, base_path(), [], ['.env']);

            // Temizlik
            File::deleteDirectory($extractPath);

            // Migration rollback (isteğe bağlı - tehlikeli olabilir)
            // Artisan::call('migrate:rollback', ['--step' => 1, '--force' => true]);

            // Cache temizle
            $this->clearCache();

            // Bakım modunu kapat
            Artisan::call('up');

            return [
                'success' => true,
                'message' => 'Rollback başarıyla tamamlandı.',
            ];

        } catch (\Exception $e) {
            Artisan::call('up');
            Log::error('Rollback failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Rollback başarısız: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Rollback noktalarını listele
     */
    public function getRollbackList(): array
    {
        $rollbacks = [];
        
        if (File::exists($this->rollbackPath)) {
            $files = File::files($this->rollbackPath);
            foreach ($files as $file) {
                if (str_starts_with($file->getFilename(), 'rollback_')) {
                    $rollbacks[] = [
                        'name' => $file->getFilename(),
                        'path' => $file->getRealPath(),
                        'size' => $this->formatBytes($file->getSize()),
                        'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    ];
                }
            }
        }
        
        return collect($rollbacks)->sortByDesc('date')->values()->all();
    }

    /**
     * Eski rollback noktalarını temizle
     */
    protected function cleanupOldRollbacks(int $keep = 3): void
    {
        $rollbacks = $this->getRollbackList();
        
        if (count($rollbacks) > $keep) {
            $toDelete = array_slice($rollbacks, $keep);
            foreach ($toDelete as $rollback) {
                File::delete($rollback['path']);
            }
        }
    }

    // ==========================================
    // MEVCUT METODLAR (değişiklik yok)
    // ==========================================

    /**
     * Son güncelleme tarihini al
     */
    protected function getLastUpdateDate(): ?string
    {
        $migrationPath = database_path('migrations');
        if (File::exists($migrationPath)) {
            $files = File::files($migrationPath);
            if (count($files) > 0) {
                $latestFile = collect($files)->sortByDesc(fn($f) => $f->getMTime())->first();
                return date('Y-m-d', $latestFile->getMTime());
            }
        }
        return null;
    }

    /**
     * Veritabanı yedeklemesi oluştur
     */
    public function backupDatabase(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "db_backup_{$timestamp}.sql";
        $filepath = $this->backupPath . '/' . $filename;

        try {
            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            $key = "Tables_in_{$dbName}";
            
            $sql = "-- CastBook Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Version: " . $this->getCurrentVersion() . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$key;
                
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $values = collect((array) $row)->map(function ($value) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    })->implode(', ');
                    
                    $columns = collect(array_keys((array) $row))->map(fn($c) => "`{$c}`")->implode(', ');
                    $sql .= "INSERT INTO `{$tableName}` ({$columns}) VALUES ({$values});\n";
                }
                $sql .= "\n";
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            File::put($filepath, $sql);

            return [
                'success' => true,
                'message' => 'Veritabanı yedeklendi.',
                'filename' => $filename,
                'path' => $filepath,
                'size' => $this->formatBytes(File::size($filepath)),
            ];
        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Yedekleme hatası: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dosya yedeklemesi oluştur
     */
    public function backupFiles(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "files_backup_{$timestamp}.zip";
        $filepath = $this->backupPath . '/' . $filename;

        try {
            $zip = new ZipArchive();
            if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('ZIP dosyası oluşturulamadı.');
            }

            $foldersToBackup = ['app', 'config', 'database/migrations', 'routes', 'resources/views'];
            
            foreach ($foldersToBackup as $folder) {
                $fullPath = base_path($folder);
                if (File::exists($fullPath)) {
                    $this->addFolderToZip($zip, $fullPath, $folder);
                }
            }
            
            if (File::exists(base_path('.env'))) {
                $zip->addFile(base_path('.env'), '.env');
            }

            $zip->close();

            return [
                'success' => true,
                'message' => 'Dosyalar yedeklendi.',
                'filename' => $filename,
                'path' => $filepath,
                'size' => $this->formatBytes(File::size($filepath)),
            ];
        } catch (\Exception $e) {
            Log::error('File backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Dosya yedekleme hatası: ' . $e->getMessage(),
            ];
        }
    }

    protected function addFolderToZip(ZipArchive $zip, string $folder, string $relativePath): void
    {
        $files = File::allFiles($folder);
        foreach ($files as $file) {
            $localPath = $relativePath . '/' . $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $localPath);
        }
    }

    public function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            return [
                'success' => true,
                'message' => 'Migration tamamlandı.',
                'output' => $output,
            ];
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Migration hatası: ' . $e->getMessage(),
            ];
        }
    }

    public function clearCache(): array
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            return [
                'success' => true,
                'message' => 'Önbellek temizlendi.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Önbellek temizleme hatası: ' . $e->getMessage(),
            ];
        }
    }

    public function getBackupList(): array
    {
        $backups = [];
        
        if (File::exists($this->backupPath)) {
            $files = File::files($this->backupPath);
            foreach ($files as $file) {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    'type' => str_contains($file->getFilename(), 'db_') ? 'database' : 'files',
                ];
            }
        }
        
        return collect($backups)->sortByDesc('date')->values()->all();
    }

    public function getBackupPath(string $filename): ?string
    {
        $path = $this->backupPath . '/' . $filename;
        if (File::exists($path)) {
            return $path;
        }
        return null;
    }

    public function deleteBackup(string $filename): bool
    {
        $path = $this->backupPath . '/' . $filename;
        if (File::exists($path)) {
            return File::delete($path);
        }
        return false;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
