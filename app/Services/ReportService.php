<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Donor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    /**
     * Generate project financial report.
     */
    public function generateProjectFinancialReport(Project $project, string $format = 'pdf'): string
    {
        $data = $this->prepareProjectFinancialData($project);

        if ($format === 'pdf') {
            return $this->generatePDF('reports.project-financial', $data, $project);
        }

        throw new \Exception('Unsupported report format');
    }

    /**
     * Generate budget report.
     */
    public function generateBudgetReport(Budget $budget, string $format = 'pdf'): string
    {
        $data = $this->prepareBudgetData($budget);

        if ($format === 'pdf') {
            return $this->generatePDF('reports.budget', $data, $budget->project);
        }

        throw new \Exception('Unsupported report format');
    }

    /**
     * Prepare project financial data for report.
     */
    protected function prepareProjectFinancialData(Project $project): array
    {
        $project->load([
            'budgets.items',
            'expenses',
            'donors',
            'creator',
        ]);

        // Calculate budget vs actual by category
        $budgetByCategory = [];
        $actualByCategory = [];

        foreach ($project->budgets as $budget) {
            foreach ($budget->items as $item) {
                if (! isset($budgetByCategory[$item->category])) {
                    $budgetByCategory[$item->category] = 0;
                    $actualByCategory[$item->category] = 0;
                }

                $budgetByCategory[$item->category] += $item->allocated_amount;
                $actualByCategory[$item->category] += $item->spent_amount;
            }
        }

        // Calculate variance
        $varianceAnalysis = [];
        foreach ($budgetByCategory as $category => $budgeted) {
            $actual = $actualByCategory[$category] ?? 0;
            $variance = $budgeted - $actual;
            $variancePercentage = $budgeted > 0 ? (($variance / $budgeted) * 100) : 0;

            $varianceAnalysis[$category] = [
                'budgeted' => $budgeted,
                'actual' => $actual,
                'variance' => $variance,
                'variance_percentage' => $variancePercentage,
            ];
        }

        return [
            'project' => $project,
            'total_budget' => $project->total_budget,
            'total_spent' => $project->total_spent,
            'total_remaining' => $project->remaining_budget,
            'utilization_percentage' => $project->budget_utilization,
            'budget_by_category' => $budgetByCategory,
            'actual_by_category' => $actualByCategory,
            'variance_analysis' => $varianceAnalysis,
            'donor_funding' => $project->donors->map(function ($donor) {
                return [
                    'name' => $donor->name,
                    'funding_amount' => $donor->pivot->funding_amount,
                    'is_restricted' => $donor->pivot->is_restricted,
                ];
            }),
            'generated_at' => now(),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Prepare budget data for report.
     */
    protected function prepareBudgetData(Budget $budget): array
    {
        $budget->load([
            'project',
            'items',
            'creator',
            'approver',
        ]);

        return [
            'budget' => $budget,
            'project' => $budget->project,
            'items' => $budget->items,
            'total_allocated' => $budget->total_allocated,
            'total_spent' => $budget->total_spent,
            'total_remaining' => $budget->total_remaining,
            'utilization_percentage' => $budget->utilization_percentage,
            'alert_level' => $budget->alert_level,
            'generated_at' => now(),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Generate PDF from view.
     */
    protected function generatePDF(string $view, array $data, Project $project): string
    {
        // Generate PDF
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateFilename($project);

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put("reports/{$filename}", $pdfContent);

        // Create report record
        $this->createReportRecord($project, $filename, $view);

        return $filename;
    }

    /**
     * Generate unique filename for report.
     */
    protected function generateFilename(Project $project): string
    {
        return sprintf(
            '%s-%s.pdf',
            $project->code,
            now()->format('Ymd-His')
        );
    }

    /**
     * Create report record in database.
     */
    protected function createReportRecord(Project $project, string $filename, string $type): Report
    {
        return Report::create([
            'project_id' => $project->id,
            'type' => $type,
            'file_path' => "reports/{$filename}",
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);
    }

    /**
     * Get report download path.
     */
    public function getReportDownloadPath(string $filename): string
    {
        return Storage::disk('local')->path("reports/{$filename}");
    }

    /**
     * Get report view URL.
     */
    public function getReportViewUrl(string $filename): string
    {
        return url("api/reports/view/{$filename}");
    }

    /**
     * Generate Budget vs Actual Report.
     */
    public function generateBudgetVsActualReport(array $parameters): array
    {
        $cacheKey = 'report:budget-vs-actual:'.md5(json_encode($parameters));

        return Cache::remember($cacheKey, 300, function () use ($parameters) {
            $startDate = $parameters['start_date'] ?? null;
            $endDate = $parameters['end_date'] ?? null;
            $projectIds = $parameters['project_ids'] ?? [];
            $categoryIds = $parameters['category_ids'] ?? [];

            // Build budget items query
            $budgetQuery = BudgetItem::query()
                ->with(['budget.project', 'category'])
                ->whereHas('budget', function ($query) use ($projectIds) {
                    if (! empty($projectIds)) {
                        $query->whereIn('project_id', $projectIds);
                    }
                });

            if (! empty($categoryIds)) {
                $budgetQuery->whereIn('expense_category_id', $categoryIds);
            }

            $budgetItems = $budgetQuery->get();

            // Build expense query
            $expenseQuery = Expense::query()
                ->with(['project', 'category'])
                ->where('status', 'Paid');

            if (! empty($projectIds)) {
                $expenseQuery->whereIn('project_id', $projectIds);
            }

            if (! empty($categoryIds)) {
                $expenseQuery->whereIn('expense_category_id', $categoryIds);
            }

            if ($startDate && $endDate) {
                $expenseQuery->whereBetween('expense_date', [$startDate, $endDate]);
            }

            $expenses = $expenseQuery->get();

            // Group data by project and category
            $data = [];

            foreach ($budgetItems as $budgetItem) {
                $projectId = $budgetItem->budget->project_id;
                $categoryId = $budgetItem->expense_category_id;
                $key = "{$projectId}_{$categoryId}";

                if (! isset($data[$key])) {
                    $data[$key] = [
                        'project_id' => $projectId,
                        'project_name' => $budgetItem->budget->project->name,
                        'category_id' => $categoryId,
                        'category_name' => $budgetItem->category->name,
                        'budget' => 0,
                        'actual' => 0,
                    ];
                }

                $data[$key]['budget'] += $budgetItem->amount;
            }

            foreach ($expenses as $expense) {
                $key = "{$expense->project_id}_{$expense->expense_category_id}";

                if (! isset($data[$key])) {
                    $data[$key] = [
                        'project_id' => $expense->project_id,
                        'project_name' => $expense->project->name,
                        'category_id' => $expense->expense_category_id,
                        'category_name' => $expense->category->name,
                        'budget' => 0,
                        'actual' => 0,
                    ];
                }

                $data[$key]['actual'] += $expense->amount;
            }

            // Calculate variances
            foreach ($data as &$item) {
                $item['variance'] = $item['actual'] - $item['budget'];
                $item['variance_percentage'] = $item['budget'] > 0
                    ? (($item['variance'] / $item['budget']) * 100)
                    : 0;
            }

            return [
                'type' => 'budget-vs-actual',
                'parameters' => $parameters,
                'data' => array_values($data),
                'summary' => [
                    'total_budget' => array_sum(array_column($data, 'budget')),
                    'total_actual' => array_sum(array_column($data, 'actual')),
                    'total_variance' => array_sum(array_column($data, 'variance')),
                ],
            ];
        });
    }

    /**
     * Generate Cash Flow Report.
     */
    public function generateCashFlowReport(array $parameters): array
    {
        $cacheKey = 'report:cash-flow:'.md5(json_encode($parameters));

        return Cache::remember($cacheKey, 300, function () use ($parameters) {
            $startDate = Carbon::parse($parameters['start_date'] ?? now()->subYear());
            $endDate = Carbon::parse($parameters['end_date'] ?? now());
            $grouping = $parameters['grouping'] ?? 'month';

            // Get all paid expenses (outflows)
            $expenses = Expense::query()
                ->where('status', 'Paid')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->select(
                    DB::raw($this->getDateGrouping('expense_date', $grouping).' as period'),
                    DB::raw('SUM(amount) as amount')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Get donor funding (inflows) - using project_donors pivot
            $funding = DB::table('project_donors')
                ->whereBetween('funding_period_start', [$startDate, $endDate])
                ->orWhereBetween('funding_period_end', [$startDate, $endDate])
                ->select(
                    DB::raw($this->getDateGrouping('funding_period_start', $grouping).' as period'),
                    DB::raw('SUM(funding_amount) as amount')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Combine and calculate net flow
            $periods = $this->generatePeriods($startDate, $endDate, $grouping);
            $cashFlowData = [];
            $runningBalance = 0;

            foreach ($periods as $period) {
                $inflow = $funding->where('period', $period)->sum('amount');
                $outflow = $expenses->where('period', $period)->sum('amount');
                $netFlow = $inflow - $outflow;
                $runningBalance += $netFlow;

                $cashFlowData[] = [
                    'period' => $period,
                    'inflow' => $inflow,
                    'outflow' => $outflow,
                    'net_flow' => $netFlow,
                    'running_balance' => $runningBalance,
                ];
            }

            return [
                'type' => 'cash-flow',
                'parameters' => $parameters,
                'data' => $cashFlowData,
                'summary' => [
                    'total_inflow' => array_sum(array_column($cashFlowData, 'inflow')),
                    'total_outflow' => array_sum(array_column($cashFlowData, 'outflow')),
                    'total_net_flow' => array_sum(array_column($cashFlowData, 'net_flow')),
                    'final_balance' => end($cashFlowData)['running_balance'] ?? 0,
                ],
            ];
        });
    }

    /**
     * Generate Expense Summary Report.
     */
    public function generateExpenseSummaryReport(array $parameters): array
    {
        $cacheKey = 'report:expense-summary:'.md5(json_encode($parameters));

        return Cache::remember($cacheKey, 300, function () use ($parameters) {
            $startDate = $parameters['start_date'] ?? null;
            $endDate = $parameters['end_date'] ?? null;
            $projectIds = $parameters['project_ids'] ?? [];
            $categoryIds = $parameters['category_ids'] ?? [];
            $groupBy = $parameters['group_by'] ?? 'category';

            $query = Expense::query()
                ->with(['project', 'category'])
                ->where('status', 'Paid');

            if ($startDate && $endDate) {
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            }

            if (! empty($projectIds)) {
                $query->whereIn('project_id', $projectIds);
            }

            if (! empty($categoryIds)) {
                $query->whereIn('expense_category_id', $categoryIds);
            }

            $expenses = $query->get();
            $totalExpenses = $expenses->sum('amount');

            $groupedData = match ($groupBy) {
                'project' => $this->groupByProject($expenses, $totalExpenses),
                'month' => $this->groupByMonth($expenses, $totalExpenses),
                default => $this->groupByCategory($expenses, $totalExpenses),
            };

            return [
                'type' => 'expense-summary',
                'parameters' => $parameters,
                'data' => $groupedData,
                'summary' => [
                    'total_expenses' => $totalExpenses,
                    'total_count' => $expenses->count(),
                    'average_expense' => $expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0,
                ],
            ];
        });
    }

    /**
     * Generate Project Status Report.
     */
    public function generateProjectStatusReport(array $parameters): array
    {
        $cacheKey = 'report:project-status:'.md5(json_encode($parameters));

        return Cache::remember($cacheKey, 300, function () use ($parameters) {
            $projectId = $parameters['project_id'];

            $project = Project::with([
                'budgets.items.category',
                'expenses.category',
                'donors',
                'creator',
            ])->findOrFail($projectId);

            $totalBudget = $project->budgets->sum(function ($budget) {
                return $budget->items->sum('amount');
            });

            $totalSpent = $project->expenses->where('status', 'Paid')->sum('amount');
            $remainingBudget = $totalBudget - $totalSpent;
            $utilizationPercentage = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;

            // Budget breakdown by category
            $budgetByCategory = $project->budgets->flatMap(function ($budget) {
                return $budget->items;
            })->groupBy('expense_category_id')->map(function ($items) {
                return [
                    'category_name' => $items->first()->category->name ?? 'Uncategorized',
                    'budget' => $items->sum('amount'),
                ];
            })->values();

            // Expenses by category
            $expensesByCategory = $project->expenses
                ->where('status', 'Paid')
                ->groupBy('expense_category_id')
                ->map(function ($expenses) {
                    return [
                        'category_name' => $expenses->first()->category->name ?? 'Uncategorized',
                        'actual' => $expenses->sum('amount'),
                    ];
                })->values();

            // Timeline progress
            $startDate = Carbon::parse($project->start_date);
            $endDate = Carbon::parse($project->end_date);
            $today = Carbon::now();

            $totalDuration = $startDate->diffInDays($endDate);
            $elapsedDuration = $startDate->diffInDays($today);
            $timelineProgress = $totalDuration > 0 ? min(100, ($elapsedDuration / $totalDuration) * 100) : 0;

            return [
                'type' => 'project-status',
                'parameters' => $parameters,
                'project' => [
                    'id' => $project->id,
                    'code' => $project->code,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'start_date' => $project->start_date->format('Y-m-d'),
                    'end_date' => $project->end_date->format('Y-m-d'),
                    'office_location' => $project->office_location,
                ],
                'financial_summary' => [
                    'total_budget' => $totalBudget,
                    'total_spent' => $totalSpent,
                    'remaining_budget' => $remainingBudget,
                    'utilization_percentage' => round($utilizationPercentage, 2),
                ],
                'budget_by_category' => $budgetByCategory,
                'expenses_by_category' => $expensesByCategory,
                'timeline' => [
                    'start_date' => $project->start_date->format('Y-m-d'),
                    'end_date' => $project->end_date->format('Y-m-d'),
                    'total_duration_days' => $totalDuration,
                    'elapsed_duration_days' => $elapsedDuration,
                    'remaining_duration_days' => max(0, $endDate->diffInDays($today)),
                    'timeline_progress_percentage' => round($timelineProgress, 2),
                ],
                'donors' => $project->donors->map(function ($donor) {
                    return [
                        'id' => $donor->id,
                        'name' => $donor->name,
                        'funding_amount' => $donor->pivot->funding_amount,
                        'is_restricted' => $donor->pivot->is_restricted,
                    ];
                }),
            ];
        });
    }

    /**
     * Generate Donor Contributions Report.
     */
    public function generateDonorContributionsReport(array $parameters): array
    {
        $cacheKey = 'report:donor-contributions:'.md5(json_encode($parameters));

        return Cache::remember($cacheKey, 300, function () use ($parameters) {
            $startDate = $parameters['start_date'] ?? null;
            $endDate = $parameters['end_date'] ?? null;
            $donorIds = $parameters['donor_ids'] ?? [];

            $query = Donor::with(['projects', 'inKindContributions']);

            if (! empty($donorIds)) {
                $query->whereIn('id', $donorIds);
            }

            $donors = $query->get();

            $donorData = $donors->map(function ($donor) use ($startDate, $endDate) {
                // Calculate total funding
                $fundingQuery = $donor->projects();

                if ($startDate && $endDate) {
                    $fundingQuery->wherePivot('funding_period_start', '>=', $startDate)
                        ->wherePivot('funding_period_end', '<=', $endDate);
                }

                $totalFunding = $fundingQuery->sum('project_donors.funding_amount');

                // Get in-kind contributions
                $inKindQuery = $donor->inKindContributions();

                if ($startDate && $endDate) {
                    $inKindQuery->whereBetween('contribution_date', [$startDate, $endDate]);
                }

                $inKindContributions = $inKindQuery->get();
                $totalInKindValue = $inKindContributions->sum('estimated_value');

                // Projects funded
                $projectsFunded = $donor->projects->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'code' => $project->code,
                        'name' => $project->name,
                        'funding_amount' => $project->pivot->funding_amount,
                        'is_restricted' => $project->pivot->is_restricted,
                    ];
                });

                return [
                    'donor_id' => $donor->id,
                    'donor_name' => $donor->name,
                    'donor_type' => $donor->type,
                    'country' => $donor->country,
                    'total_funding' => $totalFunding,
                    'total_in_kind_value' => $totalInKindValue,
                    'total_contribution' => $totalFunding + $totalInKindValue,
                    'projects_count' => $projectsFunded->count(),
                    'projects_funded' => $projectsFunded,
                    'in_kind_contributions_count' => $inKindContributions->count(),
                ];
            });

            return [
                'type' => 'donor-contributions',
                'parameters' => $parameters,
                'data' => $donorData,
                'summary' => [
                    'total_donors' => $donorData->count(),
                    'total_funding' => $donorData->sum('total_funding'),
                    'total_in_kind' => $donorData->sum('total_in_kind_value'),
                    'total_contributions' => $donorData->sum('total_contribution'),
                    'total_projects' => $donorData->sum('projects_count'),
                ],
            ];
        });
    }

    /**
     * Generate Custom Report.
     */
    public function generateCustomReport(array $parameters): array
    {
        // Custom report builder - delegates to specific report types
        $reportType = $parameters['type'] ?? 'expense-summary';

        return match ($reportType) {
            'budget-vs-actual' => $this->generateBudgetVsActualReport($parameters),
            'cash-flow' => $this->generateCashFlowReport($parameters),
            'project-status' => $this->generateProjectStatusReport($parameters),
            'donor-contributions' => $this->generateDonorContributionsReport($parameters),
            default => $this->generateExpenseSummaryReport($parameters),
        };
    }

    /**
     * Save report metadata to database.
     */
    public function saveReportMetadata(
        string $type,
        string $title,
        array $parameters,
        int $userId,
        ?string $filePath = null
    ): Report {
        return Report::create([
            'type' => $type,
            'title' => $title,
            'parameters' => $parameters,
            'file_path' => $filePath,
            'generated_by' => $userId,
            'status' => $filePath ? 'completed' : 'pending',
        ]);
    }

    /**
     * Clear report cache.
     */
    public function clearCache(?string $type = null): void
    {
        if ($type) {
            Cache::tags(['reports', "report:{$type}"])->flush();
        } else {
            Cache::tags('reports')->flush();
        }
    }

    /**
     * Get date grouping SQL expression.
     */
    private function getDateGrouping(string $column, string $grouping): string
    {
        return match ($grouping) {
            'quarter' => "CONCAT(YEAR({$column}), '-Q', QUARTER({$column}))",
            'year' => "YEAR({$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * Generate periods based on date range and grouping.
     */
    private function generatePeriods(Carbon $startDate, Carbon $endDate, string $grouping): array
    {
        $periods = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $periods[] = match ($grouping) {
                'quarter' => $current->format('Y').'-Q'.$current->quarter,
                'year' => $current->format('Y'),
                default => $current->format('Y-m'),
            };

            $current = match ($grouping) {
                'quarter' => $current->addQuarter(),
                'year' => $current->addYear(),
                default => $current->addMonth(),
            };
        }

        return $periods;
    }

    /**
     * Group expenses by category.
     */
    private function groupByCategory(Collection $expenses, float $totalExpenses): array
    {
        return $expenses->groupBy('expense_category_id')->map(function ($items) use ($totalExpenses) {
            $amount = $items->sum('amount');

            return [
                'category_name' => $items->first()->category->name ?? 'Uncategorized',
                'amount' => $amount,
                'count' => $items->count(),
                'percentage' => $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0,
            ];
        })->values()->toArray();
    }

    /**
     * Group expenses by project.
     */
    private function groupByProject(Collection $expenses, float $totalExpenses): array
    {
        return $expenses->groupBy('project_id')->map(function ($items) use ($totalExpenses) {
            $amount = $items->sum('amount');

            return [
                'project_name' => $items->first()->project->name ?? 'Unknown Project',
                'amount' => $amount,
                'count' => $items->count(),
                'percentage' => $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0,
            ];
        })->values()->toArray();
    }

    /**
     * Group expenses by month.
     */
    private function groupByMonth(Collection $expenses, float $totalExpenses): array
    {
        return $expenses->groupBy(function ($expense) {
            return Carbon::parse($expense->expense_date)->format('Y-m');
        })->map(function ($items, $month) use ($totalExpenses) {
            $amount = $items->sum('amount');

            return [
                'month' => $month,
                'amount' => $amount,
                'count' => $items->count(),
                'percentage' => $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0,
            ];
        })->values()->toArray();
    }
}
