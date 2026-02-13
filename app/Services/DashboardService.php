<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Dashboard Service
 *
 * Provides business logic for dashboard data generation
 * Includes KPI calculations, chart data, and activity feeds
 */
class DashboardService
{
    /**
     * Cache TTL in minutes
     */
    protected const CACHE_TTL = 5;

    /**
     * Get Programs Manager dashboard data
     */
    public function getProgramsManagerDashboard(User $user): array
    {
        return Cache::remember("dashboard_pm_{$user->id}", self::CACHE_TTL * 60, function () {
            // Calculate total budget across all approved budgets
            $totalBudget = \App\Models\Budget::query()
                ->where('status', 'approved')
                ->sum('total_amount');

            // Calculate YTD spending
            $ytdSpending = \App\Models\Expense::query()
                ->whereYear('expense_date', now()->year)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');

            // Calculate available funds
            $totalAllocated = \App\Models\BudgetItem::query()
                ->whereHas('budget', function ($query) {
                    $query->where('status', 'approved');
                })
                ->sum('allocated_amount');

            $totalSpent = \App\Models\BudgetItem::query()
                ->whereHas('budget', function ($query) {
                    $query->where('status', 'approved');
                })
                ->sum('spent_amount');

            $availableFunds = $totalAllocated - $totalSpent;

            // Count pending approvals
            $pendingExpenses = \App\Models\Expense::query()
                ->where('status', 'pending')
                ->count();

            $pendingBudgets = \App\Models\Budget::query()
                ->where('status', 'pending')
                ->count();

            $pendingPOs = \App\Models\PurchaseOrder::query()
                ->where('status', 'pending')
                ->count();

            return [
                'kpis' => [
                    'total_budget' => (float) $totalBudget,
                    'ytd_spending' => (float) $ytdSpending,
                    'available_funds' => (float) $availableFunds,
                    'pending_approvals' => [
                        'count' => $pendingExpenses + $pendingBudgets + $pendingPOs,
                        'breakdown' => [
                            'expenses' => $pendingExpenses,
                            'budgets' => $pendingBudgets,
                            'purchase_orders' => $pendingPOs,
                        ],
                    ],
                ],
                'charts' => [
                    'budget_utilization' => $this->getBudgetUtilizationData(),
                    'expense_trends' => $this->getExpenseTrendsData(),
                    'donor_allocation' => $this->getDonorAllocationData(),
                    'cash_flow_projection' => $this->getCashFlowProjectionData(),
                ],
                'recent_activity' => $this->getRecentActivity(20),
                'pending_items' => $this->getPendingItems(),
            ];
        });
    }

    /**
     * Get Finance Officer dashboard data
     */
    public function getFinanceOfficerDashboard(User $user): array
    {
        return Cache::remember("dashboard_fo_{$user->id}", self::CACHE_TTL * 60, function () {
            try {
                // Calculate monthly budget (current month)
                $monthlyBudgetSum = \App\Models\BudgetItem::query()
                    ->whereHas('budget', function ($query) {
                        $query->where('status', 'approved')
                            ->where('fiscal_year', now()->year);
                    })
                    ->sum('allocated_amount');

                $monthlyBudget = $monthlyBudgetSum > 0 ? $monthlyBudgetSum / 12 : 0;

                // Calculate actual expenses this month
                $actualExpenses = \App\Models\Expense::query()
                    ->whereYear('expense_date', now()->year)
                    ->whereMonth('expense_date', now()->month)
                    ->whereIn('status', ['approved', 'paid'])
                    ->sum('amount');

                // Calculate pending expenses - ensure we get valid numbers
                $pendingExpensesSum = \App\Models\Expense::query()
                    ->where('status', 'pending')
                    ->sum('amount');

                $pendingExpensesCount = \App\Models\Expense::query()
                    ->where('status', 'pending')
                    ->count();

                // Calculate cash balance from bank accounts
                $cashBalance = \App\Models\BankAccount::query()
                    ->where('is_active', true)
                    ->sum('current_balance');

                return [
                    'kpis' => [
                        'monthly_budget' => (float) ($monthlyBudget ?? 0),
                        'actual_expenses' => (float) ($actualExpenses ?? 0),
                        'pending_expenses' => [
                            'count' => (int) $pendingExpensesCount,
                            'total_amount' => (float) ($pendingExpensesSum ?? 0),
                        ],
                        'cash_balance' => (float) ($cashBalance ?? 0),
                    ],
                    'charts' => [
                        'budget_vs_actual' => $this->getBudgetVsActualData(),
                        'expense_categories' => $this->getExpenseCategoriesData(),
                    ],
                    'recent_activity' => $this->getRecentActivity(20),
                    'recent_transactions' => $this->getRecentTransactions(30),
                    'po_status' => $this->getPurchaseOrderStatus(),
                    'payment_schedule' => $this->getUpcomingPayments(30),
                ];
            } catch (\Exception $e) {
                Log::error('Error in getFinanceOfficerDashboard: '.$e->getMessage(), [
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Return safe defaults
                return [
                    'kpis' => [
                        'monthly_budget' => 0.0,
                        'actual_expenses' => 0.0,
                        'pending_expenses' => [
                            'count' => 0,
                            'total_amount' => 0.0,
                        ],
                        'cash_balance' => 0.0,
                    ],
                    'charts' => [
                        'budget_vs_actual' => [
                            'labels' => [],
                            'datasets' => [],
                        ],
                        'expense_categories' => [
                            'labels' => [],
                            'datasets' => [],
                        ],
                    ],
                    'recent_transactions' => [],
                    'po_status' => [
                        'pending' => 0,
                        'approved' => 0,
                        'partially_received' => 0,
                        'completed' => 0,
                    ],
                    'payment_schedule' => [],
                ];
            }
        });
    }

    /**
     * Get Project Officer dashboard data
     */
    public function getProjectOfficerDashboard(User $user): array
    {
        return Cache::remember("dashboard_po_{$user->id}", self::CACHE_TTL * 60, function () use ($user) {
            // Get assigned projects
            $assignedProjects = \App\Models\Project::query()
                ->whereHas('teamMembers', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('status', 'active')
                ->get();

            // Calculate total project budget
            $projectBudget = $assignedProjects->sum('total_budget');

            // Calculate budget used from expenses
            $projectIds = $assignedProjects->pluck('id');
            $budgetUsed = \App\Models\Expense::query()
                ->whereIn('project_id', $projectIds)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');

            $remainingBudget = $projectBudget - $budgetUsed;

            // Calculate activities completed (using comments/activity logs as proxy)
            $activitiesCompleted = \App\Models\ActivityLog::query()
                ->where('user_id', $user->id)
                ->whereIn('activity_type', ['task_completed', 'milestone_completed'])
                ->count();

            return [
                'kpis' => [
                    'project_budget' => (float) $projectBudget,
                    'budget_used' => (float) $budgetUsed,
                    'remaining_budget' => (float) $remainingBudget,
                    'activities_completed' => $activitiesCompleted,
                ],
                'assigned_projects' => $this->getAssignedProjects($user),
                'project_activities' => $this->getProjectActivities($user, 20),
                'recent_activity' => $this->getProjectActivities($user, 20),
                'personal_tasks' => $this->getPersonalTasks($user),
                'project_timeline' => $this->getProjectTimeline($user),
                'team_collaboration' => $this->getTeamCollaboration($user, 20),
            ];
        });
    }

    /**
     * Get notifications for user
     */
    public function getNotifications(User $user): array
    {
        return [
            'unread_count' => 0,
            'notifications' => [],
        ];
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(User $user, int $notificationId): void
    {
        // Implementation will be added when notification system is implemented
    }

    /**
     * Get budget utilization data for chart
     */
    protected function getBudgetUtilizationData(): array
    {
        $budgetData = \App\Models\Budget::query()
            ->with('project:id,name')
            ->where('status', 'approved')
            ->select('id', 'project_id', 'total_amount')
            ->get();

        $labels = [];
        $data = [];

        foreach ($budgetData as $budget) {
            $labels[] = $budget->project?->name ?? 'Unknown Project';
            $data[] = (float) $budget->total_amount;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Budget Utilization',
                    'data' => $data,
                    'backgroundColor' => [
                        '#1E40AF',
                        '#2563EB',
                        '#60A5FA',
                        '#93C5FD',
                        '#DBEAFE',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get expense trends data for chart
     */
    protected function getExpenseTrendsData(): array
    {
        $months = [];
        $actualExpenses = [];
        $budgetedAmounts = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            // Get actual expenses for this month
            $actual = \App\Models\Expense::query()
                ->whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');

            $actualExpenses[] = (float) $actual;

            // Get budgeted amounts for this month (fiscal year based)
            $budgeted = \App\Models\BudgetItem::query()
                ->whereHas('budget', function ($query) use ($date) {
                    $query->where('fiscal_year', $date->year)
                        ->where('status', 'approved');
                })
                ->sum('allocated_amount');

            $budgetedAmounts[] = (float) $budgeted / 12; // Average per month
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Actual Expenses',
                    'data' => $actualExpenses,
                    'borderColor' => '#1E40AF',
                    'backgroundColor' => 'rgba(30, 64, 175, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Budgeted',
                    'data' => $budgetedAmounts,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Get donor fund allocation data for chart
     */
    protected function getDonorAllocationData(): array
    {
        $donorData = \App\Models\Donor::query()
            ->select('donors.id', 'donors.name')
            ->selectRaw('COALESCE(SUM(project_donors.funding_amount), 0) as total_funding')
            ->leftJoin('project_donors', 'donors.id', '=', 'project_donors.donor_id')
            ->where('donors.status', 'active')
            ->groupBy('donors.id', 'donors.name')
            ->orderByDesc('total_funding')
            ->limit(10)
            ->get();

        $labels = [];
        $data = [];

        foreach ($donorData as $donor) {
            $labels[] = $donor->name;
            $data[] = (float) $donor->total_funding;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Funding Amount',
                    'data' => $data,
                    'backgroundColor' => '#1E40AF',
                ],
            ],
        ];
    }

    /**
     * Get cash flow projection data for chart
     */
    protected function getCashFlowProjectionData(): array
    {
        $months = [];
        $inflows = [];
        $outflows = [];

        for ($i = 0; $i < 6; $i++) {
            $date = now()->addMonths($i);
            $months[] = $date->format('M Y');

            // Calculate inflows (historical average for past months, projected for future)
            if ($i === 0) {
                // Current month - use actual + projected
                $inflow = \App\Models\CashFlow::query()
                    ->where('type', 'inflow')
                    ->whereYear('transaction_date', $date->year)
                    ->whereMonth('transaction_date', $date->month)
                    ->sum('amount');
            } else {
                // Future months - use average from last 3 months
                $inflow = \App\Models\CashFlow::query()
                    ->where('type', 'inflow')
                    ->where('transaction_date', '>=', now()->subMonths(3))
                    ->where('transaction_date', '<=', now())
                    ->avg('amount') ?? 0;
            }

            $inflows[] = (float) $inflow;

            // Calculate outflows
            if ($i === 0) {
                $outflow = \App\Models\CashFlow::query()
                    ->where('type', 'outflow')
                    ->whereYear('transaction_date', $date->year)
                    ->whereMonth('transaction_date', $date->month)
                    ->sum('amount');
            } else {
                // Use average expenses from last 3 months for projection
                $outflow = \App\Models\Expense::query()
                    ->whereIn('status', ['approved', 'paid'])
                    ->where('expense_date', '>=', now()->subMonths(3))
                    ->where('expense_date', '<=', now())
                    ->avg('amount') ?? 0;
            }

            $outflows[] = (float) $outflow;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Inflow',
                    'data' => $inflows,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Outflow',
                    'data' => $outflows,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Get budget vs actual data for chart
     */
    protected function getBudgetVsActualData(): array
    {
        try {
            $categories = \App\Models\ExpenseCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(5)
                ->get();

            $labels = [];
            $budgetData = [];
            $actualData = [];

            foreach ($categories as $category) {
                $labels[] = $category->name;

                // Get allocated budget for this category
                $budget = \App\Models\BudgetItem::query()
                    ->where('category', $category->name)
                    ->whereHas('budget', function ($query) {
                        $query->where('status', 'approved')
                            ->where('fiscal_year', now()->year);
                    })
                    ->sum('allocated_amount');

                $budgetData[] = (float) ($budget ?? 0);

                // Get actual expenses for this category
                $actual = \App\Models\Expense::query()
                    ->where('expense_category_id', $category->id)
                    ->whereYear('expense_date', now()->year)
                    ->whereIn('status', ['approved', 'paid'])
                    ->sum('amount');

                $actualData[] = (float) ($actual ?? 0);
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Budget',
                        'data' => $budgetData,
                        'backgroundColor' => '#60A5FA',
                    ],
                    [
                        'label' => 'Actual',
                        'data' => $actualData,
                        'backgroundColor' => '#1E40AF',
                    ],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error in getBudgetVsActualData: '.$e->getMessage());

            return [
                'labels' => [],
                'datasets' => [],
            ];
        }
    }

    /**
     * Get expense categories data for chart
     */
    protected function getExpenseCategoriesData(): array
    {
        try {
            $categoryExpenses = \App\Models\Expense::query()
                ->select('expense_category_id')
                ->selectRaw('SUM(amount) as total')
                ->with('expenseCategory:id,name')
                ->whereIn('status', ['approved', 'paid'])
                ->whereYear('expense_date', now()->year)
                ->groupBy('expense_category_id')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $labels = [];
            $data = [];

            foreach ($categoryExpenses as $expense) {
                $labels[] = $expense->expenseCategory?->name ?? 'Uncategorized';
                $data[] = (float) ($expense->total ?? 0);
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => [
                            '#1E40AF',
                            '#2563EB',
                            '#60A5FA',
                            '#93C5FD',
                            '#DBEAFE',
                        ],
                    ],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error in getExpenseCategoriesData: '.$e->getMessage());

            return [
                'labels' => [],
                'datasets' => [],
            ];
        }
    }

    /**
     * Get recent activity feed
     */
    protected function getRecentActivity(int $limit = 20): array
    {
        $activities = \App\Models\ActivityLog::query()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $feed = [];

        foreach ($activities as $activity) {
            $properties = is_string($activity->properties) ? json_decode($activity->properties, true) : $activity->properties;

            $feed[] = [
                'id' => $activity->id,
                'icon' => $this->getActivityIcon($activity->activity_type),
                'title' => $activity->user?->name ?? 'System',
                'description' => $activity->description,
                'time' => $this->formatActivityTime($activity->created_at),
            ];
        }

        return $feed;
    }

    /**
     * Get icon for activity type
     */
    protected function getActivityIcon(string $activityType): string
    {
        return match ($activityType) {
            'expense_created' => 'fa-receipt',
            'expense_approved' => 'fa-check-circle',
            'expense_rejected' => 'fa-times-circle',
            'expense_paid' => 'fa-money-bill-wave',
            'project_created' => 'fa-folder-plus',
            'project_updated' => 'fa-edit',
            'budget_created' => 'fa-chart-line',
            'budget_approved' => 'fa-check-double',
            'purchase_order_created' => 'fa-shopping-cart',
            'purchase_order_approved' => 'fa-clipboard-check',
            'document_uploaded' => 'fa-file-upload',
            'comment_added' => 'fa-comment',
            'user_login' => 'fa-sign-in-alt',
            default => 'fa-bell',
        };
    }

    /**
     * Format activity time as relative
     */
    protected function formatActivityTime(\DateTime|string $time): string
    {
        $carbon = $time instanceof \DateTime ? \Carbon\Carbon::instance($time) : \Carbon\Carbon::parse($time);

        $diffInMinutes = $carbon->diffInMinutes(now());

        if ($diffInMinutes < 1) {
            return 'Just now';
        } elseif ($diffInMinutes < 60) {
            return $diffInMinutes.' minute'.($diffInMinutes > 1 ? 's' : '').' ago';
        } elseif ($diffInMinutes < 1440) {
            $hours = floor($diffInMinutes / 60);

            return $hours.' hour'.($hours > 1 ? 's' : '').' ago';
        } elseif ($diffInMinutes < 10080) {
            $days = floor($diffInMinutes / 1440);

            return $days.' day'.($days > 1 ? 's' : '').' ago';
        } else {
            return $carbon->format('M d, Y');
        }
    }

    /**
     * Get pending approval items
     */
    protected function getPendingItems(): array
    {
        return [];
    }

    /**
     * Get recent transactions
     */
    protected function getRecentTransactions(int $days = 30): array
    {
        return [];
    }

    /**
     * Get purchase order status summary
     */
    protected function getPurchaseOrderStatus(): array
    {
        try {
            return [
                'pending' => \App\Models\PurchaseOrder::query()->where('status', 'pending')->count(),
                'approved' => \App\Models\PurchaseOrder::query()->where('status', 'approved')->count(),
                'partially_received' => \App\Models\PurchaseOrder::query()->where('status', 'partially_received')->count(),
                'completed' => \App\Models\PurchaseOrder::query()->where('status', 'completed')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPurchaseOrderStatus: '.$e->getMessage());

            return [
                'pending' => 0,
                'approved' => 0,
                'partially_received' => 0,
                'completed' => 0,
            ];
        }
    }

    /**
     * Get upcoming payments
     */
    protected function getUpcomingPayments(int $days = 30): array
    {
        return [];
    }

    /**
     * Get assigned projects for user
     */
    protected function getAssignedProjects(User $user): array
    {
        $projects = \App\Models\Project::query()
            ->whereHas('teamMembers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('creator:id,name')
            ->withCount(['expenses' => function ($query) {
                $query->whereIn('status', ['approved', 'paid']);
            }])
            ->limit(5)
            ->get();

        return $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'code' => $project->code,
                'status' => $project->status,
                'total_budget' => (float) $project->total_budget,
                'start_date' => $project->start_date?->format('M d, Y'),
                'end_date' => $project->end_date?->format('M d, Y'),
                'expenses_count' => $project->expenses_count,
            ];
        })->toArray();
    }

    /**
     * Get project activities
     */
    protected function getProjectActivities(User $user, int $limit = 20): array
    {
        $activities = \App\Models\ActivityLog::query()
            ->with('user:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $feed = [];

        foreach ($activities as $activity) {
            $feed[] = [
                'id' => $activity->id,
                'icon' => $this->getActivityIcon($activity->activity_type),
                'title' => $activity->user?->name ?? 'System',
                'description' => $activity->description,
                'time' => $this->formatActivityTime($activity->created_at),
            ];
        }

        return $feed;
    }

    /**
     * Get personal tasks
     */
    protected function getPersonalTasks(User $user): array
    {
        return [];
    }

    /**
     * Get project timeline
     */
    protected function getProjectTimeline(User $user): array
    {
        return [];
    }

    /**
     * Get team collaboration feed
     */
    protected function getTeamCollaboration(User $user, int $limit = 20): array
    {
        return [];
    }
}
