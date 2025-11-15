<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => \App\Models\Project::factory(),
            'fiscal_year' => fake()->randomElement(['2024', '2025', '2026']),
            'total_amount' => fake()->randomFloat(2, 50000, 500000),
            'status' => fake()->randomElement(['draft', 'submitted', 'approved', 'rejected']),
            'approved_by' => null,
            'approved_at' => null,
            'created_by' => \App\Models\User::factory(),
        ];
    }

    /**
     * Indicate that the budget is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => \App\Models\User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the budget is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
