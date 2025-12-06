# Castbook Production Deploy Planı

Bu doküman `castbook.castamon.com` alan adı üzerinden prod ortamına çıkış için DNS, sürümleme ve güncelleme akışını özetler.

## 1. DNS Hızlı Kurulum

1. Sunucu IP adresini (ör. `49.12.174.89`) not alın.
2. `castamon.com` DNS yönetim panelinden aşağıdaki kayıtları ekleyin:
   - `A` kaydı: `castbook` → `49.12.174.89`
   - `AAAA` kaydı (opsiyonel IPv6): `castbook` → `2a03:...`
   - `TXT` kaydı: `castbook` → SSL/Domain doğrulama için gerekiyorsa ekleyin.
3. DNS TTL değerini test fazında 300s (5 dk) tutun; stabil hale gelince 3600s’e yükseltebilirsiniz.
4. Değişiklikleri doğrulamak için:
   ```bash
   dig +short castbook.castamon.com
   nslookup castbook.castamon.com
   ```
5. Sertifika kurulumu için Let’s Encrypt/ACME istemcisi çalıştırırken FQDN olarak `castbook.castamon.com` seçin. Otomatik yenileme için crontab’a `certbot renew` ekleyin.

## 2. Sürümleme Stratejisi

- **SemVer** (MAJOR.MINOR.PATCH) kullanıyoruz. Örn. `v1.0.0` ilk release, `v1.1.0` yeni özellik, `v1.1.1` bugfix.
- `main` branch her zaman release’e hazır kodu tutar. Yeni özellikler `feature/*` branch’lerinde geliştirilir.
- Release hazırlığında:
  1. `main`’den `release/v1.2.0` branch’i açın.
  2. Test ve son kontroller tamamlanınca `git tag v1.2.0` ile etiketleyin ve tag’i push edin.
  3. GitHub Actions pipeline’ı tag push’unda artefact oluşturup release notu ile eşleştirir.
- `CHANGELOG.md` veya GitHub Releases üzerinden özet değişiklikler girin; CI pipeline’ı tag’i otomatik olarak build/test eder.

## 3. Güncelleme (Update) Akışı

### 3.1. Zero-Downtime Yapısı

Sunucuda `/var/www/castbook` altında aşağıdaki yapı önerilir:

```
/var/www/castbook
 ├── releases/
 │    ├── 2025-10-25_120000/
 │    └── 2025-10-20_083000/
 ├── shared/
 │    ├── .env
 │    ├── storage/
 │    └── public/uploads/
 └── current -> releases/2025-10-25_120000
```

- Deployment sırasında yeni sürüm `releases/<timestamp>` klasörüne çıkarılır.
- `shared` içindeki `.env`, `storage` ve yükleme dizinleri symlink ile bağlanır.
- Her şey başarılı olduğunda `current` symlink’i yeni release’e çevrilir ve nginx `root` / php-fpm `document_root` bu symlink’i işaret eder.

### 3.2. Güncelleme Scripti

`scripts/deploy/update_release.sh` örnek bir akış sunar:

```bash
#!/usr/bin/env bash
set -euo pipefail

RELEASE_DIR="/var/www/castbook/releases/$(date +%Y-%m-%d_%H%M%S)"
GIT_REF="${1:-main}"

git clone --depth=1 --branch "$GIT_REF" git@github.com:yourorg/castbook.git "$RELEASE_DIR"
cd "$RELEASE_DIR"

composer install --no-dev --prefer-dist --optimize-autoloader
cp /var/www/castbook/shared/.env .
php artisan key:generate --force --ansi
php artisan migrate --force
php artisan config:cache
php artisan route:cache

npm ci
npm run build

ln -snf /var/www/castbook/shared/storage storage
ln -snf /var/www/castbook/shared/public/uploads public/uploads

ln -snf "$RELEASE_DIR" /var/www/castbook/current
php artisan queue:restart || true
```

> **Not:** Script’i root olarak değil deploy kullanıcısı ile çalıştırın. Öncesinde gerekli dizin izinlerini (`chown deploy:www-data`) ayarlayın.

### 3.3. Güncelleme Adımları
1. CI pipeline’ı başarılı olduktan sonra sunucuya bağlanın.
2. `./scripts/deploy/update_release.sh v1.2.0` komutunu çalıştırın (tag veya branch belirtebilirsiniz).
3. `nginx -t` ile konfigürasyonu test edin, gerekli ise reload (`systemctl reload nginx`).
4. `php artisan queue:restart` ve `systemctl status queue-worker` ile worker’ların aktif olduğunu doğrulayın.
5. `php artisan up` komutuyla bakım modundan çıkın.

## 4. Versiyon Güncelleme ve Rollback

- Yeni release’de sorun çıkarsa `ln -snf /var/www/castbook/releases/<eski>` ile symlink’i geri çevirin, ardından `php artisan migrate:rollback` çalıştırın (gerekiyorsa).
- Her release öncesi `mysqldump` ve `storage` klasörü snapshot’ı alın; rollback sürecinde kullandığınız backup’lar hazır olsun.

## 5. İzleme ve Sağlık Kontrolleri

- API health endpoint: `GET https://castbook.castamon.com/health` → `{ "status": "ok" }` döner.
- Let’s Encrypt yenilemesi günlük cron ile takip edilmeli (`systemctl status certbot.timer`).
- Loglar için `laravel.log` → logrotate kurgulayın; metric/alerting için Sentry, NewRelic veya Prometheus entegrasyonu önerilir.

## 6. CI/CD Otomasyonu

- GitHub Actions dosyası `.github/workflows/ci.yml` SemVer tag (örn. `v1.2.0`) push edildiğinde otomatik olarak:
  1. Build testi (`composer install`, `npm run build`, `php artisan test`).
  2. SSH üzerinden üretim sunucusunda `bash ./scripts/deploy/update_release.sh <tag>` komutunu çalıştırır.
- Gerekli secrets: `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_KEY` (private key). İsteğe bağlı olarak `DEPLOY_PORT` tanımlanabilir.
- İlk çalıştırmadan önce sunucuda `git@github.com:yourorg/castbook.git` erişimine izin verildiğinden emin olun (deploy kullanıcısının known_hosts dosyasında GitHub fingerprint’i olmalı).
- Otomatik deploy istemiyorsanız workflow’daki `deploy-production` job’unu `if` koşulu veya manual workflow_dispatch ile sınırlandırabilirsiniz.

## 7. RACI (Öneri)

| Alan | Sorumlu | Onay | Danışman | Bilgilendirilecek |
| --- | --- | --- | --- | --- |
| DNS Yönetimi | DevOps | CTO | Ürün | Satın alma |
| Deploy Script Güncellemesi | Backend Lead | DevOps | QA | PM |
| Sürüm Etiketleme | Backend Lead | PM | QA | Tüm takım |
| Rollback | DevOps | CTO | Backend Lead | Ürün/CS |

Bu rehber ile `castbook.castamon.com` üzerinde hızlı bir kuruluma, düzenli sürümlemeye ve kolay rollback/güncelleme akışına sahip olabilirsiniz.
