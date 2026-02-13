<?php

namespace App\Services;

use App\Models\Donor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

/**
 * Service for generating donor-related reports and PDFs.
 */
class DonorReportService
{
    /**
     * Generate financial report data for a donor with optional filters.
     */
    public function generateFinancialReport(Donor $donor, array $filters = []): array
    {
        // Load relationships
        $donor->load([
            'projects',
            'inKindContributions.project',
        ]);

        // Build projects query with filters
        $projectsQuery = $donor->projects()
            ->withPivot(['funding_amount', 'funding_period_start', 'funding_period_end', 'is_restricted', 'notes']);

        // Apply date filters
        if (! empty($filters['date_from'])) {
            $projectsQuery->where('project_donors.funding_period_start', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $projectsQuery->where('project_donors.funding_period_end', '<=', $filters['date_to']);
        }

        $projects = $projectsQuery->get();

        // Get in-kind contributions with filters
        $inKindQuery = $donor->inKindContributions()->with('project');

        if (! empty($filters['date_from'])) {
            $inKindQuery->where('contribution_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $inKindQuery->where('contribution_date', '<=', $filters['date_to']);
        }

        $includeInKind = $filters['include_in_kind'] ?? true;
        $inKindContributions = $includeInKind ? $inKindQuery->get() : collect();

        // Calculate totals
        $totalFunding = $projects->sum('pivot.funding_amount');
        $restrictedFunding = $projects->where('pivot.is_restricted', true)->sum('pivot.funding_amount');
        $unrestrictedFunding = $totalFunding - $restrictedFunding;
        $totalInKindValue = $inKindContributions->sum('estimated_value');

        // Project counts by status
        $activeProjectsCount = $projects->whereIn('status', ['active', 'planning'])->count();
        $completedProjectsCount = $projects->where('status', 'completed')->count();

        // Funding timeline (yearly breakdown)
        $fundingTimeline = $this->generateFundingTimeline($projects, $inKindContributions, $includeInKind);

        return [
            'donor' => $donor,
            'projects' => $projects,
            'in_kind_contributions' => $inKindContributions,
            'total_funding' => $totalFunding,
            'restricted_funding' => $restrictedFunding,
            'unrestricted_funding' => $unrestrictedFunding,
            'total_in_kind_value' => $totalInKindValue,
            'grand_total' => $totalFunding + $totalInKindValue,
            'active_projects_count' => $activeProjectsCount,
            'completed_projects_count' => $completedProjectsCount,
            'total_projects_count' => $projects->count(),
            'funding_timeline' => $fundingTimeline,
            'include_in_kind' => $includeInKind,
            'filters' => $filters,
        ];
    }

    /**
     * Stream donor financial report PDF directly to browser.
     */
    public function streamDonorFinancialReportPDF(Donor $donor, array $filters = []): \Illuminate\Http\Response
    {
        $data = $this->generateFinancialReport($donor, $filters);

        // Add PDF metadata
        $logoPath = public_path('images/canzim-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,'.$logoData;
        }

        $data['reportTitle'] = 'Donor Financial Report';
        $data['logoBase64'] = $logoBase64;
        $data['organizationName'] = 'Climate Action Network Zimbabwe';
        $data['generatedBy'] = auth()->user()->name;
        $data['userRole'] = auth()->user()->role->name ?? 'User';
        $data['generatedAt'] = now()->format('M d, Y H:i:s');
        $data['year'] = now()->year;

        $pdf = Pdf::loadView('pdf.donors.donor-financial-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        $donorSlug = str_replace(' ', '-', strtolower($donor->name));
        $filename = sprintf('donor-%s-financial-report-%s.pdf', $donorSlug, now()->format('Ymd'));

        return $pdf->stream($filename);
    }

    /**
     * Generate funding timeline (yearly breakdown).
     */
    protected function generateFundingTimeline(Collection $projects, Collection $inKindContributions, bool $includeInKind): array
    {
        $timeline = [];

        // Group cash funding by year
        foreach ($projects as $project) {
            $year = $project->pivot->funding_period_start
                ? \Carbon\Carbon::parse($project->pivot->funding_period_start)->year
                : 'Undated';

            if (! isset($timeline[$year])) {
                $timeline[$year] = [
                    'year' => $year,
                    'cash_funding' => 0,
                    'in_kind_value' => 0,
                    'total' => 0,
                ];
            }

            $timeline[$year]['cash_funding'] += $project->pivot->funding_amount;
        }

        // Add in-kind contributions by year if included
        if ($includeInKind) {
            foreach ($inKindContributions as $contribution) {
                $year = $contribution->contribution_date
                    ? \Carbon\Carbon::parse($contribution->contribution_date)->year
                    : 'Undated';

                if (! isset($timeline[$year])) {
                    $timeline[$year] = [
                        'year' => $year,
                        'cash_funding' => 0,
                        'in_kind_value' => 0,
                        'total' => 0,
                    ];
                }

                $timeline[$year]['in_kind_value'] += $contribution->estimated_value;
            }
        }

        // Calculate totals
        foreach ($timeline as $year => &$data) {
            $data['total'] = $data['cash_funding'] + $data['in_kind_value'];
        }

        // Sort by year
        ksort($timeline);

        return array_values($timeline);
    }
}
