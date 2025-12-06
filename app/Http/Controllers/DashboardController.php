<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TaxCalendar;
use App\Models\TaxDeclaration;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        $firms = Firm::query()
            ->withSum(['transactions as debit_total' => fn ($query) => $query->where('type', 'debit')], 'amount')
            ->withSum(['transactions as credit_total' => fn ($query) => $query->where('type', 'credit')], 'amount')
            ->with(['invoices' => fn ($query) => $query->whereIn('status', ['unpaid', 'partial'])->select('id', 'firm_id', 'date', 'due_date', 'status')])
            ->orderBy('name')
            ->get();

        $totalReceivable = 0;
        $overdueFirmCount = 0;

        $firmRows = $firms->map(function (Firm $firm) use ($now, &$totalReceivable, &$overdueFirmCount) {
            $totalDebt = (float) ($firm->debit_total ?? 0);
            $totalCollection = (float) ($firm->credit_total ?? 0);
            $remaining = $totalDebt - $totalCollection;

            $hasOverdueInvoice = $firm->invoices->contains(function ($invoice) use ($now) {
                $dueDate = $invoice->due_date ?? $invoice->date;
                return $dueDate ? Carbon::parse($dueDate)->isBefore($now->startOfDay()) : false;
            });

            $hasPartialInvoice = $firm->invoices->contains(fn ($invoice) => $invoice->status === 'partial');

            if ($remaining <= 0) {
                $statusLabel = 'Ödendi';
                $badgeClass = 'success';
            } elseif ($hasOverdueInvoice) {
                $statusLabel = 'Gecikmiş';
                $badgeClass = 'danger';
                $overdueFirmCount++;
            } elseif ($hasPartialInvoice) {
                $statusLabel = 'Kısmi Ödeme';
                $badgeClass = 'warning text-dark';
            } else {
                $statusLabel = 'Bekliyor';
                $badgeClass = 'warning text-dark';
            }

            $totalReceivable += max($remaining, 0);

            return [
                'id' => $firm->id,
                'name' => $firm->name,
                'total_debt' => $totalDebt,
                'total_collection' => $totalCollection,
                'remaining' => $remaining,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
            ];
        });

        $monthlyCollection = (float) Payment::whereBetween('date', [
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth(),
        ])->sum('amount');

        $upcomingInvoicesCount = Invoice::whereIn('status', ['unpaid', 'partial'])
            ->where(function ($query) use ($now) {
                $query->whereBetween('due_date', [
                    $now->copy()->startOfDay(),
                    $now->copy()->addDays(7)->endOfDay(),
                ])->orWhere(function ($inner) use ($now) {
                    $inner->whereNull('due_date')
                        ->whereBetween('date', [
                            $now->copy()->startOfDay(),
                            $now->copy()->addDays(7)->endOfDay(),
                        ]);
                });
            })
            ->count();

        $taxSummaryQuery = TaxDeclaration::query()
            ->whereMonth('due_date', $now->month)
            ->whereYear('due_date', $now->year);

        $taxSummary = [
            'total' => (clone $taxSummaryQuery)->count(),
            'pending' => (clone $taxSummaryQuery)->where('status', 'pending')->count(),
            'overdue' => (clone $taxSummaryQuery)
                ->whereIn('status', ['pending', 'filed'])
                ->where('due_date', '<', $now->toDateString())
                ->count(),
        ];

        $metrics = [
            'total_receivable' => $totalReceivable,
            'monthly_collection' => $monthlyCollection,
            'overdue_firm_count' => $overdueFirmCount,
            'upcoming_invoice_count' => $upcomingInvoicesCount,
            'tax_summary' => $taxSummary,
        ];


        // Yaklaşan beyannameler (bugün dahil 7 gün içinde) - Bugün olanlar önce
        $upcomingDeclarations = TaxDeclaration::query()
            ->with(['firm:id,name', 'taxForm:id,code,name'])
            ->whereIn('status', ['pending', 'filed'])
            ->whereBetween('due_date', [
                $now->toDateString(),
                $now->copy()->addDays(7)->toDateString()
            ])
            ->orderByRaw("CASE WHEN DATE(due_date) = ? THEN 0 ELSE 1 END", [$now->toDateString()])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Bugün son günü olan beyanname sayısı
        $todayDueCount = TaxDeclaration::query()
            ->whereIn('status', ['pending', 'filed'])
            ->whereDate('due_date', $now->toDateString())
            ->count();

        // Metrics'e ekle (tax_summary altına)
        $taxSummary['today'] = $todayDueCount;

        // Gecikmiş beyannameler (overdue)
        $overdueDeclarations = TaxDeclaration::query()
            ->with(['firm:id,name', 'taxForm:id,code,name'])
            ->whereIn('status', ['pending', 'filed'])
            ->where('due_date', '<', $now->toDateString())
            ->orderBy('due_date')
            ->get();

        // Son 6 aylık gelir ve fatura verileri (Chart için)
        $chartData = $this->getMonthlyChartData($now);

        // GİB Resmi Vergi Takvimi (önümüzdeki 14 gün)
        $gibCalendar = TaxCalendar::active()
            ->withInDays(14)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Gelir Tahmini (Önümüzdeki 3 ay)
        $forecast = $this->getForecastData($now);

        return view('dashboard', [
            'metrics' => $metrics,
            'firms' => $firmRows,
            'upcomingDeclarations' => $upcomingDeclarations,
            'overdueDeclarations' => $overdueDeclarations,
            'chartData' => $chartData,
            'gibCalendar' => $gibCalendar,
            'forecast' => $forecast,
        ]);
    }

    /**
     * Önümüzdeki 3 ay için gelir tahmini
     */
    protected function getForecastData(Carbon $now): array
    {
        // Aktif firma sayısı ve toplam aylık ücret
        $activeFirms = Firm::where('status', 'active')->get();
        $totalMonthlyFee = $activeFirms->sum('monthly_fee');
        $firmCount = $activeFirms->count();

        // Son 3 ayın ortalama tahsilat oranı
        $last3MonthsStart = $now->copy()->subMonths(3)->startOfMonth();
        $invoicedLast3 = Invoice::where('date', '>=', $last3MonthsStart)->sum('amount');
        $collectedLast3 = Payment::where('date', '>=', $last3MonthsStart)->sum('amount');
        $collectionRate = $invoicedLast3 > 0 ? min(($collectedLast3 / $invoicedLast3) * 100, 100) : 85;

        // Önümüzdeki 3 ay tahmini
        $months = [];
        for ($i = 1; $i <= 3; $i++) {
            $month = $now->copy()->addMonths($i);
            $expectedInvoice = $totalMonthlyFee;
            $expectedCollection = $expectedInvoice * ($collectionRate / 100);

            $months[] = [
                'month' => $month->translatedFormat('F Y'),
                'short_month' => $month->translatedFormat('M'),
                'expected_invoice' => $expectedInvoice,
                'expected_collection' => $expectedCollection,
            ];
        }

        return [
            'firm_count' => $firmCount,
            'monthly_total' => $totalMonthlyFee,
            'collection_rate' => round($collectionRate, 1),
            'months' => $months,
            'total_forecast' => array_sum(array_column($months, 'expected_collection')),
        ];
    }

    /**
     * Son 6 aylık gelir ve fatura verilerini hesapla
     */
    protected function getMonthlyChartData(Carbon $now): array
    {
        $labels = [];
        $invoiceAmounts = [];
        $paymentAmounts = [];
        $invoiceCounts = [];

        // Son 6 ay için veri topla
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            // Ay etiketi (Türkçe)
            $labels[] = $month->locale('tr')->isoFormat('MMM YYYY');

            // Fatura tutarları
            $invoiceTotal = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');
            $invoiceAmounts[] = round((float) $invoiceTotal, 2);

            // Tahsilat tutarları
            $paymentTotal = Payment::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');
            $paymentAmounts[] = round((float) $paymentTotal, 2);

            // Fatura sayıları
            $invoiceCount = Invoice::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->count();
            $invoiceCounts[] = $invoiceCount;
        }

        return [
            'labels' => $labels,
            'invoiceAmounts' => $invoiceAmounts,
            'paymentAmounts' => $paymentAmounts,
            'invoiceCounts' => $invoiceCounts,
        ];
    }
}
