<?php

namespace Database\Factories;

use App\Enums\CashflowType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'type' => fake()->randomElement([CashflowType::INFLOW, CashflowType::OUTFLOW]),
            'description' => fake()->sentence(),
        ];
    }
}
