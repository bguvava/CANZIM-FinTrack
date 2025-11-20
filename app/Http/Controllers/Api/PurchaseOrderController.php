<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MarkReceivedRequest;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPDFService;
use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseOrderService $purchaseOrderService,
        protected PurchaseOrderPDFService $pdfService
    ) {}

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['vendor', 'project', 'creator', 'approver', 'expenses'])
            ->orderBy('created_at', 'desc');

        // Role-based filtering
        $user = $request->user();
        if ($user->role->slug === 'project-officer') {
            $query->where('created_by', $user->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('order_date', [$request->date_from, $request->date_to]);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('po_number', 'like', '%'.$request->search.'%')
                    ->orWhere('notes', 'like', '%'.$request->search.'%');
            });
        }

        $purchaseOrders = $query->paginate($request->get('per_page', 15));

        return response()->json($purchaseOrders);
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        try {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            $purchaseOrder = $this->purchaseOrderService->createPurchaseOrder($data, $items);

            return response()->json([
                'message' => 'Purchase order created successfully',
                'purchase_order' => $purchaseOrder->load(['vendor', 'project', 'items']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load([
            'vendor',
            'project',
            'items',
            'expenses',
            'creator',
            'submitter',
            'approver',
            'rejector',
        ]);

        return response()->json($purchaseOrder);
    }

    /**
     * Update the specified purchase order.
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);

        try {
            $data = $request->validated();
            $items = $data['items'] ?? null;
            unset($data['items']);

            $updatedPO = $this->purchaseOrderService->updatePurchaseOrder($purchaseOrder, $data, $items);

            return response()->json([
                'message' => 'Purchase order updated successfully',
                'purchase_order' => $updatedPO->load(['vendor', 'project', 'items']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit purchase order for approval.
     */
    public function submit(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('submit', $purchaseOrder);

        try {
            $submittedPO = $this->purchaseOrderService->submitForApproval($purchaseOrder);

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order submitted for approval',
                'data' => $submittedPO,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Approve purchase order.
     */
    public function approve(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('approve', $purchaseOrder);

        try {
            $approvedPO = $this->purchaseOrderService->approvePurchaseOrder($purchaseOrder);

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order approved successfully',
                'data' => $approvedPO,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject purchase order.
     */
    public function reject(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('reject', $purchaseOrder);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $rejectedPO = $this->purchaseOrderService->rejectPurchaseOrder(
                $purchaseOrder,
                $validated['rejection_reason']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order rejected',
                'data' => $rejectedPO,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error rejecting purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark items as received.
     */
    public function receive(MarkReceivedRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('receive', $purchaseOrder);

        try {
            $updatedPO = $this->purchaseOrderService->markItemsReceived(
                $purchaseOrder,
                $request->validated()['items']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Items marked as received',
                'data' => $updatedPO->load(['items']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error marking items as received',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete purchase order.
     */
    public function complete(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('complete', $purchaseOrder);

        try {
            $completedPO = $this->purchaseOrderService->completePurchaseOrder($purchaseOrder);

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order marked as completed',
                'data' => $completedPO,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error completing purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel purchase order.
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('cancel', $purchaseOrder);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        try {
            $cancelledPO = $this->purchaseOrderService->cancelPurchaseOrder(
                $purchaseOrder,
                $validated['cancellation_reason']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order cancelled',
                'data' => $cancelledPO,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get dashboard statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $stats = [
            'total_count' => PurchaseOrder::count(),
            'draft_count' => PurchaseOrder::draft()->count(),
            'pending_count' => PurchaseOrder::pending()->count(),
            'approved_count' => PurchaseOrder::approved()->count(),
            'completed_count' => PurchaseOrder::completed()->count(),
            'total_value' => PurchaseOrder::sum('total_amount'),
            'recent_orders' => PurchaseOrder::with(['vendor', 'project'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Remove the specified purchase order.
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('delete', $purchaseOrder);

        try {
            $purchaseOrder->delete();

            return response()->json([
                'message' => 'Purchase order deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export purchase order to PDF.
     */
    public function exportPDF(PurchaseOrder $purchaseOrder): BinaryFileResponse|JsonResponse
    {
        $this->authorize('view', $purchaseOrder);

        try {
            $filename = $this->pdfService->generatePurchaseOrderPDF($purchaseOrder);
            $filePath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating purchase order PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export vendor payment status report to PDF.
     */
    public function exportVendorPaymentStatus(Request $request): BinaryFileResponse|JsonResponse
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        try {
            $validated = $request->validate([
                'vendor_id' => 'nullable|exists:vendors,id',
                'status' => 'nullable|in:draft,pending,approved,received,completed,rejected,cancelled',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            $filename = $this->pdfService->generateVendorPaymentStatusReport($validated);
            $filePath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating vendor payment status report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export purchase orders list to PDF.
     */
    public function exportListPDF(Request $request): BinaryFileResponse|JsonResponse
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        try {
            $validated = $request->validate([
                'status' => 'nullable|in:draft,pending,approved,received,completed,rejected,cancelled',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $filename = $this->pdfService->generatePurchaseOrdersListPDF($validated);
            $filePath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating purchase orders list PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get expenses linked to a purchase order.
     */
    public function getExpenses(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('view', $purchaseOrder);

        $expenses = $purchaseOrder->expenses()
            ->select('id', 'amount', 'description', 'expense_date', 'status')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        return response()->json([
            'status' => 'success',
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
        ]);
    }
}
