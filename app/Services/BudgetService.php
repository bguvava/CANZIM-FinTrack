<?php

namespace App\Services;

use App\Exceptions\BudgetValidationException;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\BudgetReallocation;
use App\Models\Project;
use App\Models\User;
use App\Notifications\BudgetAlertNotification;
use App\Notifications\BudgetApprovedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BudgetService
{
    /**
     * Create a new budget with line items.
     *
     * @throws BudgetValidationException
     */
    public function createBudget(array $data): Budget
    {
        // Calculate total amount from items before transaction
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $totalAmount += (float) ($item['allocated_amount'] ?? 0);
            }
        }

        // Validate budget total BEFORE creating (fail fast)
        $this->validateBudgetTotalBeforeCreate(
            (int) $data['project_id'],
            $totalAmount
        );

        return DB::transaction(function () use ($data, $totalAmount) {
            // Create the budget
            $budget = Budget::create([
                'project_id' => $data['project_id'],
                'fiscal_year' => $data['fiscal_year'],
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'created_by' => $data['created_by'],
            ]);

            // Create budget items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $budget->items()->create([
                        'category' => $item['category'],
                        'description' => $item['description'] ?? null,
                        'cost_code' => $item['cost_code'] ?? null,
                        'allocated_amount' => $item['allocated_amount'],
                        'spent_amount' => 0,
                        'remaining_amount' => $item['allocated_amount'],
                    ]);
                }
            }

            return $budget->load('items');
        });
    }

    /**
     * Approve a budget.
     */
    public function approveBudget(Budget $budget, User $approver): Budget
    {
        return DB::transaction(function () use ($budget, $approver) {
            $budget->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Update project total budget
            $budget->project->update([
                'total_budget' => $budget->total_amount,
            ]);

            // Send notification to project team
            $this->sendBudgetApprovalNotification($budget);

            // Clear cache
            Cache::forget("budget_{$budget->id}");
            Cache::forget("project_{$budget->project_id}_budgets");

            return $budget;
        });
    }

    /**
     * Update an existing budget.
     *
     * @throws BudgetValidationException
     */
    public function updateBudget(Budget $budget, array $data): Budget
    {
        // Calculate total amount from items
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $totalAmount += (float) ($item['allocated_amount'] ?? 0);
            }
        }

        // Validate budget total BEFORE updating
        $this->validateBudgetTotalBeforeUpdate($budget, $totalAmount);

        return DB::transaction(function () use ($budget, $data, $totalAmount) {
            // Update budget
            $budget->update([
                'project_id' => $data['project_id'],
                'fiscal_year' => $data['fiscal_year'],
                'total_amount' => $totalAmount,
            ]);

            // Delete old items and create new ones
            $budget->items()->delete();

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $budget->items()->create([
                        'category' => $item['category'],
                        'description' => $item['description'] ?? null,
                        'cost_code' => $item['cost_code'] ?? null,
                        'allocated_amount' => $item['allocated_amount'],
                        'spent_amount' => 0,
                        'remaining_amount' => $item['allocated_amount'],
                    ]);
                }
            }

            // Clear cache
            Cache::forget("budget_{$budget->id}");
            Cache::forget("project_{$budget->project_id}_budgets");

            return $budget->load('items');
        });
    }

    /**
     * Create a budget reallocation request.
     */
    public function requestReallocation(array $data): BudgetReallocation
    {
        return DB::transaction(function () use ($data) {
            // Validate reallocation
            $fromItem = BudgetItem::findOrFail($data['from_budget_item_id']);
            $toItem = BudgetItem::findOrFail($data['to_budget_item_id']);

            if ($fromItem->remaining_amount < $data['amount']) {
                throw new \Exception('Insufficient funds in source budget item');
            }

            // Create reallocation request
            $reallocation = BudgetReallocation::create([
                'budget_id' => $data['budget_id'],
                'from_budget_item_id' => $data['from_budget_item_id'],
                'to_budget_item_id' => $data['to_budget_item_id'],
                'amount' => $data['amount'],
                'justification' => $data['justification'],
                'status' => 'pending',
                'requested_by' => $data['requested_by'],
            ]);

            return $reallocation;
        });
    }

    /**
     * Approve a budget reallocation.
     */
    public function approveReallocation(BudgetReallocation $reallocation, User $approver): BudgetReallocation
    {
        return DB::transaction(function () use ($reallocation, $approver) {
            // Update budget items
            $fromItem = $reallocation->fromBudgetItem;
            $toItem = $reallocation->toBudgetItem;

            $fromItem->decrement('allocated_amount', $reallocation->amount);
            $fromItem->update(['remaining_amount' => $fromItem->allocated_amount - $fromItem->spent_amount]);

            $toItem->increment('allocated_amount', $reallocation->amount);
            $toItem->update(['remaining_amount' => $toItem->allocated_amount - $toItem->spent_amount]);

            // Update reallocation status
            $reallocation->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Log to audit trail (Module 7 - Activity Logging)
            /* activity()
                ->performedOn($reallocation->budget)
                ->causedBy($approver)
                ->withProperties([
                    'reallocation_id' => $reallocation->id,
                    'from_item' => $fromItem->category,
                    'to_item' => $toItem->category,
                    'amount' => $reallocation->amount,
                ])
                ->log('budget_reallocation_approved'); */

            return $reallocation;
        });
    }

    /**
     * Check budget thresholds and send alerts.
     */
    public function checkBudgetThresholds(Budget $budget): void
    {
        $utilization = $budget->utilization_percentage;
        $thresholds = [50, 90, 100];

        foreach ($thresholds as $threshold) {
            $cacheKey = "budget_{$budget->id}_alert_{$threshold}";

            if ($utilization >= $threshold && ! Cache::has($cacheKey)) {
                $this->sendBudgetAlert($budget, $threshold);
                Cache::put($cacheKey, true, now()->addDays(7));
            }
        }
    }

    /**
     * Update budget item spent amount.
     */
    public function updateBudgetItemSpent(BudgetItem $item, float $amount): void
    {
        DB::transaction(function () use ($item, $amount) {
            $item->increment('spent_amount', $amount);
            $item->update(['remaining_amount' => $item->allocated_amount - $item->spent_amount]);

            // Check thresholds for the entire budget
            $this->checkBudgetThresholds($item->budget);
        });
    }

    /**
     * Validate budget total against donor funding BEFORE creating.
     *
     * This method validates the budget total before the transaction starts
     * to provide immediate feedback and avoid rolling back transactions.
     *
     * @throws BudgetValidationException
     */
    protected function validateBudgetTotalBeforeCreate(int $projectId, float $budgetTotal, ?int $excludeBudgetId = null): void
    {
        $project = Project::find($projectId);

        if (! $project) {
            throw new BudgetValidationException('Project not found', [
                'project_id' => $projectId,
            ]);
        }

        // Get total donor funding for the project
        // Use coalesce to handle NULL values from the sum
        $totalDonorFunding = (float) ($project->donors()->sum('project_donors.funding_amount') ?? 0);

        // Only validate if there's donor funding set up for the project
        // Projects without donor funding assigned can still create budgets
        if ($totalDonorFunding <= 0) {
            return;
        }

        // Calculate existing budgets (excluding the specified budget if editing)
        $existingBudgetsQuery = $project->budgets();
        if ($excludeBudgetId !== null) {
            $existingBudgetsQuery->where('id', '!=', $excludeBudgetId);
        }
        $existingBudgets = (float) ($existingBudgetsQuery->sum('total_amount') ?? 0);

        $availableFunding = $totalDonorFunding - $existingBudgets;

        if ($budgetTotal > $availableFunding) {
            throw new BudgetValidationException(
                sprintf(
                    'Budget total ($%s) exceeds available donor funding. Total funding: $%s, Already allocated: $%s, Available: $%s',
                    number_format($budgetTotal, 2),
                    number_format($totalDonorFunding, 2),
                    number_format($existingBudgets, 2),
                    number_format($availableFunding, 2)
                ),
                [
                    'budget_total' => $budgetTotal,
                    'total_funding' => $totalDonorFunding,
                    'existing_budgets' => $existingBudgets,
                    'available_funding' => $availableFunding,
                ]
            );
        }
    }

    /**
     * Validate budget total against donor funding BEFORE updating.
     *
     * @throws BudgetValidationException
     */
    protected function validateBudgetTotalBeforeUpdate(Budget $budget, float $budgetTotal): void
    {
        $this->validateBudgetTotalBeforeCreate($budget->project_id, $budgetTotal, $budget->id);
    }

    /**
     * Validate budget total against donor funding.
     *
     * Note: Validation is only performed if the project has donors with funding amounts.
     * If no donor funding is set up, the budget can still be created.
     *
     * @deprecated Use validateBudgetTotalBeforeCreate instead for new budget creation
     *
     * @throws BudgetValidationException
     */
    protected function validateBudgetTotal(Budget $budget): void
    {
        $project = $budget->project;

        if (! $project) {
            throw new BudgetValidationException('Budget has no associated project', [
                'budget_id' => $budget->id,
            ]);
        }

        $totalDonorFunding = (float) ($project->donors()->sum('project_donors.funding_amount') ?? 0);

        // Only validate if there's donor funding set up for the project
        // Projects without donor funding assigned can still create budgets
        if ($totalDonorFunding > 0) {
            // Calculate existing budgets (excluding the current one)
            $existingBudgets = (float) ($project->budgets()
                ->where('id', '!=', $budget->id)
                ->sum('total_amount') ?? 0);

            $availableFunding = $totalDonorFunding - $existingBudgets;

            if ($budget->total_amount > $availableFunding) {
                throw new BudgetValidationException(
                    sprintf(
                        'Budget total ($%s) exceeds available donor funding. Total funding: $%s, Already allocated: $%s, Available: $%s',
                        number_format($budget->total_amount, 2),
                        number_format($totalDonorFunding, 2),
                        number_format($existingBudgets, 2),
                        number_format($availableFunding, 2)
                    ),
                    [
                        'budget_total' => $budget->total_amount,
                        'total_funding' => $totalDonorFunding,
                        'existing_budgets' => $existingBudgets,
                        'available_funding' => $availableFunding,
                    ]
                );
            }
        }
    }

    /**
     * Send budget approval notification.
     */
    protected function sendBudgetApprovalNotification(Budget $budget): void
    {
        $recipients = $budget->project->teamMembers;

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new BudgetApprovedNotification($budget));
        }
    }

    /**
     * Send budget alert notification.
     */
    protected function sendBudgetAlert(Budget $budget, int $threshold): void
    {
        $recipients = collect([
            $budget->project->creator,
            ...$budget->project->teamMembers,
        ])->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new BudgetAlertNotification($budget, $threshold));
        }
    }

    /**
     * Get budget summary statistics.
     */
    public function getBudgetSummary(Budget $budget): array
    {
        return [
            'total_allocated' => $budget->total_allocated,
            'total_spent' => $budget->total_spent,
            'total_remaining' => $budget->total_remaining,
            'utilization_percentage' => $budget->utilization_percentage,
            'alert_level' => $budget->alert_level,
            'items_by_category' => $budget->items()
                ->selectRaw('category, SUM(allocated_amount) as total_allocated, SUM(spent_amount) as total_spent')
                ->groupBy('category')
                ->get(),
        ];
    }

    /**
     * Get budget categories.
     */
    public function getBudgetCategories(): array
    {
        return [
            'Travel',
            'Staff Salaries',
            'Procurement/Supplies',
            'Consultants',
            'Other',
        ];
    }

    /**
     * Record an expense against a budget item.
     */
    public function recordExpense(int $budgetItemId, float $amount): BudgetItem
    {
        return DB::transaction(function () use ($budgetItemId, $amount) {
            $budgetItem = BudgetItem::findOrFail($budgetItemId);

            // Update spent and remaining amounts
            $budgetItem->spent_amount += $amount;
            $budgetItem->remaining_amount = $budgetItem->allocated_amount - $budgetItem->spent_amount;
            $budgetItem->save();

            // Check if budget threshold is reached for the parent budget
            if ($budgetItem->budget) {
                $this->checkBudgetThresholds($budgetItem->budget);
            }

            return $budgetItem;
        });
    }
}
