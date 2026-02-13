<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\Donor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Service for building and generating custom reports dynamically.
 */
class CustomReportService
{
    /**
     * Build dynamic query based on entity and parameters.
     *
     * @param  array  $params  Query parameters including entity, filters, grouping, ordering
     * @return Builder The constructed query builder
     *
     * @throws ValidationException If entity is invalid or filters exceed maximum
     */
    public function buildDynamicQuery(array $params): Builder
    {
        $supportedEntities = ['expenses', 'projects', 'budgets', 'donors', 'purchase_orders'];
        $maxFilters = 5;

        // Validate entity
        $entity = $params['entity'] ?? null;
        if (! in_array($entity, $supportedEntities)) {
            throw ValidationException::withMessages([
                'entity' => ['Invalid entity. Must be one of: '.implode(', ', $supportedEntities)],
            ]);
        }

        // Validate filter count
        $filters = $params['filters'] ?? [];
        if (count($filters) > $maxFilters) {
            throw ValidationException::withMessages([
                'filters' => ['Maximum of '.$maxFilters.' filters allowed.'],
            ]);
        }

        // Get base query for entity
        $query = $this->getBaseQuery($entity);

        // Apply filters
        $query = $this->applyFilters($query, $filters, $entity);

        // Apply grouping
        if (! empty($params['grouping'])) {
            $query = $this->applyGrouping($query, $params['grouping'], $entity);
        }

        // Apply ordering
        $orderBy = $params['order_by'] ?? 'created_at';
        $orderDir = $params['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query;
    }

    /**
     * Generate custom report data.
     *
     * @param  array  $params  Report parameters
     * @return array Report data with results, totals, and metadata
     */
    public function generateCustomReport(array $params): array
    {
        $query = $this->buildDynamicQuery($params);

        // Get paginated data
        $perPage = $params['per_page'] ?? 50;
        $results = $query->paginate($perPage);

        // Calculate aggregates
        $aggregates = $this->calculateAggregates($params['entity'], $query);

        return [
            'entity' => $params['entity'],
            'data' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ],
            'aggregates' => $aggregates,
            'filters_applied' => $params['filters'] ?? [],
            'grouping' => $params['grouping'] ?? null,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Export custom report as PDF.
     *
     * @param  array  $params  Report parameters
     * @return Response PDF stream response
     */
    public function exportCustomReportPDF(array $params): Response
    {
        // Generate report data (without pagination for PDF)
        $params['per_page'] = 1000; // Reasonable limit for PDF
        $reportData = $this->generateCustomReport($params);

        // Prepare PDF data
        $pdfData = [
            'reportTitle' => $this->getReportTitle($params['entity']),
            'entity' => $params['entity'],
            'data' => $reportData['data'],
            'aggregates' => $reportData['aggregates'],
            'filters' => $params['filters'] ?? [],
            'grouping' => $params['grouping'] ?? null,
            'logoBase64' => $this->loadLogoAsBase64(),
            'generatedBy' => auth()->user()?->full_name ?? auth()->user()?->name ?? 'System',
            'userRole' => auth()->user()?->role?->name ?? 'User',
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
            'year' => now()->year,
            'organizationName' => 'Climate Action Network Zimbabwe',
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.reports.custom-report', $pdfData)
            ->setPaper('a4', 'landscape') // Landscape for better table fit
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        $filename = sprintf('custom-report-%s-%s.pdf', $params['entity'], now()->format('Ymd'));

        return $pdf->stream($filename);
    }

    /**
     * Get base query for entity.
     */
    protected function getBaseQuery(string $entity): Builder
    {
        return match ($entity) {
            'expenses' => Expense::query()->with(['project', 'category', 'submitter']),
            'projects' => Project::query()->with(['budgets', 'expenses', 'donors']),
            'budgets' => Budget::query()->with(['project', 'items']),
            'donors' => Donor::query()->with(['projects', 'inKindContributions']),
            'purchase_orders' => PurchaseOrder::query()->with(['vendor', 'project', 'items']),
            default => throw new \InvalidArgumentException("Unsupported entity: {$entity}"),
        };
    }

    /**
     * Apply filters to query dynamically.
     */
    protected function applyFilters(Builder $query, array $filters, string $entity): Builder
    {
        foreach ($filters as $filterKey => $filterValue) {
            if (empty($filterValue)) {
                continue;
            }

            match ($filterKey) {
                'date_from' => $query->where($this->getDateColumn($entity), '>=', $filterValue),
                'date_to' => $query->where($this->getDateColumn($entity), '<=', $filterValue),
                'status' => is_array($filterValue)
                    ? $query->whereIn('status', $filterValue)
                    : $query->where('status', $filterValue),
                'category_id' => is_array($filterValue)
                    ? $query->whereIn('expense_category_id', $filterValue)
                    : $query->where('expense_category_id', $filterValue),
                'project_id' => is_array($filterValue)
                    ? $query->whereIn('project_id', $filterValue)
                    : $query->where('project_id', $filterValue),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Apply grouping to query.
     * Note: This applies ordering for grouping display, not SQL GROUP BY.
     */
    protected function applyGrouping(Builder $query, string $grouping, string $entity): Builder
    {
        return match ($grouping) {
            'status' => $query->orderBy('status'),
            'category' => $entity === 'expenses' ? $query->orderBy('expense_category_id') : $query,
            'project' => $query->orderBy('project_id'),
            'month' => $query->orderBy($this->getDateColumn($entity)),
            default => $query,
        };
    }

    /**
     * Calculate aggregates for the query.
     */
    protected function calculateAggregates(string $entity, Builder $query): array
    {
        // Clone query to avoid affecting original
        $aggregateQuery = clone $query;

        return match ($entity) {
            'expenses' => [
                'count' => $aggregateQuery->count(),
                'total_amount' => $aggregateQuery->sum('amount'),
                'average_amount' => $aggregateQuery->avg('amount'),
            ],
            'projects' => [
                'count' => $aggregateQuery->count(),
                'total_budget' => $aggregateQuery->sum('total_budget'),
            ],
            'budgets' => [
                'count' => $aggregateQuery->count(),
                'total_amount' => $aggregateQuery->sum('total_amount'),
            ],
            'donors' => [
                'count' => $aggregateQuery->count(),
            ],
            'purchase_orders' => [
                'count' => $aggregateQuery->count(),
                'total_amount' => $aggregateQuery->sum('total_amount'),
            ],
            default => ['count' => $aggregateQuery->count()],
        };
    }

    /**
     * Get date column for entity.
     */
    protected function getDateColumn(string $entity): string
    {
        return match ($entity) {
            'expenses' => 'expense_date',
            'projects' => 'start_date',
            'budgets' => 'period_start',
            'donors' => 'created_at',
            'purchase_orders' => 'order_date',
            default => 'created_at',
        };
    }

    /**
     * Get report title for entity.
     */
    protected function getReportTitle(string $entity): string
    {
        return match ($entity) {
            'expenses' => 'Custom Expense Report',
            'projects' => 'Custom Project Report',
            'budgets' => 'Custom Budget Report',
            'donors' => 'Custom Donor Report',
            'purchase_orders' => 'Custom Purchase Order Report',
            default => 'Custom Report',
        };
    }

    /**
     * Load CANZIM logo as base64.
     */
    protected function loadLogoAsBase64(): string
    {
        $logoPath = public_path('images/canzim-logo.png');

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));

            return 'data:image/png;base64,'.$logoData;
        }

        return '';
    }
}
