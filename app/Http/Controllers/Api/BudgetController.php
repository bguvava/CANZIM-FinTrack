<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BudgetValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetReallocationRequest;
use App\Http\Requests\StoreBudgetRequest;
use App\Models\ActivityLog;
use App\Models\Budget;
use App\Models\BudgetReallocation;
use App\Models\Project;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(
        protected BudgetService $budgetService
    ) {}

    /**
     * Display all budgets with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Budget::query()
            ->with(['project.donors', 'items', 'creator', 'approver']);

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply project filter
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Order by latest
        $query->latest();

        // Paginate
        $perPage = $request->input('per_page', 25);
        $budgets = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $budgets,
        ]);
    }

    /**
     * Display budgets for a specific project.
     */
    public function projectIndex(Project $project): JsonResponse
    {
        $budgets = $project->budgets()
            ->with(['items', 'creator', 'approver', 'project.donors'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $budgets,
        ]);
    }

    /**
     * Store a newly created budget.
     */
    public function store(StoreBudgetRequest $request): JsonResponse
    {
        // Ensure user is authenticated
        $userId = auth()->id();
        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        try {
            $budget = $this->budgetService->createBudget(
                array_merge($request->validated(), [
                    'created_by' => $userId,
                ])
            );

            // Log activity
            try {
                $budget->load('project');
                ActivityLog::log(
                    $userId,
                    'budget_created',
                    'Created budget for project: '.($budget->project->name ?? 'Unknown').' ($'.$budget->total_amount.')',
                    ['budget_id' => $budget->id, 'project_id' => $budget->project_id, 'total_amount' => $budget->total_amount]
                );
            } catch (\Throwable $e) {
                // Don't let logging errors break the main flow
            }

            return response()->json([
                'success' => true,
                'message' => 'Budget created successfully',
                'data' => $budget,
            ], 201);
        } catch (BudgetValidationException $e) {
            // Return 422 for budget validation errors (e.g., exceeds funding)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'budget' => [$e->getMessage()],
                ],
                'details' => $e->getDetails(),
            ], 422);
        } catch (\Exception $e) {
            // Log unexpected errors for debugging
            \Log::error('Budget creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create budget. Please try again later.',
            ], 500);
        }
    }

    /**
     * Display the specified budget.
     */
    public function show(Budget $budget): JsonResponse
    {
        $budget->load(['items', 'project', 'project.donors', 'creator', 'approver', 'reallocations']);

        $summary = $this->budgetService->getBudgetSummary($budget);

        return response()->json([
            'success' => true,
            'data' => [
                'budget' => $budget,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Update the specified budget.
     */
    public function update(StoreBudgetRequest $request, Budget $budget): JsonResponse
    {
        // Only allow updating draft budgets
        if ($budget->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft budgets can be updated.',
            ], 422);
        }

        try {
            $budget = $this->budgetService->updateBudget($budget, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Budget updated successfully',
                'data' => $budget,
            ]);
        } catch (BudgetValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'budget' => [$e->getMessage()],
                ],
                'details' => $e->getDetails(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Budget update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'budget_id' => $budget->id,
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update budget. Please try again later.',
            ], 500);
        }
    }

    /**
     * Approve a budget.
     */
    public function approve(Budget $budget): JsonResponse
    {
        try {
            $approvedBudget = $this->budgetService->approveBudget($budget, auth()->user());

            // Log activity
            try {
                $budget->load('project');
                ActivityLog::log(
                    auth()->id(),
                    'budget_approved',
                    'Approved budget for project: '.($budget->project->name ?? 'Unknown').' ($'.$budget->total_amount.')',
                    ['budget_id' => $budget->id, 'project_id' => $budget->project_id, 'total_amount' => $budget->total_amount]
                );
            } catch (\Throwable $e) {
                // Don't let logging errors break the main flow
            }

            return response()->json([
                'success' => true,
                'message' => 'Budget approved successfully',
                'data' => $approvedBudget,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve budget: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request budget reallocation.
     */
    public function requestReallocation(StoreBudgetReallocationRequest $request): JsonResponse
    {
        try {
            $reallocation = $this->budgetService->requestReallocation(
                array_merge($request->validated(), [
                    'requested_by' => auth()->id(),
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Reallocation request created successfully',
                'data' => $reallocation,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reallocation request: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve budget reallocation.
     */
    public function approveReallocation(BudgetReallocation $reallocation): JsonResponse
    {
        try {
            $approvedReallocation = $this->budgetService->approveReallocation(
                $reallocation,
                auth()->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Reallocation approved successfully',
                'data' => $approvedReallocation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve reallocation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget categories.
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->budgetService->getBudgetCategories(),
        ]);
    }
}
