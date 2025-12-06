<?php

namespace App\Services;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceGenerationService
{
    protected int $invoiceDay;
    protected int $dueDays;

    public function __construct()
    {
        $this->invoiceDay = (int) Setting::getValue('invoice_day', '1');
        $this->dueDays = (int) Setting::getValue('invoice_due_days', '10');
    }

    public function ensureMonthlyInvoice(Firm $firm, Carbon $month): ?Invoice
    {
        if ($firm->contract_start_at && $month->copy()->endOfMonth()->lt($firm->contract_start_at->startOfDay())) {
            return null;
        }

        $invoiceDate = $this->invoiceDateForMonth($month);
        $amount = $firm->priceForDate($invoiceDate);

        if ($amount <= 0) {
            return null;
        }

        $exists = Invoice::query()
            ->where('firm_id', $firm->id)
            ->whereYear('date', $invoiceDate->year)
            ->whereMonth('date', $invoiceDate->month)
            ->exists();

        if ($exists) {
            return null;
        }

        return DB::transaction(function () use ($firm, $invoiceDate, $amount) {
            $description = 'Aylık muhasebe ücreti ' . $invoiceDate->format('m/Y');

            $invoice = Invoice::create([
                'firm_id' => $firm->id,
                'date' => $invoiceDate,
                'due_date' => $this->dueDateForInvoice($invoiceDate),
                'amount' => $amount,
                'description' => $description,
                'status' => 'unpaid',
            ]);

            // Line item oluştur
            $invoice->lineItems()->create([
                'description' => $description,
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
                'sort_order' => 0,
                'item_type' => 'monthly_fee',
            ]);

            $invoice->transactions()->create([
                'firm_id' => $firm->id,
                'type' => 'debit',
                'amount' => $amount,
                'date' => $invoiceDate,
                'description' => $description,
            ]);

            return $invoice;
        });
    }

    public function invoiceDateForMonth(Carbon $month): Carbon
    {
        $target = $month->copy()->startOfMonth();
        $day = min(max($this->invoiceDay, 1), $target->daysInMonth);

        return $target->copy()->day($day);
    }

    public function dueDateForInvoice(Carbon $invoiceDate): Carbon
    {
        return $invoiceDate->copy()->addDays($this->dueDays);
    }
}
