<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseApproval>
 */
class ExpenseApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'approval_level' => fake()->randomElement(['Finance Officer', 'Programs Manager']),
            'action' => fake()->randomElement(['Approved', 'Rejected']),
            'user_id' => User::factory(),
            'comments' => fake()->optional()->sentence(),
            'action_date' => now(),
        ];
    }
}
