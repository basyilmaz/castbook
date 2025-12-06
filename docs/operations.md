# Operasyon Notları

## Aylık Fatura Cron Kurulumu
1. Sunucuda crontab -e komutunu çalıştırın.
2. Uygulama klasörüne gidip aşağıdaki girdiyi ekleyin (Laravel scheduler):
   `ash
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
   `
3. Ayarlar ekranında Aylık fatura otomasyonunda e-posta bildirimi gönder seçeneğini aktif hale getirin ve alıcı e-posta listesini doldurun.
4. Manuel test için komutu elle çalıştırın:
   `ash
   php artisan app:generate-monthly-invoices --month=2024-05
   `
5. storage/logs/laravel.log dosyasını kontrol ederek başarı veya hata mesajlarını doğrulayın.

## SMTP Doğrulama Adımları
1. Ayarlar > Mail bölümünde Hazır SMTP Ayarı listesinden servis sağlayıcınızı seçin (Gmail, Outlook, Yandex).
2. Host/port otomatik dolacaktır; kullanıcı adı ve uygulama şifresini ilgili sağlayıcı yönergelerine göre girin.
3. Ön ayar seçildiğinde host/port alanları otomatik doldurulur notunu takip edin ve gönderici e-postasının yetkili olduğundan emin olun.
4. Test için yeni bir faturayı manuel oluşturup tahsilat ekleyin ve php artisan app:generate-monthly-invoices --month=<geçerli ay> çalıştırın.
5. Özet maili eriştiğiniz gelen kutusunda doğrulayın; gelmezse storage/logs/laravel.log dosyasını kontrol edin.
6. Gmail için uygulama şifresi, Outlook için Exchange uygulama parolası, Yandex için SMTP erişimi açık olmalıdır.

## Yedek Alma ve Geri Yükleme
- **Şifreli yedek:** “Yedek indir” formuna parola girerseniz dosya AES-256-GCM ile şifrelenir.
- **Ön izleme (dry-run):** Geri yükleme formunda “Yedeği Analiz Et” butonunu kullanarak kayıt sayılarını ve checksum bilgisini inceleyin.
- **Geri yükleme:** Analizden sonra onay kutusunu işaretleyip “Yedeği Geri Yükle” butonuna basın. Şifreli yedekler için aynı parolayı girmeniz gerekir.
- **Ortam güvenliği:** `.env` dosyasında `BACKUP_RESTORE_ENABLED=true` olmadan restore işlemi yapılamaz. Varsayılan olarak kapalıdır; yalnızca yetkili ortamlarda açın.
- **Dosya boyutu limiti:** `BACKUP_MAX_UPLOAD_MB` değeri (varsayılan 20 MB) üzerindeki yedekler reddedilir. Gerekirse yedekleri parçalara ayırın.
- **Audit:** Restore denemeleri `storage/logs/laravel.log` içinde `backup.restore.*` olarak loglanır; başarısız şifre denemeleri throttle tutar.

## Log Redaksiyonu ve Hassas Veri Koruması
- config/logging.php içinde özel kanal tanımlayıp 	ap ile maskeleme (SMTP şifreleri, kişisel veri) middleware’i ekleyin.
- Sentry/ELK gibi dış log servislerinde projeye özel scrubber kurallarını aktive edin.
- Log paylaşmadan önce php artisan sanitize:logs (planlanan script) veya manuel regex ile hassas alanları maskalayın.
- Operasyon ekibi, paylaşılan loglarda gizli veri tespit ederse logu kaldırıp güvenlik ekibine raporlamalıdır.
