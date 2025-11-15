<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\Donor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashFlow>
 */
class CashFlowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['inflow', 'outflow']);
        $amount = fake()->randomFloat(2, 1000, 100000);
        $balanceBefore = fake()->randomFloat(2, 50000, 500000);

        return [
            'transaction_number' => 'TXN-'.date('Y').'-'.fake()->unique()->numerify('####'),
            'type' => $type,
            'bank_account_id' => BankAccount::factory(),
            'project_id' => Project::factory(),
            'donor_id' => $type === 'inflow' ? Donor::factory() : null,
            'expense_id' => $type === 'outflow' ? Expense::factory() : null,
            'transaction_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $type === 'inflow' ? $balanceBefore + $amount : $balanceBefore - $amount,
            'description' => fake()->sentence(),
            'reference' => fake()->optional()->numerify('REF-####'),
            'is_reconciled' => false,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the cash flow is an inflow.
     */
    public function inflow(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? fake()->randomFloat(2, 1000, 100000);
            $balanceBefore = $attributes['balance_before'] ?? fake()->randomFloat(2, 50000, 500000);

            return [
                'type' => 'inflow',
                'donor_id' => Donor::factory(),
                'expense_id' => null,
                'balance_after' => $balanceBefore + $amount,
            ];
        });
    }

    /**
     * Indicate that the cash flow is an outflow.
     */
    public function outflow(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? fake()->randomFloat(2, 1000, 100000);
            $balanceBefore = $attributes['balance_before'] ?? fake()->randomFloat(2, 50000, 500000);

            return [
                'type' => 'outflow',
                'donor_id' => null,
                'expense_id' => Expense::factory(),
                'balance_after' => $balanceBefore - $amount,
            ];
        });
    }

    /**
     * Indicate that the cash flow is reconciled.
     */
    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_reconciled' => true,
            'reconciled_at' => fake()->dateTimeBetween($attributes['transaction_date'], 'now'),
            'reconciled_by' => User::factory(),
        ]);
    }
}
