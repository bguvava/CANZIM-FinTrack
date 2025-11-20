<?php

namespace Database\Factories;

use App\Models\BudgetItem;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expenseDate = fake()->dateTimeBetween('-6 months', 'now');
        $statuses = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Paid'];
        $status = fake()->randomElement($statuses);

        // Reuse existing expense category or create new one
        $expenseCategory = ExpenseCategory::inRandomOrder()->first() ?? ExpenseCategory::factory()->create();

        return [
            'expense_number' => 'EXP-'.date('Y').'-'.str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'project_id' => Project::factory(),
            'budget_item_id' => BudgetItem::factory(),
            'expense_category_id' => $expenseCategory->id,
            'expense_date' => $expenseDate,
            'amount' => fake()->randomFloat(2, 50, 5000),
            'currency' => 'USD',
            'description' => fake()->sentence(),
            'status' => $status,
            'submitted_by' => User::factory(),
            'submitted_at' => $status !== 'Draft' ? $expenseDate : null,
        ];
    }

    /**
     * Indicate that the expense is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Draft',
            'submitted_by' => null,
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the expense is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Submitted',
            'submitted_by' => User::factory(),
            'submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the expense is under review.
     */
    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Under Review',
            'submitted_by' => User::factory(),
            'submitted_at' => now()->subDays(2),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the expense is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Approved',
            'submitted_by' => User::factory(),
            'submitted_at' => now()->subDays(3),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now()->subDays(2),
            'approved_by' => User::factory(),
            'approved_at' => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the expense is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Rejected',
            'submitted_by' => User::factory(),
            'submitted_at' => now()->subDays(2),
            'rejected_by' => User::factory(),
            'rejected_at' => now()->subDays(1),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the expense is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Paid',
            'submitted_by' => User::factory(),
            'submitted_at' => now()->subDays(4),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now()->subDays(3),
            'approved_by' => User::factory(),
            'approved_at' => now()->subDays(2),
            'paid_by' => User::factory(),
            'paid_at' => now()->subDays(1),
            'payment_reference' => 'PAY-'.fake()->randomNumber(6),
            'payment_method' => fake()->randomElement(['Bank Transfer', 'Cash', 'Cheque']),
        ]);
    }

    /**
     * Indicate that the expense has a receipt.
     */
    public function withReceipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'receipt_path' => 'receipts/'.date('Y').'/'.date('m').'/'.fake()->uuid().'.pdf',
            'receipt_original_name' => 'receipt_'.fake()->randomNumber(4).'.pdf',
            'receipt_file_size' => fake()->numberBetween(50000, 2000000),
        ]);
    }
}
