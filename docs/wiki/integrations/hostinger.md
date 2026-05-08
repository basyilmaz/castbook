---
status: current
updated: 2026-05-07
---

# Hostinger Entegrasyonu

## Hesap Bilgileri

| Alan | Değer |
|------|-------|
| Kullanıcı adı | `u473759453` |
| SSH IP | `76.13.34.119` |
| SSH Port | `65002` |
| hPanel URL | https://hpanel.hostinger.com |
| Domain yönetimi | `castbook.castintech.com` üzerinden |

## SSH Erişimi

```bash
ssh -i ~/.ssh/metas_hostinger_ed25519 -p 65002 u473759453@76.13.34.119
```

hPanel'deki SSH key adı: `codex` (oluşturma tarihi: 2026-04-21)

> ⚠️ `147.93.37.8` yanlış IP'dir. CI/CD secrets'ta `DEPLOY_HOST=76.13.34.119` olmalı.

## Dizin Yapısı

```
~/apps/castbook/          # Uygulama kodu (git)
~/.acme.sh/              # SSL sertifika yönetimi
~/domains/castbook.castintech.com/  # Hostinger domain konfig
```

## PHP Versiyonları

| Binary | Versiyon |
|--------|----------|
| `/opt/alt/php82/usr/bin/php` | PHP 8.2 (production için) |
| `/usr/bin/php` | PHP 8.3.x (default, test amaçlı) |

Üretimde PHP 8.2 kullanılır (cron ve artisan komutları bu binary ile çalışır).

## hPanel Cron Jobs

| Schedule | Komut | Amaç |
|----------|-------|-------|
| `* * * * *` | `/opt/alt/php82/usr/bin/php ~/apps/castbook/artisan schedule:run >> /dev/null 2>&1` | Laravel scheduler |
| `0 2 * * *` | `~/.acme.sh/acme.sh --cron --home ~/.acme.sh >> /dev/null 2>&1` | SSL yenileme |

> Not: `crontab -l` shared hosting'de çalışmaz. Cron'lar hPanel → Advanced → Cron Jobs üzerinden yönetilir.

## SSL Yönetimi

Hostinger web sunucusu `~/.acme.sh/castbook.castintech.com_ecc/` dizininden sertifikayı doğrudan okur. hPanel Custom SSL upload gerekmez — acme.sh yenilediğinde otomatik devreye girer.

## Kısıtlamalar

- `npm`, `node` sunucuda mevcut değil → frontend assets git'e commit'lenmeli
- `crontab` komutu yok → hPanel üzerinden cron yönetimi
- hPanel Cron Jobs API: `/api/wh-api/api/hapi/v1/accounts/u473759453/cron-jobs` (browser session ile kullanılabilir, API token gerektirilebilir)

## Related

- [systems/deployment.md](../systems/deployment.md)
- [processes/ssl-renewal.md](../processes/ssl-renewal.md)
- [integrations/github-actions.md](github-actions.md)
