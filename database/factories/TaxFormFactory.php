<?php

namespace Database\Factories;

use App\Models\TaxForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxFormFactory extends Factory
{
    protected $model = TaxForm::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->lexify('TF?')) . $this->faker->numerify('###'),
            'name' => $this->faker->sentence(3),
            'frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'yearly']),
            'default_due_day' => $this->faker->numberBetween(1, 28),
            'is_active' => true,
        ];
    }
}
