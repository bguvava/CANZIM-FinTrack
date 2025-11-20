<?php

namespace App\Services;

use App\Models\Donor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DonorPDFService
{
    /**
     * Generate Donor Financial Report PDF.
     */
    public function generateDonorFinancialReport(Donor $donor): string
    {
        $data = $this->prepareDonorFinancialData($donor);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.donor-financial-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateDonorReportFilename($donor);

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put("reports/donors/{$filename}", $pdfContent);

        return $filename;
    }

    /**
     * Generate Donors Summary Report PDF.
     */
    public function generateDonorsSummaryReport(array $filters = []): string
    {
        $data = $this->prepareDonorsSummaryData($filters);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.donors-summary-report', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateDonorsSummaryFilename();

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put("reports/donors/{$filename}", $pdfContent);

        return $filename;
    }

    /**
     * Prepare donor financial data for PDF.
     */
    protected function prepareDonorFinancialData(Donor $donor): array
    {
        $donor->load([
            'projects' => function ($query) {
                $query->withPivot(['funding_amount', 'funding_period_start', 'funding_period_end', 'is_restricted', 'notes']);
            },
            'projects.budgets',
            'inKindContributions',
            'communications' => function ($query) {
                $query->orderBy('communication_date', 'desc');
            },
        ]);

        // Calculate funding totals
        $totalFunding = $donor->projects->sum('pivot.funding_amount');
        $restrictedFunding = $donor->projects->where('pivot.is_restricted', true)->sum('pivot.funding_amount');
        $unrestrictedFunding = $totalFunding - $restrictedFunding;
        $totalInKindValue = $donor->inKindContributions->sum('estimated_value');

        // Get active projects
        $activeProjects = $donor->projects->filter(function ($project) {
            return $project->status !== 'cancelled' && $project->status !== 'completed';
        });

        // Get completed projects
        $completedProjects = $donor->projects->filter(function ($project) {
            return $project->status === 'completed';
        });

        // Group in-kind contributions by category
        $inKindByCategory = $donor->inKindContributions->groupBy('category')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total_value' => $items->sum('estimated_value'),
            ];
        });

        // Recent communications (last 10)
        $recentCommunications = $donor->communications->take(10);

        return [
            'donor' => $donor,
            'total_funding' => $totalFunding,
            'restricted_funding' => $restrictedFunding,
            'unrestricted_funding' => $unrestrictedFunding,
            'total_inkind_value' => $totalInKindValue,
            'grand_total_contribution' => $totalFunding + $totalInKindValue,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'total_projects' => $donor->projects->count(),
            'active_projects_count' => $activeProjects->count(),
            'in_kind_contributions' => $donor->inKindContributions,
            'in_kind_by_category' => $inKindByCategory,
            'recent_communications' => $recentCommunications,
            'total_communications' => $donor->communications->count(),
            'generated_at' => now()->format('d M Y H:i:s'),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Prepare donors summary data for PDF.
     */
    protected function prepareDonorsSummaryData(array $filters): array
    {
        $status = $filters['status'] ?? 'active';
        $minFunding = $filters['min_funding'] ?? 0;

        // Build query
        $query = Donor::with(['projects', 'inKindContributions'])
            ->where('status', $status);

        if ($minFunding > 0) {
            $query->whereHas('projects', function ($q) use ($minFunding) {
                $q->havingRaw('SUM(funding_amount) >= ?', [$minFunding]);
            });
        }

        $donors = $query->orderBy('donor_name')->get();

        // Calculate donor statistics
        $donorStats = $donors->map(function ($donor) {
            $totalFunding = $donor->projects->sum('pivot.funding_amount');
            $restrictedFunding = $donor->projects->where('pivot.is_restricted', true)->sum('pivot.funding_amount');
            $inKindValue = $donor->inKindContributions->sum('estimated_value');
            $activeProjects = $donor->projects->filter(function ($project) {
                return $project->status !== 'cancelled' && $project->status !== 'completed';
            })->count();

            return [
                'donor' => $donor,
                'total_funding' => $totalFunding,
                'restricted_funding' => $restrictedFunding,
                'unrestricted_funding' => $totalFunding - $restrictedFunding,
                'in_kind_value' => $inKindValue,
                'total_contribution' => $totalFunding + $inKindValue,
                'active_projects' => $activeProjects,
                'total_projects' => $donor->projects->count(),
            ];
        });

        // Sort by total contribution (highest first)
        $donorStats = $donorStats->sortByDesc('total_contribution')->values();

        // Calculate grand totals
        $grandTotalFunding = $donorStats->sum('total_funding');
        $grandRestrictedFunding = $donorStats->sum('restricted_funding');
        $grandUnrestrictedFunding = $donorStats->sum('unrestricted_funding');
        $grandInKindValue = $donorStats->sum('in_kind_value');
        $grandTotalContribution = $grandTotalFunding + $grandInKindValue;

        return [
            'donor_stats' => $donorStats,
            'grand_total_funding' => $grandTotalFunding,
            'grand_restricted_funding' => $grandRestrictedFunding,
            'grand_unrestricted_funding' => $grandUnrestrictedFunding,
            'grand_inkind_value' => $grandInKindValue,
            'grand_total_contribution' => $grandTotalContribution,
            'total_donors' => $donors->count(),
            'status_filter' => ucfirst($status),
            'min_funding_filter' => $minFunding > 0 ? '$'.number_format($minFunding, 2) : 'None',
            'generated_at' => now()->format('d M Y H:i:s'),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Generate filename for donor financial report.
     */
    protected function generateDonorReportFilename(Donor $donor): string
    {
        $donorSlug = str_replace(' ', '-', strtolower($donor->name));

        return sprintf(
            'donor-financial-report-%s-%s.pdf',
            $donorSlug,
            now()->format('Ymd-His')
        );
    }

    /**
     * Generate filename for donors summary report.
     */
    protected function generateDonorsSummaryFilename(): string
    {
        return sprintf(
            'donors-summary-report-%s.pdf',
            now()->format('Ymd-His')
        );
    }

    /**
     * Get report download path.
     */
    public function getReportDownloadPath(string $filename): string
    {
        return Storage::disk('local')->path("reports/donors/{$filename}");
    }
}
