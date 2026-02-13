<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignProjectRequest;
use App\Http\Requests\StoreCommunicationRequest;
use App\Http\Requests\StoreDonorRequest;
use App\Http\Requests\StoreInKindContributionRequest;
use App\Http\Requests\UpdateDonorRequest;
use App\Http\Resources\DonorResource;
use App\Models\ActivityLog;
use App\Models\Communication;
use App\Models\Donor;
use App\Models\InKindContribution;
use App\Models\Project;
use App\Services\DonorPDFService;
use App\Services\DonorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DonorController extends Controller
{
    public function __construct(
        protected DonorService $donorService,
        protected DonorPDFService $pdfService,
        protected \App\Services\DonorReportService $reportService
    ) {}

    /**
     * Display a listing of donors.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Donor::class);

        $query = Donor::with(['projects', 'inKindContributions']);

        // Search (debounced 300ms on frontend)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filters (max 3 active at once)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('funding_min') || $request->filled('funding_max')) {
            $query->whereHas('projects', function ($q) use ($request) {
                if ($request->filled('funding_min')) {
                    $q->where('project_donors.funding_amount', '>=', $request->funding_min);
                }
                if ($request->filled('funding_max')) {
                    $q->where('project_donors.funding_amount', '<=', $request->funding_max);
                }
            });
        }

        if ($request->filled('has_projects')) {
            if ($request->has_projects === 'yes') {
                $query->has('projects');
            } else {
                $query->doesntHave('projects');
            }
        }

        // Pagination (25 per page)
        $donors = $query->latest()->paginate(25);

        return response()->json([
            'success' => true,
            'data' => DonorResource::collection($donors),
            'pagination' => [
                'total' => $donors->total(),
                'per_page' => $donors->perPage(),
                'current_page' => $donors->currentPage(),
                'last_page' => $donors->lastPage(),
            ],
        ]);
    }

    /**
     * Get donors for projects assigned to the current user (Project Officers).
     */
    public function donorsForMyProjects(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get project IDs for projects the user is assigned to
        $projectIds = Project::query()
            ->whereHas('teamMembers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->pluck('id');

        // Get donors linked to these projects
        $donors = Donor::query()
            ->whereHas('projects', function ($query) use ($projectIds) {
                $query->whereIn('projects.id', $projectIds);
            })
            ->with('projects')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => DonorResource::collection($donors),
        ]);
    }

    /**
     * Store a newly created donor.
     */
    public function store(StoreDonorRequest $request): JsonResponse
    {
        $this->authorize('create', Donor::class);

        try {
            $donor = Donor::create($request->validated());

            ActivityLog::log(auth()->id(), 'donor_created', 'Donor created: '.$donor->name, [
                'donor_id' => $donor->id,
                'name' => $donor->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Donor created successfully',
                'data' => new DonorResource($donor),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create donor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified donor with relationships.
     */
    public function show(Donor $donor): JsonResponse
    {
        $this->authorize('view', $donor);

        $donor->load([
            'projects',
            'inKindContributions.project',
            'communications' => fn ($q) => $q->latest('communication_date'),
        ]);

        return response()->json([
            'success' => true,
            'data' => new DonorResource($donor),
        ]);
    }

    /**
     * Update the specified donor.
     */
    public function update(UpdateDonorRequest $request, Donor $donor): JsonResponse
    {
        $this->authorize('update', $donor);

        try {
            $donor->update($request->validated());

            ActivityLog::log(auth()->id(), 'donor_updated', 'Donor updated: '.$donor->name, [
                'donor_id' => $donor->id,
                'name' => $donor->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Donor updated successfully',
                'data' => new DonorResource($donor->load(['projects', 'inKindContributions'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update donor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete the specified donor (soft delete).
     */
    public function destroy(Donor $donor): JsonResponse
    {
        $this->authorize('delete', $donor);

        // Check if donor can be deleted
        if (! $this->donorService->canDeleteDonor($donor)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete donor with active funded projects. Please remove all project assignments first.',
            ], 422);
        }

        try {
            $donor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Donor deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete donor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign donor to a project with funding details.
     */
    public function assignToProject(AssignProjectRequest $request, Donor $donor): JsonResponse
    {
        $this->authorize('assignToProject', $donor);

        try {
            // Check if already assigned
            if ($donor->projects()->where('project_id', $request->project_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donor is already assigned to this project',
                ], 422);
            }

            $this->donorService->assignToProject($donor, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Donor assigned to project successfully',
                'data' => $this->donorService->getFundingSummary($donor),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign donor to project: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove donor from a project.
     */
    public function removeFromProject(Donor $donor, Project $project): JsonResponse
    {
        $this->authorize('assignToProject', $donor);

        try {
            $removed = $this->donorService->removeFromProject($donor, $project->id);

            if (! $removed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove donor from project with linked expenses',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Donor removed from project successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove donor from project: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get funding summary for a donor.
     */
    public function fundingSummary(Donor $donor): JsonResponse
    {
        $this->authorize('view', $donor);

        return response()->json([
            'success' => true,
            'data' => $this->donorService->getFundingSummary($donor),
        ]);
    }

    /**
     * Deactivate/Activate a donor.
     */
    public function toggleStatus(Donor $donor): JsonResponse
    {
        $this->authorize('update', $donor);

        try {
            $newStatus = $donor->status === 'active' ? 'inactive' : 'active';

            // If deactivating, check for active projects
            if ($newStatus === 'inactive') {
                $activeProjects = $donor->projects()
                    ->whereIn('status', ['active', 'planning'])
                    ->count();

                if ($activeProjects > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot deactivate donor with {$activeProjects} active project(s). Please complete or cancel these projects first.",
                    ], 422);
                }
            }

            $donor->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => "Donor {$newStatus} successfully",
                'data' => new DonorResource($donor->load(['projects', 'inKindContributions'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update donor status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store in-kind contribution.
     */
    public function storeInKindContribution(StoreInKindContributionRequest $request): JsonResponse
    {
        $this->authorize('create', Donor::class);

        try {
            $contribution = InKindContribution::create(array_merge(
                $request->validated(),
                ['created_by' => auth()->id()]
            ));

            return response()->json([
                'success' => true,
                'message' => 'In-kind contribution recorded successfully',
                'data' => $contribution->load(['donor', 'project']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record in-kind contribution: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store communication log.
     */
    public function storeCommunication(StoreCommunicationRequest $request): JsonResponse
    {
        $this->authorize('create', Donor::class);

        try {
            $data = $request->validated();

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('communications', 'public');
                $data['attachment_path'] = $path;
            }

            $data['created_by'] = auth()->id();

            $communication = Communication::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Communication logged successfully',
                'data' => $communication->load('creator'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log communication: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get donor statistics for dashboard.
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', Donor::class);

        return response()->json([
            'success' => true,
            'data' => $this->donorService->getDonorStatistics(),
        ]);
    }

    /**
     * Generate PDF financial report for a donor with optional filters.
     */
    public function generateReport(Request $request, Donor $donor): BinaryFileResponse|JsonResponse
    {
        $this->authorize('view', $donor);

        // Validate filters
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
            'include_in_kind' => 'nullable|in:true,false,1,0',
        ]);

        // Convert string boolean to actual boolean
        if (isset($filters['include_in_kind'])) {
            $filters['include_in_kind'] = filter_var($filters['include_in_kind'], FILTER_VALIDATE_BOOLEAN);
        }

        try {
            $filename = $this->pdfService->generateDonorFinancialReport($donor, $filters);
            $filepath = $this->pdfService->getReportDownloadPath($filename);

            return response()->download($filepath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export donor financial report as PDF stream.
     */
    public function exportFinancialReport(Request $request, Donor $donor): \Illuminate\Http\Response|JsonResponse
    {
        $this->authorize('view', $donor);

        // Validate filters
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'include_in_kind' => 'nullable|in:true,false,1,0',
        ]);

        // Convert string boolean to actual boolean
        if (isset($filters['include_in_kind'])) {
            $filters['include_in_kind'] = filter_var($filters['include_in_kind'], FILTER_VALIDATE_BOOLEAN);
        }

        try {
            return $this->reportService->streamDonorFinancialReportPDF($donor, $filters);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted donor.
     */
    public function restore(int $id): JsonResponse
    {
        $this->authorize('create', Donor::class);

        try {
            $donor = Donor::withTrashed()->findOrFail($id);

            if (! $donor->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donor is not deleted',
                ], 422);
            }

            $donor->restore();

            return response()->json([
                'success' => true,
                'message' => 'Donor restored successfully',
                'data' => new DonorResource($donor->load(['projects', 'inKindContributions'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore donor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get chart data for donor analytics.
     */
    public function chartData(): JsonResponse
    {
        $this->authorize('viewAny', Donor::class);

        try {
            $chartData = $this->donorService->generateChartData();

            return response()->json([
                'success' => true,
                'data' => $chartData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get funding timeline for a specific donor.
     */
    public function getFundingTimeline(Donor $donor): JsonResponse
    {
        $this->authorize('view', $donor);

        try {
            // Get project funding grouped by year
            $fundingByYear = $donor->projects()
                ->withPivot(['funding_amount', 'funding_period_start', 'funding_period_end'])
                ->get()
                ->groupBy(function ($project) {
                    return $project->pivot->funding_period_start
                        ? \Carbon\Carbon::parse($project->pivot->funding_period_start)->year
                        : 'Undated';
                })
                ->map(fn ($projects) => $projects->sum('pivot.funding_amount'))
                ->sortKeys();

            // Format for Chart.js
            $chartData = [
                'labels' => $fundingByYear->keys()->toArray(),
                'datasets' => [
                    [
                        'label' => 'Funding Amount (USD)',
                        'data' => $fundingByYear->values()->toArray(),
                        'backgroundColor' => '#1E40AF',
                        'borderColor' => '#1E40AF',
                        'borderWidth' => 1,
                    ],
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $chartData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch funding timeline: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get communications for a specific donor.
     */
    public function getCommunications(Donor $donor): JsonResponse
    {
        $this->authorize('view', $donor);

        try {
            $communications = $donor->communications()
                ->with('creator')
                ->latest('communication_date')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $communications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch communications: '.$e->getMessage(),
            ], 500);
        }
    }
}
