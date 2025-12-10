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
        $taxForms = \App\Models\TaxForm::active()->orderBy('code')->get();
        return view('firms.create', ['firm' => new Firm(), 'taxForms' => $taxForms]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $firm = Firm::create($data);

        // Formdan beyanname seçimi yapıldıysa onu kullan
        $taxFormIds = $request->input('tax_forms', []);
        
        if (!empty($taxFormIds)) {
            // Manuel seçim yapıldı
            $this->syncTaxForms($firm, $taxFormIds);
            $message = 'Firma başarıyla oluşturuldu. ' . count($taxFormIds) . ' beyanname türü atandı.';
        } else {
            // Otomatik ata (şirket türüne göre)
            $message = 'Firma başarıyla oluşturuldu.';
            try {
                $autoAssignService = app(\App\Services\TaxFormAutoAssignService::class);
                $result = $autoAssignService->assignDefaultForms($firm);
                
                if (!empty($result['assigned'])) {
                    $message .= ' ' . count($result['assigned']) . ' vergi formu otomatik atandı.';
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('TaxFormAutoAssign failed: ' . $e->getMessage());
            }
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
        $taxForms = \App\Models\TaxForm::active()->orderBy('code')->get();
        $firm->load('taxForms');
        return view('firms.edit', compact('firm', 'taxForms'));
    }

    public function update(Request $request, Firm $firm)
    {
        $data = $this->validatedData($request);

        $originalStart = $firm->contract_start_at;

        $firm->update($data);

        if ($originalStart?->ne($firm->contract_start_at)) {
            $firm->forceFill(['initial_debt_synced_at' => null])->save();
        }

        // Beyanname türlerini senkronize et
        $taxFormIds = $request->input('tax_forms', []);
        $this->syncTaxForms($firm, $taxFormIds);

        return redirect()
            ->route('firms.show', $firm)
            ->with('status', 'Firma bilgileri güncellendi.');
    }

    /**
     * Firmayı ve tüm ilişkili verileri kalıcı olarak siler
     */
    public function destroy(Firm $firm)
    {
        $firmName = $firm->name;

        // Tüm ilişkili verileri sil
        $firm->taxDeclarations()->delete();
        $firm->invoices()->each(function ($invoice) {
            $invoice->payments()->delete();
            $invoice->lineItems()->delete();
            $invoice->transactions()->delete();
            $invoice->forceDelete();
        });
        $firm->payments()->delete();
        $firm->transactions()->delete();
        $firm->taxForms()->detach();

        // Firmayı kalıcı olarak sil
        $firm->forceDelete();

        return redirect()
            ->route('firms.index')
            ->with('status', "'{$firmName}' firması ve tüm verileri kalıcı olarak silindi.");
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
            ->selectRaw('EXTRACT(YEAR FROM period_start)::integer as year')
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
            // Otomasyon ayarları
            'auto_invoice_enabled' => ['nullable', 'boolean'],
            'tax_tracking_enabled' => ['nullable', 'boolean'],
            // KDV varsayılanları
            'default_vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_vat_included' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Toplu firma ekleme sayfası
     */
    public function import(): View
    {
        return view('firms.import');
    }

    /**
     * Örnek CSV şablonu indir
     */
    public function importTemplate()
    {
        $headers = ['Firma Adı', 'Vergi No', 'Aylık Ücret', 'Yetkili', 'Telefon', 'E-posta', 'Adres', 'Şirket Türü', 'Notlar'];
        $example = ['Örnek Firma Ltd. Şti.', '1234567890', '3500', 'Ahmet Yılmaz', '0532 123 4567', 'info@ornekfirma.com', 'İstanbul', 'limited', 'Örnek not'];

        $content = implode(';', $headers) . "\n" . implode(';', $example);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="firma_sablonu.csv"',
        ]);
    }

    /**
     * CSV dosyasından firmaları içe aktar
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        
        // BOM karakterini kaldır
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        $lines = array_filter(explode("\n", $content));
        
        if (count($lines) < 2) {
            return back()->withErrors(['file' => 'Dosya en az 2 satır içermelidir (başlık + veri).']);
        }

        // Ayraç tespit et
        $separator = str_contains($lines[0], ';') ? ';' : ',';
        
        // Başlık satırını atla
        array_shift($lines);

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $data = str_getcsv($line, $separator);
            
            // Firma adı zorunlu
            if (empty($data[0])) {
                $errors[] = "Satır " . ($index + 2) . ": Firma adı boş olamaz.";
                continue;
            }

            $firmData = [
                'name' => trim($data[0]),
                'tax_no' => ! empty($data[1]) ? trim($data[1]) : null,
                'monthly_fee' => $this->parseNumber($data[2] ?? '0'),
                'contact_person' => ! empty($data[3]) ? trim($data[3]) : null,
                'contact_phone' => ! empty($data[4]) ? trim($data[4]) : null,
                'contact_email' => ! empty($data[5]) ? trim($data[5]) : null,
                'address' => ! empty($data[6]) ? trim($data[6]) : null,
                'company_type' => $this->parseCompanyType($data[7] ?? ''),
                'notes' => ! empty($data[8]) ? trim($data[8]) : null,
                'status' => 'active',
                'contract_start_at' => now()->toDateString(),
            ];

            // Var olan firmayı bul (vergi no veya isim ile)
            $existing = null;
            if (! empty($firmData['tax_no'])) {
                $existing = Firm::where('tax_no', $firmData['tax_no'])->first();
            }
            if (! $existing) {
                $existing = Firm::where('name', $firmData['name'])->first();
            }

            if ($existing) {
                // Sadece boş olmayan alanları güncelle
                $updateData = array_filter($firmData, fn($v) => $v !== null && $v !== '');
                unset($updateData['status'], $updateData['contract_start_at']);
                $existing->update($updateData);
                $updated++;
            } else {
                Firm::create($firmData);
                $created++;
            }
        }

        $message = "{$created} firma eklendi";
        if ($updated > 0) {
            $message .= ", {$updated} firma güncellendi";
        }
        if (count($errors) > 0) {
            $message .= ". " . count($errors) . " satırda hata oluştu.";
        }

        return redirect()
            ->route('firms.index')
            ->with('status', $message);
    }

    protected function parseNumber(string $value): float
    {
        // Türkçe format: 3.500,00 veya 3500
        $value = trim($value);
        $value = str_replace('.', '', $value); // Binlik ayracı kaldır
        $value = str_replace(',', '.', $value); // Ondalık ayracı düzelt
        return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    protected function parseCompanyType(string $value): string
    {
        $value = strtolower(trim($value));
        return match(true) {
            str_contains($value, 'limited') || str_contains($value, 'ltd') => 'limited',
            str_contains($value, 'anonim') || str_contains($value, 'a.ş') => 'joint_stock',
            default => 'individual',
        };
    }

    /**
     * Firma için beyanname türlerini senkronize et
     */
    protected function syncTaxForms(Firm $firm, array $taxFormIds): void
    {
        // Mevcut ilişkileri temizle ve yenilerini ekle
        $syncData = [];
        foreach ($taxFormIds as $taxFormId) {
            $syncData[$taxFormId] = ['is_active' => true];
        }
        
        $firm->taxForms()->sync($syncData);
    }
}
