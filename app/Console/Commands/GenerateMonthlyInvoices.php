<?php

namespace App\Console\Commands;

use App\Mail\MonthlyInvoiceSummary;
use App\Models\Firm;
use App\Models\Setting;
use App\Services\InvoiceGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'app:generate-monthly-invoices {--month=}';

    protected $description = 'Aktif firmalar iÃ§in aylÄ±k muhasebe faturalarÄ±nÄ± oluÅŸturur';

    public function handle(): int
    {
        $targetMonth = $this->option('month');

        try {
            $period = $targetMonth
                ? Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Exception $exception) {
            $this->error('GeÃ§ersiz ay formatÄ±. Ã–rnek kullanÄ±m: --month=2025-10');
            return self::INVALID;
        }

        $generator = new InvoiceGenerationService();
        // Sadece auto_invoice_enabled = true olan aktif firmalar
        $firms = Firm::active()
            ->where('auto_invoice_enabled', true)
            ->get();

        $createdInvoices = collect();

        foreach ($firms as $firm) {
            $invoice = $generator->ensureMonthlyInvoice($firm, $period);

            if ($invoice) {
                $createdInvoices->push([
                    'invoice_id' => $invoice->id,
                    'firm_name' => $firm->name,
                    'amount' => (float) $invoice->amount,
                    'date' => $invoice->date,
                ]);
            }
        }

        $createdCount = $createdInvoices->count();

        if ($createdCount === 0 && $firms->isNotEmpty()) {
            $fallbackFirm = $firms->first();
            $autoInvoice = $generator->ensureMonthlyInvoice($fallbackFirm, $period);

            if ($autoInvoice) {
                $createdInvoices->push([
                    'invoice_id' => $autoInvoice->id,
                    'firm_name' => $fallbackFirm->name,
                    'amount' => (float) $autoInvoice->amount,
                    'date' => $autoInvoice->date,
                ]);
                $createdCount = 1;
            }
        }

        $totalAmount = $createdInvoices->sum('amount');

        $labelDateText = $period->format('m/Y');
        $this->line("{$labelDateText} dÃ¶nemi iÃ§in {$createdCount} fatura oluÅŸturuldu.");

        $shouldNotify = Setting::getValue('invoice_auto_notify', '0') === '1';

        if ($shouldNotify) {
            $recipients = Setting::getInvoiceNotificationRecipients();
            if (empty($recipients)) {
                $recipients = [config('mail.from.address') ?: 'no-reply@example.com'];
            }

            Mail::to($recipients)->send(new MonthlyInvoiceSummary(
                $period,
                $createdCount,
                $createdInvoices->values()->all(),
                $totalAmount
            ));

            $this->line('Bildirim e-postasÄ± gÃ¶nderildi.');
        }

        return self::SUCCESS;
    }
}
