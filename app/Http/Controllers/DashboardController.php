<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Dashboard Controller
 *
 * Handles dashboard data requests for all user roles:
 * - Programs Manager: Org-wide financial overview
 * - Finance Officer: Financial operations focus
 * - Project Officer: Project-specific metrics
 */
class DashboardController extends Controller
{
    /**
     * Dashboard Service instance
     */
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard data for authenticated user
     *
     * Returns role-specific KPIs, charts data, and activity feed
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $role = $user->role->slug;

            $dashboardData = match ($role) {
                'programs-manager' => $this->dashboardService->getProgramsManagerDashboard($user),
                'finance-officer' => $this->dashboardService->getFinanceOfficerDashboard($user),
                'project-officer' => $this->dashboardService->getProjectOfficerDashboard($user),
                default => throw new \Exception('Invalid user role'),
            };

            return response()->json([
                'status' => 'success',
                'data' => $dashboardData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notifications for authenticated user
     */
    public function notifications(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $notifications = $this->dashboardService->getNotifications($user);

            return response()->json([
                'status' => 'success',
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load notifications',
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request, int $notificationId): JsonResponse
    {
        try {
            $user = $request->user();
            $this->dashboardService->markNotificationRead($user, $notificationId);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as read',
            ], 500);
        }
    }
}
