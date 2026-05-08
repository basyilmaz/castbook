---
status: current
updated: 2026-05-07
---

# GitHub Actions CI/CD

## Workflow: `.github/workflows/ci.yml`

### Job 1: `build-test`

Tetikleyici: `main` branch'e push veya PR.

| Adım | Detay |
|------|-------|
| MySQL 8.0 servisi | `muhasebe_ci` DB, root/root credentials |
| PHP 8.2 | shivammathur/setup-php@v2 |
| Composer install | `--no-interaction --prefer-dist --optimize-autoloader` |
| npm ci + build | Frontend assets üretimi |
| Migrations | `php artisan migrate --force` |
| Tests | `php artisan test` |

### Job 2: `deploy-production`

Tetikleyici: `v*` pattern ile etiket push (ör. `git tag v1.2.3`).
Bağımlılık: `build-test` job başarılı olmalı.

```yaml
- uses: appleboy/ssh-action@v1.0.3
  with:
    host: ${{ secrets.DEPLOY_HOST }}
    username: ${{ secrets.DEPLOY_USER }}
    key: ${{ secrets.DEPLOY_KEY }}
    port: 65002
    script: |
      bash ~/apps/castbook/scripts/deploy/update_release.sh ${{ github.ref_name }}
```

## Gerekli Repository Secrets

| Secret | Açıklama | Değer |
|--------|----------|-------|
| `DEPLOY_HOST` | SSH sunucu IP | `76.13.34.119` |
| `DEPLOY_USER` | SSH kullanıcı | `u473759453` |
| `DEPLOY_KEY` | ED25519 özel anahtar | `metas_hostinger_ed25519` içeriği |

## Test Ortamı `.env`

CI'da `.env.example` kopyalanır; aşağıdaki değerler önemlidir:
```env
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=muhasebe_ci
DB_USERNAME=root
DB_PASSWORD=root
BACKUP_RESTORE_ENABLED=false
```

## Önemli Not: Frontend Assets

`public/build/` git'e commit'lenmektedir. Sunucuda `npm` yoktur, bu nedenle deploy adımında frontend build çalıştırılmaz — CI'da üretilen assets tag ile birlikte git'e push edilir.

## Deploy Tetikleme

```bash
git tag v1.0.5
git push origin v1.0.5
```

## Related

- [systems/deployment.md](../systems/deployment.md)
- [integrations/hostinger.md](hostinger.md)
