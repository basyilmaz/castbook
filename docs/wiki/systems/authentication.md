---
status: current
updated: 2026-05-07
---

# Kimlik Doğrulama Sistemi

## Yöntem

CastBook, cookie tabanlı değil **URL parametreli token auth** kullanır.

- Model: `App\Models\AuthToken`
- Parametre adı: `_auth` (URL query string)
- Tokens tablosu sütunları: `token`, `expires_at`, `user_id`

> ❓ `is_valid` sütunu tabloda bulunmayabilir — `findValidToken()` sadece `token` ve `expires_at` sorgular.

## Neden Cookie Değil?

Tasarım Railway platformu için yapılmış. Cross-domain deployment senaryolarında cookie paylaşımı zorken URL token basit kalır. Hostinger'da da aynı mekanizma kullanılıyor.

## Login Akışı

1. `POST /login` → AuthController → kullanıcı doğrulama
2. AuthToken oluştur (DB'ye kaydet, expires_at = now + TTL)
3. Kullanıcıyı `/?_auth=<token>` ya da dashboard'a yönlendir
4. Sonraki tüm istekler `?_auth=<token>` ile token gönderir

## Admin Oluşturma

Prod sunucuda admin kullanıcı yoksa:
```bash
php artisan user:ensure-admin \
  --email=basyilmaz@gmail.com \
  --password=<şifre> \
  --name='Bas Yilmaz'
```

## Login URL

Production login: `https://castbook.castintech.com/` (root URL, `/login` değil)

## Related

- [overview.md](../overview.md)
