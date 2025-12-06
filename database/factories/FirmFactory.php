<?php

namespace Database\Factories;

use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\Factory;

class FirmFactory extends Factory
{
    protected $model = Firm::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'tax_no' => $this->faker->numerify('##########'),
            'contact_person' => $this->faker->name(),
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'monthly_fee' => $this->faker->randomFloat(2, 500, 5000),
            'status' => 'active',
            'contract_start_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'notes' => $this->faker->sentence(),
        ];
    }
}
