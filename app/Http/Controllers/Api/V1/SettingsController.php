<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmailSettingsRequest;
use App\Http\Requests\UpdateFinancialSettingsRequest;
use App\Http\Requests\UpdateNotificationSettingsRequest;
use App\Http\Requests\UpdateOrganizationSettingsRequest;
use App\Http\Requests\UpdateSecuritySettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settingsService
    ) {}

    /**
     * Get all settings
     */
    public function index(): JsonResponse
    {
        Gate::authorize('manage-settings');

        $settings = $this->settingsService->getAllSettings();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get settings by group
     */
    public function show(string $group): JsonResponse
    {
        Gate::authorize('manage-settings');

        if (! in_array($group, ['organization', 'financial', 'email', 'security', 'notifications', 'general'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid settings group',
            ], 400);
        }

        $settings = $this->settingsService->getByGroup($group);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update organization settings
     */
    public function updateOrganization(UpdateOrganizationSettingsRequest $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        try {
            $settings = $this->settingsService->updateOrganization($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Organization settings updated successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update financial settings
     */
    public function updateFinancial(UpdateFinancialSettingsRequest $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        try {
            $settings = $this->settingsService->updateFinancial($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Financial settings updated successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update email settings
     */
    public function updateEmail(UpdateEmailSettingsRequest $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        try {
            $settings = $this->settingsService->updateEmail($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Email settings updated successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update security settings
     */
    public function updateSecurity(UpdateSecuritySettingsRequest $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        try {
            $settings = $this->settingsService->updateSecurity($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Security settings updated successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        try {
            $settings = $this->settingsService->updateNotifications($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload organization logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        try {
            $path = $this->settingsService->uploadLogo($request->file('logo'));

            return response()->json([
                'success' => true,
                'message' => 'Logo uploaded successfully',
                'data' => ['path' => $path],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload logo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all caches
     */
    public function clearCache(): JsonResponse
    {
        Gate::authorize('manage-settings');

        try {
            $cleared = $this->settingsService->clearAllCaches();

            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully',
                'data' => ['cleared' => $cleared],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear caches: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system health status
     */
    public function systemHealth(): JsonResponse
    {
        Gate::authorize('manage-settings');

        try {
            $health = $this->settingsService->getSystemHealth();

            return response()->json([
                'success' => true,
                'data' => $health,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system health: '.$e->getMessage(),
            ], 500);
        }
    }
}
