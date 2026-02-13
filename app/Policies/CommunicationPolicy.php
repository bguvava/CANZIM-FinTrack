<?php

namespace App\Policies;

use App\Models\Communication;
use App\Models\User;

class CommunicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer', 'Project Officer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Communication $communication): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer', 'Project Officer']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer', 'Project Officer']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Communication $communication): bool
    {
        // Allow creator or Programs Manager
        return $communication->created_by === $user->id || $user->role->name === 'Programs Manager';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Communication $communication): bool
    {
        // Allow creator or Programs Manager
        return $communication->created_by === $user->id || $user->role->name === 'Programs Manager';
    }
}
