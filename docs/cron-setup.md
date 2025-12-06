# CRON Job Kurulumu

CastBook'un zamanlanmış görevlerinin otomatik çalışması için cron job kurmak gerekiyor.

## Linux/macOS Kurulumu

```bash
# Cron editörünü aç
crontab -e

# Aşağıdaki satırı ekle (PATH'i projenin yoluna göre düzenle)
* * * * * cd /var/www/castbook && php artisan schedule:run >> /dev/null 2>&1
```

## Windows Kurulumu (Task Scheduler)

1. **Task Scheduler'ı Aç**
   - Windows tuşu + R → `taskschd.msc` yaz

2. **Yeni Görev Oluştur**
   - Actions → "Create Task"

3. **Genel Ayarlar**
   - Name: `CastBook Laravel Scheduler`
   - Run whether user is logged on or not ✓

4. **Triggers (Tetikleyiciler)**
   - New → Daily
   - Repeat task every: 1 minute
   - Duration: Indefinitely

5. **Actions (Eylemler)**
   - New → Start a program
   - Program: `C:\xampp\php\php.exe` (PHP yolunuz)
   - Arguments: `artisan schedule:run`
   - Start in: `C:\YazilimProjeler\castbook` (Proje yolu)

## Zamanlanmış Görevler

| Komut | Zamanlama | Açıklama |
|-------|-----------|----------|
| `app:generate-monthly-invoices` | Ayın X. günü 08:00 | Aylık faturaları oluşturur |
| `email:payment-reminders` | Her gün 09:00 | Ödeme hatırlatmaları gönderir |
| `app:generate-tax-declarations` | Her ayın 1'i 08:30 | Beyannameleri oluşturur |
| `email:weekly-summary` | Haftanın X. günü 09:00 | Haftalık özet gönderir |

## Manuel Test

```bash
# Tüm zamanlanmış görevleri listele
php artisan schedule:list

# Scheduler'ı manuel çalıştır
php artisan schedule:run

# Belirli bir komutu test et
php artisan email:weekly-summary --force
php artisan email:payment-reminders --force
```

## Supervisor Kurulumu (Opsiyonel - Queue İşlemleri)

E-posta gönderimlerinin hızlı olması için queue worker kullanabilirsiniz:

```ini
[program:castbook-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/castbook/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/castbook/storage/logs/worker.log
```
