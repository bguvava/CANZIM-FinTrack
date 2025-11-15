<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Expense $expense): bool
    {
        if (in_array($user->role->slug, ['programs-manager', 'finance-officer'])) {
            return true;
        }

        return $expense->submitted_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Expense $expense): bool
    {
        if (! in_array($expense->status, ['Draft', 'Rejected'])) {
            return false;
        }

        return $expense->submitted_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Expense $expense): bool
    {
        if ($expense->status !== 'Draft') {
            return false;
        }

        return $expense->submitted_by === $user->id || $user->role->slug === 'programs-manager';
    }

    /**
     * Determine whether the user can submit the expense.
     */
    public function submit(User $user, Expense $expense): bool
    {
        if (! in_array($expense->status, ['Draft', 'Rejected'])) {
            return false;
        }

        return $expense->submitted_by === $user->id;
    }

    /**
     * Determine whether the user can review the expense (Finance Officer).
     */
    public function review(User $user, Expense $expense): bool
    {
        if ($user->role->slug !== 'finance-officer') {
            return false;
        }

        return $expense->status === 'Submitted';
    }

    /**
     * Determine whether the user can approve the expense (Programs Manager).
     */
    public function approve(User $user, Expense $expense): bool
    {
        if ($user->role->slug !== 'programs-manager') {
            return false;
        }

        return $expense->status === 'Under Review';
    }

    /**
     * Determine whether the user can mark as paid (Finance Officer).
     */
    public function markAsPaid(User $user, Expense $expense): bool
    {
        if ($user->role->slug !== 'finance-officer') {
            return false;
        }

        return $expense->status === 'Approved';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Expense $expense): bool
    {
        return $user->role->slug === 'programs-manager';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Expense $expense): bool
    {
        return $user->role->slug === 'programs-manager';
    }
}
