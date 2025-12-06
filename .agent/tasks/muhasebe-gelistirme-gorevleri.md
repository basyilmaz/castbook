# 📋 CastBook Geliştirme Görev Listesi

**Oluşturma Tarihi:** 06 Aralık 2025  
**Kaynak:** Muhasebe Perspektifli Sistem Analizi  
**Toplam Görev:** 52 adet

---

## 📊 Görev Özeti

| Öncelik | Görev Sayısı | Tahmini Süre |
|---------|--------------|--------------|
| 🔴 P0 - Kritik | 8 | 1-2 Hafta |
| 🟠 P1 - Yüksek | 12 | 2-3 Hafta |
| 🟡 P2 - Orta | 18 | 3-4 Hafta |
| 🟢 P3 - Düşük | 14 | 4+ Hafta |

---

## 🔴 P0 - KRİTİK GÖREVLER (1-2 Hafta)

### FATURA-001: Yaşlandırma Raporu
- [ ] **Açıklama:** Alacak yaşlandırma raporu oluşturma (0-30, 30-60, 60-90, 90+ gün)
- **Modül:** Raporlar
- **Dosyalar:** 
  - `app/Http/Controllers/ReportController.php`
  - `resources/views/reports/aging.blade.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Firma bazlı yaşlandırma görünümü
  - [ ] Günlük aralıklara göre gruplandırma
  - [ ] Toplam tutarlar kolonu
  - [ ] CSV/PDF export
  - [ ] Filtreleme (firma, tarih aralığı)

---

### FATURA-002: KDV Oranları Yönetimi
- [ ] **Açıklama:** Fatura satır kalemlerinde farklı KDV oranları seçimi
- **Modül:** Faturalar
- **Dosyalar:**
  - `app/Models/InvoiceLineItem.php`
  - `database/migrations/xxx_add_vat_rate_to_line_items.php` (yeni)
  - `resources/views/invoices/_line_items.blade.php`
- **Kabul Kriterleri:**
  - [ ] KDV oranları: %0, %1, %10, %20
  - [ ] Satır bazlı KDV seçimi
  - [ ] Otomatik KDV tutarı hesaplama
  - [ ] Fatura toplamında KDV ayrıştırması
  - [ ] Ayarlardan KDV oranları yönetimi

---

### TAHSILAT-001: Tahsilat Düzenleme
- [ ] **Açıklama:** Mevcut tahsilatları düzenleme özelliği
- **Modül:** Tahsilatlar
- **Dosyalar:**
  - `app/Http/Controllers/PaymentController.php` (edit, update metotları)
  - `resources/views/payments/edit.blade.php` (yeni)
  - `routes/web.php`
- **Kabul Kriterleri:**
  - [ ] Tahsilat düzenleme formu
  - [ ] Tarih, tutar, yöntem değiştirilebilir
  - [ ] Fatura durumu otomatik güncelleme
  - [ ] Audit log kaydı

---

### FIRMA-001: Firma Autocomplete Dropdown
- [ ] **Açıklama:** Firma seçim alanlarında arama/autocomplete
- **Modül:** Genel UI
- **Dosyalar:**
  - `resources/views/invoices/_form.blade.php`
  - `resources/views/payments/create.blade.php`
  - `resources/js/components/firm-select.js` (yeni)
- **Kabul Kriterleri:**
  - [ ] Yazarken arama
  - [ ] Son kullanılan firmalar önce
  - [ ] Aylık ücret bilgisi görünür
  - [ ] Keyboard navigation

---

### RAPOR-001: KDV Raporu
- [ ] **Açıklama:** Dönemsel KDV özet raporu
- **Modül:** Raporlar
- **Dosyalar:**
  - `app/Http/Controllers/ReportController.php`
  - `resources/views/reports/vat.blade.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Aylık/çeyreklik KDV toplamları
  - [ ] Hesaplanan KDV vs İndirilecek KDV
  - [ ] Firma bazlı detay
  - [ ] CSV export

---

### BEYANNAME-001: Beyanname Tutar Girişi
- [ ] **Açıklama:** Her beyanname için tutar alanı ekleme
- **Modül:** Beyannameler
- **Dosyalar:**
  - `app/Models/TaxDeclaration.php`
  - `database/migrations/xxx_add_amount_to_tax_declarations.php` (yeni)
  - `resources/views/tax-declarations/edit.blade.php`
- **Kabul Kriterleri:**
  - [ ] Beyanname tutarı input alanı
  - [ ] Ödenen tutar alanı
  - [ ] Kalan tutar hesaplaması
  - [ ] Raporda tutar toplamları

---

### MOBIL-001: Fatura Listesi Mobil Görünüm
- [ ] **Açıklama:** Fatura tablosunun mobil uyumlu kart görünümü
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/views/invoices/index.blade.php`
  - `resources/css/app.css`
- **Kabul Kriterleri:**
  - [ ] Mobilde kart görünümü (md altı ekranlar)
  - [ ] Swipe actions (sağa: ödendi, sola: sil)
  - [ ] Touch-friendly butonlar (min 44px)
  - [ ] Özet bilgiler görünür

---

### GUVENLIK-001: Rate Limiting Genişletme
- [ ] **Açıklama:** Tüm API endpoint'lerine rate limiting
- **Modül:** Güvenlik
- **Dosyalar:**
  - `app/Http/Kernel.php`
  - `routes/web.php`
- **Kabul Kriterleri:**
  - [ ] Form submit işlemlerine limit
  - [ ] Export işlemlerine limit
  - [ ] AJAX çağrılarına limit
  - [ ] 429 hata sayfası

---

## 🟠 P1 - YÜKSEK ÖNCELİKLİ GÖREVLER (2-3 Hafta)

### CEK-001: Çek/Senet Takip Modülü
- [ ] **Açıklama:** Vadeli ödeme araçları yönetimi
- **Modül:** Yeni Modül
- **Dosyalar:**
  - `app/Models/Check.php` (yeni)
  - `app/Http/Controllers/CheckController.php` (yeni)
  - `resources/views/checks/*` (yeni)
  - `database/migrations/xxx_create_checks_table.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Çek/Senet ekleme formu
  - [ ] Vade tarihi takibi
  - [ ] Durum yönetimi (Beklemede, Bankada, Tahsil Edildi, Karşılıksız)
  - [ ] Dashboard widget
  - [ ] Vadesi gelen bildirimi

---

### EFATURA-001: e-Fatura Entegrasyon Altyapısı
- [ ] **Açıklama:** GİB e-Fatura portal entegrasyonu için altyapı
- **Modül:** Entegrasyon
- **Dosyalar:**
  - `app/Services/EInvoiceService.php` (yeni)
  - `config/einvoice.php` (yeni)
  - `app/Models/Invoice.php` (güncelleme)
- **Kabul Kriterleri:**
  - [ ] e-Fatura XML şema desteği
  - [ ] UBL 2.1 format
  - [ ] Mükellef sorgulama API
  - [ ] Ayarlardan aktif/pasif

---

### FATURA-003: Fatura Numaralama Stratejisi
- [ ] **Açıklama:** Otomatik seri-sıra numarası üretimi
- **Modül:** Faturalar
- **Dosyalar:**
  - `app/Services/InvoiceNumberService.php` (yeni)
  - `app/Models/Setting.php`
  - `resources/views/settings/tabs/general.blade.php`
- **Kabul Kriterleri:**
  - [ ] Yıl-Seri-No formatı (2024-A-0001)
  - [ ] Ayarlardan format belirleme
  - [ ] Otomatik artış
  - [ ] Yıl değişiminde sıfırlama opsiyonu

---

### FATURA-004: Fatura Şablonları
- [ ] **Açıklama:** Farklı sektörler için hazır fatura şablonları
- **Modül:** Faturalar
- **Dosyalar:**
  - `app/Models/InvoiceTemplate.php` (yeni)
  - `resources/views/invoices/templates/*` (yeni)
- **Kabul Kriterleri:**
  - [ ] Şablon oluşturma/kaydetme
  - [ ] Şablondan fatura oluşturma
  - [ ] Varsayılan şablon belirleme
  - [ ] Şablon paylaşımı (admin)

---

### RAPOR-002: Müşteri Karlılık Analizi
- [ ] **Açıklama:** Firma bazlı gelir/maliyet karşılaştırması
- **Modül:** Raporlar
- **Dosyalar:**
  - `app/Http/Controllers/ReportController.php`
  - `resources/views/reports/profitability.blade.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Firma bazlı toplam gelir
  - [ ] Beyanname sayısı/maliyeti
  - [ ] Karlılık oranı hesaplama
  - [ ] Sıralama ve filtreleme

---

### RAPOR-003: Aylık/Yıllık Mukayese
- [ ] **Açıklama:** Önceki dönemle karşılaştırmalı rapor
- **Modül:** Raporlar
- **Dosyalar:**
  - `app/Http/Controllers/ReportController.php`
  - `resources/views/reports/comparison.blade.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Bu ay vs geçen ay
  - [ ] Bu yıl vs geçen yıl
  - [ ] Yüzde değişim gösterimi
  - [ ] Trend grafiği

---

### DASHBOARD-001: Nakit Akış Tahmini
- [ ] **Açıklama:** Vadesi gelen faturalar bazlı projeksiyon
- **Modül:** Dashboard
- **Dosyalar:**
  - `app/Http/Controllers/DashboardController.php`
  - `resources/views/dashboard/_cashflow.blade.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Haftalık beklenen tahsilat
  - [ ] Vadesi geçen alacaklar
  - [ ] Grafik görünümü
  - [ ] 30-60-90 gün projeksiyon

---

### MOBIL-002: Dashboard Mobil Optimizasyon
- [ ] **Açıklama:** Dashboard kartlarının mobil görünümü
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/views/dashboard/*`
  - `resources/css/app.css`
- **Kabul Kriterleri:**
  - [ ] Kartlar tam genişlik (mobilde)
  - [ ] Öncelikli kartlar üstte
  - [ ] Daraltılabilir bölümler
  - [ ] Pull-to-refresh

---

### MOBIL-003: Firma Listesi Mobil
- [ ] **Açıklama:** Firma listesinin mobil kart görünümü
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/views/firms/index.blade.php`
- **Kabul Kriterleri:**
  - [ ] Kart görünümü
  - [ ] Hızlı arama
  - [ ] Son görüntülenen firmalar

---

### BILDIRIM-001: Push Notification Desteği
- [ ] **Açıklama:** Tarayıcı push bildirimleri
- **Modül:** Bildirimler
- **Dosyalar:**
  - `resources/js/push-notifications.js` (yeni)
  - `public/sw.js` (yeni)
- **Kabul Kriterleri:**
  - [ ] İzin isteme akışı
  - [ ] Beyanname hatırlatma
  - [ ] Vade hatırlatma
  - [ ] Ayarlardan açma/kapama

---

### IMPORT-001: Excel Import Desteği
- [ ] **Açıklama:** CSV yanında .xlsx format desteği
- **Modül:** Import/Export
- **Dosyalar:**
  - `composer.json` (maatwebsite/excel)
  - `app/Http/Controllers/FirmImportController.php`
  - `app/Http/Controllers/InvoiceImportController.php`
- **Kabul Kriterleri:**
  - [ ] .xlsx dosya yükleme
  - [ ] Sütun eşleştirme
  - [ ] Hata raporlama
  - [ ] Önizleme

---

### EXPORT-001: Excel Export
- [ ] **Açıklama:** Raporlarda .xlsx export
- **Modül:** Import/Export
- **Dosyalar:**
  - `app/Exports/*` (yeni)
  - `app/Http/Controllers/ReportController.php`
- **Kabul Kriterleri:**
  - [ ] Tüm raporlarda Excel butonu
  - [ ] Formatlı hücreler
  - [ ] Formüller (toplamlar)

---

## 🟡 P2 - ORTA ÖNCELİKLİ GÖREVLER (3-4 Hafta)

### SIRKET-001: Çoklu Şirket Desteği
- [ ] **Açıklama:** Tek kullanıcı birden fazla şirketi yönetebilme
- **Modül:** Core
- **Dosyalar:**
  - `app/Models/Company.php` (yeni)
  - `database/migrations/xxx_create_companies_table.php` (yeni)
  - Tüm modellere `company_id` ekleme
- **Kabul Kriterleri:**
  - [ ] Şirket seçim dropdown
  - [ ] Şirket bazlı veri izolasyonu
  - [ ] Şirket değiştirme
  - [ ] Her şirket için ayrı ayarlar

---

### PARA-001: Çoklu Para Birimi
- [ ] **Açıklama:** USD, EUR desteği ve kur takibi
- **Modül:** Finansal
- **Dosyalar:**
  - `app/Models/Currency.php` (yeni)
  - `app/Services/ExchangeRateService.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Faturada para birimi seçimi
  - [ ] Günlük kur çekme (TCMB)
  - [ ] Raporlarda TL karşılığı
  - [ ] Kur farkı hesaplama

---

### FATURA-005: İade Faturası
- [ ] **Açıklama:** Negatif fatura oluşturma
- **Modül:** Faturalar
- **Dosyalar:**
  - `app/Models/Invoice.php`
  - `resources/views/invoices/create.blade.php`
- **Kabul Kriterleri:**
  - [ ] İade fatura tipi
  - [ ] Orijinal faturaya referans
  - [ ] Negatif tutar gösterimi
  - [ ] Cari hesapta alacak kaydı

---

### FATURA-006: Fatura Onay Akışı
- [ ] **Açıklama:** Taslak → Onay → Gönderildi akışı
- **Modül:** Faturalar
- **Dosyalar:**
  - `app/Models/Invoice.php`
  - `app/Enums/InvoiceStatus.php` (güncelleme)
- **Kabul Kriterleri:**
  - [ ] Taslak durumu
  - [ ] Onay bekliyor durumu
  - [ ] Onay/red işlemi
  - [ ] E-posta bildirimi

---

### BEYANNAME-002: Beyanname Dosya Eki
- [ ] **Açıklama:** Beyannamelere PDF/belge yükleme
- **Modül:** Beyannameler
- **Dosyalar:**
  - `app/Models/TaxDeclaration.php`
  - `resources/views/tax-declarations/edit.blade.php`
  - `storage/app/declarations/*`
- **Kabul Kriterleri:**
  - [ ] Dosya yükleme alanı
  - [ ] PDF, JPG, PNG desteği
  - [ ] Dosya önizleme
  - [ ] Dosya indirme

---

### BEYANNAME-003: Düzeltme Beyannamesi Detayı
- [ ] **Açıklama:** Düzeltme sayısı ve gerekçesi
- **Modül:** Beyannameler
- **Dosyalar:**
  - `app/Models/TaxDeclaration.php`
  - `resources/views/tax-declarations/edit.blade.php`
- **Kabul Kriterleri:**
  - [ ] Düzeltme sayısı alanı
  - [ ] Düzeltme gerekçesi textarea
  - [ ] Orijinal beyanname referansı

---

### TAHSILAT-002: Kredi Kartı Komisyon Hesaplama
- [ ] **Açıklama:** Ödeme yöntemine göre komisyon
- **Modül:** Tahsilatlar
- **Dosyalar:**
  - `app/Models/PaymentMethod.php` (yeni veya güncelleme)
  - `resources/views/payments/create.blade.php`
- **Kabul Kriterleri:**
  - [ ] Yöntem bazlı komisyon oranı
  - [ ] Otomatik komisyon hesaplama
  - [ ] Net tutar gösterimi

---

### TAHSILAT-003: Havale/EFT Referans Zorunluluğu
- [ ] **Açıklama:** Yöntem bazlı zorunlu alanlar
- **Modül:** Tahsilatlar
- **Dosyalar:**
  - `app/Http/Controllers/PaymentController.php`
  - `resources/views/payments/_form.blade.php`
- **Kabul Kriterleri:**
  - [ ] Havale/EFT seçilince referans zorunlu
  - [ ] Dinamik form validation
  - [ ] Banka seçimi

---

### FIRMA-002: Favori Firmalar
- [ ] **Açıklama:** Sık kullanılan firmaları işaretleme
- **Modül:** Firmalar
- **Dosyalar:**
  - `app/Models/Firm.php`
  - `resources/views/firms/index.blade.php`
- **Kabul Kriterleri:**
  - [ ] Yıldız ile işaretleme
  - [ ] Favoriler önce listeleme
  - [ ] Dropdown'larda favori gösterimi

---

### FIRMA-003: Firma Kartı Görünümü
- [ ] **Açıklama:** Liste yerine kart/grid görünümü
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/views/firms/index.blade.php`
- **Kabul Kriterleri:**
  - [ ] Liste/Kart toggle
  - [ ] Kart tasarımı
  - [ ] Bakiye ve durum gösterimi
  - [ ] Tercih kaydetme

---

### DASHBOARD-002: Widget Özelleştirme
- [ ] **Açıklama:** Kullanıcı bazlı widget seçimi
- **Modül:** Dashboard
- **Dosyalar:**
  - `app/Models/UserDashboardPreference.php` (yeni)
  - `resources/views/dashboard/*`
- **Kabul Kriterleri:**
  - [ ] Widget göster/gizle
  - [ ] Widget sıralama (drag-drop)
  - [ ] Varsayılan düzen

---

### DASHBOARD-003: Bugün Yapılacaklar
- [ ] **Açıklama:** Günlük görev checklistesi
- **Modül:** Dashboard
- **Dosyalar:**
  - `resources/views/dashboard/_todos.blade.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Vadesi gelen faturalar
  - [ ] Bugün son gün beyannameler
  - [ ] Tamamlandı işaretleme

---

### UI-001: Rapor Tarih Seçici
- [ ] **Açıklama:** Görsel date range picker
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/js/components/date-range-picker.js` (yeni)
  - `resources/views/reports/*`
- **Kabul Kriterleri:**
  - [ ] Takvim görünümü
  - [ ] Hazır aralıklar (Bu ay, Geçen ay, Bu yıl)
  - [ ] Özel aralık seçimi

---

### UI-002: Tema Renk Seçimi
- [ ] **Açıklama:** Kullanıcı bazlı renk tercihi
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/views/settings/tabs/general.blade.php`
  - `resources/css/app.css`
- **Kabul Kriterleri:**
  - [ ] 5-6 renk paleti
  - [ ] Anlık önizleme
  - [ ] Tercih kaydetme

---

### YARDIM-001: Video Tutoriallar
- [ ] **Açıklama:** Özellik bazlı video rehberler
- **Modül:** Yardım
- **Dosyalar:**
  - `resources/views/help/videos.blade.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Başlangıç videosu
  - [ ] Fatura oluşturma videosu
  - [ ] Beyanname takibi videosu
  - [ ] Responsive video player

---

### YARDIM-002: SSS Genişletme
- [ ] **Açıklama:** Sık sorulan sorular bölümü
- **Modül:** Yardım
- **Dosyalar:**
  - `resources/views/help/faq.blade.php`
- **Kabul Kriterleri:**
  - [ ] Kategori bazlı SSS
  - [ ] Arama özelliği
  - [ ] Accordion görünümü

---

## 🟢 P3 - DÜŞÜK ÖNCELİKLİ GÖREVLER (4+ Hafta)

### UI-003: Dark Mode
- [ ] **Açıklama:** Koyu tema desteği
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/css/app.css`
  - `resources/views/layouts/app.blade.php`
- **Kabul Kriterleri:**
  - [ ] Tam dark mode desteği
  - [ ] Sistem tercihine göre otomatik
  - [ ] Toggle butonu

---

### API-001: RESTful API Oluşturma
- [ ] **Açıklama:** Üçüncü taraf entegrasyonlar için API
- **Modül:** API
- **Dosyalar:**
  - `routes/api.php`
  - `app/Http/Controllers/Api/*` (yeni)
- **Kabul Kriterleri:**
  - [ ] Token tabanlı auth
  - [ ] Firma, Fatura, Tahsilat endpoint'leri
  - [ ] Rate limiting
  - [ ] API dokümantasyonu

---

### ARSIV-001: Tarihsel Veri Arşivleme
- [ ] **Açıklama:** Eski dönem verilerinin ayrı tutulması
- **Modül:** Sistem
- **Dosyalar:**
  - `app/Console/Commands/ArchiveOldData.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] X yıldan eski verileri arşivleme
  - [ ] Arşiv görüntüleme
  - [ ] Geri yükleme

---

### BANKA-001: Banka Mutabakatı
- [ ] **Açıklama:** Banka hesap ekstresi import
- **Modül:** Finansal
- **Dosyalar:**
  - `app/Services/BankReconciliationService.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Ekstre dosya import
  - [ ] Otomatik eşleştirme
  - [ ] Manuel eşleştirme

---

### LOGIN-001: Demo Hesap Bilgisi
- [ ] **Açıklama:** Login sayfasında demo bilgisi gösterme
- **Modül:** Auth
- **Dosyalar:**
  - `resources/views/auth/login.blade.php`
- **Kabul Kriterleri:**
  - [ ] Demo kullanıcı bilgisi alert
  - [ ] Tek tıkla doldurma

---

### LOGIN-002: Beni Hatırla
- [ ] **Açıklama:** Oturum hatırlama özelliği
- **Modül:** Auth
- **Dosyalar:**
  - `resources/views/auth/login.blade.php`
  - `app/Http/Controllers/AuthController.php`
- **Kabul Kriterleri:**
  - [ ] Remember me checkbox
  - [ ] Uzun süreli token

---

### FIRMA-004: Zengin Metin Editörü
- [ ] **Açıklama:** Firma notları için WYSIWYG editör
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/views/firms/_form.blade.php`
- **Kabul Kriterleri:**
  - [ ] Bold, italic, liste
  - [ ] Link ekleme
  - [ ] Basit formatlama

---

### FATURA-007: Siralama Secenekleri
- [ ] **Açıklama:** Fatura listesinde sıralama
- **Modül:** Faturalar
- **Dosyalar:**
  - `app/Http/Controllers/InvoiceController.php`
  - `resources/views/invoices/index.blade.php`
- **Kabul Kriterleri:**
  - [ ] Tarih, tutar, firma sıralaması
  - [ ] Artan/azalan
  - [ ] Tercih kaydetme

---

### GUVENLIK-002: Content Security Policy
- [ ] **Açıklama:** CSP header ekleme
- **Modül:** Güvenlik
- **Dosyalar:**
  - `app/Http/Middleware/SecurityHeaders.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Strict CSP kuralları
  - [ ] Report-only mode
  - [ ] Violation logging

---

### PERFORMANS-001: Lazy Loading
- [ ] **Açıklama:** Büyük listelerde virtualization
- **Modül:** Performans
- **Dosyalar:**
  - `resources/views/invoices/index.blade.php`
- **Kabul Kriterleri:**
  - [ ] Infinite scroll
  - [ ] Skeleton loading
  - [ ] Sayfa boyutu optimizasyonu

---

### PERFORMANS-002: Cache Stratejisi
- [ ] **Açıklama:** Firma ve dropdown cache
- **Modül:** Performans
- **Dosyalar:**
  - `app/Services/CacheService.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Firma listesi cache
  - [ ] Dropdown verileri cache
  - [ ] Cache invalidation

---

### BEYANNAME-004: Beyanname Şablonları
- [ ] **Açıklama:** Sık kullanılan form kombinasyonları
- **Modül:** Beyannameler
- **Dosyalar:**
  - `app/Models/TaxFormTemplate.php` (yeni)
- **Kabul Kriterleri:**
  - [ ] Şablon oluşturma
  - [ ] Toplu atama

---

### MOBIL-004: Pull-to-Refresh
- [ ] **Açıklama:** Mobilde aşağı çekerek yenileme
- **Modül:** UI/UX
- **Dosyalar:**
  - `resources/js/components/pull-refresh.js` (yeni)
- **Kabul Kriterleri:**
  - [ ] Listelerde çalışma
  - [ ] Loading animasyonu
  - [ ] iOS/Android uyumlu

---

---

## 📝 GÖREV DURUMU AÇIKLAMALARI

- [ ] **Bekliyor:** Henüz başlanmadı
- [~] **Devam ediyor:** Üzerinde çalışılıyor
- [x] **Tamamlandı:** Bitmiş ve test edilmiş

---

## 🔄 GÜNCELLEME GEÇMİŞİ

| Tarih | Güncelleme |
|-------|------------|
| 06.12.2025 | İlk görev listesi oluşturuldu |

---

*Bu görev listesi, muhasebe perspektifli sistem analizinden türetilmiştir.*
