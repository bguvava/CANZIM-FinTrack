<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewExpenseRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ApprovalService;
use App\Services\ExpensePDFService;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService,
        protected ApprovalService $approvalService,
        protected ExpensePDFService $expensePDFService
    ) {
        // Policy-based authorization handled per method
    }

    /**
     * Display a listing of expenses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with(['project', 'category', 'budgetItem', 'purchaseOrder.vendor', 'submitter', 'reviewer', 'approver'])
            ->orderBy('created_at', 'desc');

        // Role-based filtering
        $user = $request->user();
        if ($user->role->slug === 'project-officer') {
            // Project officers see only their own expenses (including drafts)
            $query->where('submitted_by', $user->id);
        } else {
            // Finance officers and programs managers see only submitted/non-draft expenses
            $query->where('status', '!=', 'Draft');
        }

        // Apply filters (max 5)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('expense_number', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        $expenses = $query->paginate($request->get('per_page', 15));

        return response()->json($expenses);
    }

    /**
     * Store a newly created expense.
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['submitted_by'] = $request->user()->id;

            $expense = $this->expenseService->createExpense(
                $data,
                $request->file('receipt')
            );

            // Log activity
            try {
                ActivityLog::log(
                    $request->user()->id,
                    'expense_created',
                    'Created expense: '.$expense->description.' ($'.$expense->amount.')',
                    ['expense_id' => $expense->id, 'amount' => $expense->amount, 'project_id' => $expense->project_id]
                );
            } catch (\Throwable $e) {
                // Don't let logging errors break the main flow
            }

            return response()->json([
                'message' => 'Expense created successfully',
                'expense' => $expense->load(['project', 'category', 'budgetItem']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating expense',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense): JsonResponse
    {
        $expense->load([
            'project',
            'category',
            'budgetItem',
            'purchaseOrder.vendor',
            'submitter',
            'reviewer',
            'approver',
            'rejector',
            'payer',
            'approvals.user',
            'cashFlow.bankAccount',
        ]);

        return response()->json($expense);
    }

    /**
     * Update the specified expense.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        try {
            $updatedExpense = $this->expenseService->updateExpense(
                $expense,
                $request->validated(),
                $request->file('receipt')
            );

            return response()->json([
                'message' => 'Expense updated successfully',
                'expense' => $updatedExpense->load(['project', 'category', 'budgetItem']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating expense',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        try {
            $this->expenseService->deleteExpense($expense);

            return response()->json([
                'message' => 'Expense deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting expense',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit expense for approval.
     */
    public function submit(Expense $expense, Request $request): JsonResponse
    {
        $this->authorize('submit', $expense);

        try {
            $submittedExpense = $this->expenseService->submitExpense($expense, $request->user());

            return response()->json([
                'message' => 'Expense submitted for approval',
                'expense' => $submittedExpense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error submitting expense',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Review expense (Finance Officer).
     */
    public function review(ReviewExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('review', $expense);

        try {
            $reviewedExpense = $this->expenseService->reviewExpense(
                $expense,
                $request->user(),
                $request->action,
                $request->comments
            );

            return response()->json([
                'message' => $request->action === 'approve'
                    ? 'Expense forwarded to Programs Manager'
                    : 'Expense returned for revision',
                'expense' => $reviewedExpense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error reviewing expense',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve expense (Programs Manager).
     */
    public function approve(ReviewExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('approve', $expense);

        try {
            if ($request->action === 'approve') {
                $approvedExpense = $this->expenseService->approveExpense(
                    $expense,
                    $request->user(),
                    $request->comments
                );
                $message = 'Expense approved successfully';

                // Log approval activity
                try {
                    ActivityLog::log(
                        $request->user()->id,
                        'expense_approved',
                        'Approved expense: '.$expense->description.' ($'.$expense->amount.')',
                        ['expense_id' => $expense->id, 'amount' => $expense->amount]
                    );
                } catch (\Throwable $e) {
                    // Don't let logging errors break the main flow
                }
            } else {
                $approvedExpense = $this->expenseService->rejectExpense(
                    $expense,
                    $request->user(),
                    $request->comments ?? 'Rejected'
                );
                $message = 'Expense rejected';

                // Log rejection activity
                try {
                    ActivityLog::log(
                        $request->user()->id,
                        'expense_rejected',
                        'Rejected expense: '.$expense->description.' ($'.$expense->amount.')',
                        ['expense_id' => $expense->id, 'amount' => $expense->amount, 'reason' => $request->comments]
                    );
                } catch (\Throwable $e) {
                    // Don't let logging errors break the main flow
                }
            }

            return response()->json([
                'message' => $message,
                'expense' => $approvedExpense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing expense',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('markAsPaid', $expense);

        $validated = $request->validate([
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Validate bank account is active
        $bankAccount = \App\Models\BankAccount::find($validated['bank_account_id']);
        if (! $bankAccount || ! $bankAccount->is_active) {
            return response()->json([
                'message' => 'The selected bank account is not active.',
                'errors' => [
                    'bank_account_id' => ['The selected bank account is not active.'],
                ],
            ], 422);
        }

        try {
            $paidExpense = $this->expenseService->markAsPaid(
                $expense,
                $request->user(),
                $validated
            );

            return response()->json([
                'message' => 'Expense marked as paid successfully',
                'expense' => $paidExpense,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error marking expense as paid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending expenses for Finance Officer.
     */
    public function pendingReview(Request $request): JsonResponse
    {
        $expenses = $this->approvalService->getPendingForFinanceOfficer();

        return response()->json($expenses);
    }

    /**
     * Get pending expenses for Programs Manager.
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        $expenses = $this->approvalService->getPendingForManager();

        return response()->json($expenses);
    }

    /**
     * Get expense categories.
     */
    public function categories(): JsonResponse
    {
        $categories = ExpenseCategory::active()->ordered()->get();

        return response()->json($categories);
    }

    /**
     * Link an expense to a purchase order.
     */
    public function linkPurchaseOrder(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('linkPurchaseOrder', $expense);

        $validated = $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
        ]);

        try {
            // Load relationships
            $purchaseOrder = \App\Models\PurchaseOrder::findOrFail($validated['purchase_order_id']);

            // Validate: PO must be approved or completed (security check first)
            if (! in_array($purchaseOrder->status, ['Approved', 'Completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Can only link expenses to approved or completed purchase orders',
                ], 422);
            }

            // Validate: Must belong to same project
            if ($expense->project_id !== $purchaseOrder->project_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Expense and purchase order must belong to the same project',
                ], 422);
            }

            // Link the expense
            $expense->purchase_order_id = $purchaseOrder->id;
            $expense->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Expense linked to purchase order',
                'expense' => $expense->fresh(['purchaseOrder']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error linking expense to purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlink an expense from a purchase order.
     */
    public function unlinkPurchaseOrder(Expense $expense): JsonResponse
    {
        $this->authorize('linkPurchaseOrder', $expense);

        try {
            $expense->purchase_order_id = null;
            $expense->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Expense unlinked from purchase order',
                'expense' => $expense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error unlinking expense from purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export single expense details as PDF.
     */
    public function exportPDF(Expense $expense)
    {
        $this->authorize('view', $expense);

        try {
            return $this->expensePDFService->streamExpenseDetailsPDF($expense);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating expense PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export filtered expense list as PDF.
     */
    public function exportListPDF(Request $request)
    {
        $this->authorize('viewAny', Expense::class);

        try {
            $query = Expense::with(['project', 'category', 'budgetItem', 'submitter']);

            // Role-based filtering
            $user = $request->user()->load('role');
            if ($user->role->slug === 'project-officer') {
                $query->where('submitted_by', $user->id);
            }

            // Apply filters
            $filters = [];

            if ($request->filled('status')) {
                $query->where('status', $request->status);
                $filters['status'] = $request->status;
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
                $filters['project_id'] = $request->project_id;
            }

            if ($request->filled('expense_category_id')) {
                $query->where('expense_category_id', $request->expense_category_id);
                $filters['category_id'] = $request->expense_category_id;
            }

            if ($request->filled('date_from')) {
                $query->where('expense_date', '>=', $request->date_from);
                $filters['date_from'] = $request->date_from;
            }

            if ($request->filled('date_to')) {
                $query->where('expense_date', '<=', $request->date_to);
                $filters['date_to'] = $request->date_to;
            }

            $expenses = $query->orderBy('expense_date', 'desc')->get();

            return $this->expensePDFService->streamExpenseListPDF($expenses, $filters);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating expense list PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
