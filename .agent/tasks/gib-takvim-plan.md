# 📋 GİB Vergi Takvimi Entegrasyonu - Uygulama Planı

**Tarih:** 6 Aralık 2025  
**Öncelik:** P1 - Yüksek  
**Tahmini Süre:** 1-2 Gün

---

## 🎯 Hedef

GİB (Gelir İdaresi Başkanlığı) Vergi Takvimi'nden resmi beyanname son tarihlerini otomatik olarak çekmek ve CastBook sistemiyle entegre etmek.

---

## 🔍 Mevcut Durum Analizi

### Site Yapısı
- **URL:** https://gib.gov.tr/vergi-takvimi
- **Teknoloji:** Next.js (React SSR)
- **Veri Yükleme:** Client-side JavaScript ile dinamik yükleme
- **Filtreleme:** Günlük, Haftalık, Aylık, Yıllık seçenekleri

### Tespit Edilen Vergi Türleri (Aralık 2025 Örneği)
| Tarih | Beyanname / Yükümlülük |
|-------|------------------------|
| 01.12 | Kasım KDV I Dönemi |
| 09.12 | Kasım 1-15 Dönem Noterlerce Yapılan Makbuz Karşılığı Ödemeler |
| 10.12 | KKDF Beyannamesi |
| 15.12 | Kasım Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba) |
| 15.12 | Kasım Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs) |
| 17.12 | Damga Vergisi Beyannamesi |
| 17.12 | Muhtasar ve Prim Hizmet Beyannamesi |
| 24.12 | KDV Beyannamesi |
| 26.12 | Gelir Vergisi (Basit Usul) |
| 26.12 | Kurumlar Vergisi (Geçici Vergi) |

---

## 📐 Önerilen Çözüm Yaklaşımları

### Seçenek 1: Web Scraping (Önerilmez ❌)
- **Artılar:** Hızlı başlangıç
- **Eksiler:** 
  - Site yapısı değişince bozulabilir
  - Rate limiting riski
  - Yasal belirsizlik
  - Next.js client-side rendering scrape etmek zor

### Seçenek 2: Manuel Veri Girişi + Şablon (Mevcut ✅)
- **Artılar:** 
  - Güvenilir, kontrollü
  - Hızlı uygulanabilir
- **Eksiler:**
  - Yıllık güncelleme gerektirir

### Seçenek 3: GİB API (Eğer varsa 🔍)
- GİB'in resmi API'si olup olmadığını araştırmak gerekiyor
- e-Beyan sistemi API'lerini incelemek

### Seçenek 4: Statik Vergi Takvimi Seed Data ✅ **ÖNERİLEN**
- 2025 ve 2026 için tüm beyanname tarihlerini manuel olarak hazırlayıp seed data olarak eklemek
- Yılda 1 kez güncelleme yeterli
- En güvenilir ve basit çözüm

---

## 🚀 Uygulama Planı (Seçenek 4)

### Faz 1: Veri Yapısı (1 saat)
1. `tax_calendar` tablosu oluşturma
   ```php
   Schema::create('tax_calendars', function (Blueprint $table) {
       $table->id();
       $table->integer('year');
       $table->integer('month');
       $table->integer('day');
       $table->string('code');           // KDV, MUHTASAR, GECICI_VERGI vb.
       $table->string('name');           // Tam adı
       $table->text('description')->nullable();
       $table->string('applicable_to')->nullable(); // Tüm mükellefler, Şirketler vb.
       $table->boolean('is_active')->default(true);
       $table->timestamps();
   });
   ```

### Faz 2: Seeder Data (2 saat)
1. `TaxCalendarSeeder.php` oluşturma
2. 2025 ve 2026 için tüm beyanname tarihlerini ekleme
3. Kaynak: https://gib.gov.tr/vergi-takvimi

### Faz 3: Dashboard Widget Geliştirme (1 saat)
1. "Resmi Vergi Takvimi" kartı ekleme
2. Bugün ve önümüzdeki 7 gün içindeki resmi tarihler
3. GİB kaynak linki

### Faz 4: Beyanname Oluşturma Entegrasyonu (2 saat)
1. Firma eklendiğinde otomatik beyanname önerisi
2. Resmi tarihlere göre son gün hesaplama
3. Tatil/resmi gün kontrolü (hafta sonu kaydırma)

---

## 📊 Örnek Veri Yapısı

```json
{
  "2025-12-24": {
    "code": "KDV",
    "name": "Katma Değer Vergisi Beyannamesi",
    "description": "Kasım 2025 dönemi KDV beyannamesi son günü",
    "period": "Kasım 2025",
    "applicable_to": ["limited", "anonim", "sahis"]
  }
}
```

---

## ✅ Hızlı Başlangıç Önerisi

GİB sitesinden veri çekmek yerine, daha pragmatik bir yaklaşım:

1. **Aralık 2025 verilerini manuel olarak ekleyelim** (15 dakika)
2. **Dashboard'da "GİB Vergi Takvimi" kartı gösterelim** (30 dakika)
3. **Beyanname oluştururken otomatik son tarih önerisi** (30 dakika)

Bu şekilde hemen değer üretebiliriz, sonra tam entegrasyon yapılabilir.

---

## 🔗 Alternatif Kaynaklar

1. **GİB Mevzuat:** https://www.gib.gov.tr/gibmevzuat
2. **e-Beyan Sistemi:** https://ebeyanname.gib.gov.tr/
3. **TÜRMOB Takvimi:** Serbest Muhasebeci Mali Müşavirler Odası

---

## 📝 Kullanıcı Onayı Bekliyor

**Soru:** Hangi yaklaşımı tercih ediyorsunuz?

1. ⚡ **Hızlı başlangıç** - Sadece Aralık 2025 verilerini ekleyelim, Dashboard'da gösterelim
2. 📅 **Tam takvim** - 2025-2026 için tüm verileri hazırlayalım
3. 🔍 **API araştırması** - GİB'in resmi API'si olup olmadığını araştıralım

---

**Hazırlayan:** AI Product Manager  
**Son Güncelleme:** 6 Aralık 2025 03:35
