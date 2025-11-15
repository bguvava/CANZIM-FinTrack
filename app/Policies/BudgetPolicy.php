<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Programs Manager and Finance Officer can view budgets
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Budget $budget): bool
    {
        // Programs Manager and Finance Officer can view budget details
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Programs Manager can create budgets
        return $user->role->name === 'Programs Manager';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Budget $budget): bool
    {
        // Only Programs Manager can update budgets in draft status
        return $user->role->name === 'Programs Manager' && $budget->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Budget $budget): bool
    {
        // Only Programs Manager can delete budgets in draft status
        return $user->role->name === 'Programs Manager' && $budget->status === 'draft';
    }

    /**
     * Determine whether the user can approve the budget.
     */
    public function approve(User $user, Budget $budget): bool
    {
        // Only Programs Manager can approve budgets
        return $user->role->name === 'Programs Manager' && $budget->status === 'submitted';
    }

    /**
     * Determine whether the user can request reallocation.
     */
    public function requestReallocation(User $user): bool
    {
        // Programs Manager and Finance Officer can request reallocations
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can approve reallocation.
     */
    public function approveReallocation(User $user): bool
    {
        // Only Programs Manager can approve reallocations
        return $user->role->name === 'Programs Manager';
    }
}
