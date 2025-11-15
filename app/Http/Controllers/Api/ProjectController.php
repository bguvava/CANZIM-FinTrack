<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService,
        protected ReportService $reportService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::with(['donors', 'creator', 'budgets']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filters (max 3)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('donor_id')) {
            $query->whereHas('donors', function ($q) use ($request) {
                $q->where('donors.id', $request->donor_id);
            });
        }

        if ($request->filled('office_location')) {
            $query->where('office_location', $request->office_location);
        }

        // Pagination
        $projects = $query->latest()->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        try {
            $project = $this->projectService->createProject(
                array_merge($request->validated(), [
                    'created_by' => auth()->id(),
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load([
            'donors',
            'budgets.items',
            'teamMembers',
            'creator',
        ]);

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        try {
            $updatedProject = $this->projectService->updateProject(
                $project,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $updatedProject,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Archive the specified resource.
     */
    public function archive(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        try {
            $this->projectService->archiveProject($project);

            return response()->json([
                'success' => true,
                'message' => 'Project archived successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive project: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        try {
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate financial report for project.
     */
    public function generateReport(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        try {
            $filename = $this->reportService->generateProjectFinancialReport($project);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url("api/reports/download/{$filename}"),
                    'view_url' => url("api/reports/view/{$filename}"),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get project statistics.
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $stats = $this->projectService->getProjectStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
