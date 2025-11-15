<?php

namespace App\Policies;

use App\Models\CashFlow;
use App\Models\User;

class CashFlowPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->slug, ['finance-officer', 'programs-manager', 'executive-director']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CashFlow $cashFlow): bool
    {
        return in_array($user->role->slug, ['finance-officer', 'programs-manager', 'executive-director']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->slug === 'finance-officer';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CashFlow $cashFlow): bool
    {
        // Can only update if not reconciled
        if ($cashFlow->is_reconciled) {
            return false;
        }

        return $user->role->slug === 'finance-officer';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CashFlow $cashFlow): bool
    {
        // Can only delete if not reconciled
        if ($cashFlow->is_reconciled) {
            return false;
        }

        return $user->role->slug === 'finance-officer';
    }

    /**
     * Determine whether the user can reconcile cash flows.
     */
    public function reconcile(User $user, CashFlow $cashFlow): bool
    {
        if ($cashFlow->is_reconciled) {
            return false;
        }

        return $user->role->slug === 'finance-officer';
    }

    /**
     * Determine whether the user can view projections.
     */
    public function viewProjections(User $user): bool
    {
        return in_array($user->role->slug, ['finance-officer', 'programs-manager', 'executive-director']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CashFlow $cashFlow): bool
    {
        return $user->role->slug === 'finance-officer';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CashFlow $cashFlow): bool
    {
        return false;
    }
}
