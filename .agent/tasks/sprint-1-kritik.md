# 🏃 Sprint 1: Kritik Eksiklikler
**Süre:** Hafta 1-2 (10 iş günü)  
**Durum:** 🟡 Başlamadı

---

## 📋 Görev Listesi

### 1️⃣ Landing Page & Marka Kimliği (3-4 gün)

#### T1.1.1 - Marka İsmi Kararı
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 30 dakika
- **Açıklama:** "CastBook" veya "Kod Muhasebe" arasında seçim yap
- **Kabul Kriterleri:**
  - [ ] İsim kesinleşti
  - [ ] Domain kontrolü yapıldı
  - [ ] Tüm dosyalarda tutarlılık sağlandı

#### T1.1.2 - Logo Tasarımı
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 1 saat
- **Açıklama:** Profesyonel logo oluştur veya tasarla
- **Kabul Kriterleri:**
  - [ ] Logo SVG formatında
  - [ ] Favicon oluşturuldu
  - [ ] Light/dark versiyonlar mevcut

#### T1.1.3 - Renk & Tipografi Rehberi
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🟡 Yüksek
- **Tahmini Süre:** 1 saat
- **Açıklama:** Tutarlı tasarım sistemi dokümante et
- **Kabul Kriterleri:**
  - [ ] Primary, secondary, accent renkler belirlendi
  - [ ] Font ailesi kesinleşti
  - [ ] CSS variables güncellendi

#### T1.1.4 - Landing Page
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 2-3 gün
- **Açıklama:** Profesyonel tanıtım sayfası oluştur
- **Alt Görevler:**
  - [ ] Hero section (başlık, CTA)
  - [ ] Özellikler bölümü (6 özellik kartı)
  - [ ] Nasıl Çalışır bölümü
  - [ ] Fiyatlandırma tablosu
  - [ ] Müşteri yorumları (placeholder)
  - [ ] Demo talep formu
  - [ ] Footer
- **Kabul Kriterleri:**
  - [ ] Responsive tasarım
  - [ ] SEO meta tags
  - [ ] Yükleme süresi < 3 saniye

---

### 2️⃣ Kimlik Doğrulama İyileştirmeleri (1 gün)

#### T1.2.1 - "Beni Hatırla" Checkbox
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 30 dakika
- **Dosya:** `resources/views/auth/login.blade.php`
- **Teknik Notlar:**
  ```php
  // AuthController'da remember parametresi zaten destekleniyor
  Auth::attempt($credentials, $request->boolean('remember'));
  ```
- **Kabul Kriterleri:**
  - [ ] Checkbox görünür
  - [ ] Session 30 gün kalıcı oluyor
  - [ ] Test yazıldı

#### T1.2.2 - Şifremi Unuttum Akışı
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 2-3 saat
- **Dosyalar:**
  - [ ] `routes/web.php` - Route ekle
  - [ ] `AuthController` - Metodlar ekle
  - [ ] `resources/views/auth/forgot-password.blade.php`
  - [ ] `resources/views/auth/reset-password.blade.php`
  - [ ] `resources/views/emails/password-reset.blade.php`
- **Kabul Kriterleri:**
  - [ ] E-posta gönderiliyor
  - [ ] Token 60 dakika geçerli
  - [ ] Şifre başarıyla sıfırlanıyor
  - [ ] Rate limiting var (5 deneme/saat)

#### T1.2.3 - Login Sayfası Iyileştirmesi
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🟡 Yüksek
- **Tahmini Süre:** 1 saat
- **Açıklama:** Login sayfasını marka ile uyumlu hale getir
- **Kabul Kriterleri:**
  - [ ] Logo eklendi
  - [ ] Renkler tutarlı
  - [ ] Mobile responsive

---

### 3️⃣ Dashboard Kritik İyileştirmeler (2 gün)

#### T1.3.1 - Firma Satırlarını Tıklanabilir Yap
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 30 dakika
- **Dosya:** `resources/views/dashboard.blade.php`
- **Değişiklik:**
  ```blade
  {{-- Mevcut --}}
  <tr>
      <td>{{ $firm['name'] }}</td>
  
  {{-- Yeni --}}
  <tr onclick="window.location='{{ route('firms.show', $firm['id']) }}'" 
      style="cursor: pointer;">
      <td>
          <a href="{{ route('firms.show', $firm['id']) }}" class="text-decoration-none">
              {{ $firm['name'] }}
          </a>
      </td>
  ```
- **Kabul Kriterleri:**
  - [ ] Tüm satır tıklanabilir
  - [ ] Hover efekti var
  - [ ] Firma detay sayfasına yönlendiriyor

#### T1.3.2 - Chart.js Entegrasyonu
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 1 saat
- **Teknik Notlar:**
  ```bash
  npm install chart.js
  ```
  ```javascript
  // resources/js/app.js
  import Chart from 'chart.js/auto';
  window.Chart = Chart;
  ```
- **Kabul Kriterleri:**
  - [ ] Chart.js yüklendi
  - [ ] Vite config güncellendi
  - [ ] Test chart çalışıyor

#### T1.3.3 - Aylık Gelir Trend Grafiği
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🔴 Kritik
- **Tahmini Süre:** 2 saat
- **Dosyalar:**
  - [ ] `DashboardController.php` - Son 6 ay verisi
  - [ ] `resources/views/dashboard/_revenue_chart.blade.php`
- **Kabul Kriterleri:**
  - [ ] Line chart gösteriliyor
  - [ ] Son 6 ay verisi
  - [ ] Hover'da detay gösterimi
  - [ ] Responsive

#### T1.3.4 - Son İşlemler Widget
- **Durum:** ⬜ Başlamadı
- **Öncelik:** 🟡 Yüksek
- **Tahmini Süre:** 1 saat
- **Açıklama:** Dashboard'da son 5 işlemi göster
- **Dosyalar:**
  - [ ] `DashboardController.php` - Son transactions
  - [ ] `resources/views/dashboard/_recent_activity.blade.php`
- **Kabul Kriterleri:**
  - [ ] Son 5 işlem görünüyor
  - [ ] Fatura/ödeme ikonu
  - [ ] Zaman damgası
  - [ ] Tıklanabilir satırlar

---

## 📊 Sprint Metrikleri

| Metrik | Hedef | Mevcut |
|--------|-------|--------|
| Toplam Görev | 11 | 11 |
| Tamamlanan | 11 | 0 |
| İlerleme | %100 | %0 |
| Tahmini Süre | 10 gün | - |

---

## 🚦 Günlük İlerleme

### Gün 1 (Tarih: __)
- [ ] T1.1.1 - Marka ismi
- [ ] T1.2.1 - Beni hatırla

### Gün 2 (Tarih: __)
- [ ] T1.1.2 - Logo
- [ ] T1.1.3 - Renk rehberi

### Gün 3-5 (Tarih: __)
- [ ] T1.1.4 - Landing page

### Gün 6 (Tarih: __)
- [ ] T1.2.2 - Şifremi unuttum
- [ ] T1.2.3 - Login iyileştirme

### Gün 7-8 (Tarih: __)
- [ ] T1.3.1 - Tıklanabilir satırlar
- [ ] T1.3.2 - Chart.js
- [ ] T1.3.3 - Gelir grafiği
- [ ] T1.3.4 - Son işlemler

### Gün 9-10 (Tarih: __)
- Test ve düzeltmeler
- Sprint review

---

## ⚠️ Riskler & Blocker'lar

| Risk | Olasılık | Etki | Mitigasyon |
|------|----------|------|------------|
| Logo tasarımı uzar | Orta | Düşük | Placeholder ile devam |
| SMTP ayarları eksik | Yüksek | Orta | Mailtrap kullan |
| Chart.js öğrenme | Düşük | Düşük | Hazır örnekler kullan |

---

## 🔗 Bağımlılıklar

- T1.1.4 (Landing) → T1.1.1 (Marka ismi) gerektirir
- T1.1.4 (Landing) → T1.1.2 (Logo) gerektirir
- T1.2.2 (Şifre sıfırlama) → SMTP ayarları gerektirir
- T1.3.3 (Grafik) → T1.3.2 (Chart.js) gerektirir
