<?php

namespace App\Console\Commands;

use App\Models\Firm;
use App\Services\FirmInvoiceBackfillService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncHistoricalInvoices extends Command
{
    protected $signature = 'app:sync-historical-invoices {--firm=} {--up-to=}';

    protected $description = 'Sozlesme baslangic tarihine gore eksik faturalari olusturur';

    public function handle(): int
    {
        $firmId = $this->option('firm');
        $upToOption = $this->option('up-to');

        $upTo = null;

        if ($upToOption) {
            try {
                $upTo = Carbon::createFromFormat('Y-m', $upToOption)->startOfMonth();
            } catch (\Exception $exception) {
                $this->error('Gecersiz --up-to degeri. Format: YYYY-MM');
                return self::INVALID;
            }
        }

        $service = new FirmInvoiceBackfillService();

        if ($firmId) {
            $firm = Firm::find($firmId);

            if (! $firm) {
                $this->error("Firma bulunamadi (ID: {$firmId})");
                return self::FAILURE;
            }

            if (! $firm->contract_start_at) {
                $this->warn('Bu firmaya sozlesme baslangic tarihi eklenmemis.');
            }

            $result = $service->syncFirm($firm, $upTo);

            $this->info("Firma #{$firm->id} icin {$result['created']} fatura uretildi.");
            if (! empty($result['months'])) {
                $this->line('Olusturulan aylar: ' . implode(', ', $result['months']));
            }

            return self::SUCCESS;
        }

        $summary = $service->syncAll($upTo);

        $this->info("{$summary['firms']} firma kontrol edildi, {$summary['invoices_created']} fatura olusturuldu.");

        foreach ($summary['details'] as $firmId => $months) {
            $this->line("#{$firmId}: " . implode(', ', $months));
        }

        return self::SUCCESS;
    }
}
