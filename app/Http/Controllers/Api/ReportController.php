<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomReportRequest;
use App\Http\Requests\GenerateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportPDFService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected ReportPDFService $pdfService
    ) {}

    /**
     * Get report generation history for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::with('generator')
            ->where('generated_by', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->has('report_type')) {
            $query->ofType($request->input('report_type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $reports = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'data' => ReportResource::collection($reports->items()),
                'links' => [
                    'first' => $reports->url(1),
                    'last' => $reports->url($reports->lastPage()),
                    'prev' => $reports->previousPageUrl(),
                    'next' => $reports->nextPageUrl(),
                ],
            ],
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Generate Budget vs Actual report.
     */
    public function budgetVsActual(GenerateReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $reportData = $this->reportService->generateBudgetVsActualReport($validated);

        $filePath = $this->pdfService->generateBudgetVsActualPDF($reportData, Auth::id());

        $report = $this->reportService->saveReportMetadata(
            type: 'budget-vs-actual',
            title: 'Budget vs Actual Report',
            parameters: $validated,
            userId: Auth::id(),
            filePath: $filePath
        );

        return response()->json([
            'success' => true,
            'message' => 'Budget vs Actual report generated successfully.',
            'data' => new ReportResource($report->load('generator')),
        ], 201);
    }

    /**
     * Generate Cash Flow report.
     */
    public function cashFlow(GenerateReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $reportData = $this->reportService->generateCashFlowReport($validated);

        $filePath = $this->pdfService->generateCashFlowPDF($reportData, Auth::id());

        $report = $this->reportService->saveReportMetadata(
            type: 'cash-flow',
            title: 'Cash Flow Report',
            parameters: $validated,
            userId: Auth::id(),
            filePath: $filePath
        );

        return response()->json([
            'success' => true,
            'message' => 'Cash Flow report generated successfully.',
            'data' => new ReportResource($report->load('generator')),
        ], 201);
    }

    /**
     * Generate Expense Summary report.
     */
    public function expenseSummary(GenerateReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $reportData = $this->reportService->generateExpenseSummaryReport($validated);

        $filePath = $this->pdfService->generateExpenseSummaryPDF($reportData, Auth::id());

        $report = $this->reportService->saveReportMetadata(
            type: 'expense-summary',
            title: 'Expense Summary Report',
            parameters: $validated,
            userId: Auth::id(),
            filePath: $filePath
        );

        return response()->json([
            'success' => true,
            'message' => 'Expense Summary report generated successfully.',
            'data' => new ReportResource($report->load('generator')),
        ], 201);
    }

    /**
     * Generate Project Status report.
     */
    public function projectStatus(GenerateReportRequest $request): JsonResponse
    {
        $this->authorize('viewProjectStatusReports', Report::class);

        $validated = $request->validated();

        $reportData = $this->reportService->generateProjectStatusReport($validated);

        $filePath = $this->pdfService->generateProjectStatusPDF($reportData, Auth::id());

        $report = $this->reportService->saveReportMetadata(
            type: 'project-status',
            title: 'Project Status Report',
            parameters: $validated,
            userId: Auth::id(),
            filePath: $filePath
        );

        return response()->json([
            'success' => true,
            'message' => 'Project Status report generated successfully.',
            'data' => new ReportResource($report->load('generator')),
        ], 201);
    }

    /**
     * Generate Donor Contributions report.
     */
    public function donorContributions(GenerateReportRequest $request): JsonResponse
    {
        $this->authorize('viewDonorReports', Report::class);

        $validated = $request->validated();

        $reportData = $this->reportService->generateDonorContributionsReport($validated);

        $filePath = $this->pdfService->generateDonorContributionsPDF($reportData, Auth::id());

        $report = $this->reportService->saveReportMetadata(
            type: 'donor-contributions',
            title: 'Donor Contributions Report',
            parameters: $validated,
            userId: Auth::id(),
            filePath: $filePath
        );

        return response()->json([
            'success' => true,
            'message' => 'Donor Contributions report generated successfully.',
            'data' => new ReportResource($report->load('generator')),
        ], 201);
    }

    /**
     * Generate Custom report.
     */
    public function custom(CustomReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $reportData = $this->reportService->generateCustomReport($validated);

        $filePath = match ($validated['report_type']) {
            'budget-vs-actual' => $this->pdfService->generateBudgetVsActualPDF($reportData, Auth::id()),
            'cash-flow' => $this->pdfService->generateCashFlowPDF($reportData, Auth::id()),
            'expense-summary' => $this->pdfService->generateExpenseSummaryPDF($reportData, Auth::id()),
            'project-status' => $this->pdfService->generateProjectStatusPDF($reportData, Auth::id()),
            'donor-contributions' => $this->pdfService->generateDonorContributionsPDF($reportData, Auth::id()),
        };

        $report = $this->reportService->saveReportMetadata(
            type: $validated['report_type'],
            title: $validated['title'],
            parameters: array_merge(['filters' => $validated['filters'] ?? []], [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]),
            userId: Auth::id(),
            filePath: $filePath
        );

        return response()->json([
            'success' => true,
            'message' => 'Custom report generated successfully.',
            'data' => new ReportResource($report->load('generator')),
        ], 201);
    }

    /**
     * Download report PDF.
     */
    public function download(Report $report): BinaryFileResponse
    {
        $this->authorize('view', $report);

        if (! Storage::disk('public')->exists($report->file_path)) {
            abort(404, 'Report file not found.');
        }

        $filePath = Storage::disk('public')->path($report->file_path);

        return response()->download(
            $filePath,
            basename($report->file_path),
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report): JsonResponse
    {
        $this->authorize('view', $report);

        return response()->json([
            'success' => true,
            'data' => new ReportResource($report->load('generator')),
        ]);
    }

    /**
     * Remove the specified report.
     */
    public function destroy(Report $report): JsonResponse
    {
        $this->authorize('delete', $report);

        // Delete PDF file if exists
        if ($report->file_path) {
            $this->pdfService->deletePDF($report->file_path);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully.',
        ]);
    }
}
