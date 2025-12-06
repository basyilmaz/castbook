<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\InvoiceExtraField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceExtraFieldController extends Controller
{
    public function index(): View
    {
        $fields = InvoiceExtraField::query()
            ->with('firm:id,name')
            ->orderBy('firm_id')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('settings.invoice-extra-fields.index', compact('fields'));
    }

    public function create(): View
    {
        $firms = Firm::orderBy('name')->get(['id', 'name']);

        return view('settings.invoice-extra-fields.create', compact('firms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'firm_id' => ['required', 'exists:firms,id'],
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                Rule::unique('invoice_extra_fields')->where('firm_id', $request->input('firm_id')),
            ],
            'label' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['text', 'number', 'date', 'select'])],
            'options' => ['nullable', 'string', 'max:500'],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // If type is not select, clear options
        if ($data['type'] !== 'select') {
            $data['options'] = null;
        }

        InvoiceExtraField::create($data);

        return redirect()
            ->route('settings.invoice-extra-fields.index')
            ->with('status', 'Ekstra alan başarıyla oluşturuldu.');
    }

    public function edit(InvoiceExtraField $invoiceExtraField): View
    {
        $firms = Firm::orderBy('name')->get(['id', 'name']);

        return view('settings.invoice-extra-fields.edit', compact('invoiceExtraField', 'firms'));
    }

    public function update(Request $request, InvoiceExtraField $invoiceExtraField): RedirectResponse
    {
        $data = $request->validate([
            'firm_id' => ['required', 'exists:firms,id'],
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                Rule::unique('invoice_extra_fields')
                    ->where('firm_id', $request->input('firm_id'))
                    ->ignore($invoiceExtraField->id),
            ],
            'label' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in(['text', 'number', 'date', 'select'])],
            'options' => ['nullable', 'string', 'max:500'],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // If type is not select, clear options
        if ($data['type'] !== 'select') {
            $data['options'] = null;
        }

        $invoiceExtraField->update($data);

        return redirect()
            ->route('settings.invoice-extra-fields.index')
            ->with('status', 'Ekstra alan güncellendi.');
    }

    public function destroy(InvoiceExtraField $invoiceExtraField): RedirectResponse
    {
        // Check if this field has any values
        if ($invoiceExtraField->values()->exists()) {
            return back()->withErrors([
                'field' => 'Bu alan kullanımda olduğu için silinemez. Önce alanı pasif hale getirebilirsiniz.',
            ]);
        }

        $invoiceExtraField->delete();

        return redirect()
            ->route('settings.invoice-extra-fields.index')
            ->with('status', 'Ekstra alan silindi.');
    }
}
