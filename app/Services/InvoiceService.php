<?php

namespace App\Services;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class InvoiceService
{
    /**
     * Filtrelenmiş fatura listesi
     */
    public function getFilteredInvoices(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('firm:id,name')
            ->whereHas('firm') // Silinen firmalar hariç
            ->withSum('payments', 'amount')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['firm_id'] ?? null, fn ($q, $firmId) => $q->where('firm_id', $firmId))
            ->when($filters['date_from'] ?? null, function ($q, $from) {
                try {
                    $start = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
                    $q->where('date', '>=', $start);
                } catch (\Throwable $e) {
                    // Geçersiz tarih, filtre uygulanmaz
                }
            })
            ->when($filters['date_to'] ?? null, function ($q, $to) {
                try {
                    $end = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
                    $q->where('date', '<=', $end);
                } catch (\Throwable $e) {
                    // Geçersiz tarih, filtre uygulanmaz
                }
            })
            ->latest('date')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Fatura oluştur
     */
    public function createInvoice(array $data): Invoice
    {
        $invoice = Invoice::create($data);
        
        // Cari hesaba borç kaydı
        $invoice->transactions()->create([
            'firm_id' => $invoice->firm_id,
            'type' => 'debit',
            'amount' => $invoice->amount,
            'date' => $invoice->date,
            'description' => "Fatura: {$invoice->description}",
        ]);
        
        return $invoice;
    }

    /**
     * Fatura güncelle
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        $oldAmount = (float) $invoice->amount;
        
        $invoice->update($data);
        
        // Tutar değiştiyse cari hesabı güncelle
        if (abs($oldAmount - (float) $invoice->amount) > 0.01) {
            $this->updateInvoiceTransaction($invoice, $oldAmount);
        }
        
        return $invoice;
    }

    /**
     * Fatura sil
     */
    public function deleteInvoice(Invoice $invoice): bool
    {
        // Ödenmiş faturaları silme
        if ($invoice->status === 'paid') {
            return false;
        }
        
        // Cari hesap kaydını sil
        $invoice->transactions()->delete();
        
        return $invoice->delete();
    }

    /**
     * Toplu durum güncelleme
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Invoice::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Toplu silme
     */
    public function bulkDelete(array $ids): array
    {
        $invoices = Invoice::whereIn('id', $ids)
            ->whereNotIn('status', ['paid', 'partial'])
            ->get();

        $deletedCount = 0;
        foreach ($invoices as $invoice) {
            $invoice->transactions()->delete();
            $invoice->delete();
            $deletedCount++;
        }

        return [
            'deleted' => $deletedCount,
            'skipped' => count($ids) - $deletedCount,
        ];
    }

    /**
     * Fatura çoğalt
     */
    public function duplicateInvoice(Invoice $invoice): Invoice
    {
        $newNumber = $this->generateNextNumber($invoice);
        
        $duplicate = $invoice->replicate();
        $duplicate->official_number = $newNumber;
        $duplicate->date = Carbon::now();
        $duplicate->due_date = Carbon::now()->addDays((int) Setting::getValue('invoice_due_days', 30));
        $duplicate->status = 'unpaid';
        $duplicate->paid_at = null;
        $duplicate->save();
        
        // Cari hesap kaydı
        $duplicate->transactions()->create([
            'firm_id' => $duplicate->firm_id,
            'type' => 'debit',
            'amount' => $duplicate->amount,
            'date' => $duplicate->date,
            'description' => "Fatura: {$duplicate->description}",
        ]);
        
        return $duplicate;
    }

    /**
     * Aylık toplu fatura oluştur
     */
    public function generateMonthlyInvoices(Carbon $month): array
    {
        $firms = Firm::active()
            ->where('monthly_fee', '>', 0)
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($firms as $firm) {
            // Bu ay zaten fatura var mı?
            $exists = Invoice::where('firm_id', $firm->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $this->createInvoice([
                'firm_id' => $firm->id,
                'date' => $month->copy()->startOfMonth(),
                'due_date' => $month->copy()->endOfMonth(),
                'amount' => $firm->monthly_fee,
                'description' => $month->locale('tr')->isoFormat('MMMM YYYY') . ' ayı hizmet bedeli',
                'official_number' => $this->generateMonthlyNumber($firm, $month),
                'status' => 'unpaid',
            ]);
            
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Firma için ödenmemiş faturaları getir
     */
    public function getOutstandingInvoices(int $firmId): Collection
    {
        return Invoice::where('firm_id', $firmId)
            ->outstanding()
            ->withSum('payments', 'amount')
            ->orderByDesc('date')
            ->get()
            ->map(function ($invoice) {
                $invoice->remaining_amount = max(
                    0,
                    (float) $invoice->amount - (float) ($invoice->payments_sum_amount ?? 0)
                );
                return $invoice;
            })
            ->filter(fn ($invoice) => $invoice->remaining_amount > 0.009);
    }

    /**
     * Cari hesap işlemini güncelle
     */
    protected function updateInvoiceTransaction(Invoice $invoice, float $oldAmount): void
    {
        $transaction = $invoice->transactions()
            ->where('type', 'debit')
            ->first();

        if ($transaction) {
            $transaction->update(['amount' => $invoice->amount]);
        }
    }

    /**
     * Sıradaki fatura numarasını oluştur
     */
    protected function generateNextNumber(Invoice $invoice): string
    {
        $prefix = preg_replace('/\d+$/', '', $invoice->official_number);
        $lastNumber = Invoice::where('official_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('official_number');
        
        if ($lastNumber) {
            $num = (int) preg_replace('/[^\d]/', '', $lastNumber);
            return $prefix . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        }
        
        return $prefix . '0001';
    }

    /**
     * Aylık fatura numarası oluştur
     */
    protected function generateMonthlyNumber(Firm $firm, Carbon $month): string
    {
        $prefix = 'AF-' . $month->format('Ym') . '-';
        $count = Invoice::where('official_number', 'like', $prefix . '%')->count();
        
        return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
