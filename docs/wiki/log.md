# Wiki Activity Log

Append-only. En yeni girdi en üstte.

---

## 2026-05-07 — Wiki init + deployment reconciliation

**Op:** `init_wiki` + `reconcile_project_state`

**Pages created:**
- `overview.md` — Proje genel bakış, özellikler, stack
- `systems/deployment.md` — Hostinger SSH deploy, update_release.sh
- `systems/authentication.md` — AuthToken cookie-less auth
- `processes/ssl-renewal.md` — acme.sh + hPanel cron, Cloudflare Full Strict
- `integrations/hostinger.md` — SSH IP, PHP, cron jobs, dizin yapısı
- `integrations/github-actions.md` — CI/CD workflow, secrets
- `glossary.md` — Terimler
- `index.md` — Catalog

**Key findings this session:**
- SSH IP düzeltildi: `147.93.37.8` → `76.13.34.119` (GitHub secret `DEPLOY_HOST` güncellendi)
- Hostinger web sunucusu acme.sh sertifikasını doğrudan `~/.acme.sh/castbook.castintech.com_ecc/` dizininden okuyor — hPanel Custom SSL upload gerekmiyor
- SSL tam otomatik: günlük 02:00 hPanel cron + acme.sh → sertifika yenileme → web sunucusu otomatik devreye alıyor
- Cloudflare Origin Certificate gerekmedi — mevcut çözüm yeterli
- CI/CD pipeline aktif: build-test (push/PR) + deploy-production (v* tag)
- Cron jobs confirmed: Laravel scheduler (her dakika, PHP 8.2) + acme.sh (günlük 02:00)
