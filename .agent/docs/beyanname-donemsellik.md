# 📅 Beyanname Dönemsellik Mantığı

Bu dokümantasyon, CastBook'un beyanname dönemsellik mantığını açıklar.

---

## 🔑 Temel Kurallar

**Beyannameler dönem sonundan FREKANSA GÖRE farklı sürelerde bildirilir:**

| Frekans | Kaç Ay Sonra? | Örnek |
|---------|---------------|-------|
| **Aylık** | 1 ay sonra | Kasım 2025 KDV → 26 Aralık 2025 |
| **Çeyreklik** | 2 ay sonra | Q3 2025 (Eylül sonu) → 17 Kasım 2025 |
| **Yıllık** | 4+ ay sonra | 2024 Kurumlar → 25 Nisan 2025 |

---

## 📊 Dönem vs Son Tarih Örnekleri

### Aylık Beyannameler

| Dönem | Beyanname | Son Tarih |
|-------|-----------|-----------|
| Kasım 2025 | KDV | 26 Aralık 2025 |
| Kasım 2025 | Muhtasar | 17 Aralık 2025 |
| Kasım 2025 | Damga | 17 Aralık 2025 |
| Kasım 2025 | Ba-Bs | 15 Aralık 2025 |
| Kasım 2025 | KKDF | 10 Aralık 2025 |

### Çeyreklik Beyannameler (Geçici Vergi)

| Dönem | Period End | Son Tarih | Açıklama |
|-------|------------|-----------|----------|
| Q1 2025 (Oca-Şub-Mar) | 31 Mart | 17 Mayıs 2025 | +2 ay |
| Q2 2025 (Nis-May-Haz) | 30 Haziran | 17 Ağustos 2025 | +2 ay |
| Q3 2025 (Tem-Ağu-Eyl) | 30 Eylül | 17 Kasım 2025 | +2 ay |
| Q4 2025 (Eki-Kas-Ara) | 31 Aralık | 17 Şubat 2026 | +2 ay |

### Yıllık Beyannameler

| Dönem | Beyanname | Son Tarih |
|-------|-----------|-----------|
| 2024 | Gelir Vergisi (1. Taksit) | 31 Mart 2025 |
| 2024 | Kurumlar Vergisi | 25 Nisan 2025 |
| 2024 | Gelir Vergisi (2. Taksit) | 31 Temmuz 2025 |

---

## 🗄️ Veritabanı Yapısı

### TaxDeclaration (Firma Beyannameleri)

```
id: 1
firm_id: 5
tax_form_id: 1 (KDV)
period_start: 2025-11-01   ← Dönem başı
period_end: 2025-11-30     ← Dönem sonu
period_label: "11/2025"    ← Görüntüleme etiketi
due_date: 2025-12-26       ← Son tarih (BİR SONRAKI AY)
status: pending
```

### TaxCalendar (GİB Resmi Takvim)

```
id: 85
year: 2025
month: 12                  ← Son tarih ayı
day: 26
due_date: 2025-12-26
code: KDV
name: Katma Değer Vergisi Beyannamesi
period_label: "Kasım 2025" ← İlgili dönem (ÖNCEKİ AY)
```

---

## ⚙️ Kod Mantığı

### 1. Beyanname Oluşturma (`GenerateTaxDeclarations`)

```php
// Dönem sonu hesapla
[$start, $end, $label] = $this->resolvePeriodRange($taxForm->frequency, $period);

// Son tarih = Dönem sonundan BİR SONRAKI AY
$dueDate = $this->calculateDueDate($end, $dueDay);

private function calculateDueDate(Carbon $periodEnd, int $day): Carbon
{
    // Bir sonraki aya geç
    $dueMonth = $periodEnd->copy()->addMonth()->startOfMonth();
    
    // O ayın kaç günü var kontrol et
    $safeDay = max(1, min($day, $dueMonth->daysInMonth));

    return $dueMonth->day($safeDay);
}
```

### 2. GİB Resmi Tarih Eşleştirme (`TaxForm`)

```php
public function getOfficialDueDate(Carbon $periodEnd): ?Carbon
{
    // Dönem sonundan BİR SONRAKI AYDA arama yap
    $searchMonth = $periodEnd->copy()->addMonth();

    $calendarEntry = TaxCalendar::query()
        ->where('code', $this->gib_code)
        ->where('year', $searchMonth->year)
        ->where('month', $searchMonth->month)
        ->first();

    return $calendarEntry?->due_date;
}
```

### 3. GİB Takvim Oluşturma (`TaxCalendarService`)

```php
protected array $declarations = [
    'KDV' => [
        'day' => 26,           // Her ayın 26'sı
        'offset_month' => -1,  // ÖNCEKİ AY dönemi için
    ],
];

// Aralık ayındaki takvim kaydı → Kasım dönemi içindir
$periodMonth = Carbon::createFromDate($year, $month, 1)
    ->addMonths($config['offset_month']); // -1 = önceki ay
```

---

## 📆 Beyanname Son Gün Kuralları

| Beyanname | Son Gün | Dönem |
|-----------|---------|-------|
| **KDV** | Ayın 26'sı | Önceki ay |
| **Muhtasar** | Ayın 17'si | Önceki ay |
| **Damga** | Ayın 17'si | Önceki ay |
| **Ba-Bs** | Ayın 15'i | Önceki ay |
| **KKDF** | Ayın 10'u | Önceki ay |
| **Geçici Vergi** | Takip eden 2. ayın 17'si | Önceki çeyrek |
| **Kurumlar** | 25 Nisan | Önceki yıl |
| **Gelir (1. Taksit)** | 31 Mart | Önceki yıl |
| **Gelir (2. Taksit)** | 31 Temmuz | Önceki yıl |

---

## ⚠️ Hafta Sonu Kuralı

Son tarih hafta sonuna denk gelirse:
- **Cumartesi** → Önceki **Cuma**'ya çekilir
- **Pazar** → Önceki **Cuma**'ya çekilir (2 gün geri)

```php
if ($date->isSaturday()) {
    $date->subDay();
} elseif ($date->isSunday()) {
    $date->subDays(2);
}
```

---

## 🔄 Özet Akış

```
1. Kasım 2025 biter
2. Aralık 2025'te o dönemin beyannameleri verilir:
   - KDV: 26 Aralık
   - Muhtasar: 17 Aralık
   - Ba-Bs: 15 Aralık
3. TaxDeclaration kaydı:
   - period_start: 2025-11-01
   - period_end: 2025-11-30
   - due_date: 2025-12-26 (KDV için)
```

---

**Son Güncelleme:** 6 Aralık 2025
