<?php

namespace Database\Factories;

use App\Models\Firm;
use App\Models\TaxDeclaration;
use App\Models\TaxForm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TaxDeclarationFactory extends Factory
{
    protected $model = TaxDeclaration::class;

    public function definition(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return [
            'firm_id' => Firm::factory(),
            'tax_form_id' => TaxForm::factory(),
            'period_start' => $start,
            'period_end' => $end,
            'period_label' => $start->format('Y-m'),
            'due_date' => (clone $end)->addDays(10),
            'status' => 'pending',
            'filed_at' => null,
            'paid_at' => null,
            'notes' => null,
        ];
    }
}
