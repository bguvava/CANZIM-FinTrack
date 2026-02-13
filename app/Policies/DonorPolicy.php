<?php

namespace App\Policies;

use App\Models\Donor;
use App\Models\User;

class DonorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role && in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Donor $donor): bool
    {
        return $user->role && in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role && $user->role->name === 'Programs Manager';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Donor $donor): bool
    {
        return $user->role && $user->role->name === 'Programs Manager';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Donor $donor): bool
    {
        return $user->role && $user->role->name === 'Programs Manager';
    }

    /**
     * Determine whether the user can assign donor to project.
     */
    public function assignToProject(User $user, Donor $donor): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can generate reports.
     */
    public function generateReport(User $user, Donor $donor): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }
}
