<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseApproval;
use App\Models\User;
use App\Notifications\ExpenseApprovedNotification;
use App\Notifications\ExpenseRejectedNotification;
use App\Notifications\ExpenseReviewedNotification;
use App\Notifications\ExpenseSubmittedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ExpenseService
{
    public function __construct(
        protected FileUploadService $fileUploadService,
        protected BudgetService $budgetService
    ) {}

    /**
     * Create a new expense.
     */
    public function createExpense(array $data, $file = null): Expense
    {
        return DB::transaction(function () use ($data, $file) {
            // Generate expense number
            $data['expense_number'] = $this->generateExpenseNumber();
            $data['status'] = 'Draft';

            // Upload receipt if provided
            if ($file) {
                $receiptData = $this->fileUploadService->uploadReceipt($file);
                $data['receipt_path'] = $receiptData['path'];
                $data['receipt_original_name'] = $receiptData['original_name'];
                $data['receipt_file_size'] = $receiptData['size'];
            }

            return Expense::create($data);
        });
    }

    /**
     * Submit expense for approval.
     */
    public function submitExpense(Expense $expense, User $user): Expense
    {
        $expense->update([
            'status' => 'Submitted',
            'submitted_by' => $user->id,
            'submitted_at' => now(),
        ]);

        // Notify Finance Officers
        $financeOfficers = User::whereHas('role', function ($query) {
            $query->where('slug', 'finance-officer');
        })->get();
        Notification::send($financeOfficers, new ExpenseSubmittedNotification($expense));

        return $expense->fresh();
    }

    /**
     * Review expense by Finance Officer.
     */
    public function reviewExpense(Expense $expense, User $user, string $action, ?string $comments = null): Expense
    {
        return DB::transaction(function () use ($expense, $user, $action, $comments) {
            if ($action === 'approve') {
                $expense->update([
                    'status' => 'Under Review',
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'review_comments' => $comments,
                ]);

                // Log approval
                ExpenseApproval::create([
                    'expense_id' => $expense->id,
                    'approval_level' => 'Finance Officer',
                    'action' => 'Approved',
                    'user_id' => $user->id,
                    'comments' => $comments,
                    'action_date' => now(),
                ]);

                // Notify Programs Managers
                $managers = User::whereHas('role', function ($query) {
                    $query->where('slug', 'programs-manager');
                })->get();
                Notification::send($managers, new ExpenseReviewedNotification($expense));
            } else {
                $expense->update([
                    'status' => 'Rejected',
                    'rejected_by' => $user->id,
                    'rejected_at' => now(),
                    'rejection_reason' => $comments,
                ]);

                // Log rejection
                ExpenseApproval::create([
                    'expense_id' => $expense->id,
                    'approval_level' => 'Finance Officer',
                    'action' => 'Rejected',
                    'user_id' => $user->id,
                    'comments' => $comments,
                    'action_date' => now(),
                ]);

                // Notify submitter
                $expense->submitter->notify(new ExpenseRejectedNotification($expense, $comments));
            }

            return $expense->fresh();
        });
    }

    /**
     * Approve expense by Programs Manager.
     */
    public function approveExpense(Expense $expense, User $user, ?string $comments = null): Expense
    {
        return DB::transaction(function () use ($expense, $user, $comments) {
            $expense->update([
                'status' => 'Approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_comments' => $comments,
            ]);

            // Log approval
            ExpenseApproval::create([
                'expense_id' => $expense->id,
                'approval_level' => 'Programs Manager',
                'action' => 'Approved',
                'user_id' => $user->id,
                'comments' => $comments,
                'action_date' => now(),
            ]);

            // Update budget
            if ($expense->budget_item_id) {
                $this->budgetService->recordExpense($expense->budget_item_id, $expense->amount);
            }

            // Notify submitter
            $expense->submitter->notify(new ExpenseApprovedNotification($expense));

            return $expense->fresh();
        });
    }

    /**
     * Reject expense by Programs Manager.
     */
    public function rejectExpense(Expense $expense, User $user, string $reason): Expense
    {
        return DB::transaction(function () use ($expense, $user, $reason) {
            $expense->update([
                'status' => 'Rejected',
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Log rejection
            ExpenseApproval::create([
                'expense_id' => $expense->id,
                'approval_level' => 'Programs Manager',
                'action' => 'Rejected',
                'user_id' => $user->id,
                'comments' => $reason,
                'action_date' => now(),
            ]);

            // Notify submitter
            $expense->submitter->notify(new ExpenseRejectedNotification($expense, $reason));

            return $expense->fresh();
        });
    }

    /**
     * Mark expense as paid.
     */
    public function markAsPaid(Expense $expense, User $user, array $paymentData): Expense
    {
        return DB::transaction(function () use ($expense, $user, $paymentData) {
            $expense->update([
                'status' => 'Paid',
                'paid_by' => $user->id,
                'paid_at' => now(),
                'payment_reference' => $paymentData['payment_reference'] ?? null,
                'payment_method' => $paymentData['payment_method'] ?? null,
                'payment_notes' => $paymentData['payment_notes'] ?? null,
            ]);

            // Record cash outflow (will be implemented in cash flow module)
            // $this->cashFlowService->recordOutflow($expense);

            return $expense->fresh();
        });
    }

    /**
     * Update expense.
     */
    public function updateExpense(Expense $expense, array $data, $file = null): Expense
    {
        return DB::transaction(function () use ($expense, $data, $file) {
            // Upload new receipt if provided
            if ($file) {
                // Delete old receipt
                if ($expense->receipt_path) {
                    $this->fileUploadService->deleteReceipt($expense->receipt_path);
                }

                $receiptData = $this->fileUploadService->uploadReceipt($file);
                $data['receipt_path'] = $receiptData['path'];
                $data['receipt_original_name'] = $receiptData['original_name'];
                $data['receipt_file_size'] = $receiptData['size'];
            }

            $expense->update($data);

            return $expense->fresh();
        });
    }

    /**
     * Delete expense.
     */
    public function deleteExpense(Expense $expense): bool
    {
        // Delete receipt
        if ($expense->receipt_path) {
            $this->fileUploadService->deleteReceipt($expense->receipt_path);
        }

        return $expense->delete();
    }

    /**
     * Generate unique expense number.
     */
    private function generateExpenseNumber(): string
    {
        $year = date('Y');
        $lastExpense = Expense::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastExpense ? ((int) substr($lastExpense->expense_number, -4)) + 1 : 1;

        return 'EXP-'.$year.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
