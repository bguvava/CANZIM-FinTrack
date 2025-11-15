<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Project;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
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
}
