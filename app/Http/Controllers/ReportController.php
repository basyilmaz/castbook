<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function balances(Request $request): View
    {
        $status = $request->get('status');
        $perPage = $this->perPage($request->integer('per_page'), [10, 25, 50, 100], 25);

        $baseQuery = Firm::query()
            ->select('firms.*')
            ->when($status, fn ($query) => $query->where('status', $status));

        $transactionTotals = DB::table('transactions')
            ->selectRaw('firm_id,
                SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) AS debit_total,
                SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) AS credit_total')
            ->groupBy('firm_id');

        $firms = (clone $baseQuery)
            ->leftJoinSub($transactionTotals, 'tx', fn ($join) => $join->on('tx.firm_id', '=', 'firms.id'))
            ->selectRaw('COALESCE(tx.debit_total, 0) AS debit_total')
            ->selectRaw('COALESCE(tx.credit_total, 0) AS credit_total')
            ->orderBy('firms.name')
            ->paginate($perPage)
            ->through(function ($firm) {
                $firm->balance_total = (float) $firm->debit_total - (float) $firm->credit_total;
                return $firm;
            })
            ->withQueryString();

        $totalsRow = DB::table('transactions')
            ->join('firms', 'firms.id', '=', 'transactions.firm_id')
            ->when($status, fn ($query) => $query->where('firms.status', $status))
            ->selectRaw('
                SUM(CASE WHEN transactions.type = "debit" THEN transactions.amount ELSE 0 END) AS debit_total,
                SUM(CASE WHEN transactions.type = "credit" THEN transactions.amount ELSE 0 END) AS credit_total
            ')
            ->first();

        $totals = [
            'debit' => (float) ($totalsRow->debit_total ?? 0),
            'credit' => (float) ($totalsRow->credit_total ?? 0),
            'balance' => (float) ($totalsRow->debit_total ?? 0) - (float) ($totalsRow->credit_total ?? 0),
        ];

        return view('reports.balances', compact('firms', 'totals', 'status', 'perPage'));
    }

    /**
     * Bakiye raporu CSV export
     */
    public function exportBalances(Request $request): StreamedResponse
    {
        $status = $request->get('status');

        $transactionTotals = DB::table('transactions')
            ->selectRaw('firm_id,
                SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) AS debit_total,
                SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) AS credit_total')
            ->groupBy('firm_id');

        $firms = Firm::query()
            ->select('firms.*')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->leftJoinSub($transactionTotals, 'tx', fn ($join) => $join->on('tx.firm_id', '=', 'firms.id'))
            ->selectRaw('COALESCE(tx.debit_total, 0) AS debit_total')
            ->selectRaw('COALESCE(tx.credit_total, 0) AS credit_total')
            ->orderBy('firms.name')
            ->get();

        return $this->streamCsv('bakiye_raporu', ['Firma Adı', 'Vergi No', 'Toplam Borç', 'Toplam Tahsilat', 'Kalan Bakiye', 'Durum'], 
            $firms->map(fn ($firm) => [
                $firm->name,
                $firm->tax_no ?? '-',
                number_format($firm->debit_total, 2, ',', '.'),
                number_format($firm->credit_total, 2, ',', '.'),
                number_format((float) $firm->debit_total - (float) $firm->credit_total, 2, ',', '.'),
                $firm->status === 'active' ? 'Aktif' : 'Pasif',
            ])->toArray()
        );
    }

    public function collections(Request $request): View
    {
        $selectedFirm = $request->integer('firm_id') ?: null;
        $year = $request->integer('year') ?: Carbon::now()->year;
        $perPage = $this->perPage($request->integer('per_page'), [6, 12, 24], 12);
        $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endOfYear = (clone $startOfYear)->endOfYear();

        $payments = Payment::query()
            ->when($selectedFirm, fn ($query, $firmId) => $query->where('firm_id', $firmId))
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->get();

        $grouped = $payments
            ->groupBy(function ($payment) {
                return Carbon::parse($payment->date)->format('Y-m');
            })
            ->map(function ($items, $period) {
                return (object) [
                    'period' => $period,
                    'total_amount' => $items->sum('amount'),
                    'payment_count' => $items->count(),
                ];
            })
            ->sortByDesc('period')
            ->values();

        $page = $request->integer('page') ?: 1;
        $paginatedItems = $grouped->forPage($page, $perPage)->values();

        $monthly = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $grouped->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $totalsRow = [
            'total_amount' => $payments->sum('amount'),
            'payment_count' => $payments->count(),
        ];

        $firms = Firm::orderBy('name')->select('id', 'name')->get();
        $years = collect(range(Carbon::now()->year - 5, Carbon::now()->year))->sortDesc();

        $totals = [
            'year_total' => (float) ($totalsRow['total_amount'] ?? 0),
            'payment_count' => (int) ($totalsRow['payment_count'] ?? 0),
        ];

        return view('reports.collections', compact(
            'monthly',
            'firms',
            'selectedFirm',
            'year',
            'years',
            'totals',
            'perPage'
        ));
    }

    /**
     * Tahsilat raporu CSV export
     */
    public function exportCollections(Request $request): StreamedResponse
    {
        $selectedFirm = $request->integer('firm_id') ?: null;
        $year = $request->integer('year') ?: Carbon::now()->year;
        $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endOfYear = (clone $startOfYear)->endOfYear();

        $payments = Payment::query()
            ->with('firm:id,name')
            ->when($selectedFirm, fn ($query, $firmId) => $query->where('firm_id', $firmId))
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->orderByDesc('date')
            ->get();

        return $this->streamCsv("tahsilat_raporu_{$year}", ['Tarih', 'Firma', 'Tutar', 'Yöntem', 'Not'], 
            $payments->map(fn ($p) => [
                $p->date?->format('d.m.Y'),
                $p->firm->name ?? '-',
                number_format($p->amount, 2, ',', '.'),
                $p->method ?? '-',
                $p->notes ?? '-',
            ])->toArray()
        );
    }

    public function overdues(): View
    {
        $today = Carbon::today();

        $invoices = Invoice::query()
            ->with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($query) use ($today) {
                $query->whereNotNull('due_date')
                    ->where('due_date', '<', $today)
                    ->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('due_date')->where('date', '<', $today);
                    });
            })
            ->orderBy('due_date')
            ->orderBy('date')
            ->get()
            ->map(function ($invoice) use ($today) {
                $reference = $invoice->due_date ?? $invoice->date;
                $invoice->days_overdue = (int) ($reference?->diffInDays($today) ?? 0);
                return $invoice;
            });

        return view('reports.overdues', compact('invoices', 'today'));
    }

    /**
     * Gecikmiş faturalar CSV export
     */
    public function exportOverdues(): StreamedResponse
    {
        $today = Carbon::today();

        $invoices = Invoice::query()
            ->with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($query) use ($today) {
                $query->whereNotNull('due_date')
                    ->where('due_date', '<', $today)
                    ->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('due_date')->where('date', '<', $today);
                    });
            })
            ->orderBy('due_date')
            ->get();

        return $this->streamCsv('geciken_faturalar', ['Firma', 'Fatura No', 'Fatura Tarihi', 'Vade Tarihi', 'Tutar', 'Geciken Gün', 'Durum'], 
            $invoices->map(fn ($inv) => [
                $inv->firm->name ?? '-',
                $inv->official_number ?? '#' . $inv->id,
                $inv->date?->format('d.m.Y'),
                $inv->due_date?->format('d.m.Y') ?? '-',
                number_format($inv->amount, 2, ',', '.'),
                (int) (($inv->due_date ?? $inv->date)?->diffInDays($today) ?? 0),
                $inv->status === 'partial' ? 'Kısmi' : 'Ödenmedi',
            ])->toArray()
        );
    }

    public function invoices(Request $request): View
    {
        $selectedFirm = $request->integer('firm_id') ?: null;
        $year = $request->integer('year') ?: Carbon::now()->year;
        $perPage = $this->perPage($request->integer('per_page'), [10, 25, 50, 100], 25);

        $baseQuery = Invoice::query()
            ->when($selectedFirm, fn ($query, $firmId) => $query->where('firm_id', $firmId))
            ->whereYear('date', $year);

        $summaryRow = (clone $baseQuery)
            ->selectRaw('COUNT(*) AS count')
            ->selectRaw('SUM(amount) AS total_amount')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) AS paid_amount')
            ->selectRaw('SUM(CASE WHEN status = "unpaid" THEN amount ELSE 0 END) AS unpaid_amount')
            ->first();

        $statusBreakdown = (clone $baseQuery)
            ->select('status')
            ->selectRaw('COUNT(*) AS count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $invoices = (clone $baseQuery)
            ->with('firm:id,name')
            ->orderByDesc('date')
            ->paginate($perPage)
            ->withQueryString();

        $firms = Firm::orderBy('name')->select('id', 'name')->get();
        $years = collect(range(Carbon::now()->year - 5, Carbon::now()->year))->sortDesc();

        $summary = [
            'count' => (int) ($summaryRow->count ?? 0),
            'total_amount' => (float) ($summaryRow->total_amount ?? 0),
            'paid_amount' => (float) ($summaryRow->paid_amount ?? 0),
            'unpaid_amount' => (float) ($summaryRow->unpaid_amount ?? 0),
        ];

        return view('reports.invoices', compact(
            'invoices',
            'summary',
            'statusBreakdown',
            'firms',
            'selectedFirm',
            'year',
            'years',
            'perPage'
        ));
    }

    /**
     * Fatura raporu CSV export
     */
    public function exportInvoices(Request $request): StreamedResponse
    {
        $selectedFirm = $request->integer('firm_id') ?: null;
        $year = $request->integer('year') ?: Carbon::now()->year;

        $invoices = Invoice::query()
            ->with('firm:id,name')
            ->when($selectedFirm, fn ($query, $firmId) => $query->where('firm_id', $firmId))
            ->whereYear('date', $year)
            ->orderByDesc('date')
            ->get();

        $statusLabels = [
            'paid' => 'Ödendi',
            'partial' => 'Kısmi',
            'unpaid' => 'Ödenmedi',
            'cancelled' => 'İptal',
        ];

        return $this->streamCsv("fatura_raporu_{$year}", ['Fatura No', 'Firma', 'Tarih', 'Vade Tarihi', 'Tutar', 'Durum', 'Açıklama'], 
            $invoices->map(fn ($inv) => [
                $inv->official_number ?? '#' . $inv->id,
                $inv->firm->name ?? '-',
                $inv->date?->format('d.m.Y'),
                $inv->due_date?->format('d.m.Y') ?? '-',
                number_format($inv->amount, 2, ',', '.'),
                $statusLabels[$inv->status] ?? $inv->status,
                $inv->description ?? '-',
            ])->toArray()
        );
    }

    /**
     * CSV dosyası oluştur ve stream et
     */
    private function streamCsv(string $filename, array $headers, array $data): StreamedResponse
    {
        $filename = $filename . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($headers, $data) {
            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Başlıklar
            fputcsv($output, $headers, ';');
            
            // Veriler
            foreach ($data as $row) {
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Bakiye raporu PDF export
     */
    public function pdfBalances(Request $request)
    {
        $status = $request->get('status');

        $transactionTotals = DB::table('transactions')
            ->selectRaw('firm_id,
                SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) AS debit_total,
                SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) AS credit_total')
            ->groupBy('firm_id');

        $firms = Firm::query()
            ->select('firms.*')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->leftJoinSub($transactionTotals, 'tx', fn ($join) => $join->on('tx.firm_id', '=', 'firms.id'))
            ->selectRaw('COALESCE(tx.debit_total, 0) AS debit_total')
            ->selectRaw('COALESCE(tx.credit_total, 0) AS credit_total')
            ->orderBy('firms.name')
            ->get();

        $totals = [
            'debit' => $firms->sum('debit_total'),
            'credit' => $firms->sum('credit_total'),
            'balance' => $firms->sum('debit_total') - $firms->sum('credit_total'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.balances', [
            'firms' => $firms,
            'totals' => $totals,
            'title' => 'Müşteri Bakiye Raporu',
            'companyName' => \App\Models\Setting::getValue('company_name', 'CastBook'),
        ]);

        return $pdf->download('bakiye_raporu_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Fatura raporu PDF export
     */
    public function pdfInvoices(Request $request)
    {
        $selectedFirm = $request->integer('firm_id') ?: null;
        $year = $request->integer('year') ?: Carbon::now()->year;

        $invoices = Invoice::query()
            ->with('firm:id,name')
            ->when($selectedFirm, fn ($query, $firmId) => $query->where('firm_id', $firmId))
            ->whereYear('date', $year)
            ->orderByDesc('date')
            ->get();

        $summary = [
            'count' => $invoices->count(),
            'total_amount' => $invoices->sum('amount'),
            'paid_amount' => $invoices->where('status', 'paid')->sum('amount'),
            'unpaid_amount' => $invoices->whereIn('status', ['unpaid', 'partial'])->sum('amount'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoices', [
            'invoices' => $invoices,
            'summary' => $summary,
            'year' => $year,
            'title' => "{$year} Yılı Fatura Raporu",
            'companyName' => \App\Models\Setting::getValue('company_name', 'CastBook'),
        ]);

        return $pdf->download("fatura_raporu_{$year}_" . now()->format('Ymd') . '.pdf');
    }

    /**
     * Gecikmiş ödemeler PDF export
     */
    public function pdfOverdues()
    {
        $today = Carbon::today();

        $invoices = Invoice::query()
            ->with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($query) use ($today) {
                $query->whereNotNull('due_date')
                    ->where('due_date', '<', $today)
                    ->orWhere(function ($inner) use ($today) {
                        $inner->whereNull('due_date')->where('date', '<', $today);
                    });
            })
            ->orderBy('due_date')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.overdues', [
            'invoices' => $invoices,
            'today' => $today,
            'title' => 'Gecikmiş Ödemeler Raporu',
            'companyName' => \App\Models\Setting::getValue('company_name', 'CastBook'),
        ]);

        return $pdf->download('geciken_odemeler_' . now()->format('Ymd') . '.pdf');
    }

    private function perPage(?int $requested, array $allowed, int $default): int
    {
        if (! $requested || ! in_array($requested, $allowed, true)) {
            return $default;
        }

        return $requested;
    }
}
