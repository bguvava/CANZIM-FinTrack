<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BudgetReallocation>
 */
class BudgetReallocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_id' => \App\Models\Budget::factory(),
            'from_budget_item_id' => \App\Models\BudgetItem::factory(),
            'to_budget_item_id' => \App\Models\BudgetItem::factory(),
            'amount' => fake()->randomFloat(2, 1000, 10000),
            'justification' => fake()->sentence(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'requested_by' => \App\Models\User::factory(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the reallocation is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => \App\Models\User::factory(),
            'approved_at' => now(),
        ]);
    }
}
