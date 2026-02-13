<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine if the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view documents
    }

    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // User can view if they have access to the parent entity
        return $this->hasAccessToParent($user, $document);
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can upload documents
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        // User can update if they uploaded it or are Programs Manager
        return $document->uploaded_by === $user->id ||
            $user->role->slug === 'programs-manager';
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // User can delete if they uploaded it or are Programs Manager
        return $document->uploaded_by === $user->id ||
            $user->role->slug === 'programs-manager';
    }

    /**
     * Determine if the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        // User can download if they have access to the parent entity
        return $this->hasAccessToParent($user, $document);
    }

    /**
     * Determine if the user can replace the document.
     */
    public function replace(User $user, Document $document): bool
    {
        // User can replace if they uploaded it or are Programs Manager
        return $document->uploaded_by === $user->id ||
            $user->role->slug === 'programs-manager';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return $user->role->slug === 'programs-manager';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return $user->role->slug === 'programs-manager';
    }

    /**
     * Check if user has access to the parent entity.
     */
    private function hasAccessToParent(User $user, Document $document): bool
    {
        $documentable = $document->documentable;

        // Standalone documents (no parent) are accessible to all authenticated users
        if (! $documentable) {
            return true;
        }

        // For Project documents - all roles can access
        if ($document->documentable_type === 'App\\Models\\Project') {
            return true;
        }

        // For Budget documents
        if ($document->documentable_type === 'App\\Models\\Budget') {
            return $documentable->created_by === $user->id ||
                $user->role->slug === 'programs-manager' ||
                $user->role->slug === 'finance-officer';
        }

        // For Expense documents
        if ($document->documentable_type === 'App\\Models\\Expense') {
            return $documentable->submitted_by === $user->id ||
                $user->role->slug === 'programs-manager' ||
                $user->role->slug === 'finance-officer';
        }

        // For PurchaseOrder documents - Programs Manager and Finance Officer
        if ($document->documentable_type === 'App\\Models\\PurchaseOrder') {
            return $user->role->slug === 'programs-manager' ||
                $user->role->slug === 'finance-officer';
        }

        // For Donor documents - authenticated users
        if ($document->documentable_type === 'App\\Models\\Donor') {
            return true;
        }

        return false;
    }
}
