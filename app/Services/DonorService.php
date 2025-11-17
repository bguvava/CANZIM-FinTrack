<?php

namespace App\Services;

use App\Models\Donor;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DonorService
{
    /**
     * Assign donor to a project with funding details.
     */
    public function assignToProject(Donor $donor, array $data): void
    {
        $donor->projects()->attach($data['project_id'], [
            'funding_amount' => $data['funding_amount'],
            'funding_period_start' => $data['funding_period_start'] ?? null,
            'funding_period_end' => $data['funding_period_end'] ?? null,
            'is_restricted' => $data['is_restricted'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Remove donor from a project.
     */
    public function removeFromProject(Donor $donor, int $projectId): bool
    {
        // Simply detach donor from project
        // Expenses are tied to projects, not directly to donors
        $donor->projects()->detach($projectId);

        return true;
    }

    /**
     * Calculate total cash funding provided by donor.
     */
    public function calculateTotalFunding(Donor $donor): float
    {
        return (float) $donor->projects()->sum('project_donors.funding_amount');
    }

    /**
     * Calculate total restricted funding.
     */
    public function calculateRestrictedFunding(Donor $donor): float
    {
        return (float) $donor->projects()
            ->wherePivot('is_restricted', true)
            ->sum('project_donors.funding_amount');
    }

    /**
     * Calculate total unrestricted funding.
     */
    public function calculateUnrestrictedFunding(Donor $donor): float
    {
        return (float) $donor->projects()
            ->wherePivot('is_restricted', false)
            ->sum('project_donors.funding_amount');
    }

    /**
     * Calculate total in-kind contribution value.
     */
    public function calculateInKindTotal(Donor $donor): float
    {
        return (float) $donor->inKindContributions()->sum('estimated_value');
    }

    /**
     * Get funding summary for donor.
     */
    public function getFundingSummary(Donor $donor): array
    {
        return [
            'total_funding' => $this->calculateTotalFunding($donor),
            'restricted_funding' => $this->calculateRestrictedFunding($donor),
            'unrestricted_funding' => $this->calculateUnrestrictedFunding($donor),
            'in_kind_total' => $this->calculateInKindTotal($donor),
            'active_projects_count' => $donor->projects()->where('status', 'active')->count(),
            'last_contribution_date' => $this->getLastContributionDate($donor),
        ];
    }

    /**
     * Get last contribution date (cash or in-kind).
     */
    protected function getLastContributionDate(Donor $donor): ?string
    {
        $lastProject = $donor->projects()->latest('project_donors.created_at')->first();
        $lastInKind = $donor->inKindContributions()->latest('contribution_date')->first();

        if (! $lastProject && ! $lastInKind) {
            return null;
        }

        if (! $lastProject) {
            return $lastInKind->contribution_date;
        }

        if (! $lastInKind) {
            return $lastProject->pivot->created_at->format('Y-m-d');
        }

        return max(
            $lastProject->pivot->created_at->format('Y-m-d'),
            $lastInKind->contribution_date
        );
    }

    /**
     * Check if donor can be deleted.
     */
    public function canDeleteDonor(Donor $donor): bool
    {
        // Cannot delete if has active funded projects
        return $donor->projects()->whereIn('status', ['active', 'planning'])->count() === 0;
    }

    /**
     * Check if donor can be deactivated.
     */
    public function canDeactivateDonor(Donor $donor): bool
    {
        // Cannot deactivate if has active funded projects
        return $donor->projects()->whereIn('status', ['active', 'planning'])->count() === 0;
    }

    /**
     * Get donor statistics for dashboard.
     */
    public function getDonorStatistics(): array
    {
        return [
            'total_donors' => Donor::count(),
            'active_donors' => Donor::where('status', 'active')->count(),
            'total_funding' => DB::table('project_donors')->sum('funding_amount'),
            'average_funding' => DB::table('project_donors')->avg('funding_amount'),
        ];
    }

    /**
     * Generate chart data for donor analytics.
     */
    public function generateChartData(): array
    {
        return [
            'funding_distribution' => $this->getFundingDistributionData(),
            'top_donors' => $this->getTopDonorsData(),
            'funding_timeline' => $this->getFundingTimelineData(),
        ];
    }

    /**
     * Get funding distribution data (restricted vs unrestricted).
     */
    protected function getFundingDistributionData(): array
    {
        $restricted = DB::table('project_donors')
            ->where('is_restricted', true)
            ->sum('funding_amount');

        $unrestricted = DB::table('project_donors')
            ->where('is_restricted', false)
            ->sum('funding_amount');

        return [
            'labels' => ['Restricted Funding', 'Unrestricted Funding'],
            'datasets' => [
                [
                    'data' => [(float) $restricted, (float) $unrestricted],
                    'backgroundColor' => ['#1E40AF', '#60A5FA'], // CANZIM blue palette
                ],
            ],
        ];
    }

    /**
     * Get top 10 donors by total funding.
     */
    protected function getTopDonorsData(): array
    {
        $topDonors = DB::table('donors')
            ->join('project_donors', 'donors.id', '=', 'project_donors.donor_id')
            ->select('donors.name', DB::raw('SUM(project_donors.funding_amount) as total_funding'))
            ->groupBy('donors.id', 'donors.name')
            ->orderByDesc('total_funding')
            ->limit(10)
            ->get();

        return [
            'labels' => $topDonors->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Total Funding',
                    'data' => $topDonors->pluck('total_funding')->map(fn($value) => (float) $value)->toArray(),
                    'backgroundColor' => '#2563EB', // CANZIM blue
                ],
            ],
        ];
    }

    /**
     * Get funding timeline data (last 12 months).
     */
    protected function getFundingTimelineData(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'year' => $date->year,
                'month_num' => $date->month,
            ]);
        }

        $fundingByMonth = DB::table('project_donors')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(funding_amount) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn($item) => $item->year . '-' . $item->month);

        $monthlyData = $months->map(function ($month) use ($fundingByMonth) {
            $key = $month['year'] . '-' . $month['month_num'];

            return (float) ($fundingByMonth->get($key)?->total ?? 0);
        });

        return [
            'labels' => $months->pluck('month')->toArray(),
            'datasets' => [
                [
                    'label' => 'Monthly Funding',
                    'data' => $monthlyData->toArray(),
                    'backgroundColor' => 'rgba(37, 99, 235, 0.2)', // CANZIM blue with transparency
                    'borderColor' => '#2563EB',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
        ];
    }
}
