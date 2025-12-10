# Beyanname ve Fatura KDV Sistemi - Detaylı Görev Listesi

## 📋 Genel Bilgi
- **Oluşturulma:** 2025-12-10
- **Öncelik:** Yüksek
- **Risk Seviyesi:** Orta (mevcut verilerde değişiklik var)

---

## ✅ Görev Listesi

### Görev 1: Firma Tablosu Migration
**Amaç:** Firma bazlı otomasyon ayarları için yeni alanlar ekle

**Değişiklikler:**
- [ ] `auto_invoice_enabled` (boolean, default: true)
- [ ] `tax_tracking_enabled` (boolean, default: true)  
- [ ] `default_vat_rate` (decimal 5,2, default: 20.00)
- [ ] `default_vat_included` (boolean, default: true)

**Dosyalar:**
- `database/migrations/XXXX_add_invoice_tax_settings_to_firms.php`
- `app/Models/Firm.php` ($fillable, $casts)

**Doğrulama:**
- [ ] Migration çalışıyor: `php artisan migrate`
- [ ] Mevcut firmalar default değerlerle güncellendi
- [ ] Firma listesi hala çalışıyor
- [ ] Firma detay sayfası hala çalışıyor

---

### Görev 2: Fatura Tablosu Migration
**Amaç:** KDV hesaplama alanlarını fatura tablosuna ekle

**Değişiklikler:**
- [ ] `vat_rate` (decimal 5,2, nullable, default: 20.00)
- [ ] `vat_included` (boolean, default: true)
- [ ] `subtotal` (decimal 15,2, nullable)
- [ ] `vat_amount` (decimal 15,2, nullable)

**Dosyalar:**
- `database/migrations/XXXX_add_vat_fields_to_invoices.php`
- `app/Models/Invoice.php` ($fillable, $casts, helper methods)

**Doğrulama:**
- [ ] Migration çalışıyor
- [ ] Mevcut faturalar bozulmadı
- [ ] Fatura listesi hala çalışıyor
- [ ] Fatura detay sayfası hala çalışıyor

---

### Görev 3: Firm Model Güncelleme
**Amaç:** Yeni alanlar için model desteği

**Değişiklikler:**
- [ ] $fillable güncelle
- [ ] $casts güncelle
- [ ] `isAutoInvoiceEnabled()` helper metodu
- [ ] `isTaxTrackingEnabled()` helper metodu

**Dosyalar:**
- `app/Models/Firm.php`

**Doğrulama:**
- [ ] `Firm::create()` yeni alanlarla çalışıyor
- [ ] `$firm->isAutoInvoiceEnabled()` doğru değer döndürüyor

---

### Görev 4: Invoice Model Güncelleme
**Amaç:** KDV hesaplama mantığı

**Değişiklikler:**
- [ ] $fillable güncelle
- [ ] $casts güncelle
- [ ] `calculateVat()` metodu
- [ ] `getFormattedVatRate()` accessor
- [ ] Model events (creating/updating): subtotal/vat_amount otomatik hesaplama

**Dosyalar:**
- `app/Models/Invoice.php`

**Doğrulama:**
- [ ] KDV dahil fatura oluşturma doğru hesaplama
- [ ] KDV hariç fatura oluşturma doğru hesaplama
- [ ] Mevcut faturalar hala okunabilir

---

### Görev 5: FirmController Güncelleme
**Amaç:** Yeni alanları store/update metodlarına ekle

**Değişiklikler:**
- [ ] `validatedData()` metoduna yeni alanlar
- [ ] Validation kuralları ekle

**Dosyalar:**
- `app/Http/Controllers/FirmController.php`

**Doğrulama:**
- [ ] Firma oluşturma yeni alanlarla çalışıyor
- [ ] Firma düzenleme yeni alanlarla çalışıyor

---

### Görev 6: Firma Detay UI
**Amaç:** Otomasyon ayarları bölümü ekle

**Değişiklikler:**
- [ ] "Otomasyon Ayarları" card
- [ ] Checkbox: Aylık Otomatik Fatura
- [ ] Checkbox: Beyanname Takibi
- [ ] Dropdown: Varsayılan KDV Oranı (%1, %10, %20, Özel)
- [ ] Radio: KDV Dahil/Hariç
- [ ] AJAX kayıt (inline edit veya form)

**Dosyalar:**
- `resources/views/firms/show.blade.php`
- `resources/views/firms/edit.blade.php`

**Doğrulama:**
- [ ] Checkbox'lar görünüyor ve çalışıyor
- [ ] KDV dropdown işlevsel
- [ ] Kaydet butonu çalışıyor
- [ ] Sayfa yenilenince değerler korunuyor

---

### Görev 7: Fatura Oluşturma/Düzenleme UI
**Amaç:** KDV alanlarını fatura formlarına ekle

**Değişiklikler:**
- [ ] KDV Oranı dropdown (%1, %10, %20, Özel input)
- [ ] KDV Dahil/Hariç radio
- [ ] JavaScript: Anlık hesaplama gösterimi
- [ ] Firma seçilince varsayılan KDV değerlerini doldur

**Dosyalar:**
- `resources/views/invoices/create.blade.php`
- `resources/views/invoices/edit.blade.php`
- `resources/views/invoices/_form.blade.php` (varsa)

**Doğrulama:**
- [ ] Form görünüyor
- [ ] Hesaplama doğru
- [ ] Kayıt başarılı
- [ ] Düzenleme mevcut değerleri gösteriyor

---

### Görev 8: GenerateMonthlyInvoices Komutu
**Amaç:** auto_invoice_enabled filtresi ekle

**Değişiklikler:**
- [ ] Query'ye `where('auto_invoice_enabled', true)` ekle
- [ ] Fatura oluştururken firma KDV ayarlarını kullan
- [ ] Log mesajlarını güncelle

**Dosyalar:**
- `app/Console/Commands/GenerateMonthlyInvoices.php`
- `app/Services/InvoiceGenerationService.php`

**Doğrulama:**
- [ ] `auto_invoice_enabled=false` firmalar atlanıyor
- [ ] Oluşturulan faturalarda KDV alanları dolu
- [ ] Mevcut çalışan mantık bozulmadı

---

### Görev 9: GenerateTaxDeclarations Komutu
**Amaç:** tax_tracking_enabled filtresi ekle

**Değişiklikler:**
- [ ] Query'ye firma filtresi ekle:
  - `firm.status = 'active'`
  - `firm.tax_tracking_enabled = true`
- [ ] Yıllık beyannamelerde firma türüne göre form seçimi

**Dosyalar:**
- `app/Console/Commands/GenerateTaxDeclarations.php`

**Doğrulama:**
- [ ] `tax_tracking_enabled=false` firmalar atlanıyor
- [ ] Pasif firmalar atlanıyor
- [ ] Şahıs firması → Gelir Vergisi
- [ ] Limited/A.Ş → Kurumlar Vergisi

---

### Görev 10: InvoiceService Güncelleme
**Amaç:** Servis katmanına KDV hesaplama ekle

**Değişiklikler:**
- [ ] `calculateVatAmounts()` metodu
- [ ] Fatura oluşturma/güncelleme metodlarına KDV entegrasyonu

**Dosyalar:**
- `app/Services/InvoiceService.php`

**Doğrulama:**
- [ ] API üzerinden fatura oluşturma KDV hesaplıyor
- [ ] Servisi kullanan tüm yerler çalışıyor

---

### Görev 11: Test Dosyaları
**Amaç:** Otomatik testler ile doğrulama

**Yeni Testler:**
- [ ] `FirmAutomationSettingsTest` - Firma ayarları
- [ ] `InvoiceVatCalculationTest` - KDV hesaplama
- [ ] `GenerateMonthlyInvoicesFilterTest` - Komut filtreleme
- [ ] `GenerateTaxDeclarationsFilterTest` - Beyanname filtreleme

**Dosyalar:**
- `tests/Feature/FirmAutomationSettingsTest.php`
- `tests/Unit/InvoiceVatCalculationTest.php`

**Doğrulama:**
- [ ] `php artisan test` tüm testler geçiyor

---

### Görev 12: Logout Sorunu Araştırma
**Amaç:** Bazı linklerden sonra logout'a düşme sorununu bul ve çöz

**Araştırma Adımları:**
- [ ] TokenAuthentication middleware inceleme
- [ ] Session timeout ayarları kontrolü
- [ ] AJAX isteklerinde token yenileme kontrolü
- [ ] CSRF token sorunları kontrolü
- [ ] Hangi sayfalarda/linklerde sorun oluşuyor tespit

**Olası Sebepler:**
1. Token expire süresi çok kısa
2. AJAX isteklerinde token header eksik
3. Session cookie sorunları
4. Redirect sonrası token kaybı

**Dosyalar (potansiyel):**
- `app/Http/Middleware/TokenAuthentication.php`
- `resources/js/app.js` (axios interceptor)
- `config/session.php`

**Doğrulama:**
- [ ] Tüm sayfa navigasyonlarında login kalınıyor
- [ ] 30 dakika beklemeden logout olmuyor

---

## 📊 İlerleme Özeti

| No | Görev | Durum |
|----|-------|-------|
| 1 | Firma Migration | ⬜ Bekliyor |
| 2 | Fatura Migration | ⬜ Bekliyor |
| 3 | Firm Model | ⬜ Bekliyor |
| 4 | Invoice Model | ⬜ Bekliyor |
| 5 | FirmController | ⬜ Bekliyor |
| 6 | Firma UI | ⬜ Bekliyor |
| 7 | Fatura UI | ⬜ Bekliyor |
| 8 | GenerateMonthlyInvoices | ⬜ Bekliyor |
| 9 | GenerateTaxDeclarations | ⬜ Bekliyor |
| 10 | InvoiceService | ⬜ Bekliyor |
| 11 | Testler | ⬜ Bekliyor |
| 12 | Logout Sorunu | ⬜ Bekliyor |

---

## ⚠️ Dikkat Edilecekler

1. **Her migration sonrası test:** Migration'dan sonra mevcut sayfaların çalıştığını kontrol et
2. **Geriye uyumluluk:** Mevcut faturalar null KDV alanlarıyla çalışmalı
3. **Default değerler:** Migration'larda akıllı default değerler kullan
4. **Rollback planı:** Her adımda rollback mümkün olmalı
5. **Commit sıklığı:** Her görev sonrası commit yap
