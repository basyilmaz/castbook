<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\InvoiceExtraField;
use App\Models\Setting;
use App\Services\InvoiceGenerationService;
use App\Services\InvoiceService;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}
    
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'firm_id', 'date_from', 'date_to', 'per_page']);
        $perPage = (int) ($filters['per_page'] ?? 10);

        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $filters['per_page'] = $perPage;

        // Service layer kullanarak fatura listesini getir
        $invoices = $this->invoiceService->getFilteredInvoices($filters, $perPage);
        $firms = Firm::orderBy('name')->get(['id', 'name']);

        return view('invoices.index', [
            'invoices' => $invoices,
            'firms' => $firms,
            'filters' => $filters,
            'perPage' => $perPage,
        ]);
    }

    public function create(Request $request): View
    {
        $firms = Firm::active()->orderBy('name')->get(['id', 'name', 'monthly_fee', 'default_vat_rate', 'default_vat_included']);
        $invoiceDate = Carbon::now()->startOfMonth();
        $dueDays = (int) Setting::getValue('invoice_default_due_days', 10);

        $invoice = new Invoice([
            'date' => $invoiceDate,
            'due_date' => $dueDays > 0 ? $invoiceDate->copy()->addDays($dueDays) : $invoiceDate->copy()->addDays(10),
            'amount' => 0,
            'description' => Setting::getValue('invoice_default_description', ''),
        ]);

        $prefillFirmId = $request->integer('firm_id') ?: old('firm_id');
        $extraFields = collect();
        $selectedFirm = null;

        if ($prefillFirmId) {
            $selectedFirm = $firms->firstWhere('id', $prefillFirmId) ?? Firm::find($prefillFirmId);
            if ($selectedFirm) {
                $invoice->amount = $selectedFirm->priceForDate($invoiceDate);
                $invoice->vat_rate = $selectedFirm->default_vat_rate ?? 20;
                $invoice->vat_included = $selectedFirm->default_vat_included ?? true;
            }

            $extraFields = InvoiceExtraField::query()
                ->where('firm_id', $prefillFirmId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        return view('invoices.create', compact('invoice', 'firms', 'prefillFirmId', 'extraFields', 'selectedFirm'));
    }

    public function store(Request $request): RedirectResponse
    {
        $firmId = $request->input('firm_id');

        $extraFields = InvoiceExtraField::query()
            ->where('firm_id', $firmId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $data = $this->validatedData($request, null, $extraFields);

        $firm = Firm::find($data['firm_id']);
        $invoiceDate = Carbon::parse($data['date']);
        $expectedAmount = $firm?->priceForDate($invoiceDate) ?? 0.0;

        // Line items'dan toplam tutarı hesapla
        $lineItems = $request->input('line_items', []);
        $totalAmount = 0;
        
        foreach ($lineItems as $item) {
            $quantity = floatval($item['quantity'] ?? 1);
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $totalAmount += $quantity * $unitPrice;
        }

        // Eğer line items varsa, hesaplanan toplamı kullan
        if (!empty($lineItems) && $totalAmount > 0) {
            $data['amount'] = $totalAmount;
        } elseif (($data['amount'] ?? 0) <= 0 && $expectedAmount > 0) {
            $data['amount'] = $expectedAmount;
        }

        $invoice = Invoice::create($data);

        $description = $invoice->description ?: 'Aylık muhasebe ücreti';

        // Line items oluştur
        if (!empty($lineItems)) {
            $sortOrder = 0;
            foreach ($lineItems as $item) {
                if (empty($item['description']) || empty($item['unit_price'])) {
                    continue;
                }
                
                $quantity = floatval($item['quantity'] ?? 1);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $amount = $quantity * $unitPrice;
                
                $invoice->lineItems()->create([
                    'description' => $item['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'amount' => $amount,
                    'sort_order' => $sortOrder++,
                    'item_type' => 'service',
                ]);
            }
        } else {
            // Eski mantık: Tek satırlık line item
            $invoice->lineItems()->create([
                'description' => $description,
                'quantity' => 1,
                'unit_price' => $invoice->amount,
                'amount' => $invoice->amount,
                'sort_order' => 0,
                'item_type' => 'extra',
            ]);
        }

        $invoice->transactions()->create([
            'firm_id' => $invoice->firm_id,
            'type' => 'debit',
            'amount' => $invoice->amount,
            'date' => $invoice->date,
            'description' => $description,
        ]);

        $this->syncExtraValues($invoice, $extraFields, $data['extra_fields'] ?? []);

        $redirect = redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Fatura oluşturuldu ve cari borç kaydedildi.');

        if ($expectedAmount > 0 && abs($expectedAmount - $invoice->amount) > 0.01) {
            $redirect->with('warning', 'Uyarı: Bu tarih için standart ücret ' . Format::money($expectedAmount) . '.');
        }

        return $redirect;
    }

    public function show(Invoice $invoice): View
    {
        $invoice->loadSum('payments', 'amount');
        $invoice->loadMissing(['firm', 'payments', 'extraValues.field', 'lineItems']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Faturayı kopyalama formunu göster
     */
    public function duplicate(Invoice $invoice): View
    {
        $invoice->loadMissing(['firm', 'lineItems', 'extraValues']);
        
        $firms = Firm::active()->orderBy('name')->get(['id', 'name', 'monthly_fee']);
        
        // Yeni tarihler
        $newDate = Carbon::now()->startOfMonth();
        $dueDays = (int) Setting::getValue('invoice_default_due_days', 10);
        
        // Kopyalanmış fatura objesi oluştur
        $copiedInvoice = new Invoice([
            'firm_id' => $invoice->firm_id,
            'date' => $newDate,
            'due_date' => $dueDays > 0 ? $newDate->copy()->addDays($dueDays) : $newDate->copy()->addDays(10),
            'amount' => $invoice->amount,
            'description' => $invoice->description,
            'official_number' => null, // Yeni numara alacak
        ]);
        
        // Line items'ı kopyala (ID'siz)
        $copiedItems = $invoice->lineItems->map(function ($item) {
            return new \App\Models\InvoiceLineItem([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'sort_order' => $item->sort_order,
                'item_type' => $item->item_type,
            ]);
        });
        
        $copiedInvoice->setRelation('lineItems', $copiedItems);
        
        $prefillFirmId = $invoice->firm_id;
        
        $extraFields = InvoiceExtraField::query()
            ->where('firm_id', $invoice->firm_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        return view('invoices.create', [
            'invoice' => $copiedInvoice,
            'firms' => $firms,
            'prefillFirmId' => $prefillFirmId,
            'extraFields' => $extraFields,
            'isDuplicate' => true,
            'sourceInvoice' => $invoice,
        ]);
    }


    public function edit(Invoice $invoice): View|RedirectResponse
    {
        if (in_array($invoice->status, ['paid', 'partial'], true)) {
            return redirect()->route('invoices.show', $invoice)
                ->withErrors(['invoice' => 'Ödenmiş veya kısmen ödenmiş faturalar düzenlenemez.']);
        }

        $firms = Firm::orderBy('name')->get(['id', 'name', 'monthly_fee', 'default_vat_rate', 'default_vat_included']);
        $selectedFirm = $invoice->firm;

        $extraFields = InvoiceExtraField::query()
            ->where('firm_id', $invoice->firm_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $invoice->loadMissing(['extraValues', 'lineItems']);

        return view('invoices.edit', compact('invoice', 'firms', 'extraFields', 'selectedFirm'));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if (in_array($invoice->status, ['paid', 'partial'], true)) {
            return redirect()->route('invoices.show', $invoice)
                ->withErrors(['invoice' => 'Ödenmiş veya kısmen ödenmiş faturalar düzenlenemez.']);
        }

        $extraFields = InvoiceExtraField::query()
            ->where('firm_id', $invoice->firm_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $data = $this->validatedData($request, $invoice, $extraFields);

        // Line items'dan toplam tutarı hesapla
        $lineItems = $request->input('line_items', []);
        $totalAmount = 0;
        
        foreach ($lineItems as $item) {
            $quantity = floatval($item['quantity'] ?? 1);
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $totalAmount += $quantity * $unitPrice;
        }

        // Eğer line items varsa, hesaplanan toplamı kullan
        if (!empty($lineItems) && $totalAmount > 0) {
            $data['amount'] = $totalAmount;
        }

        $invoice->update($data);

        // Line items güncelle
        if (!empty($lineItems)) {
            // Güncellenecek ve yeni eklenecek ID'leri takip et
            $existingIds = [];
            $sortOrder = 0;
            
            foreach ($lineItems as $item) {
                if (empty($item['description']) || empty($item['unit_price'])) {
                    continue;
                }
                
                $quantity = floatval($item['quantity'] ?? 1);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $amount = $quantity * $unitPrice;
                
                $lineItemData = [
                    'description' => $item['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'amount' => $amount,
                    'sort_order' => $sortOrder++,
                    'item_type' => 'service',
                ];
                
                if (!empty($item['id'])) {
                    // Mevcut satırı güncelle
                    $existingItem = $invoice->lineItems()->find($item['id']);
                    if ($existingItem) {
                        $existingItem->update($lineItemData);
                        $existingIds[] = $existingItem->id;
                    }
                } else {
                    // Yeni satır ekle
                    $newItem = $invoice->lineItems()->create($lineItemData);
                    $existingIds[] = $newItem->id;
                }
            }
            
            // Formda olmayan (silinmiş) satırları kaldır
            $invoice->lineItems()->whereNotIn('id', $existingIds)->delete();
        }

        // Transaction güncelle
        $transaction = $invoice->transactions()->first();
        if ($transaction) {
            $transaction->update([
                'amount' => $invoice->amount,
                'date' => $invoice->date,
                'description' => $invoice->description ?? $transaction->description,
            ]);
        } else {
            $invoice->transactions()->create([
                'firm_id' => $invoice->firm_id,
                'type' => 'debit',
                'amount' => $invoice->amount,
                'date' => $invoice->date,
                'description' => $invoice->description ?? 'Aylık muhasebe ücreti',
            ]);
        }

        $expectedAmount = $invoice->firm?->priceForDate(Carbon::parse($invoice->date)) ?? 0.0;

        $redirect = redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Fatura güncellendi.');

        if ($expectedAmount > 0 && abs($expectedAmount - $invoice->amount) > 0.01) {
            $redirect->with('warning', 'Uyarı: Bu tarih için standart ücret ' . Format::money($expectedAmount) . '.');
        }

        if ($invoice->payments()->exists()) {
            $invoice->refreshPaymentStatus();
        }

        $this->syncExtraValues($invoice, $extraFields, $data['extra_fields'] ?? []);

        return $redirect;
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if (in_array($invoice->status, ['paid', 'partial'], true)) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->withErrors(['invoice' => 'Ödenmiş veya kısmen ödenmiş faturalar silinemez.']);
        }

        $invoice->transactions()->delete();
        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('status', 'Fatura silindi.');
    }

    /**
     * Toplu fatura silme
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $idsString = $request->input('ids', '');
        $ids = array_filter(array_map('intval', explode(',', $idsString)));

        if (empty($ids)) {
            return redirect()
                ->route('invoices.index')
                ->withErrors(['invoice' => 'Silinecek fatura seçilmedi.']);
        }

        // Sadece ödenmemiş faturaları sil
        $invoices = Invoice::whereIn('id', $ids)
            ->whereNotIn('status', ['paid', 'partial'])
            ->get();

        $deletedCount = 0;
        foreach ($invoices as $invoice) {
            $invoice->transactions()->delete();
            $invoice->delete();
            $deletedCount++;
        }

        $skippedCount = count($ids) - $deletedCount;
        $message = "{$deletedCount} fatura silindi.";
        
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} ödeme yapılmış fatura atlandı.)";
        }

        return redirect()
            ->route('invoices.index')
            ->with('status', $message);
    }

    /**
     * Toplu fatura durum güncelleme
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'string'],
            'status' => ['required', 'in:unpaid,partial,paid,cancelled'],
        ]);

        $ids = array_filter(array_map('intval', explode(',', $data['ids'])));

        if (empty($ids)) {
            return redirect()
                ->route('invoices.index')
                ->withErrors(['invoice' => 'Güncellenecek fatura seçilmedi.']);
        }

        $statusLabels = [
            'unpaid' => 'Ödenmedi',
            'partial' => 'Kısmi Ödeme',
            'paid' => 'Ödendi',
            'cancelled' => 'İptal',
        ];

        // Service layer kullanarak toplu güncelleme
        $updatedCount = $this->invoiceService->bulkUpdateStatus($ids, $data['status']);

        return redirect()
            ->route('invoices.index')
            ->with('status', "{$updatedCount} faturanın durumu \"{$statusLabels[$data['status']]}\" olarak güncellendi.");
    }


    public function syncMonthly(Request $request, InvoiceGenerationService $generator): RedirectResponse
    {
        $month = $request->input('month');

        try {
            $target = $month
                ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable $exception) {
            return back()->withErrors(['month' => 'Geçersiz ay formatı.']);
        }

        $firms = Firm::active()->get();
        $created = 0;

        foreach ($firms as $firm) {
            $invoice = $generator->ensureMonthlyInvoice($firm, $target);
            if ($invoice) {
                $created++;
            }
        }

        $labelDate = $generator->invoiceDateForMonth($target)->format('m/Y');
        $message = $created > 0
            ? "{$labelDate} döneminde {$created} fatura oluşturuldu."
            : "{$labelDate} dönemi için yeni fatura oluşturulmadı.";


        // Beyannameleri otomatik oluştur
        $taxDeclarationsCreated = 0;
        try {
            \Artisan::call('app:generate-tax-declarations', [
                '--month' => $target->format('Y-m')
            ]);
            
            // Command output'tan oluşturulan beyanname sayısını al
            $output = \Artisan::output();
            if (preg_match('/(\d+) beyanname oluşturuldu/', $output, $matches)) {
                $taxDeclarationsCreated = (int) $matches[1];
            }
        } catch (\Throwable $e) {
            \Log::error('Beyanname oluşturma hatası: ' . $e->getMessage());
        }

        // Mesaja beyanname bilgisini ekle
        if ($taxDeclarationsCreated > 0) {
            $message .= " {$taxDeclarationsCreated} beyanname oluşturuldu.";
        }

        return back()->with('status', $message);
    }

    protected function validatedData(Request $request, ?Invoice $invoice = null, $extraFields = []): array
    {
        $data = $request->validate([
            'firm_id'         => ['required', 'exists:firms,id'],
            'date'            => ['required', 'date'],
            'due_date'        => ['nullable', 'date', 'after_or_equal:date'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'description'     => ['nullable', 'string', 'max:255'],
            'official_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('invoices', 'official_number')->ignore($invoice?->id),
            ],
            // KDV alanları
            'vat_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_included'    => ['nullable', 'boolean'],
            'subtotal'        => ['nullable', 'numeric', 'min:0'],
            'vat_amount'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['status'] = $data['status'] ?? 'unpaid';
        $data['description'] = $data['description']
            ?? Setting::getValue('invoice_default_description', '');

        if (empty($data['due_date'])) {
            $dueDays = (int) Setting::getValue('invoice_default_due_days', 0);
            if ($dueDays > 0) {
                $data['due_date'] = Carbon::parse($data['date'])
                    ->addDays($dueDays)
                    ->format('Y-m-d');
            }
        }

        if (! empty($extraFields)) {
            $rules = [];

            foreach ($extraFields as $field) {
                $ruleParts = $field->is_required ? ['required'] : ['nullable'];

                switch ($field->type) {
                    case 'number':
                        $ruleParts[] = 'numeric';
                        break;
                    case 'date':
                        $ruleParts[] = 'date';
                        break;
                    default:
                        $ruleParts[] = 'string';
                        break;
                }

                $rules['extra_fields.' . $field->id] = implode('|', $ruleParts);
            }

            if (! empty($rules)) {
                $validatedExtras = $request->validate($rules);
                $data['extra_fields'] = $validatedExtras['extra_fields'] ?? [];
            }
        }

        return $data;
    }

    protected function syncExtraValues(Invoice $invoice, $extraFields, array $values): void
    {
        foreach ($extraFields as $field) {
            $value = $values[$field->id] ?? null;

            if ($value === null || $value === '') {
                $invoice->extraValues()
                    ->where('extra_field_id', $field->id)
                    ->delete();
                continue;
            }

            $invoice->extraValues()->updateOrCreate(
                ['extra_field_id' => $field->id],
                ['value' => $value]
            );
        }
    }
}
