<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * User Controller
 *
 * Handles user management operations
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->only(['search', 'role_id', 'role', 'status', 'office_location']);
        $perPage = (int) $request->get('per_page', 25);

        $users = $this->userService->getUsersList($filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'from' => $users->firstItem(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'to' => $users->lastItem(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'status' => 'success',
            'data' => new UserResource($user->load('role')),
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->updateUser(
            $user,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => new UserResource($updatedUser),
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user, Request $request): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->deleteUser($user, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Deactivate a user
     */
    public function deactivate(User $user, Request $request): JsonResponse
    {
        $this->authorize('deactivate', $user);

        $deactivatedUser = $this->userService->deactivateUser($user, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'User deactivated successfully.',
            'data' => new UserResource($deactivatedUser),
        ]);
    }

    /**
     * Activate a user
     */
    public function activate(User $user, Request $request): JsonResponse
    {
        $this->authorize('activate', $user);

        $activatedUser = $this->userService->activateUser($user, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'User activated successfully.',
            'data' => new UserResource($activatedUser),
        ]);
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new UserResource($request->user()->load('role')),
        ]);
    }

    /**
     * Update current user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($updatedUser),
        ]);
    }

    /**
     * Change current user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        // Verify current password
        if (! Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect.',
                'errors' => [
                    'current_password' => ['Current password is incorrect.'],
                ],
            ], 422);
        }

        $this->userService->changePassword($user, $request->input('new_password'));

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $this->userService->uploadAvatar(
            $request->user(),
            $request->file('avatar')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture uploaded successfully.',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Get available roles
     */
    public function roles(): JsonResponse
    {
        $roles = Role::select('id', 'name', 'slug')->get();

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ]);
    }

    /**
     * Get office locations
     */
    public function officeLocations(): JsonResponse
    {
        $locations = $this->userService->getOfficeLocations();

        return response()->json([
            'status' => 'success',
            'data' => $locations,
        ]);
    }

    /**
     * Search users for @mention functionality
     */
    public function search(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 100);
        $search = $request->get('search', '');

        $query = User::query()
            ->where('status', 'active')
            ->select('id', 'name', 'email')
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit($perPage)->get();

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }
}
