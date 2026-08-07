<?php

namespace Database\Factories;

use App\Enums\CashflowType;
use App\Models\Cashflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cashflow>
 */
class CashflowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'amount' => fake()->numberBetween(10000, 5000000),
            'type' => fake()->randomElement([CashflowType::INFLOW, CashflowType::OUTFLOW]),
            'description' => fake()->sentence(),
        ];
    }
}
