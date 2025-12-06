<?php

namespace App\Http\Controllers;

use App\Models\TaxCalendar;
use App\Services\TaxCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TaxCalendarController extends Controller
{
    public function __construct(
        protected TaxCalendarService $taxCalendarService
    ) {}

    /**
     * Resmi Vergi Takvimi listesi
     */
    public function index(Request $request): View
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = TaxCalendar::active()
            ->forYear($year)
            ->when($month, fn ($q, $m) => $q->where('month', $m))
            ->orderBy('due_date');

        $items = $query->get();

        // Aya göre grupla
        $groupedByMonth = $items->groupBy('month');

        // Yıl seçenekleri
        $years = TaxCalendar::distinct()->pluck('year')->sort()->values();

        // İstatistikler
        $today = Carbon::today();
        $stats = [
            'total' => TaxCalendar::active()->forYear($year)->count(),
            'upcoming_7' => TaxCalendar::active()->withinDays(7)->count(),
            'today' => TaxCalendar::active()->dueToday()->count(),
        ];

        // Eksik yıllar
        $missingYears = $this->taxCalendarService->getMissingYears();

        return view('tax-calendar.index', [
            'items' => $items,
            'groupedByMonth' => $groupedByMonth,
            'year' => $year,
            'month' => $month,
            'years' => $years,
            'stats' => $stats,
            'missingYears' => $missingYears,
        ]);
    }

    /**
     * Yeni yıl için vergi takvimi oluştur
     */
    public function generate(Request $request): RedirectResponse
    {
        $year = $request->input('year', now()->year + 1);

        // Yıl validasyonu
        if ($year < 2024 || $year > 2030) {
            return back()->with('warning', 'Geçersiz yıl. 2024-2030 arasında bir yıl seçin.');
        }

        $result = $this->taxCalendarService->generateForYear($year);

        if ($result['created'] > 0) {
            return back()->with('status', "{$year} yılı için {$result['created']} vergi takvimi girişi oluşturuldu.");
        } else {
            return back()->with('warning', "{$year} yılı için tüm veriler zaten mevcut.");
        }
    }

    /**
     * Belirli bir yılın verilerini sil
     */
    public function deleteYear(Request $request): RedirectResponse
    {
        $year = $request->input('year');

        if (!$year) {
            return back()->with('warning', 'Yıl belirtilmedi.');
        }

        $deleted = $this->taxCalendarService->deleteYear($year);

        return back()->with('status', "{$year} yılı için {$deleted} kayıt silindi.");
    }

    /**
     * API: Belirli ay için takvim verileri
     */
    public function apiMonth(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $items = TaxCalendar::active()
            ->forMonth($year, $month)
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn ($item) => $item->due_date->format('Y-m-d'));

        $monthNames = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
        ];

        return response()->json([
            'year' => $year,
            'month' => $month,
            'month_name' => $monthNames[$month] . ' ' . $year,
            'data' => $items,
        ]);
    }

    /**
     * API: Yaklaşan vergi tarihleri
     */
    public function apiUpcoming(Request $request): JsonResponse
    {
        $days = $request->input('days', 14);

        $items = TaxCalendar::active()
            ->withinDays($days)
            ->orderBy('due_date')
            ->get();

        $today = Carbon::today();

        return response()->json([
            'count' => $items->count(),
            'today_count' => $items->where('due_date', $today)->count(),
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'due_date' => $item->due_date->format('Y-m-d'),
                    'due_date_formatted' => $item->due_date->format('d.m.Y'),
                    'days_until' => $item->daysUntilDue(),
                    'is_today' => $item->isToday(),
                    'badge_class' => $item->badge_class,
                    'icon' => $item->icon,
                ];
            }),
        ]);
    }

    /**
     * API: Yeni yıl oluştur (AJAX)
     */
    public function apiGenerate(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year + 1);

        if ($year < 2024 || $year > 2030) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz yıl. 2024-2030 arasında bir yıl seçin.',
            ], 400);
        }

        $result = $this->taxCalendarService->generateForYear($year);

        return response()->json([
            'success' => true,
            'message' => "{$year} yılı için {$result['created']} giriş oluşturuldu, {$result['skipped']} giriş zaten mevcuttu.",
            'result' => $result,
        ]);
    }
}
