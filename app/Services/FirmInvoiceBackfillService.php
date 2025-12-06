<?php

namespace App\Services;

use App\Models\Firm;
use Illuminate\Support\Carbon;

class FirmInvoiceBackfillService
{
    protected InvoiceGenerationService $generator;

    public function __construct(?InvoiceGenerationService $generator = null)
    {
        $this->generator = $generator ?: new InvoiceGenerationService();
    }

    public function syncFirm(Firm $firm, ?Carbon $upTo = null): array
    {
        $result = [
            'created' => 0,
            'months' => [],
            'skipped_reason' => null,
        ];

        $hasPriceHistory = $firm->priceHistories()->exists();

        if (! $hasPriceHistory && $firm->monthly_fee <= 0) {
            $result['skipped_reason'] = 'no_price_history';
            return $result;
        }

        if (! $firm->contract_start_at) {
            $result['skipped_reason'] = 'missing_contract_start';
            return $result;
        }

        $createdMonths = [];
        $target = ($upTo ?: now())->startOfMonth();
        $cursor = $firm->contract_start_at->copy()->startOfMonth();

        if ($cursor->gt($target)) {
            $result['skipped_reason'] = 'contract_in_future';
            return $result;
        }

        while ($cursor->lte($target)) {
            $invoice = $this->generator->ensureMonthlyInvoice($firm, $cursor);

            if ($invoice) {
                $createdMonths[] = $cursor->format('Y-m');
            }

            $cursor->addMonth();
        }

        $firm->forceFill([
            'initial_debt_synced_at' => now(),
        ])->save();

        $result['created'] = count($createdMonths);
        $result['months'] = $createdMonths;

        return $result;
    }

    public function syncAll(?Carbon $upTo = null): array
    {
        $summary = [
            'firms' => 0,
            'invoices_created' => 0,
            'details' => [],
            'skipped' => [],
        ];

        Firm::query()->chunk(50, function ($firms) use (&$summary, $upTo) {
            foreach ($firms as $firm) {
                $result = $this->syncFirm($firm, $upTo);

                $summary['firms']++;
                $summary['invoices_created'] += $result['created'];
                if (! empty($result['months'])) {
                    $summary['details'][$firm->id] = $result['months'];
                } elseif ($result['skipped_reason']) {
                    $summary['skipped'][$firm->id] = $result['skipped_reason'];
                }
            }
        });

        return $summary;
    }
}
