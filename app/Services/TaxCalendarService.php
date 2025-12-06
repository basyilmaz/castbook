<?php

namespace App\Services;

use App\Models\TaxCalendar;
use Illuminate\Support\Carbon;

/**
 * GİB Vergi Takvimi Otomatik Oluşturma Servisi
 * 
 * Resmi beyanname tarihlerini algoritmik olarak hesaplar.
 * Kaynak kurallar: https://gib.gov.tr/vergi-takvimi
 */
class TaxCalendarService
{
    /**
     * Beyanname türleri ve varsayılan son günleri
     */
    protected array $declarations = [
        'KDV' => [
            'name' => 'Katma Değer Vergisi Beyannamesi',
            'day' => 26,     // Her ayın 26'sı önceki ayın KDV'si
            'frequency' => 'monthly',
            'offset_month' => -1, // Önceki ay için
        ],
        'MUHTASAR' => [
            'name' => 'Muhtasar ve Prim Hizmet Beyannamesi',
            'day' => 17,
            'frequency' => 'monthly',
            'offset_month' => -1,
        ],
        'DAMGA' => [
            'name' => 'Damga Vergisi Beyannamesi',
            'day' => 17,
            'frequency' => 'monthly',
            'offset_month' => -1,
        ],
        'BA' => [
            'name' => 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)',
            'day' => 15,
            'frequency' => 'monthly',
            'offset_month' => -1,
        ],
        'BS' => [
            'name' => 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)',
            'day' => 15,
            'frequency' => 'monthly',
            'offset_month' => -1,
        ],
        'KKDF' => [
            'name' => 'KKDF Kesintisi Bildirimi',
            'day' => 10,
            'frequency' => 'monthly',
            'offset_month' => -1,
        ],
    ];

    /**
     * Geçici vergi dönemleri (çeyrek dönem)
     */
    protected array $quarterlyDeclarations = [
        'GECICI_VERGI_GELIR' => [
            'name' => 'Gelir Vergisi Geçici Vergi Beyannamesi',
        ],
        'GECICI_VERGI_KURUMLAR' => [
            'name' => 'Kurumlar Vergisi Geçici Vergi Beyannamesi',
        ],
    ];

    /**
     * Yıllık beyannameler
     */
    protected array $yearlyDeclarations = [
        'KURUMLAR' => [
            'name' => 'Kurumlar Vergisi Beyannamesi',
            'month' => 4,
            'day' => 25,
        ],
        'GELIR_1' => [
            'name' => 'Yıllık Gelir Vergisi Beyannamesi (1. Taksit)',
            'month' => 3,
            'day' => 31,
        ],
        'GELIR_2' => [
            'name' => 'Yıllık Gelir Vergisi Beyannamesi (2. Taksit)',
            'month' => 7,
            'day' => 31,
        ],
    ];

    /**
     * Belirli bir yıl için tüm vergi takvimini oluştur
     */
    public function generateForYear(int $year): array
    {
        $created = [];
        $skipped = [];

        // Aylık beyannameler
        foreach (range(1, 12) as $month) {
            foreach ($this->declarations as $code => $config) {
                $periodMonth = Carbon::createFromDate($year, $month, 1)->addMonths($config['offset_month']);
                $dueDate = $this->calculateDueDate($year, $month, $config['day']);

                $result = $this->createEntry([
                    'year' => $year,
                    'month' => $month,
                    'day' => $dueDate->day,
                    'due_date' => $dueDate->toDateString(),
                    'code' => $code === 'BA' || $code === 'BS' ? 'BA_BS' : $code,
                    'name' => $config['name'],
                    'period_label' => $periodMonth->translatedFormat('F Y'),
                    'frequency' => 'monthly',
                ]);

                if ($result['created']) {
                    $created[] = $result['entry'];
                } else {
                    $skipped[] = $result['entry'];
                }
            }
        }

        // Geçici vergi (çeyrek dönem) - Şubat, Mayıs, Ağustos, Kasım aylarının 17'si
        $quarterlyMonths = [
            2 => ['quarter' => 4, 'period_year' => $year - 1, 'label' => ($year - 1) . ' Q4'],  // 4. çeyrek: Şubat
            5 => ['quarter' => 1, 'period_year' => $year, 'label' => $year . ' Q1'],           // 1. çeyrek: Mayıs
            8 => ['quarter' => 2, 'period_year' => $year, 'label' => $year . ' Q2'],           // 2. çeyrek: Ağustos
            11 => ['quarter' => 3, 'period_year' => $year, 'label' => $year . ' Q3'],          // 3. çeyrek: Kasım
        ];

        foreach ($quarterlyMonths as $month => $info) {
            $dueDate = $this->calculateDueDate($year, $month, 17);

            foreach ($this->quarterlyDeclarations as $code => $config) {
                $result = $this->createEntry([
                    'year' => $year,
                    'month' => $month,
                    'day' => $dueDate->day,
                    'due_date' => $dueDate->toDateString(),
                    'code' => 'GECICI_VERGI',
                    'name' => $config['name'] . ' (' . $info['quarter'] . '. Dönem)',
                    'period_label' => $info['label'],
                    'frequency' => 'quarterly',
                ]);

                if ($result['created']) {
                    $created[] = $result['entry'];
                } else {
                    $skipped[] = $result['entry'];
                }
            }
        }

        // Yıllık beyannameler
        foreach ($this->yearlyDeclarations as $code => $config) {
            $dueDate = $this->calculateDueDate($year, $config['month'], $config['day']);
            $periodYear = $year - 1; // Önceki yılın beyanı

            $result = $this->createEntry([
                'year' => $year,
                'month' => $config['month'],
                'day' => $dueDate->day,
                'due_date' => $dueDate->toDateString(),
                'code' => str_contains($code, 'GELIR') ? 'GELIR' : 'KURUMLAR',
                'name' => $config['name'],
                'period_label' => (string) $periodYear,
                'frequency' => 'yearly',
            ]);

            if ($result['created']) {
                $created[] = $result['entry'];
            } else {
                $skipped[] = $result['entry'];
            }
        }

        return [
            'created' => count($created),
            'skipped' => count($skipped),
            'total' => count($created) + count($skipped),
            'year' => $year,
        ];
    }

    /**
     * Son gün hesapla (hafta sonuna denk gelirse önceki Cuma'ya çek)
     */
    protected function calculateDueDate(int $year, int $month, int $day): Carbon
    {
        // Ayın son günü kontrolü
        $lastDayOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->day;
        $day = min($day, $lastDayOfMonth);

        $date = Carbon::createFromDate($year, $month, $day);

        // Hafta sonu kontrolü - Cumartesi veya Pazar ise Cuma'ya çek
        if ($date->isSaturday()) {
            $date->subDay();
        } elseif ($date->isSunday()) {
            $date->subDays(2);
        }

        return $date;
    }

    /**
     * Tek kayıt oluştur (duplicate kontrolü ile)
     */
    protected function createEntry(array $data): array
    {
        // Aynı tarih, kod ve isimde kayıt var mı?
        $exists = TaxCalendar::where('due_date', $data['due_date'])
            ->where('code', $data['code'])
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return ['created' => false, 'entry' => $data];
        }

        $entry = TaxCalendar::create([
            'year' => $data['year'],
            'month' => $data['month'],
            'day' => $data['day'],
            'due_date' => $data['due_date'],
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['name'] . ' - ' . $data['period_label'] . ' dönemi son günü',
            'period_label' => $data['period_label'],
            'frequency' => $data['frequency'],
            'applicable_to' => null,
            'is_active' => true,
        ]);

        return ['created' => true, 'entry' => $entry];
    }

    /**
     * Mevcut verilen yılları getir
     */
    public function getAvailableYears(): array
    {
        return TaxCalendar::distinct()->pluck('year')->sort()->values()->toArray();
    }

    /**
     * Belirli bir yılın verilerini sil
     */
    public function deleteYear(int $year): int
    {
        return TaxCalendar::where('year', $year)->delete();
    }

    /**
     * Eksik yılları tespit et
     */
    public function getMissingYears(): array
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;
        $availableYears = $this->getAvailableYears();

        $missing = [];
        if (!in_array($currentYear, $availableYears)) {
            $missing[] = $currentYear;
        }
        if (!in_array($nextYear, $availableYears)) {
            $missing[] = $nextYear;
        }

        return $missing;
    }
}
