# CastBook Değişiklik Günlüğü (Changelog)

Tüm önemli değişiklikler bu dosyada belgelenmektedir.
Format [Keep a Changelog](https://keepachangelog.com/tr/1.0.0/) standardına uymaktadır.
Versiyon numaralandırması [Semantic Versioning](https://semver.org/lang/tr/) kullanmaktadır.

---

## [2.9.1] - 2025-12-30

### Eklenenler
- 🌙 Dark mode toggle butonu (güneş/ay ikonu)
- ⏰ Türkiye timezone desteği (Europe/Istanbul)
- ⚠️ Lisans bitiş uyarısı (7 gün kala)
- 🔄 Geri yükleme noktası sistemi
- 💾 SQLite ve MySQL uyumlu veritabanı yedekleme

### Düzeltmeler
- 🎨 Dark mode için kapsamlı CSS stilleri
- 🔧 Rollback → Geri Yükleme Türkçeleştirme
- 🔗 Footer'da CastBook ve CastinTech linki
- 🏗️ Güncelleme sekmesi redirect sorunu

---

## [2.9.0] - 2025-12-29

### Eklenenler
- 🔄 WordPress tarzı otomatik güncelleme sistemi
- 📦 Güncelleme paketi indirme ve uygulama
- 🔐 Lisans güvenlik güçlendirmesi (AES-256)
- 🖥️ Hardware fingerprint doğrulama
- ✅ Checksum doğrulama sistemi
- 🔌 API entegrasyonu ile lisans kontrolü
- 🧪 LicenseService unit testleri

### Değişiklikler
- Lisans sekmesi ayarlara eklendi
- Güncelleme sekmesi bakım işlemleri ile genişletildi

---

## [2.8.0] - 2025-12-28

### Eklenenler
- 🧙 Kurulum sihirbazı (5 adımlı)
- 📋 Sistem gereksinimleri kontrolü
- 🔧 Veritabanı bağlantı testi
- 👤 Admin kullanıcı oluşturma
- ⚙️ Uygulama ayarları yapılandırma

---

## [2.7.0] ve Öncesi

Önceki versiyonlar için git commit geçmişine bakınız.

---

## Versiyon Güncelleme Rehberi

Yeni bir versiyon çıkarırken:

1. **CHANGELOG.md** dosyasının başına yeni versiyon bölümü ekle
2. **config/app.php** dosyasındaki `version` değerini güncelle
3. Değişiklikleri commit et: `git commit -m "release: vX.Y.Z"`
4. Tag oluştur: `git tag vX.Y.Z`
5. Push: `git push && git push --tags`

### Versiyon Numaralandırma

- **MAJOR** (X.0.0): Geriye uyumsuz API değişiklikleri
- **MINOR** (0.X.0): Geriye uyumlu yeni özellikler
- **PATCH** (0.0.X): Geriye uyumlu hata düzeltmeleri
