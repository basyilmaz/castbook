<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class UpdateService
{
    protected string $backupPath;
    protected string $updatePath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        $this->updatePath = storage_path('app/updates');
        
        // Klasörleri oluştur
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
        if (!File::exists($this->updatePath)) {
            File::makeDirectory($this->updatePath, 0755, true);
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
        
        return [
            'current' => $version,
            'formatted' => 'v' . $version,
            'last_update' => $this->getLastUpdateDate(),
        ];
    }

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
            // Tablo listesini al
            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            $key = "Tables_in_{$dbName}";
            
            $sql = "-- CastBook Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Version: " . $this->getCurrentVersion() . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$key;
                
                // Tablo yapısı
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Tablo verileri
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

            // Sadece kritik dosyaları yedekle
            $foldersToBackup = ['app', 'config', 'database/migrations', 'routes', 'resources/views'];
            
            foreach ($foldersToBackup as $folder) {
                $fullPath = base_path($folder);
                if (File::exists($fullPath)) {
                    $this->addFolderToZip($zip, $fullPath, $folder);
                }
            }
            
            // .env dosyasını yedekle
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

    /**
     * Klasörü ZIP'e ekle
     */
    protected function addFolderToZip(ZipArchive $zip, string $folder, string $relativePath): void
    {
        $files = File::allFiles($folder);
        foreach ($files as $file) {
            $localPath = $relativePath . '/' . $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $localPath);
        }
    }

    /**
     * Migration çalıştır
     */
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

    /**
     * Cache temizle
     */
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

    /**
     * Yedekleme listesi
     */
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

    /**
     * Yedekleme indir
     */
    public function getBackupPath(string $filename): ?string
    {
        $path = $this->backupPath . '/' . $filename;
        if (File::exists($path)) {
            return $path;
        }
        return null;
    }

    /**
     * Yedekleme sil
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->backupPath . '/' . $filename;
        if (File::exists($path)) {
            return File::delete($path);
        }
        return false;
    }

    /**
     * Byte formatla
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
