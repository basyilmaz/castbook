<?php

namespace Database\Factories;

use App\Models\Firm;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $date = Carbon::now()->subDays($this->faker->numberBetween(0, 30));
        $dueDate = (clone $date)->addDays(10);

        return [
            'firm_id' => Firm::factory(),
            'date' => $date->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 500, 5000),
            'description' => $this->faker->sentence(),
            'official_number' => null,
            'status' => 'unpaid',
            'paid_at' => null,
        ];
    }
}
