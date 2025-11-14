<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * User Policy
 *
 * Authorization logic for user management operations
 */
class UserPolicy
{
    /**
     * Determine if the user can view any users
     */
    public function viewAny(User $user): bool
    {
        // Only Programs Manager can view all users
        return $user->hasRole('programs-manager');
    }

    /**
     * Determine if the user can view a specific user
     */
    public function view(User $user, User $model): bool
    {
        // Programs Manager can view all, users can view themselves
        return $user->hasRole('programs-manager') || $user->id === $model->id;
    }

    /**
     * Determine if the user can create users
     */
    public function create(User $user): bool
    {
        // Only Programs Manager can create users
        return $user->hasRole('programs-manager');
    }

    /**
     * Determine if the user can update users
     */
    public function update(User $user, User $model): bool
    {
        // Programs Manager can update all, users can update themselves (limited fields)
        return $user->hasRole('programs-manager') || $user->id === $model->id;
    }

    /**
     * Determine if the user can delete users
     */
    public function delete(User $user, User $model): bool
    {
        // Only Programs Manager can delete users, but not themselves
        return $user->hasRole('programs-manager') && $user->id !== $model->id;
    }

    /**
     * Determine if the user can deactivate users
     */
    public function deactivate(User $user, User $model): bool
    {
        // Only Programs Manager can deactivate users, but not themselves
        return $user->hasRole('programs-manager') && $user->id !== $model->id;
    }

    /**
     * Determine if the user can activate users
     */
    public function activate(User $user): bool
    {
        // Only Programs Manager can activate users
        return $user->hasRole('programs-manager');
    }

    /**
     * Determine if the user can view activity logs
     */
    public function viewActivityLogs(User $user): bool
    {
        // Only Programs Manager can view activity logs
        return $user->hasRole('programs-manager');
    }

    /**
     * Determine if the user can bulk delete activity logs
     */
    public function bulkDeleteLogs(User $user): bool
    {
        // Only Programs Manager can bulk delete logs
        return $user->hasRole('programs-manager');
    }
}
