<?php

namespace App\Services;

use App\Models\Donor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DonorPDFService
{
    /**
     * Generate Donor Financial Report PDF with optional filters.
     */
    public function generateDonorFinancialReport(Donor $donor, array $filters = []): string
    {
        $data = $this->prepareDonorFinancialData($donor, $filters);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.donors.donor-financial-report', $data)
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
     * Prepare donor financial data for PDF with optional filters.
     */
    protected function prepareDonorFinancialData(Donor $donor, array $filters = []): array
    {
        // Build query with filters
        $projectsQuery = $donor->projects()
            ->withPivot(['funding_amount', 'funding_period_start', 'funding_period_end', 'is_restricted', 'notes']);

        // Apply date filters to project funding periods
        if (! empty($filters['date_from'])) {
            $projectsQuery->where('project_donors.funding_period_start', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $projectsQuery->where('project_donors.funding_period_end', '<=', $filters['date_to']);
        }

        // Filter by specific projects
        if (! empty($filters['project_ids']) && is_array($filters['project_ids'])) {
            $projectsQuery->whereIn('projects.id', $filters['project_ids']);
        }

        $projects = $projectsQuery->get();

        // Load in-kind contributions with filters
        $inKindQuery = $donor->inKindContributions()->with('project');

        if (! empty($filters['date_from'])) {
            $inKindQuery->where('contribution_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $inKindQuery->where('contribution_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['project_ids']) && is_array($filters['project_ids'])) {
            $inKindQuery->whereIn('project_id', $filters['project_ids']);
        }

        $inKindContributions = ! empty($filters['include_in_kind']) && $filters['include_in_kind']
            ? $inKindQuery->get()
            : collect();

        // Load communications
        $donor->load([
            'communications' => fn ($query) => $query->orderBy('communication_date', 'desc')->limit(10),
        ]);

        // Calculate funding totals
        $totalFunding = $projects->sum('pivot.funding_amount');
        $restrictedFunding = $projects->where('pivot.is_restricted', true)->sum('pivot.funding_amount');
        $unrestrictedFunding = $totalFunding - $restrictedFunding;
        $totalInKindValue = $inKindContributions->sum('estimated_value');

        // Get active projects
        $activeProjects = $projects->filter(fn ($project) => in_array($project->status, ['active', 'planning']));

        // Get completed projects
        $completedProjects = $projects->filter(fn ($project) => $project->status === 'completed');

        // Group in-kind contributions by category
        $inKindByCategory = $inKindContributions->groupBy('category')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total_value' => $items->sum('estimated_value'),
            ];
        });

        // Get logo as base64
        $logoPath = public_path('images/canzim-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,'.$logoData;
        }

        return [
            'donor' => $donor,
            'total_funding' => $totalFunding,
            'restricted_funding' => $restrictedFunding,
            'unrestricted_funding' => $unrestrictedFunding,
            'total_inkind_value' => $totalInKindValue,
            'grand_total_contribution' => $totalFunding + $totalInKindValue,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'total_projects' => $projects->count(),
            'active_projects_count' => $activeProjects->count(),
            'in_kind_contributions' => $inKindContributions,
            'in_kind_by_category' => $inKindByCategory,
            'recent_communications' => $donor->communications,
            'total_communications' => $donor->communications()->count(),
            'filters' => $filters,
            'reportTitle' => 'Donor Financial Report',
            'logoBase64' => $logoBase64,
            'organizationName' => 'Climate Action Network Zimbabwe',
            'generatedBy' => auth()->user()->name,
            'userRole' => auth()->user()->role->name ?? 'User',
            'generatedAt' => now()->format('M d, Y H:i:s'),
            'year' => now()->year,
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
