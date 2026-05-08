---
status: current
updated: 2026-05-07
---

# CastBook — Proje Genel Bakış

## Ne?

CastBook, Türkiye'deki küçük ve orta ölçekli firmalar için Laravel 12 tabanlı bir muhasebe/fatura yönetim uygulamasıdır. Çoklu firma desteği sunar; her kullanıcı birden fazla müşteri firmasını yönetebilir.

## Temel Özellikler

| Alan | Açıklama |
|------|----------|
| Fatura yönetimi | Fatura oluşturma, düzenleme, PDF export (dompdf) |
| Tahsilat takibi | Ödeme kayıtları, ekstre |
| Firma yönetimi | Müşteri firma kartları, fiyat geçmişi |
| Vergi formu | Türk vergi beyannameleri, takvim |
| Raporlar | Firma bazlı raporlar |
| Bildirimler | In-app bildirim sistemi |
| Yedekleme | Opsiyonel DB yedek/geri yükleme (`BACKUP_RESTORE_ENABLED`) |

## Teknoloji Yığını

- **Backend:** PHP 8.2, Laravel 12.35.1
- **Veritabanı:** MySQL 8.0
- **Frontend:** Bootstrap 5, Vite, Bootstrap Icons
- **PDF:** barryvdh/laravel-dompdf
- **Testler:** PHPUnit 11, Laravel Feature + Unit testleri
- **CI/CD:** GitHub Actions → SSH deploy (appleboy/ssh-action)

## URL'ler

| Ortam | URL |
|-------|-----|
| Production | https://castbook.castintech.com |
| GitHub | https://github.com/basyilmaz/castbook |

## Related

- [systems/deployment.md](systems/deployment.md)
- [systems/authentication.md](systems/authentication.md)
- [processes/ssl-renewal.md](processes/ssl-renewal.md)
- [integrations/hostinger.md](integrations/hostinger.md)
- [integrations/github-actions.md](integrations/github-actions.md)
