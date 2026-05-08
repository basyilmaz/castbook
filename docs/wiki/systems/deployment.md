---
status: current
updated: 2026-05-07
---

# Deployment Sistemi

## Altyapı

| Alan | Değer |
|------|-------|
| Hosting | Hostinger Cloud Professional (shared hosting) |
| SSH IP | `76.13.34.119` |
| SSH Port | `65002` |
| SSH Kullanıcı | `u473759453` |
| SSH Key | `~/.ssh/metas_hostinger_ed25519` (yerel), hPanel'de "codex" |
| Uygulama dizini | `~/apps/castbook` |
| PHP versiyonu | `/opt/alt/php82/usr/bin/php` (PHP 8.2) |

> ⚠️ `147.93.37.8` eski/yanlış IP. Doğru SSH IP: `76.13.34.119`

## Deploy Akışı

```
git tag v<X.Y.Z>
git push --tags
```

→ GitHub Actions `deploy-production` job tetiklenir → SSH ile sunucuya bağlanır → `update_release.sh <tag>` çalışır.

## Deploy Script: `scripts/deploy/update_release.sh`

```bash
#!/usr/bin/env bash
set -euo pipefail
GIT_REF="${1:-main}"
APP_DIR="${HOME}/apps/castbook"
cd "${APP_DIR}"
git fetch --all --tags --prune
git checkout --force "${GIT_REF}"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan event:cache || true
php artisan queue:restart || true
```

## Ön Koşullar (GitHub Secrets)

| Secret | Değer |
|--------|-------|
| `DEPLOY_HOST` | `76.13.34.119` |
| `DEPLOY_USER` | `u473759453` |
| `DEPLOY_KEY` | `metas_hostinger_ed25519` private key içeriği |

## Frontend Assets

`public/build/` klasörü `.gitignore`'da **değil** — commit'lere dahil. Sunucuda `npm` mevcut olmadığından build sunucuda çalıştırılmaz; CI'da üretilen artefaktlar commit'e dahildir.

## Kısıtlamalar

- Sunucuda `crontab` komutu çalışmaz — cron yönetimi hPanel üzerinden yapılır.
- Composer `--no-dev` ile çalışır (production mode).
- Queue worker restart için `php artisan queue:restart` çalıştırılır ama süreç yönetimi hPanel tarafında.

## Related

- [integrations/github-actions.md](../integrations/github-actions.md)
- [integrations/hostinger.md](../integrations/hostinger.md)
- [processes/ssl-renewal.md](../processes/ssl-renewal.md)
