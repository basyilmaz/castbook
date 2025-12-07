---
description: Versiyon artırma ve release işlemleri
---

# Versiyonlama Kuralları

Bu proje **Semantic Versioning (SemVer)** kullanır: `MAJOR.MINOR.PATCH`

## Versiyon Formatı

```
v2.1.3
 │ │ └─ PATCH: Bug fix, küçük düzeltmeler
 │ └─── MINOR: Yeni özellik (geriye uyumlu)
 └───── MAJOR: Breaking change (büyük değişiklik)
```

## Ne Zaman Hangi Versiyon Artırılır?

| Değişiklik Tipi | Versiyon | Örnekler |
|-----------------|----------|----------|
| Bug fix, typo, stil düzeltmesi | PATCH | `2.1.3` → `2.1.4` |
| Yeni özellik, sayfa, endpoint | MINOR | `2.1.4` → `2.2.0` |
| Veritabanı migration, API değişikliği | MAJOR | `2.2.0` → `3.0.0` |

## Versiyon Dosyası

Versiyon `config/app.php` içinde tutulur:
```php
'version' => '2.1.0',
```

## Versiyon Artırma Adımları

### 1. PATCH Artırma (Bug Fix)
// turbo
```bash
php artisan app:bump-version patch
```

### 2. MINOR Artırma (Yeni Özellik)
// turbo
```bash
php artisan app:bump-version minor
```

### 3. MAJOR Artırma (Breaking Change)
```bash
php artisan app:bump-version major
```

### 4. Commit ve Push
// turbo
```bash
git add -A && git commit -m "chore: bump version to vX.Y.Z" && git push
```

### 5. Git Tag Oluşturma (Opsiyonel - Release için)
```bash
git tag -a vX.Y.Z -m "Release vX.Y.Z"
git push origin vX.Y.Z
```

## Footer'da Versiyon Gösterimi

Footer otomatik olarak `config('app.version')` değerini gösterir:
```
Muhasebe v2.1.0 © 2025
```

## Commit Mesaj Formatı

Versiyonlama ile ilgili commit mesajları:
- `chore: bump version to v2.1.0`
- `fix: [açıklama]` → PATCH artır
- `feat: [açıklama]` → MINOR artır
- `BREAKING CHANGE: [açıklama]` → MAJOR artır
