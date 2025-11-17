<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
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
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (in_array($user->role->slug, ['programs-manager', 'finance-officer', 'executive-director'])) {
            return true;
        }

        return $purchaseOrder->created_by === $user->id;
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
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if (! in_array($purchaseOrder->status, ['Draft', 'Rejected'])) {
            return false;
        }

        return $user->role->slug === 'finance-officer' && $purchaseOrder->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($purchaseOrder->status !== 'Draft') {
            return false;
        }

        return $user->role->slug === 'finance-officer' && $purchaseOrder->created_by === $user->id;
    }

    /**
     * Determine whether the user can submit the purchase order.
     */
    public function submit(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->role->slug === 'finance-officer';
    }

    /**
     * Determine whether the user can approve the purchase order.
     */
    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->role->slug === 'programs-manager';
    }

    /**
     * Determine whether the user can reject the purchase order.
     */
    public function reject(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->role->slug !== 'programs-manager') {
            return false;
        }

        return $purchaseOrder->status === 'Pending';
    }

    /**
     * Determine whether the user can mark items as received.
     */
    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->role->slug !== 'finance-officer') {
            return false;
        }

        return in_array($purchaseOrder->status, ['Approved', 'Partially Received']);
    }

    /**
     * Determine whether the user can complete the purchase order.
     */
    public function complete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->role->slug !== 'finance-officer') {
            return false;
        }

        return $purchaseOrder->status === 'Received';
    }

    /**
     * Determine whether the user can cancel the purchase order.
     */
    public function cancel(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return in_array($user->role->slug, ['finance-officer', 'programs-manager']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->role->slug === 'programs-manager';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->role->slug === 'programs-manager';
    }
}
