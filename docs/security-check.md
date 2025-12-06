# Güvenlik Kontrolleri Özet

## Kimlik Doðrulama ve Oturum
- Yeni rol modeli altyapýsý henüz uygulanmadý ancak kullanýcý yönetimi ekraný yalnýzca yönetici rolünde açýlýyor.
- Oturum açma sonrasý pasif kullanýcý kontrolü yapýlýyor, pasif hesaplar anýnda çýkýþa zorlanýyor.
- CSRF korumasý tüm formlarda etkin; testlerde doðrulamak için token kullanýmý saðlandý.

## Form Validasyonlarý
- Tahsilat formlarýnda ödeme yöntemi ayarlarla sýnýrlandýrýldý; geçersiz giriþler reddediliyor.
- Resmi fatura numarasý benzersiz ve 50 karakter sýnýrýnda tutuluyor.
- Ayarlar ekranýnda e-posta listeleri ve ödeme yöntemleri trim + filtre sonrasý kaydediliyor.

## Dosya ve Depolama
- Logo yüklemelerinde MIME ve boyut sýnýrý (2 MB) uygulanýyor.
- Yedek geri yükleme öncesi kullanýcý onayý ve JSON þema kontrolü mevcut.
- Otomatik yedek silme iþlemi transaction içinde çalýþýyor, hatada rollback.

## Komut / Cron Güvenliði
- Aylýk fatura komutu sadece aktif firmalar üzerinde çalýþýyor.
- Bildirim e-postasý gönderimi açýkça ayarlardan açýlýp kapatýlabiliyor.
- Alýcý listesi boþsa komut uyarý verip mail göndermiyor.

## Öncelikli Güvenlik Yol Haritasý
1. **Rate limiting (P0)** — Laravel rate limiter / throttling middleware ile login ve kritik POST uçlarý  brute force'a karþý sýnýrlandýrýlacak. Kýsa vadede uygulanmalý.
2. **Audit log ve aksiyon izleme (P1)** — Kullanýcý oturumlarý, kritik veri deðiþiklikleri (fatura/tahsilat silme) için denetim tablosu. Rol geniþlemesiyle paralel planlanmalý.
3. **Ýki Faktörlü Doðrulama (P2)** — Yönetici hesaplarý için TOTP tabanlý 2FA desteði. Rol/izin altyapýsý oturduktan sonra devreye alýnabilir.

Önerilen adýmlar backlog'a iþlendi ve ilgili ekiplerle paylaþýlmalýdýr.

\n\n## Yeniden Deðerlendirme Notu\n- Rol/izin ve audit log çalýþmalarýnýn tamamlanmasýnýn ardýndan P2 seviyesindeki 2FA ve dosya antivirus taramasý tekrar masaya yatýrýlacak.\n- Bu noktada kullanýcý yönetimi ekraný güncellenerek MFA zorunluluðu kural bazlý tanýmlanacak, dosya yüklemeleri için üçüncü parti AV servisleri (ClamAV, VirusTotal API) adaylarý deðerlendirilecek.
