<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\TaxForm;
use Illuminate\Http\Request;

class FirmTaxFormController extends Controller
{
    public function index(Firm $firm)
    {
        $assignedForms = $firm->taxForms()->pluck('tax_forms.id')->toArray();
        $allForms = TaxForm::active()->orderBy('code')->get();

        return response()->json([
            'assigned' => $assignedForms,
            'all' => $allForms,
        ]);
    }

    public function store(Request $request, Firm $firm)
    {
        $validated = $request->validate([
            'tax_form_ids' => 'required|array',
            'tax_form_ids.*' => 'exists:tax_forms,id',
        ]);

        $firm->taxForms()->sync($validated['tax_form_ids']);

        return response()->json([
            'message' => 'Vergi formları güncellendi.',
            'assigned' => $firm->taxForms()->pluck('tax_forms.id'),
        ]);
    }
}
