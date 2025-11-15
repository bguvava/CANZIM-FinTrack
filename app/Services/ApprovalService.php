<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\User;

class ApprovalService
{
    /**
     * Get pending expenses for Finance Officer review.
     */
    public function getPendingForFinanceOfficer(): \Illuminate\Database\Eloquent\Collection
    {
        return Expense::with(['project', 'category', 'submitter'])
            ->where('status', 'Submitted')
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get pending expenses for Programs Manager approval.
     */
    public function getPendingForManager(): \Illuminate\Database\Eloquent\Collection
    {
        return Expense::with(['project', 'category', 'submitter', 'reviewer'])
            ->where('status', 'Under Review')
            ->orderBy('reviewed_at', 'desc')
            ->get();
    }

    /**
     * Get approval history for an expense.
     */
    public function getApprovalHistory(Expense $expense): \Illuminate\Database\Eloquent\Collection
    {
        return $expense->approvals()
            ->with('user')
            ->orderBy('action_date', 'asc')
            ->get();
    }

    /**
     * Check if user can review expense.
     */
    public function canReview(User $user, Expense $expense): bool
    {
        return $user->role === 'Finance Officer' && $expense->status === 'Submitted';
    }

    /**
     * Check if user can approve expense.
     */
    public function canApprove(User $user, Expense $expense): bool
    {
        return $user->role === 'Programs Manager' && $expense->status === 'Under Review';
    }

    /**
     * Check if user can mark as paid.
     */
    public function canMarkAsPaid(User $user, Expense $expense): bool
    {
        return $user->role === 'Finance Officer' && $expense->status === 'Approved';
    }
}
