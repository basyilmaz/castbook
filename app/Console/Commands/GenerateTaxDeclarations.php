<?php

namespace App\Console\Commands;

use App\Models\FirmTaxForm;
use App\Models\TaxDeclaration;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateTaxDeclarations extends Command
{
    protected $signature = 'app:generate-tax-declarations {--month=}';

    protected $description = 'Aktif firmalar için tanımlı beyannamelerden eksik dönemleri oluşturur';

    public function handle(): int
    {
        $targetMonth = $this->option('month');

        try {
            $period = $targetMonth
                ? Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable) {
            $this->error('Geçersiz ay formatı. Örnek: --month=2024-05');
            return self::INVALID;
        }

        $created = 0;
        $usedGibDates = 0;

        $items = FirmTaxForm::query()
            ->with(['taxForm', 'firm'])
            ->whereHas('taxForm', fn ($q) => $q->where('is_active', true))
            ->where('is_active', true)
            ->get();

        foreach ($items as $item) {
            $taxForm = $item->taxForm;
            if (! $taxForm) {
                continue;
            }

            $periodRange = $this->resolvePeriodRange($taxForm->frequency, $period);
            if (! $periodRange) {
                continue;
            }

            [$start, $end, $label] = $periodRange;

            // Önce GİB resmi tarihini dene, yoksa varsayılan hesapla
            $officialDueDate = $taxForm->getOfficialDueDate($end);
            
            if ($officialDueDate) {
                $dueDate = $officialDueDate;
                $usedGibDates++;
            } else {
                $dueDay = $item->custom_due_day ?: $taxForm->default_due_day;
                $dueDate = $this->calculateDueDate($end, $dueDay, $taxForm->frequency);
            }

            $exists = TaxDeclaration::query()
                ->where('firm_id', $item->firm_id)
                ->where('tax_form_id', $taxForm->id)
                ->whereDate('period_start', $start)
                ->whereDate('period_end', $end)
                ->exists();

            if ($exists) {
                continue;
            }

            TaxDeclaration::create([
                'firm_id' => $item->firm_id,
                'tax_form_id' => $taxForm->id,
                'period_start' => $start,
                'period_end' => $end,
                'period_label' => $label,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);

            $created++;
        }

        $this->info(sprintf(
            '%s dönemi için %d beyanname oluşturuldu. (%d tanesi GİB resmi tarihi ile)',
            $period->format('m/Y'),
            $created,
            $usedGibDates
        ));

        return self::SUCCESS;
    }

    private function resolvePeriodRange(string $frequency, Carbon $reference): ?array
    {
        $start = $reference->copy()->startOfMonth();

        return match ($frequency) {
            'monthly' => [
                $start->copy(),
                $start->copy()->endOfMonth(),
                $start->format('m/Y'),
            ],
            'quarterly' => $this->quarterRange($start),
            'yearly' => [
                $start->copy()->startOfYear(),
                $start->copy()->endOfYear(),
                $start->format('Y'),
            ],
            default => null,
        };
    }

    private function quarterRange(Carbon $start): array
    {
        $quarter = (int) ceil($start->month / 3);
        $quarterStartMonth = (($quarter - 1) * 3) + 1;
        $quarterStart = Carbon::create($start->year, $quarterStartMonth, 1)->startOfMonth();
        $quarterEnd = $quarterStart->copy()->addMonths(2)->endOfMonth();

        return [
            $quarterStart,
            $quarterEnd,
            sprintf('Q%d %d', $quarter, $start->year),
        ];
    }

    /**
     * Son tarih hesapla
     * 
     * ÖNEMLİ - DÖNEM TÜRLERİNE GÖRE SON TARİH:
     * - Aylık beyannameler: Dönem sonundan BİR SONRAKI AYDA
     *   Örnek: Kasım 2025 KDV → 26 Aralık 2025
     * 
     * - Çeyreklik beyannameler (Geçici Vergi): Dönem sonundan İKİ AY SONRA
     *   Örnek: Q3 2025 (Tem-Ağu-Eyl) → 17 Kasım 2025
     * 
     * - Yıllık beyannameler: Takip eden yılın belirli aylarında
     *   Örnek: 2024 Kurumlar → 25 Nisan 2025
     * 
     * @param Carbon $periodEnd Dönem sonu tarihi
     * @param int $day Ayın kaçıncı günü
     * @param string $frequency Frekans türü (monthly, quarterly, yearly)
     * @return Carbon Son tarih
     */
    private function calculateDueDate(Carbon $periodEnd, int $day, string $frequency = 'monthly'): Carbon
    {
        // Frekansa göre kaç ay sonra?
        $monthsToAdd = match ($frequency) {
            'monthly' => 1,      // Aylık: 1 ay sonra
            'quarterly' => 2,    // Çeyreklik: 2 ay sonra
            'yearly' => 4,       // Yıllık: genelde Nisan (4 ay sonra) ama özel hesaplanır
            default => 1,
        };

        // Sonraki aya geç
        $dueMonth = $periodEnd->copy()->addMonths($monthsToAdd)->startOfMonth();
        
        // O ayın kaç günü var kontrol et
        $safeDay = max(1, min($day, $dueMonth->daysInMonth));

        return $dueMonth->day($safeDay);
    }
}

