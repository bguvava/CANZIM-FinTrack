<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInflowRequest;
use App\Http\Requests\StoreOutflowRequest;
use App\Models\BankAccount;
use App\Models\CashFlow;
use App\Services\CashFlowPDFService;
use App\Services\CashFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CashFlowController extends Controller
{
    public function __construct(
        protected CashFlowService $cashFlowService,
        protected CashFlowPDFService $pdfService
    ) {}

    /**
     * Display a listing of cash flows.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CashFlow::class);

        $query = CashFlow::with(['bankAccount', 'project', 'donor', 'expense', 'creator'])
            ->orderBy('transaction_date', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by bank account
        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by reconciliation status
        if ($request->filled('is_reconciled')) {
            $query->where('is_reconciled', $request->boolean('is_reconciled'));
        }

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transaction_date', [$request->date_from, $request->date_to]);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        $cashFlows = $query->paginate($request->get('per_page', 15));

        return response()->json($cashFlows);
    }

    /**
     * Store a newly created inflow.
     */
    public function storeInflow(StoreInflowRequest $request): JsonResponse
    {
        $this->authorize('create', CashFlow::class);

        try {
            $cashFlow = $this->cashFlowService->recordInflow($request->validated());

            return response()->json([
                'message' => 'Cash inflow recorded successfully',
                'cash_flow' => $cashFlow->load(['bankAccount', 'project', 'donor']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error recording cash inflow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created outflow.
     */
    public function storeOutflow(StoreOutflowRequest $request): JsonResponse
    {
        $this->authorize('create', CashFlow::class);

        try {
            $cashFlow = $this->cashFlowService->recordOutflow($request->validated());

            return response()->json([
                'message' => 'Cash outflow recorded successfully',
                'cash_flow' => $cashFlow->load(['bankAccount', 'project', 'expense']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error recording cash outflow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified cash flow.
     */
    public function show(CashFlow $cashFlow): JsonResponse
    {
        $this->authorize('view', $cashFlow);

        $cashFlow->load(['bankAccount', 'project', 'donor', 'expense', 'creator', 'reconciler']);

        return response()->json($cashFlow);
    }

    /**
     * Update the specified cash flow.
     */
    public function update(Request $request, CashFlow $cashFlow): JsonResponse
    {
        $this->authorize('update', $cashFlow);

        try {
            $validated = $request->validate([
                'description' => 'sometimes|string|max:1000',
                'reference' => 'nullable|string|max:255',
            ]);

            $cashFlow->update($validated);

            return response()->json([
                'message' => 'Cash flow updated successfully',
                'cash_flow' => $cashFlow->load(['bankAccount', 'project']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating cash flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reconcile a cash flow transaction.
     */
    public function reconcile(CashFlow $cashFlow): JsonResponse
    {
        $this->authorize('reconcile', $cashFlow);

        try {
            $reconciledCashFlow = $this->cashFlowService->reconcile($cashFlow->id);

            return response()->json([
                'message' => 'Transaction reconciled successfully',
                'cash_flow' => $reconciledCashFlow,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error reconciling transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cash flow projections.
     */
    public function projections(Request $request): JsonResponse
    {
        $this->authorize('viewProjections', CashFlow::class);

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'months' => 'sometimes|integer|min:1|max:12',
        ]);

        try {
            $projections = $this->cashFlowService->calculateProjection(
                $validated['bank_account_id'],
                $validated['months'] ?? 3
            );

            return response()->json($projections);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error calculating projections',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dashboard statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CashFlow::class);

        $bankAccountId = $request->get('bank_account_id');

        $query = CashFlow::query();
        if ($bankAccountId) {
            $query->where('bank_account_id', $bankAccountId);
        }

        $stats = [
            'total_inflows' => $query->clone()->where('type', 'inflow')->sum('amount'),
            'total_outflows' => $query->clone()->where('type', 'outflow')->sum('amount'),
            'reconciled_count' => $query->clone()->where('is_reconciled', true)->count(),
            'unreconciled_count' => $query->clone()->where('is_reconciled', false)->count(),
            'recent_transactions' => $query->clone()
                ->with(['bankAccount', 'project'])
                ->latest('transaction_date')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Remove the specified cash flow.
     */
    public function destroy(CashFlow $cashFlow): JsonResponse
    {
        $this->authorize('delete', $cashFlow);

        try {
            $cashFlow->delete();

            return response()->json([
                'message' => 'Cash flow deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting cash flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export cash flow statement to PDF.
     */
    public function exportStatement(Request $request): BinaryFileResponse|JsonResponse
    {
        $this->authorize('viewAny', CashFlow::class);

        try {
            $validated = $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
            ]);

            $filename = $this->pdfService->generateCashFlowStatement($validated);
            $filePath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating cash flow statement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export bank reconciliation report to PDF.
     */
    public function exportReconciliation(Request $request, BankAccount $bankAccount): BinaryFileResponse|JsonResponse
    {
        $this->authorize('viewAny', CashFlow::class);

        try {
            $validated = $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            $filename = $this->pdfService->generateReconciliationReport($bankAccount, $validated);
            $filePath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating reconciliation report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
