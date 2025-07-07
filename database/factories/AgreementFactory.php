<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agreement>
 */
class AgreementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => $this->faker->uuid,
            'version' => 'v1.0',
            'strategy' => 'standard',
            'multiplier' => 1.0,
            'vat_rate' => 0.20,
            'currency' => 'EUR',
            'language' => 'en',
            'rules' => [
                'base_charge_column' => 'Amount',
                'surcharge_prefix' => 'SUR',
            ],
        ];
    }
}
