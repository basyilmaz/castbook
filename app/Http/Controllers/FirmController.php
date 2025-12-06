<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Services\FirmInvoiceBackfillService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FirmController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $firms = Firm::query()
            ->withCount([
                'invoices as unpaid_invoices_count' => fn ($query) => $query->whereIn('status', ['unpaid', 'partial']),
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('tax_no', 'like', '%' . $search . '%')
                        ->orWhere('contact_email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('firms.index', [
            'firms' => $firms,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        return view('firms.create', ['firm' => new Firm()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $firm = Firm::create($data);

        // Şirket türüne göre otomatik vergi formu ata
        $autoAssignService = app(\App\Services\TaxFormAutoAssignService::class);
        $result = $autoAssignService->assignDefaultForms($firm);

        $message = 'Firma başarıyla oluşturuldu.';
        if (!empty($result['assigned'])) {
            $message .= ' ' . count($result['assigned']) . ' vergi formu otomatik atandı.';
        }

        return redirect()
            ->route('firms.index')
            ->with('status', $message);
    }

    public function show(Firm $firm): View
    {
        $firm->load([
            'invoices' => fn ($query) => $query->latest('date'),
            'transactions' => fn ($query) => $query->latest('date')->with('sourceable'),
            'priceHistories' => fn ($query) => $query->orderByDesc('valid_from'),
            'taxForms' => fn ($query) => $query->where('tax_forms.is_active', true)->orderBy('code'),
        ]);

        $debitTotal = $firm->transactions()->debits()->sum('amount');
        $creditTotal = $firm->transactions()->credits()->sum('amount');

        return view('firms.show', [
            'firm' => $firm,
            'debitTotal' => $debitTotal,
            'creditTotal' => $creditTotal,
        ]);
    }

    public function edit(Firm $firm): View
    {
        return view('firms.edit', compact('firm'));
    }

    public function update(Request $request, Firm $firm)
    {
        $data = $this->validatedData($request);

        $originalStart = $firm->contract_start_at;

        $firm->update($data);

        if ($originalStart?->ne($firm->contract_start_at)) {
            $firm->forceFill(['initial_debt_synced_at' => null])->save();
        }

        return redirect()
            ->route('firms.show', $firm)
            ->with('status', 'Firma bilgileri güncellendi.');
    }

    public function destroy(Firm $firm)
    {
        $firm->delete();

        return redirect()
            ->route('firms.index')
            ->with('status', 'Firma arşivlendi.');
    }

    /**
     * Firma beyanname özet sayfası
     */
    public function declarations(Request $request, Firm $firm): View
    {
        $year = $request->input('year', now()->year);

        $declarations = $firm->taxDeclarations()
            ->with('taxForm')
            ->whereYear('period_start', $year)
            ->orderBy('due_date')
            ->get();

        // Dönemlere göre grupla
        $groupedByPeriod = $declarations->groupBy('period_label');

        // Vergi formlarına göre grupla
        $groupedByForm = $declarations->groupBy(fn ($d) => $d->taxForm->code);

        // İstatistikler
        $stats = [
            'total' => $declarations->count(),
            'pending' => $declarations->where('status', 'pending')->count(),
            'filed' => $declarations->where('status', 'filed')->count(),
            'paid' => $declarations->where('status', 'paid')->count(),
            'overdue' => $declarations->filter(fn ($d) => $d->isOverdue())->count(),
        ];

        // Yıl seçenekleri
        $years = $firm->taxDeclarations()
            ->selectRaw('YEAR(period_start) as year')
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values();

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        // Eksik dönemler - tanımlı formlar için beklenilen ve eksik dönemler
        $expectedPeriods = [];
        $activeForms = $firm->taxForms()->wherePivot('is_active', true)->get();
        
        foreach ($activeForms as $form) {
            $existingPeriods = $declarations
                ->where('tax_form_id', $form->id)
                ->pluck('period_label')
                ->toArray();

            // Aylık formlar için 12 dönem beklenir
            if ($form->frequency === 'monthly') {
                for ($m = 1; $m <= 12; $m++) {
                    $period = sprintf('%02d/%d', $m, $year);
                    if (!in_array($period, $existingPeriods)) {
                        $expectedPeriods[] = [
                            'form' => $form->code,
                            'period' => $period,
                            'missing' => true,
                        ];
                    }
                }
            }
        }

        return view('firms.declarations', [
            'firm' => $firm,
            'declarations' => $declarations,
            'groupedByPeriod' => $groupedByPeriod,
            'groupedByForm' => $groupedByForm,
            'stats' => $stats,
            'year' => $year,
            'years' => $years,
            'expectedPeriods' => $expectedPeriods,
            'activeForms' => $activeForms,
        ]);
    }

    public function syncInvoices(Firm $firm, FirmInvoiceBackfillService $service)
    {
        $result = $service->syncFirm($firm);

        if ($result['skipped_reason'] ?? false) {
            return back()->with('status', match ($result['skipped_reason']) {
                'monthly_fee_zero' => 'Aylık ücret sıfır olduğu için senkronizasyon yapılmadı.',
                'missing_contract_start' => 'Öncelikle sözleşme başlangıç tarihini ekleyin.',
                'contract_in_future' => 'Sözleşme başlangıç tarihi gelecekte olduğu için fatura üretilemez.',
                default => 'Senkronizasyon yapılmadı.',
            });
        }

        $message = $result['created'] > 0
            ? "{$result['created']} adet geçmiş fatura oluşturuldu."
            : 'Eksik fatura bulunmadı.';

        return back()->with('status', $message);
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'in:individual,limited,joint_stock'],
            'tax_no' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'contract_start_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
