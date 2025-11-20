<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reportTypes = [
            'budget-vs-actual',
            'cash-flow',
            'expense-summary',
            'project-status',
            'donor-contributions',
            'custom',
        ];

        $type = fake()->randomElement($reportTypes);

        return [
            'type' => $type,
            'title' => $this->generateTitle($type),
            'parameters' => $this->generateParameters($type),
            'file_path' => fake()->boolean(70) ? 'reports/'.fake()->uuid().'.pdf' : null,
            'generated_by' => User::factory(),
            'status' => fake()->randomElement(['completed', 'pending', 'failed']),
        ];
    }

    /**
     * Generate a title based on report type.
     */
    private function generateTitle(string $type): string
    {
        return match ($type) {
            'budget-vs-actual' => 'Budget vs Actual Report - '.fake()->monthName().' '.fake()->year(),
            'cash-flow' => 'Cash Flow Report - '.fake()->monthName().' '.fake()->year(),
            'expense-summary' => 'Expense Summary Report - '.fake()->monthName().' '.fake()->year(),
            'project-status' => 'Project Status Report - '.fake()->words(3, true),
            'donor-contributions' => 'Donor Contributions Report - '.fake()->year(),
            'custom' => 'Custom Report - '.fake()->words(3, true),
            default => 'General Report',
        };
    }

    /**
     * Generate parameters based on report type.
     */
    private function generateParameters(string $type): array
    {
        $baseParams = [
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
        ];

        return match ($type) {
            'budget-vs-actual' => array_merge($baseParams, [
                'project_ids' => [1, 2],
                'category_ids' => [1, 2, 3],
            ]),
            'cash-flow' => array_merge($baseParams, [
                'grouping' => fake()->randomElement(['month', 'quarter']),
            ]),
            'expense-summary' => array_merge($baseParams, [
                'project_ids' => [1],
                'category_ids' => [1, 2],
                'group_by' => fake()->randomElement(['category', 'project', 'month']),
            ]),
            'project-status' => [
                'project_id' => 1,
            ],
            'donor-contributions' => array_merge($baseParams, [
                'donor_ids' => [1, 2],
            ]),
            default => $baseParams,
        };
    }

    /**
     * Indicate that the report is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'file_path' => 'reports/'.fake()->uuid().'.pdf',
        ]);
    }

    /**
     * Indicate that the report is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'file_path' => null,
        ]);
    }

    /**
     * Indicate that the report is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'file_path' => null,
        ]);
    }

    /**
     * Create a budget vs actual report.
     */
    public function budgetVsActual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'budget-vs-actual',
            'title' => 'Budget vs Actual Report - '.fake()->monthName().' '.fake()->year(),
        ]);
    }

    /**
     * Create a cash flow report.
     */
    public function cashFlow(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cash-flow',
            'title' => 'Cash Flow Report - '.fake()->monthName().' '.fake()->year(),
        ]);
    }

    /**
     * Create an expense summary report.
     */
    public function expenseSummary(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense-summary',
            'title' => 'Expense Summary Report - '.fake()->monthName().' '.fake()->year(),
        ]);
    }
}
