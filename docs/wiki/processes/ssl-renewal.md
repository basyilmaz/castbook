---
status: current
updated: 2026-05-07
---

# SSL Yenileme Süreci

## Mevcut Durum (2026-05-07 itibarıyla)

| Alan | Değer |
|------|-------|
| Sertifika | Let's Encrypt (EC-256), acme.sh ile yönetilen |
| CA | Let's Encrypt E8 |
| Son yenilenme | 2026-05-07 00:06 UTC |
| Geçerlilik sonu | 2026-08-05 |
| Sonraki yenileme | 2026-07-06 |

## Nasıl Çalışır?

Hostinger'ın web sunucusu (LiteSpeed/Apache) SSL sertifikasını **doğrudan acme.sh dizininden** okur:

```
~/.acme.sh/castbook.castintech.com_ecc/castbook.castintech.com.cer
~/.acme.sh/castbook.castintech.com_ecc/castbook.castintech.com.key
```

Bu dosyalar `acme.sh --cron` ile yenilendiğinde web sunucusu otomatik olarak yeni sertifikayı kullanmaya başlar. **hPanel'e manuel yükleme gerekmez.**

## Otomatik Yenileme (hPanel Cron)

```cron
0 2 * * *  /home/u473759453/.acme.sh/acme.sh --cron --home /home/u473759453/.acme.sh >> /dev/null 2>&1
```

Günlük saat 02:00'de çalışır. 60 günden önce gelince acme.sh sertifikayı yeniler (Let's Encrypt'in 90 günlük sertifikaları için 30 gün kala yenileme yapılır).

## Doğrulama Yöntemi

```bash
# Webroot challenge — acme.sh sertifikayı public dizininden doğrular
Le_Webroot='/home/u473759453/apps/castbook/public'
```

## Cloudflare Katmanı

Kullanıcılar Cloudflare edge'ini görür (Google Trust Services sertifikası). Cloudflare → Hostinger arasındaki bağlantı **Full (Strict)** moddadır — bu nedenle origin sunucusunda geçerli bir sertifika zorunludur.

## Sertifika Kontrol Komutu

```bash
# Cloudflare edge cert (kullanıcının gördüğü)
echo | openssl s_client -servername castbook.castintech.com \
  -connect castbook.castintech.com:443 2>/dev/null | openssl x509 -noout -dates

# Origin cert (Hostinger'daki gerçek sertifika)
echo | openssl s_client -servername castbook.castintech.com \
  -connect 76.13.34.119:443 2>/dev/null | openssl x509 -noout -subject -issuer -dates
```

## Geçmiş Denemeler

- **Cloudflare Origin Certificate (15 yıl)**: Denendi fakat Cloudflare dashboard SPA bu oturumda yüklenemedi (webpack chunk'ları inmesine rağmen React mount edilmedi). Gerekli olmadığı anlaşıldı — mevcut acme.sh çözümü yeterli.

## Related

- [integrations/hostinger.md](../integrations/hostinger.md)
- [systems/deployment.md](../systems/deployment.md)
