<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\TaxDeclaration;
use App\Models\TaxForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TaxDeclarationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['firm_id', 'tax_form_id', 'status', 'year', 'month']);
        $perPage = 20;

        // Varsayılan olarak sadece bekleyen beyannameleri göster
        if (!$request->has('status') && !$request->has('firm_id')) {
            $filters['status'] = 'pending';
        }

        $query = TaxDeclaration::query()
            ->with(['firm:id,name', 'taxForm:id,code,name'])
            ->whereHas('firm') // Silinen firmalar hariç
            ->when($filters['firm_id'] ?? null, fn ($q, $firmId) => $q->where('firm_id', $firmId))
            ->when($filters['tax_form_id'] ?? null, fn ($q, $formId) => $q->where('tax_form_id', $formId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['year'] ?? null, fn ($q, $year) => $q->whereYear('due_date', $year))
            ->when($filters['month'] ?? null, fn ($q, $month) => $q->whereMonth('due_date', $month))
            ->orderByDesc('due_date');

        $declarations = $query->paginate($perPage)->withQueryString();

        $firms = Firm::orderBy('name')->get(['id', 'name']);
        $forms = TaxForm::active()->orderBy('code')->get(['id', 'code', 'name']);

        // İstatistikler (sadece aktif firmalar)
        $today = Carbon::today();
        $stats = [
            'total' => TaxDeclaration::whereHas('firm')->count(),
            'pending' => TaxDeclaration::whereHas('firm')->where('status', 'pending')->count(),
            'overdue' => TaxDeclaration::whereHas('firm')
                ->where('status', 'pending')
                ->where('due_date', '<', $today)
                ->count(),
            'today' => TaxDeclaration::whereHas('firm')
                ->whereDate('due_date', $today)
                ->where('status', 'pending')
                ->count(),
            'this_week' => TaxDeclaration::whereHas('firm')
                ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
                ->where('status', 'pending')
                ->count(),
        ];

        return view('tax-declarations.index', [
            'declarations' => $declarations,
            'filters' => $filters,
            'firms' => $firms,
            'forms' => $forms,
            'stats' => $stats,
        ]);
    }

    /**
     * Takvim görünümü için verileri getir (AJAX)
     */
    public function calendar(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $declarations = TaxDeclaration::query()
            ->with(['firm:id,name', 'taxForm:id,code,name'])
            ->whereHas('firm') // Silinen firmalar hariç
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($d) => $d->due_date->format('Y-m-d'));

        $calendarData = [];
        $today = Carbon::today();

        foreach ($declarations as $date => $items) {
            $calendarData[$date] = $items->map(function ($d) use ($today) {
                $isOverdue = $d->due_date && $d->due_date->lt($today) && $d->status === 'pending';
                
                return [
                    'id' => $d->id,
                    'firm_name' => $d->firm?->name ?? '-',
                    'tax_form_code' => $d->taxForm?->code ?? '-',
                    'tax_form_name' => $d->taxForm?->name ?? '-',
                    'period_label' => $d->period_label,
                    'status' => $d->status,
                    'is_overdue' => $isOverdue,
                ];
            });
        }

        return response()->json([
            'year' => $year,
            'month' => $month,
            'month_name' => $startDate->locale('tr')->isoFormat('MMMM YYYY'),
            'data' => $calendarData,
        ]);
    }

    public function edit(TaxDeclaration $taxDeclaration): View
    {
        return view('tax-declarations.edit', [
            'declaration' => $taxDeclaration->load(['firm', 'taxForm']),
        ]);
    }

    public function update(Request $request, TaxDeclaration $taxDeclaration): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,submitted'],
            'notes' => ['nullable', 'string'],
        ]);

        // Verildi işaretlenince tarih kaydet
        if ($data['status'] === 'submitted' && !$taxDeclaration->filed_at) {
            $data['filed_at'] = now();
        }

        $taxDeclaration->update($data);

        return redirect()
            ->route('tax-declarations.index')
            ->with('status', 'Beyanname güncellendi.');
    }

    /**
     * AJAX endpoint - Hızlı durum değiştirme
     */
    public function updateStatus(Request $request, TaxDeclaration $taxDeclaration): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,submitted'],
        ]);

        // Verildi işaretlenince tarih kaydet
        if ($data['status'] === 'submitted' && !$taxDeclaration->filed_at) {
            $data['filed_at'] = now();
        }

        $taxDeclaration->update($data);

        return response()->json([
            'success' => true,
            'message' => $data['status'] === 'submitted' ? 'Beyanname verildi olarak işaretlendi.' : 'Beyanname bekliyor olarak işaretlendi.',
            'declaration' => $taxDeclaration->fresh(['taxForm']),
        ]);
    }

    /**
     * Toplu durum güncelleme - Çoklu beyanname için
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:tax_declarations,id'],
            'status' => ['required', 'in:pending,submitted'],
        ]);

        $updateData = ['status' => $data['status']];

        // Verildi işaretlenince tarih kaydet
        if ($data['status'] === 'submitted') {
            $updateData['filed_at'] = now();
        }

        $count = TaxDeclaration::whereIn('id', $data['ids'])->update($updateData);

        $statusLabel = $data['status'] === 'submitted' ? 'Verildi' : 'Bekliyor';

        return response()->json([
            'success' => true,
            'message' => "{$count} beyanname '{$statusLabel}' olarak güncellendi.",
            'updated_count' => $count,
        ]);
    }

    /**
     * Bugün son günü olan beyannameleri getir (Dashboard API)
     */
    public function todayDue(): JsonResponse
    {
        $today = Carbon::today();
        
        $declarations = TaxDeclaration::query()
            ->with(['firm:id,name', 'taxForm:id,code,name'])
            ->whereDate('due_date', $today)
            ->whereIn('status', ['pending', 'filed'])
            ->get();

        return response()->json([
            'count' => $declarations->count(),
            'declarations' => $declarations,
        ]);
    }

    /**
     * Tek tip beyanname oluştur (belirli form için tüm firmalar)
     */
    public function generateSingle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tax_form_id' => ['required', 'exists:tax_forms,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2030'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $taxForm = TaxForm::findOrFail($data['tax_form_id']);
        $period = Carbon::createFromDate($data['year'], $data['month'], 1);

        // Bu formu kullanan aktif firmalar
        $firms = Firm::active()
            ->where('tax_tracking_enabled', true)
            ->whereHas('taxForms', fn($q) => $q->where('tax_form_id', $taxForm->id)->where('firm_tax_forms.is_active', true))
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($firms as $firm) {
            $result = $this->createDeclarationForFirm($firm, $taxForm, $period);
            if ($result === 'created') {
                $created++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$taxForm->name}: {$created} beyanname oluşturuldu" . ($skipped > 0 ? ", {$skipped} zaten mevcuttu." : "."),
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Tüm beyannameleri toplu oluştur (her firma için kendi aktif formları)
     */
    public function generateAll(Request $request): JsonResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2030'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $period = Carbon::createFromDate($data['year'], $data['month'], 1);

        // Aktif ve beyanname takibi açık firmalar
        $firms = Firm::active()
            ->where('tax_tracking_enabled', true)
            ->with(['taxForms' => fn($q) => $q->where('is_active', true)])
            ->get();

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($firms as $firm) {
            foreach ($firm->taxForms as $taxForm) {
                $result = $this->createDeclarationForFirm($firm, $taxForm, $period);
                if ($result === 'created') {
                    $totalCreated++;
                } else {
                    $totalSkipped++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$totalCreated} beyanname oluşturuldu" . ($totalSkipped > 0 ? ", {$totalSkipped} zaten mevcuttu." : "."),
            'created' => $totalCreated,
            'skipped' => $totalSkipped,
        ]);
    }

    /**
     * Firma için beyanname oluştur (varsa atla)
     */
    private function createDeclarationForFirm(Firm $firm, TaxForm $taxForm, Carbon $period): string
    {
        // Periyodu hesapla
        $periodRange = $this->resolvePeriodRange($taxForm->frequency, $period);
        if (!$periodRange) {
            return 'skipped';
        }

        // Zaten var mı kontrol et
        $exists = TaxDeclaration::where('firm_id', $firm->id)
            ->where('tax_form_id', $taxForm->id)
            ->where('period_start', $periodRange['start'])
            ->where('period_end', $periodRange['end'])
            ->exists();

        if ($exists) {
            return 'skipped';
        }

        // Son tarihi hesapla
        $dueDate = $taxForm->getOfficialDueDate($periodRange['end'])
            ?? $periodRange['end']->copy()->addMonth()->day($taxForm->default_due_day ?? 26);

        TaxDeclaration::create([
            'firm_id' => $firm->id,
            'tax_form_id' => $taxForm->id,
            'period_start' => $periodRange['start'],
            'period_end' => $periodRange['end'],
            'due_date' => $dueDate,
            'status' => 'pending',
        ]);

        return 'created';
    }

    /**
     * Frekansa göre dönem hesapla
     */
    private function resolvePeriodRange(string $frequency, Carbon $period): ?array
    {
        return match ($frequency) {
            'monthly' => [
                'start' => $period->copy()->startOfMonth(),
                'end' => $period->copy()->endOfMonth(),
            ],
            'quarterly' => [
                'start' => $period->copy()->firstOfQuarter(),
                'end' => $period->copy()->lastOfQuarter(),
            ],
            'yearly' => [
                'start' => $period->copy()->startOfYear(),
                'end' => $period->copy()->endOfYear(),
            ],
            default => null,
        };
    }
}
