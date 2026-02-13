<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Create a new project with donors and team members.
     */
    public function createProject(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            // Generate project code
            $data['code'] = $this->generateProjectCode();

            // Handle location field mapping (frontend sends 'location', backend uses 'office_location')
            $officeLocation = $data['office_location'] ?? $data['location'] ?? null;

            // Create the project
            $project = Project::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_budget' => $data['total_budget'] ?? 0,
                'status' => $data['status'] ?? 'planning',
                'office_location' => $officeLocation,
                'created_by' => $data['created_by'],
            ]);

            // Handle donors - accept both 'donors' (full objects) and 'donor_ids' (simple array)
            if (isset($data['donors']) && is_array($data['donors']) && count($data['donors']) > 0) {
                $this->assignDonors($project, $data['donors']);
            } elseif (isset($data['donor_ids']) && is_array($data['donor_ids']) && count($data['donor_ids']) > 0) {
                // Convert simple donor_ids to donors array format
                $donorsArray = array_map(function ($donorId) {
                    return [
                        'donor_id' => $donorId,
                        'funding_amount' => 0,
                    ];
                }, $data['donor_ids']);
                $this->assignDonors($project, $donorsArray);
            }

            // Assign team members if provided
            if (isset($data['team_members']) && is_array($data['team_members'])) {
                $this->assignTeamMembers($project, $data['team_members']);
            }

            // Log activity (Module 7 feature)
            // activity()
            //     ->performedOn($project)
            //     ->causedBy(auth()->user())
            //     ->withProperties(['attributes' => $project->toArray()])
            //     ->log('project_created');

            // Clear cache
            Cache::forget('projects_list');

            return $project->load(['donors', 'teamMembers', 'creator']);
        });
    }

    /**
     * Update an existing project.
     */
    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $oldAttributes = $project->toArray();

            // Handle location field mapping (frontend sends 'location', backend uses 'office_location')
            $officeLocation = $data['office_location'] ?? $data['location'] ?? $project->office_location;

            // Update basic project info
            $project->update([
                'name' => $data['name'] ?? $project->name,
                'description' => $data['description'] ?? $project->description,
                'start_date' => $data['start_date'] ?? $project->start_date,
                'end_date' => $data['end_date'] ?? $project->end_date,
                'total_budget' => $data['total_budget'] ?? $project->total_budget,
                'status' => $data['status'] ?? $project->status,
                'office_location' => $officeLocation,
            ]);

            // Handle donors - accept both 'donors' (full objects) and 'donor_ids' (simple array)
            if (isset($data['donors']) && is_array($data['donors'])) {
                $this->syncDonors($project, $data['donors']);
            } elseif (isset($data['donor_ids']) && is_array($data['donor_ids'])) {
                // Convert simple donor_ids to donors array format (preserving existing funding amounts)
                $existingDonors = $project->donors->keyBy('id');
                $donorsArray = array_map(function ($donorId) use ($existingDonors) {
                    $existingPivot = $existingDonors->get($donorId)?->pivot;

                    return [
                        'donor_id' => $donorId,
                        'funding_amount' => $existingPivot?->funding_amount ?? 0,
                        'funding_period_start' => $existingPivot?->funding_period_start ?? null,
                        'funding_period_end' => $existingPivot?->funding_period_end ?? null,
                        'is_restricted' => $existingPivot?->is_restricted ?? false,
                    ];
                }, $data['donor_ids']);
                $this->syncDonors($project, $donorsArray);
            }

            // Update team members if provided
            if (isset($data['team_members'])) {
                $this->syncTeamMembers($project, $data['team_members']);
            }

            // Log changes to audit trail (Module 7 feature)
            // activity()
            //     ->performedOn($project)
            //     ->causedBy(auth()->user())
            //     ->withProperties([
            //         'old' => $oldAttributes,
            //         'attributes' => $project->fresh()->toArray(),
            //     ])
            //     ->log('project_updated');

            // Clear cache
            Cache::forget('projects_list');
            Cache::forget("project_{$project->id}");

            return $project->fresh(['donors', 'teamMembers', 'creator']);
        });
    }

    /**
     * Archive a project.
     */
    public function archiveProject(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            $project->update(['status' => 'cancelled']); // Use 'cancelled' status for archived projects

            // Log activity (Module 7 - Activity Logging)
            /* activity()
                ->performedOn($project)
                ->causedBy(auth()->user())
                ->log('project_archived'); */

            // Clear cache
            Cache::forget('projects_list');
            Cache::forget("project_{$project->id}");

            return true;
        });
    }

    /**
     * Assign donors to a project.
     */
    public function assignDonors(Project $project, array $donors): void
    {
        foreach ($donors as $donor) {
            $project->donors()->attach($donor['donor_id'], [
                'funding_amount' => $donor['funding_amount'],
                'funding_period_start' => $donor['funding_period_start'] ?? null,
                'funding_period_end' => $donor['funding_period_end'] ?? null,
                'is_restricted' => $donor['is_restricted'] ?? false,
            ]);
        }
    }

    /**
     * Sync donors with a project.
     */
    public function syncDonors(Project $project, array $donors): void
    {
        $syncData = [];

        foreach ($donors as $donor) {
            $syncData[$donor['donor_id']] = [
                'funding_amount' => $donor['funding_amount'],
                'funding_period_start' => $donor['funding_period_start'] ?? null,
                'funding_period_end' => $donor['funding_period_end'] ?? null,
                'is_restricted' => $donor['is_restricted'] ?? false,
            ];
        }

        $project->donors()->sync($syncData);
    }

    /**
     * Assign team members to a project.
     */
    public function assignTeamMembers(Project $project, array $teamMembers): void
    {
        foreach ($teamMembers as $member) {
            $project->teamMembers()->attach($member['user_id'], [
                'role' => $member['role'] ?? 'team_member',
            ]);
        }
    }

    /**
     * Sync team members with a project.
     */
    public function syncTeamMembers(Project $project, array $teamMembers): void
    {
        $syncData = [];

        foreach ($teamMembers as $member) {
            $syncData[$member['user_id']] = [
                'role' => $member['role'] ?? 'team_member',
            ];
        }

        $project->teamMembers()->sync($syncData);
    }

    /**
     * Generate a unique project code.
     */
    public function generateProjectCode(): string
    {
        $year = date('Y');
        $lastProject = Project::where('code', 'like', "PROJ-{$year}-%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastProject) {
            $lastNumber = (int) substr($lastProject->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('PROJ-%s-%04d', $year, $newNumber);
    }

    /**
     * Get project statistics.
     */
    public function getProjectStatistics(): array
    {
        return Cache::remember('project_statistics', 300, function () {
            return [
                'total_projects' => Project::count(),
                'active_projects' => Project::where('status', 'active')->count(),
                'completed_projects' => Project::where('status', 'completed')->count(),
                'total_budget' => Project::sum('total_budget'),
                'total_spent' => Project::withSum('expenses', 'amount')->get()->sum('expenses_sum_amount'),
            ];
        });
    }
}
