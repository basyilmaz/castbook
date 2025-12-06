# Castbook Prod Kurulum Notları

## 1. PHP Sürümü
- Composer paketleri PHP `>= 8.2.0` istiyor; cPanel’de MultiPHP Manager üzerinden domain’i **PHP 8.3 (ea-php83)** sürümüne çek.
- CLI tarafında da aynı sürüm kullanılmadıysa `/opt/cpanel/ea-php83/root/usr/bin/php` yolunu kullanarak script çalıştır.

## 2. App Service Provider Ayarı
`app/Providers/AppServiceProvider.php` dosyasındaki `boot()` metoduna aşağıdaki satırı ekle:

```php
Schema::defaultStringLength(191);
```

## 3. .env Örnek Ayarları
```env
APP_KEY=base64:PuDvakNSCidKmTRThqGiMkXQVBqudvcysXKl+3SG7cw=
APP_URL=https://castbook.castamon.com
APP_DEBUG=false

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file

BACKUP_RESTORE_ENABLED=false
BACKUP_MAX_UPLOAD_MB=50
```
- APP_KEY’i lokal makinede `php artisan key:generate --show` ile üret.
- Redis yoksa file/sync sürücülerini kullan.

## 4. Public Dizini Temizliği
- ZIP’ten gelen `public/C:\...` isimli klasörleri sil.
- Symlink izni yoksa `storage/app/public` içeriğini `public/storage` klasörüne manuel kopyala.

## 5. Migration Scripti
Shared hosting’de CLI olmadığı için tarayıcıdan migration çalıştırmak üzere `public/run-migrate.php` dosyasını oluştur:

```php
<?php
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$status = $kernel->call('migrate', ['--force' => true]);

header('Content-Type: text/plain; charset=utf-8');
echo $kernel->output();
echo "\nStatus: " . $status;
```
- Tarayıcıda `https://castbook.castamon.com/run-migrate.php` çağır.
- Çıktı `Status: 0` ise migration tamamlanmıştır; dosyayı sil.
- Migration sırasında hata aldıysan gerekirse veritabanındaki tabloları silip tekrar çalıştır.

## 6. Config Cache Dosyalarını Temizle
- `bootstrap/cache/config.php`
- `bootstrap/cache/routes-v7.php`

Laravel bu dosyaları otomatik yeniden oluşturur.

## 7. Admin Kullanıcı Oluşturma
1. Geçici `public/hash.php` dosyasıyla parola hash’i üret:
    ```php
    <?php echo password_hash('GucluParola123!', PASSWORD_BCRYPT);
    ```
2. phpMyAdmin’de `users` tablosuna aşağıdaki sorguyu çalıştır (hash’i yukarıdan al):
    ```sql
    INSERT INTO `users`
      (`name`, `email`, `email_verified_at`, `password`, `role`, `is_active`, `created_at`, `updated_at`)
    VALUES
      ('Admin', 'admin@castbook.com', NOW(), '<HASH>', 'admin', 1, NOW(), NOW());
    ```
3. `hash.php` dahil tüm geçici dosyaları sil.

## 8. Document Root

cPanel → Domains → `castbook.castamon.com` → Document Root = `/home/<kullanıcı>/castbook.castamon.com/public`

## 9. Testler
- `https://castbook.castamon.com/health` → `{"status":"ok"}` dönmeli.
- Admin hesabı ile giriş yapıp menüleri kontrol et.
- `storage/logs/laravel.log` ve `public/error_log`’u incele; hata yoksa gerekirse logları temizle.

## 10. Çalışma Sırası (Özet)
1. PHP versiyonunu 8.3’e ayarla.
2. Kod paketini yükle, public dizinini temizle.
3. .env ayarlarını yap, APP_KEY’i üret.
4. AppServiceProvider’da `Schema::defaultStringLength(191)` satırını kontrol et.
5. Config cache dosyalarını sil.
6. `run-migrate.php` ile migration çalıştır (bitince sil).
7. Admin hesabını phpMyAdmin üzerinden ekle.
8. Geçici scriptleri sil, `/health` ve login testini yap.
9. `APP_DEBUG=false` olduğundan emin ol.
