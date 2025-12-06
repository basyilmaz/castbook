# Muhasebe Uygulamasý

Laravel tabanlý bu proje, küçük ve orta ölçekli firmalar için fatura/tahsilat yönetimini kolaylaþtýrýr. Arayüz Bootstrap 5 üzerine kurulu olup, tema renkleri ve tipografi design token’larýyla özelleþtirilmiþtir.

## Geliþtirme Akýþý

```bash
cp .env.example .env            # ilk kurulumda
composer install
php artisan key:generate
npm install

# geliþtirme sýrasýnda iki terminal sekmesi açýn
npm run dev                     # Vite hot module reload
php artisan serve               # Laravel uygulama sunucusu
```

- `npm run dev` Vite geliþtirme sunucusunu baþlatýr ve CSS/JS deðiþikliklerini canlý olarak yükler.
- `php artisan serve` HTTP isteklerini karþýlar; alternatif olarak Valet veya baþka bir PHP sunucusu kullanabilirsiniz.
- Testler için: `php artisan test --testsuite=Unit` ve `php artisan test --testsuite=Feature`.

## Üretim Daðýtýmý

CI/CD hattýnda aþaðýdaki komutlar çalýþtýrýlarak üretim build’i hazýrlanýr. Artefakt olarak `public/build` klasörünü sunucuya taþýyýn.

```bash
npm ci
npm run build
php artisan migrate --force
php artisan config:cache route:cache view:cache
```

## Teknoloji Tercihleri

- **CSS Framework:** Bootstrap 5 (Tailwind baðýmlýlýðý kaldýrýldý). Bootstrap Icons varsayýlan ikon seti olarak devam ediyor.
- **Tema/Tasarým Token’larý:** `resources/css/theme.css` dosyasýnda renk, tipografi, spacing ve gölge deðerleri tanýmlý; dark mode `data-theme-mode` üzerinden aktifleþtiriliyor.
- **Grafik/Komponent Geliþtirme:** Figma üzerinde hazýrlanacak component kit ile eþlenik yapýlacak (bkz. marketing raporu).

## Güvenlik & Axios Yapýlandýrmasý

- Layout’da `<meta name="csrf-token">` etiketi yer alýr; `resources/js/bootstrap.js` dosyasý axios’un `X-CSRF-TOKEN` header’ýný otomatik set eder.
- AJAX/SPA istekleri için ekstra ayara gerek yoktur. Token meta etiketi bulunamadýðýnda tarayýcý konsoluna uyarý basýlýr.

## Navbar ve JS Fallback

Navbar’ýn çökmesi durumunda (JS yüklenemezse veya bootstrap bundle çalýþmazsa) `no-js` sýnýfý sayesinde menü öðeleri otomatik olarak görünür olur; hamburger butonu gizlenir ve menü full geniþlikte listelenir. Bu davranýþ `resources/css/theme.css` içerisinde dokümante edilmiþtir.

## Ortak Layout Verileri

- Layout artýk `Setting::getValue` çaðrýsý yapmaz; gerekli tüm veriler `App\Providers\AppServiceProvider` içindeki view composer aracýlýðýyla enjekte edilir (`layoutCompanyName`, `layoutLogoUrl`, `layoutThemeMode`, vs.).
- Yeni bileþenlerde bu deðiþkenleri kullanarak Setting modeline doðrudan eriþim yapýlmamasý tavsiye edilir.

## Katkýda Bulunma

1. Yeni bir branch açýn: `git checkout -b feature/isim`
2. Testleri çalýþtýrýn ve kod stilini koruyun.
3. Pull request’inizde README veya dokümantasyonu güncellemeyi unutmayýn.

## Yedekleme Kontrolleri

- `.env` içinde `BACKUP_RESTORE_ENABLED=true` olmadan yedek geri yükleme iþlemi çalýþmaz (varsayýlan `false`). Prod ortamýnda bilinçli olarak açýn.
- `BACKUP_MAX_UPLOAD_MB` (varsayýlan `20`) yedek dosya boyutu limitini belirler; büyük dosyalarý parçalara ayýrarak yükleyin.
- Geri yükleme formunda JS çalýþmasa bile menü ve formlar `no-js` fallback ile eriþilebilir kalýr.

## CI/CD

- GitHub Actions (`.github/workflows/ci.yml`) ana branch push/pull request’te composer + npm build + `php artisan test` adýmlarýný çalýþtýrýr.
- `v*` etiketi push edildiðinde ayný pipeline testlerden sonra SSH ile prod sunucusunda `bash ./scripts/deploy/update_release.sh <tag>` komutunu çaðýrýr. Bunun için `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_KEY` secrets deðerlerini tanýmlayýn.

Daha fazla detay için `docs/deploy/castbook-deploy.md` dosyasýný inceleyin.
