<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetReallocationRequest;
use App\Http\Requests\StoreBudgetRequest;
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
     * Display budgets for a project.
     */
    public function index(Project $project): JsonResponse
    {
        $budgets = $project->budgets()
            ->with(['items', 'creator', 'approver'])
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
        try {
            $budget = $this->budgetService->createBudget(
                array_merge($request->validated(), [
                    'created_by' => auth()->id(),
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Budget created successfully',
                'data' => $budget,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create budget: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified budget.
     */
    public function show(Budget $budget): JsonResponse
    {
        $budget->load(['items', 'project', 'creator', 'approver', 'reallocations']);

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
     * Approve a budget.
     */
    public function approve(Budget $budget): JsonResponse
    {
        try {
            $approvedBudget = $this->budgetService->approveBudget($budget, auth()->user());

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
