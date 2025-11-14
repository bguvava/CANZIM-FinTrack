<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Activity Log Controller
 *
 * Handles activity log operations
 */
class ActivityLogController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Get activity logs with filters
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewActivityLogs', User::class);

        $filters = $request->only(['user_id', 'activity_type', 'date_from', 'date_to']);
        $perPage = (int) $request->get('per_page', 25);

        $logs = $this->userService->getActivityLogs($filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => ActivityLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'from' => $logs->firstItem(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'to' => $logs->lastItem(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Get activity logs for a specific user
     */
    public function userActivity(User $user, Request $request): JsonResponse
    {
        $this->authorize('view', $user);

        $perPage = (int) $request->get('per_page', 25);
        $logs = $this->userService->getUserActivity($user->id, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => ActivityLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'from' => $logs->firstItem(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'to' => $logs->lastItem(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Bulk delete activity logs
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->authorize('bulkDeleteLogs', User::class);

        $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $count = $this->userService->bulkDeleteLogs(
            $request->input('date_from'),
            $request->input('date_to'),
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => "Successfully deleted {$count} activity log(s).",
            'data' => ['count' => $count],
        ]);
    }
}
