<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use PDOException;

class InstallService
{
    /**
     * Sistem gereksinimlerini kontrol et
     */
    public function checkRequirements(): array
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP >= 8.1',
                'required' => true,
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL',
                'required' => true,
                'current' => extension_loaded('pdo_mysql') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('pdo_mysql'),
            ],
            'openssl' => [
                'name' => 'OpenSSL',
                'required' => true,
                'current' => extension_loaded('openssl') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('openssl'),
            ],
            'mbstring' => [
                'name' => 'Mbstring',
                'required' => true,
                'current' => extension_loaded('mbstring') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('mbstring'),
            ],
            'tokenizer' => [
                'name' => 'Tokenizer',
                'required' => true,
                'current' => extension_loaded('tokenizer') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('tokenizer'),
            ],
            'xml' => [
                'name' => 'XML',
                'required' => true,
                'current' => extension_loaded('xml') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('xml'),
            ],
            'ctype' => [
                'name' => 'Ctype',
                'required' => true,
                'current' => extension_loaded('ctype') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('ctype'),
            ],
            'json' => [
                'name' => 'JSON',
                'required' => true,
                'current' => extension_loaded('json') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('json'),
            ],
            'bcmath' => [
                'name' => 'BCMath',
                'required' => false,
                'current' => extension_loaded('bcmath') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('bcmath'),
            ],
            'fileinfo' => [
                'name' => 'Fileinfo',
                'required' => true,
                'current' => extension_loaded('fileinfo') ? 'Yüklü' : 'Yüklü Değil',
                'status' => extension_loaded('fileinfo'),
            ],
        ];

        return $requirements;
    }

    /**
     * Klasör izinlerini kontrol et
     */
    public function checkPermissions(): array
    {
        return [
            'storage' => [
                'path' => 'storage/',
                'writable' => is_writable(storage_path()),
            ],
            'storage_logs' => [
                'path' => 'storage/logs/',
                'writable' => is_writable(storage_path('logs')),
            ],
            'storage_framework' => [
                'path' => 'storage/framework/',
                'writable' => is_writable(storage_path('framework')),
            ],
            'storage_framework_cache' => [
                'path' => 'storage/framework/cache/',
                'writable' => is_writable(storage_path('framework/cache')),
            ],
            'storage_framework_sessions' => [
                'path' => 'storage/framework/sessions/',
                'writable' => is_writable(storage_path('framework/sessions')),
            ],
            'storage_framework_views' => [
                'path' => 'storage/framework/views/',
                'writable' => is_writable(storage_path('framework/views')),
            ],
            'bootstrap_cache' => [
                'path' => 'bootstrap/cache/',
                'writable' => is_writable(base_path('bootstrap/cache')),
            ],
        ];
    }

    /**
     * Tüm gereksinimler karşılandı mı?
     */
    public function allRequirementsMet(): bool
    {
        $requirements = $this->checkRequirements();
        foreach ($requirements as $req) {
            if ($req['required'] && !$req['status']) {
                return false;
            }
        }

        $permissions = $this->checkPermissions();
        foreach ($permissions as $perm) {
            if (!$perm['writable']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Veritabanı bağlantısını test et
     */
    public function testDatabaseConnection(string $host, string $port, string $database, string $username, string $password): array
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return [
                'success' => true,
                'message' => 'Veritabanı bağlantısı başarılı!',
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * .env dosyasını oluştur veya güncelle
     */
    public function createEnvFile(array $data): bool
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        // .env.example yoksa varsayılan içerik kullan
        if (File::exists($envExamplePath)) {
            $envContent = File::get($envExamplePath);
        } else {
            $envContent = $this->getDefaultEnvContent();
        }

        // Değerleri güncelle
        $envContent = $this->updateEnvValue($envContent, 'APP_NAME', $data['app_name'] ?? 'CastBook');
        $envContent = $this->updateEnvValue($envContent, 'APP_ENV', 'production');
        $envContent = $this->updateEnvValue($envContent, 'APP_DEBUG', 'false');
        $envContent = $this->updateEnvValue($envContent, 'APP_URL', $data['app_url'] ?? 'http://localhost');
        
        $envContent = $this->updateEnvValue($envContent, 'DB_CONNECTION', 'mysql');
        $envContent = $this->updateEnvValue($envContent, 'DB_HOST', $data['db_host'] ?? 'localhost');
        $envContent = $this->updateEnvValue($envContent, 'DB_PORT', $data['db_port'] ?? '3306');
        $envContent = $this->updateEnvValue($envContent, 'DB_DATABASE', $data['db_database'] ?? '');
        $envContent = $this->updateEnvValue($envContent, 'DB_USERNAME', $data['db_username'] ?? '');
        $envContent = $this->updateEnvValue($envContent, 'DB_PASSWORD', $data['db_password'] ?? '');
        
        $envContent = $this->updateEnvValue($envContent, 'APP_INSTALLED', 'true');

        return File::put($envPath, $envContent) !== false;
    }

    /**
     * .env değerini güncelle
     */
    protected function updateEnvValue(string $content, string $key, string $value): string
    {
        $value = $this->escapeEnvValue($value);
        
        // Key mevcut mu kontrol et
        if (preg_match("/^{$key}=/m", $content)) {
            return preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        }
        
        // Key yoksa ekle
        return $content . "\n{$key}={$value}";
    }

    /**
     * .env değerini escape et
     */
    protected function escapeEnvValue(string $value): string
    {
        if (str_contains($value, ' ') || str_contains($value, '#') || str_contains($value, '"')) {
            return '"' . addslashes($value) . '"';
        }
        return $value;
    }

    /**
     * Varsayılan .env içeriği
     */
    protected function getDefaultEnvContent(): string
    {
        return <<<ENV
APP_NAME=CastBook
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=local

APP_INSTALLED=false
ENV;
    }

    /**
     * APP_KEY oluştur
     */
    public function generateAppKey(): string
    {
        $key = 'base64:' . base64_encode(random_bytes(32));
        
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            $content = $this->updateEnvValue($content, 'APP_KEY', $key);
            File::put($envPath, $content);
        }
        
        return $key;
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
                'message' => 'Migration başarıyla tamamlandı.',
                'output' => $output,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Migration hatası: ' . $e->getMessage(),
                'output' => '',
            ];
        }
    }

    /**
     * Seeder çalıştır
     */
    public function runSeeders(): array
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();
            
            return [
                'success' => true,
                'message' => 'Seeder başarıyla tamamlandı.',
                'output' => $output,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Seeder hatası: ' . $e->getMessage(),
                'output' => '',
            ];
        }
    }

    /**
     * Uygulama kurulu mu?
     */
    public function isInstalled(): bool
    {
        // .env dosyası var mı?
        if (!File::exists(base_path('.env'))) {
            return false;
        }
        
        // APP_INSTALLED=true mi?
        $installed = env('APP_INSTALLED', false);
        if (!$installed || $installed === 'false') {
            return false;
        }
        
        // Veritabanı bağlantısı var mı?
        try {
            DB::connection()->getPdo();
            // users tablosu var mı?
            return DB::getSchemaBuilder()->hasTable('users');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cache temizle
     */
    public function clearCache(): void
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (\Exception $e) {
            // Hata olsa bile devam et
        }
    }
}
