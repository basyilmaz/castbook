# 🧪 CASTBOOK - MANUEL BROWSER TEST KILAVUZU

## 🚀 SUNUCU ÇALIŞIYOR
**URL:** http://127.0.0.1:8020

---

## 📋 TEST CHECKLIST

### ✅ 1. GİRİŞ SAYFASI
**URL:** http://127.0.0.1:8020/login

**Kontroller:**
- [ ] Sayfa açılıyor mu?
- [ ] Türkçe karakterler düzgün görünüyor mu?
- [ ] "Giriş Yap" başlığı var mı?
- [ ] Email ve Şifre alanları var mı?

**Giriş Bilgileri:**
```
Email: muhasebe@example.com
Şifre: Parola123!
```

**Beklenen:** Dashboard'a yönlendirilmeli

---

### ✅ 2. DASHBOARD
**URL:** http://127.0.0.1:8020

**Kontroller:**
- [ ] Genel Bakış başlığı görünüyor mu?
- [ ] Özet kartlar (firmalar, faturalar, vb.) var mı?
- [ ] Türkçe karakterler düzgün mü?
- [ ] Menü çalışıyor mu?

---

### ✅ 3. YENİ FİRMA OLUŞTURMA
**URL:** http://127.0.0.1:8020/firms/create

**Kontroller:**
- [ ] "Yeni Firma" başlığı var mı?
- [ ] **"Firma Türü"** dropdown var mı? ⭐ YENİ
- [ ] 3 seçenek var mı?
  - [ ] Şahıs Firması
  - [ ] Limited Şirket
  - [ ] Anonim Şirket
- [ ] Bilgilendirme mesajı var mı?
  - "Vergi beyannameleri firma türüne göre otomatik atanacaktır."

**Test Senaryosu:**
```
1. Firma Adı: "Test Muhasebe Ltd."
2. Firma Türü: "Limited Şirket" SEÇ
3. Vergi No: 1234567890
4. Yetkili: Test Kişi
5. Email: test@test.com
6. Telefon: 5551234567
7. Aylık Ücret: 2000
8. Sözleşme Tarihi: Bugünden 1 ay önce
9. Durum: Aktif
10. KAYDET
```

**Beklenen:**
- Firma oluşturulmalı
- Başarı mesajı görünmeli
- Firma listesine yönlendirilmeli

---

### ✅ 4. FİRMA DETAY SAYFASI
**URL:** http://127.0.0.1:8020/firms/{id}

**Kontroller:**
- [ ] Firma bilgileri görünüyor mu?
- [ ] **"Vergi Beyannameleri"** bölümü var mı? ⭐ YENİ
- [ ] Firma türü bilgisi görünüyor mu?
  - Örn: "Limited Şirket - Kurumlar Vergisi mükellefi"
- [ ] Atanmış formlar listeleniyor mu?
- [ ] Form sayısı badge'i var mı? (örn: "5 Form")

**Beklenen Formlar (Limited Şirket için):**
- [ ] KDV-1 (Aylık - 26. gün)
- [ ] Muhtasar (Aylık - 26. gün)
- [ ] BA-BS (Aylık - Son gün)
- [ ] Geçici Vergi (3 Aylık - 31. gün)
- [ ] Kurumlar Vergisi (Yıllık - 30. gün) ← Ltd'ye özel

**Bilgilendirme Notu:**
- [ ] "Vergi formları firma türüne göre otomatik atanır" mesajı var mı?

---

### ✅ 5. AYARLAR SAYFASI
**URL:** http://127.0.0.1:8020/settings

**Tab Kontrolleri:**

#### Tab 1: Genel Ayarlar
- [ ] Şirket Bilgileri bölümü
- [ ] Logo ve Menü Özelleştirme bölümü ⭐ YENİ
  - [ ] Logo upload
  - [ ] Tema seçimi (Açık/Koyu/Otomatik)
  - [ ] Menü başlığı
  - [ ] Menü alt başlığı
- [ ] Fatura Ayarları bölümü
  - [ ] Varsayılan vade günü
  - [ ] Fatura ön eki
  - [ ] Otomatik bildirim switch ⭐ YENİ
  - [ ] Varsayılan açıklama ⭐ YENİ
- [ ] Tahsilat Yöntemleri
- [ ] Mail Sunucu Ayarları (katlanabilir) ⭐ YENİ

#### Tab 2: Fatura Ekstra Alanları (Admin)
- [ ] Tab görünüyor mu?
- [ ] Extra field listesi var mı?

#### Tab 3: Beyanname Yönetimi (Admin)
- [ ] Tab görünüyor mu?
- [ ] "Vergi Formları" bölümü var mı?
- [ ] "Tüm Formları Yönet" butonu var mı?

---

### ✅ 6. VERGİ FORMLARI YÖNETİMİ
**URL:** http://127.0.0.1:8020/settings/tax-forms

**Kontroller:**
- [ ] "Vergi Formları" başlığı var mı?
- [ ] Form listesi görünüyor mu?
- [ ] 8 form var mı?
  - [ ] KDV-1
  - [ ] Muhtasar
  - [ ] BA-BS
  - [ ] Geçici Vergi
  - [ ] Gelir Vergisi
  - [ ] Kurumlar Vergisi
  - [ ] ÖTV
  - [ ] Damga Vergisi
- [ ] Her formda şunlar var mı?
  - [ ] Kod
  - [ ] Form Adı
  - [ ] Açıklama
  - [ ] Periyot (Aylık/3 Aylık/Yıllık)
  - [ ] Vade Günü
  - [ ] Durum (Aktif/Pasif)
  - [ ] Firma Sayısı

---

### ✅ 7. VERGİ BEYANNAMELERI
**URL:** http://127.0.0.1:8020/tax-declarations

**Kontroller:**
- [ ] "Beyanname Takibi" başlığı var mı?
- [ ] Filtreleme formu var mı?
  - [ ] Firma
  - [ ] Beyanname
  - [ ] Yıl
  - [ ] Ay
  - [ ] Durum
- [ ] Boş durum mesajı: "Kayıt bulunamadı."

**Not:** Beyannameler henüz oluşturulmadı, bu normal.

---

### ✅ 8. TEST FİRMALARI KONTROLÜ
**URL:** http://127.0.0.1:8020/firms

**Kontroller:**
- [ ] 3 test firması görünüyor mu?
  - [ ] Test Şahıs Firması
  - [ ] Test Limited Şirketi
  - [ ] Test Anonim Şirketi

**Her firma için detay sayfasını aç ve kontrol et:**
- [ ] Vergi formları bölümü var mı?
- [ ] 5 form atanmış mı?
- [ ] Şahıs firmasında "Gelir Vergisi" var mı?
- [ ] Limited/Anonim'de "Kurumlar Vergisi" var mı?

---

## 🎯 ÖNEMLİ KONTROL NOKTALARI

### UTF-8 Encoding:
- [ ] Tüm sayfalarda Türkçe karakterler düzgün
- [ ] ı, ş, ü, ö, ç, ğ, İ harfleri doğru görünüyor
- [ ] ₺ sembolü düzgün

### Vergi Beyanname Sistemi:
- [ ] Firma türü seçimi çalışıyor
- [ ] Otomatik form atama çalışıyor
- [ ] Firma detayında formlar görünüyor
- [ ] Firma türüne göre doğru formlar atanmış

### Ayarlar Sayfası:
- [ ] 3 tab görünüyor
- [ ] Tüm yeni özellikler mevcut
- [ ] Mail ayarları katlanabilir

---

## ✅ BAŞARI KRİTERLERİ

Sistem başarılı sayılır eğer:
1. ✅ Tüm sayfalar açılıyor
2. ✅ UTF-8 sorunları yok
3. ✅ Firma türü seçimi çalışıyor
4. ✅ Otomatik form atama çalışıyor
5. ✅ Firma detayında vergi formları görünüyor
6. ✅ Ayarlar sayfası tam fonksiyonel

---

## 📸 EKRAN GÖRÜNTÜLERİ

Lütfen şu sayfaların ekran görüntülerini alın:
1. Firma oluşturma formu (Firma türü dropdown)
2. Firma detay sayfası (Vergi formları bölümü)
3. Ayarlar sayfası (Genel Ayarlar tab)
4. Vergi formları yönetimi sayfası

---

## 🚀 TEST SONUCU

Test tamamlandığında lütfen bildirin:
- [ ] Tüm kontroller yapıldı
- [ ] Sorun bulunan yerler (varsa)
- [ ] Ekran görüntüleri alındı

**İyi testler! 🎉**
