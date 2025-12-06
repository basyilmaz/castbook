<?php

namespace App\Http\Controllers;

use App\Models\TaxForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxFormController extends Controller
{
    public function index(): View
    {
        $taxForms = TaxForm::orderBy('code')->paginate(20);
        
        return view('settings.tax-forms.index', compact('taxForms'));
    }

    public function create(): View
    {
        return view('settings.tax-forms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:tax_forms,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'in:monthly,quarterly,annual'],
            'default_due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        TaxForm::create($data);

        return redirect()->route('settings.tax-forms.index')
            ->with('status', 'Vergi formu oluşturuldu.');
    }

    public function edit(TaxForm $taxForm): View
    {
        return view('settings.tax-forms.edit', compact('taxForm'));
    }

    public function update(Request $request, TaxForm $taxForm): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:tax_forms,code,' . $taxForm->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'in:monthly,quarterly,annual'],
            'default_due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');

        $taxForm->update($data);

        return redirect()->route('settings.tax-forms.index')
            ->with('status', 'Vergi formu güncellendi.');
    }

    public function destroy(TaxForm $taxForm): RedirectResponse
    {
        // Check if form is assigned to any firms
        if ($taxForm->firms()->exists()) {
            return redirect()->route('settings.tax-forms.index')
                ->withErrors(['tax_form' => 'Bu form firmalara atanmış, silinemez.']);
        }

        $taxForm->delete();

        return redirect()->route('settings.tax-forms.index')
            ->with('status', 'Vergi formu silindi.');
    }
}
