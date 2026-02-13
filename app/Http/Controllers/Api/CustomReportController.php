<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\CustomReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CustomReportController extends Controller
{
    public function __construct(
        protected CustomReportService $customReportService
    ) {}

    /**
     * Generate custom report based on dynamic parameters.
     */
    public function generate(Request $request): JsonResponse
    {
        // Authorization check - ensure user can generate reports
        $this->authorize('generate', Report::class);

        $validated = $request->validate([
            'entity' => [
                'required',
                Rule::in(['expenses', 'projects', 'budgets', 'donors', 'purchase_orders']),
            ],
            'filters' => ['nullable', 'array', 'max:5'],
            'filters.date_from' => ['nullable', 'date'],
            'filters.date_to' => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'filters.status' => ['nullable', 'string'],
            'filters.category_id' => ['nullable', 'integer'],
            'filters.project_id' => ['nullable', 'integer'],
            'grouping' => ['nullable', Rule::in(['status', 'category', 'project', 'month'])],
            'order_by' => ['nullable', 'string'],
            'order_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            $reportData = $this->customReportService->generateCustomReport($validated);

            return response()->json([
                'success' => true,
                'message' => 'Custom report generated successfully.',
                'data' => $reportData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Export custom report as PDF.
     */
    public function export(Request $request): Response
    {
        // Authorization check
        $this->authorize('exportPDF', Report::class);

        $validated = $request->validate([
            'entity' => [
                'required',
                Rule::in(['expenses', 'projects', 'budgets', 'donors', 'purchase_orders']),
            ],
            'filters' => ['nullable', 'array', 'max:5'],
            'filters.date_from' => ['nullable', 'date'],
            'filters.date_to' => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'filters.status' => ['nullable', 'string'],
            'filters.category_id' => ['nullable', 'integer'],
            'filters.project_id' => ['nullable', 'integer'],
            'grouping' => ['nullable', Rule::in(['status', 'category', 'project', 'month'])],
        ]);

        try {
            return $this->customReportService->exportCustomReportPDF($validated);
        } catch (\Exception $e) {
            abort(500, 'Failed to export report: '.$e->getMessage());
        }
    }
}
