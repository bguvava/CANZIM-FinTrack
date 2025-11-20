<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view any reports.
     * Programs Manager and Finance Officer can view reports.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can view the report.
     * Programs Manager and Finance Officer can view their own reports.
     */
    public function view(User $user, Report $report): bool
    {
        if (! in_array($user->role->name, ['Programs Manager', 'Finance Officer'])) {
            return false;
        }

        // Users can view reports they generated
        return $report->generated_by === $user->id;
    }

    /**
     * Determine whether the user can generate reports.
     * Programs Manager and Finance Officer can generate reports.
     */
    public function generate(User $user): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can export reports to PDF.
     * Programs Manager and Finance Officer can export PDFs.
     */
    public function exportPDF(User $user): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can view donor reports.
     * Only Programs Manager can view donor contribution reports.
     */
    public function viewDonorReports(User $user): bool
    {
        return $user->role->name === 'Programs Manager';
    }

    /**
     * Determine whether the user can view project status reports.
     * Only Programs Manager can view project status reports.
     */
    public function viewProjectStatusReports(User $user): bool
    {
        return $user->role->name === 'Programs Manager';
    }

    /**
     * Determine whether the user can delete the report.
     * Only the user who generated the report can delete it.
     */
    public function delete(User $user, Report $report): bool
    {
        return $report->generated_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role->name, ['Programs Manager', 'Finance Officer']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return $report->generated_by === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return $report->generated_by === $user->id;
    }
}
