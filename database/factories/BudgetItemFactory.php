<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BudgetItem>
 */
class BudgetItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allocated = fake()->randomFloat(2, 5000, 50000);
        $spent = fake()->randomFloat(2, 0, $allocated);
        $remaining = $allocated - $spent;

        return [
            'budget_id' => \App\Models\Budget::factory(),
            'category' => fake()->randomElement(['Travel', 'Staff Salaries', 'Procurement/Supplies', 'Consultants', 'Other']),
            'description' => fake()->sentence(),
            'cost_code' => fake()->optional()->numerify('CC-####'),
            'allocated_amount' => $allocated,
            'spent_amount' => $spent,
            'remaining_amount' => $remaining,
        ];
    }
}
