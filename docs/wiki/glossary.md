---
status: current
updated: 2026-05-07
---

# Glossary

Proje içinde geçen terimler ve kısaltmalar.

| Terim | Açıklama |
|-------|----------|
| **AuthToken** | URL `_auth` parametresiyle cookie-less kimlik doğrulama modeli (`App\Models\AuthToken`) |
| **acme.sh** | Sunucudaki Let's Encrypt SSL sertifika istemcisi |
| **hPanel** | Hostinger'ın web kontrol paneli (https://hpanel.hostinger.com) |
| **Webroot challenge** | Let's Encrypt domain doğrulama yöntemi — `public/` dizinine dosya yazar |
| **Full (Strict)** | Cloudflare SSL modu — origin sunucusunda geçerli sertifika zorunlu |
| **Edge cert** | Cloudflare'in kullanıcılara sunduğu sertifika (Google Trust Services) |
| **Origin cert** | Cloudflare→Hostinger arasında kullanılan sertifika (Let's Encrypt) |
| **update_release.sh** | Tag bazlı production deploy scripti |
| **DEPLOY_HOST** | GitHub Actions secret — SSH IP (`76.13.34.119`) |
| **schedule:run** | Laravel scheduler artisan komutu (hPanel cron her dakika çalıştırır) |
| **muhasebe** | Muhasebe = accounting (TR) |
| **dompdf** | PDF fatura üretimi için kullanılan Laravel paketi |

## Related

- [overview.md](overview.md)
