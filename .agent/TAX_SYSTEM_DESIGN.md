# VERGİ BEYANNAME SİSTEMİ - DETAYLI TASARIM

## 📋 TÜRKİYE VERGİ SİSTEMİ ANALİZİ

### 🏢 FİRMA TÜRLERİ

1. **Şahıs Firması** (Gerçek Kişi)
   - Gelir Vergisi mükellefi
   - Artan oranlı vergi (%15-%40)
   
2. **Limited Şirket** (Ltd. Şti.)
   - Kurumlar Vergisi mükellefi
   - Sabit oran %25 (2024)
   
3. **Anonim Şirket** (A.Ş.)
   - Kurumlar Vergisi mükellefi
   - Sabit oran %25 (2024)

---

## 📊 VERGİ FORMLARI VE DÖNEMLERİ

### 1. AYLIK BEYANNAMELER (Tüm Firma Türleri)

| Kod | Form Adı | Vade Günü | Açıklama |
|-----|----------|-----------|----------|
| **KDV-1** | KDV Beyannamesi | 26 | Katma Değer Vergisi |
| **Muhtasar** | Muhtasar ve Prim Hizmet | 26 | Stopaj ve SGK |
| **BA** | Mal/Hizmet Alım Bildirimi | Son gün | e-Fatura/e-Arşiv |
| **BS** | Mal/Hizmet Satış Bildirimi | Son gün | e-Fatura/e-Arşiv |

### 2. ÜÇER AYLIK BEYANNAMELER (Tüm Firma Türleri)

| Kod | Form Adı | Dönemler | Vade Günü |
|-----|----------|----------|-----------|
| **Geçici Vergi** | Geçici Vergi Beyannamesi | Q1, Q2, Q3, Q4 | Dönem sonrası ayın sonu |

**Dönemler:**
- Q1: Ocak-Şubat-Mart → Nisan sonu
- Q2: Nisan-Mayıs-Haziran → Temmuz sonu
- Q3: Temmuz-Ağustos-Eylül → Ekim sonu
- Q4: Ekim-Kasım-Aralık → Ocak sonu

### 3. YILLIK BEYANNAMELER (Firma Türüne Göre)

#### Şahıs Firması:
| Kod | Form Adı | Dönem | Vade |
|-----|----------|-------|------|
| **Gelir** | Yıllık Gelir Vergisi | 1 Mart - 2 Nisan | 2 Nisan |

#### Limited/Anonim Şirket:
| Kod | Form Adı | Dönem | Vade |
|-----|----------|-------|------|
| **Kurumlar** | Kurumlar Vergisi | 1 Nisan - 30 Nisan | 30 Nisan |

---

## 🎯 SİSTEM TASARIMI

### VERİTABANI YAPISI

#### 1. `firms` Tablosu - Yeni Alan Ekle
```sql
ALTER TABLE firms ADD COLUMN company_type ENUM('individual', 'limited', 'joint_stock') DEFAULT 'individual';
```

**Değerler:**
- `individual` = Şahıs Firması
- `limited` = Limited Şirket
- `joint_stock` = Anonim Şirket

#### 2. `tax_forms` Tablosu - Yeni Alan Ekle
```sql
ALTER TABLE tax_forms ADD COLUMN applicable_to JSON;
```

**Örnek:**
```json
{
  "company_types": ["individual", "limited", "joint_stock"],
  "auto_assign": true
}
```

### OTOMATIK ATAMA SİSTEMİ

#### Senaryo 1: Yeni Firma Oluşturulduğunda
```php
// Firma türüne göre otomatik vergi formları ata
if ($firm->company_type === 'individual') {
    // Şahıs firması formları
    $forms = ['KDV-1', 'Muhtasar', 'BA', 'BS', 'Geçici Vergi', 'Gelir'];
} else {
    // Limited/Anonim formları
    $forms = ['KDV-1', 'Muhtasar', 'BA', 'BS', 'Geçici Vergi', 'Kurumlar'];
}
```

#### Senaryo 2: Firma Türü Değiştiğinde
```php
// Eski formları kaldır, yenileri ekle
$firm->taxForms()->sync($newForms);
```

---

## 📝 UYGULAMA PLANI

### ADIM 1: Database Migration
- [x] Firms tablosuna `company_type` ekle
- [x] TaxForms tablosuna `applicable_to` ekle

### ADIM 2: Model Güncellemeleri
- [x] Firm model'e enum ekle
- [x] TaxForm model'e cast ekle

### ADIM 3: Seed Data
- [x] Standart vergi formlarını oluştur
- [x] Her forma uygulanabilir firma türlerini ata

### ADIM 4: UI Güncellemeleri
- [x] Firma oluşturma/düzenleme formuna "Firma Türü" ekle
- [x] Otomatik form atama bilgilendirmesi

### ADIM 5: Otomatik Atama Logic
- [x] FirmObserver oluştur
- [x] Firma oluşturulduğunda formları ata
- [x] Firma türü değiştiğinde formları güncelle

---

## 🎨 KULLANICI DENEYİMİ

### Firma Oluşturma:
1. Kullanıcı firma bilgilerini girer
2. **"Firma Türü"** seçer (Şahıs/Limited/Anonim)
3. Kaydet butonuna tıklar
4. ✅ Sistem otomatik olarak uygun vergi formlarını atar
5. 💡 Bilgilendirme: "6 adet vergi formu otomatik olarak atandı"

### Firma Detay Sayfası:
```
┌─────────────────────────────────────┐
│ Atanmış Vergi Formları              │
├─────────────────────────────────────┤
│ ✓ KDV-1 (Aylık - 26. gün)          │
│ ✓ Muhtasar (Aylık - 26. gün)       │
│ ✓ BA-BS (Aylık - Son gün)          │
│ ✓ Geçici Vergi (3 Aylık)           │
│ ✓ Gelir Vergisi (Yıllık)           │
│                                     │
│ [+ Manuel Form Ekle]                │
└─────────────────────────────────────┘
```

---

## ⚠️ ÖNEMLİ NOTLAR

1. **Otomatik Atama:** Firma türüne göre standart formlar otomatik atanır
2. **Manuel Ekleme:** Kullanıcı ek formlar ekleyebilir
3. **Tür Değişikliği:** Firma türü değişirse formlar güncellenir (uyarı ile)
4. **Beyanname Oluşturma:** Sadece atanmış formlar için beyanname oluşturulur

---

## 🚀 AVANTAJLAR

✅ Kullanıcı tek tek form atamak zorunda kalmaz
✅ Yanlış form ataması riski azalır
✅ Firma türüne göre doğru vergiler otomatik takip edilir
✅ Sistem Türkiye vergi sistemine uygun
✅ Esnek: Manuel ekleme/çıkarma hala mümkün
