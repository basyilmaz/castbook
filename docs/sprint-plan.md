# Sprint Planı (Hafta 1)

## P0 İş Paketleri
1. **Yedek Geri Yükleme Sertleştirmesi**
   - Teknik tasarım toplantısı: Pazartesi 10:00 (Backend + DevOps)
   - Çıktı: Dry-run rapor ekranı, checksum stratejisi, rollback planı.
2. **Kısmi Ödeme UX Doğrulaması**
   - Saha ekibi görüşmeleri: Salı 15:00 (Ürün + Destek)
   - Aksiyon: Raporlarda açık bakiye banner testi, kullanıcı geri bildirimi toplama formu.
3. **Rol/İzin Altyapısı Başlatma**
   - Kickoff oturumu: Çarşamba 11:00 (Backend + Ürün)
   - Teslim: Migrasyon taslağı, izin matrisi, rollout stratejisi.

## P1 İş Paketleri (Hazırlık)
- Audit log story refinement (Perşembe 14:00)
- Rapor performans benchmark’ı (DevOps + Backend, çalışma oturumu Cuma 09:00)

## Takip Aksiyonları
- Scheduler health check ve Slack uyarıları için DevOps çalışma notları docs/operations.md altında güncellendi.
- SMTP log redaksiyonu uygulanacak; log sanitization scripti backlog’a eklendi.

## Yeni İş Paketleri - Dry-run & Frontend Sertleştirme
- **P0.1 Dry-run geri yükleme deneyimi:** Backup analiz modunu controller seviyesinde ayrı endpoint/servis olarak ayır; ön izleme çıktısını (meta + tablo kırılımları) JSON olarak expose et, Blade view’da reusable partial ile göster. Acceptance: Şifreli/şifresiz yedekler için analiz sonuçları UI’da hatasız listeleniyor, log’a debug trace düşüyor, başarısız decrypt denemesi throttle ediliyor.
- **P0.2 Şifreleme sertleşmesi:** Yedek şifrelemede AES-256-GCM + PBKDF2(150k iter, 16B salt) yaklaşımını finalize et, tek noktada `App\Services\BackupEncryptionService` altında uygula; unit test ile happy/sad path ve checksum doğrulaması ekle. Acceptance: Service katmanı hem CLI hem HTTP için yeniden kullanılabilir, testler en az bir yanlış parola senaryosunu kapsıyor.
- **P0.3 Layout/Vite entegrasyonu:** resources/views/layouts/app.blade.php içindeki CDN bağımlılıklarını kaldır, `@vite(['resources/css/app.css','resources/js/app.js'])` ekle; Bootstrap/Tailwind çakışmasını çözmek için proje genelinde tek tasarım sistemi kararı al (başlangıçta Bootstrap’i npm’den build etmek). Acceptance: Geliştirme ve prod build’leri assetleri yerelden servis ediyor, offline ağ senaryosu manuel testte çalışıyor.
- **P1.1 Stil & tema modülerleştirme:** Inline stil ve tema scriptlerini `resources/css` ve `resources/js/theme.js` dosyalarına taşı; tema modunu Setting modelinden view composer ile layout’a inject et. Acceptance: Blade dosyasında 50 satırdan uzun inline CSS/JS kalmıyor, tema modu değişimi sayfalar arası tutarlı.
- **P1.2 Blade komponentleri:** “sayfa başlığı”, “filtre formu”, “tablo + pagination” şablonlarını `resources/views/components` altında komponentleştir; Setting verisi view composer/controller katmanından beslenecek. Acceptance: invoices, payments, dashboard Blade’lerinde tekrar eden markup kaldırılıyor, komponentler jest snapshot veya Laravel view testleriyle smoke test ediliyor.

