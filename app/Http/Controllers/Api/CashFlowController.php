<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInflowRequest;
use App\Http\Requests\StoreOutflowRequest;
use App\Models\ActivityLog;
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

        $query = CashFlow::with(['bankAccount', 'project', 'donor', 'expense.category', 'creator'])
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
                $q->where('transaction_number', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhere('reference', 'like', '%'.$request->search.'%');
            });
        }

        $cashFlows = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $cashFlows->items(),
            'meta' => [
                'current_page' => $cashFlows->currentPage(),
                'from' => $cashFlows->firstItem(),
                'last_page' => $cashFlows->lastPage(),
                'per_page' => $cashFlows->perPage(),
                'to' => $cashFlows->lastItem(),
                'total' => $cashFlows->total(),
            ],
        ]);
    }

    /**
     * Store a newly created inflow.
     */
    public function storeInflow(StoreInflowRequest $request): JsonResponse
    {
        $this->authorize('create', CashFlow::class);

        try {
            $cashFlow = $this->cashFlowService->recordInflow($request->validated());

            ActivityLog::log(auth()->id(), 'cash_inflow_recorded', 'Cash inflow recorded: '.$cashFlow->transaction_number, [
                'cash_flow_id' => $cashFlow->id,
                'amount' => $cashFlow->amount,
                'bank_account_id' => $cashFlow->bank_account_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cash inflow recorded successfully',
                'data' => $cashFlow->load(['bankAccount', 'project', 'donor']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
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

            ActivityLog::log(auth()->id(), 'cash_outflow_recorded', 'Cash outflow recorded: '.$cashFlow->transaction_number, [
                'cash_flow_id' => $cashFlow->id,
                'amount' => $cashFlow->amount,
                'bank_account_id' => $cashFlow->bank_account_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cash outflow recorded successfully',
                'data' => $cashFlow->load(['bankAccount', 'project', 'expense.category']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient bank account balance',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
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

        $cashFlow->load(['bankAccount', 'project', 'donor', 'expense.category', 'creator', 'reconciler']);

        return response()->json([
            'status' => 'success',
            'data' => $cashFlow,
        ]);
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

            ActivityLog::log(auth()->id(), 'cash_flow_updated', 'Cash flow updated: '.$cashFlow->transaction_number, [
                'cash_flow_id' => $cashFlow->id,
                'type' => $cashFlow->type,
            ]);

            $message = $cashFlow->type === 'inflow'
                ? 'Inflow transaction updated successfully'
                : 'Outflow transaction updated successfully';

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $cashFlow->load(['bankAccount', 'project']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating cash flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reconcile a cash flow transaction.
     */
    public function reconcile(Request $request, CashFlow $cashFlow): JsonResponse
    {
        $this->authorize('reconcile', $cashFlow);

        $validated = $request->validate([
            'reconciliation_date' => 'required|date',
        ]);

        try {
            $reconciledCashFlow = $this->cashFlowService->reconcile($cashFlow->id, $validated['reconciliation_date']);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction reconciled successfully',
                'data' => $reconciledCashFlow,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error reconciling transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unreconcile a cash flow transaction.
     */
    public function unreconcile(CashFlow $cashFlow): JsonResponse
    {
        // Check if user has permission (same as reconcile permission)
        if (! auth()->user()->role || auth()->user()->role->slug !== 'finance-officer') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $unreconciledCashFlow = $this->cashFlowService->unreconcile($cashFlow->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction unreconciled successfully',
                'data' => $unreconciledCashFlow,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error unreconciling transaction',
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
            $months = (int) ($validated['months'] ?? 3);
            $bankAccountId = $validated['bank_account_id'];

            // Use the service to calculate projections based on historical data
            $projectionData = $this->cashFlowService->calculateProjection($bankAccountId, $months);

            // Transform data for the frontend chart
            $monthLabels = array_column($projectionData['projections'], 'month');
            $likelyCase = array_column($projectionData['projections'], 'projected_balance');

            // Calculate best case (20% more inflows, 10% less outflows)
            $bestCase = [];
            $worstCase = [];
            $currentBalance = $projectionData['current_balance'];
            $avgInflow = $projectionData['avg_monthly_inflow'];
            $avgOutflow = $projectionData['avg_monthly_outflow'];

            $bestBalance = $currentBalance;
            $worstBalance = $currentBalance;

            for ($i = 0; $i < $months; $i++) {
                $bestBalance += ($avgInflow * 1.2) - ($avgOutflow * 0.9);
                $worstBalance += ($avgInflow * 0.8) - ($avgOutflow * 1.1);
                $bestCase[] = round($bestBalance, 2);
                $worstCase[] = round($worstBalance, 2);
            }

            return response()->json([
                'current_balance' => $projectionData['current_balance'],
                'avg_monthly_inflow' => $projectionData['avg_monthly_inflow'],
                'avg_monthly_outflow' => $projectionData['avg_monthly_outflow'],
                'avg_net_cash_flow' => $projectionData['avg_net_cash_flow'],
                'historical_months' => 6,
                'months' => $monthLabels,
                'best_case' => $bestCase,
                'likely_case' => $likelyCase,
                'worst_case' => $worstCase,
                'best_case_final' => end($bestCase) ?: $currentBalance,
                'likely_case_final' => end($likelyCase) ?: $currentBalance,
                'worst_case_final' => end($worstCase) ?: $currentBalance,
                'projections' => $projectionData['projections'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
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

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
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
                'status' => 'success',
                'message' => 'Cash flow transaction deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
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

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        try {
            $filename = $this->pdfService->generateCashFlowStatement($validated);
            $filePath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
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

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $filename = $this->pdfService->generateReconciliationReport($bankAccount, $validated);
            $filePath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating reconciliation report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
