<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * User Service
 *
 * Handles business logic for user management operations
 */
class UserService
{
    /**
     * Get paginated list of users with optional filters
     *
     * @param  array<string, mixed>  $filters
     */
    public function getUsersList(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = User::with('role')
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['role_id']), function ($q) use ($filters) {
                $q->where('role_id', $filters['role_id']);
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->when(isset($filters['office_location']), function ($q) use ($filters) {
                $q->where('office_location', $filters['office_location']);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Create a new user
     *
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data, ?int $createdBy = null): User
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'office_location' => $data['office_location'] ?? null,
                'role_id' => $data['role_id'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Log activity
            ActivityLog::log(
                $createdBy,
                'user_created',
                "Created new user: {$user->name}",
                [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'ip_address' => request()->ip(),
                ]
            );

            return $user->fresh(['role']);
        });
    }

    /**
     * Update an existing user
     *
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data, ?int $updatedBy = null): User
    {
        return DB::transaction(function () use ($user, $data, $updatedBy) {
            $changes = [];

            // Track changes for audit log
            if (isset($data['name']) && $data['name'] !== $user->name) {
                $changes['name'] = ['from' => $user->name, 'to' => $data['name']];
            }

            if (isset($data['email']) && $data['email'] !== $user->email) {
                $changes['email'] = ['from' => $user->email, 'to' => $data['email']];
            }

            if (isset($data['role_id']) && $data['role_id'] !== $user->role_id) {
                $changes['role_id'] = ['from' => $user->role_id, 'to' => $data['role_id']];
            }

            // Update user
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'phone_number' => $data['phone_number'] ?? $user->phone_number,
                'office_location' => $data['office_location'] ?? $user->office_location,
                'role_id' => $data['role_id'] ?? $user->role_id,
            ]);

            // Log activity if there were changes
            if (! empty($changes)) {
                ActivityLog::log(
                    $updatedBy,
                    'user_updated',
                    "Updated user: {$user->name}",
                    [
                        'user_id' => $user->id,
                        'changes' => $changes,
                        'ip_address' => request()->ip(),
                    ]
                );
            }

            return $user->fresh(['role']);
        });
    }

    /**
     * Deactivate a user
     */
    public function deactivateUser(User $user, ?int $deactivatedBy = null): User
    {
        $user->update(['status' => 'inactive']);

        // Revoke all tokens
        $user->tokens()->delete();

        ActivityLog::log(
            $deactivatedBy,
            'user_deactivated',
            "Deactivated user: {$user->name}",
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => request()->ip(),
            ]
        );

        return $user->fresh(['role']);
    }

    /**
     * Activate a user
     */
    public function activateUser(User $user, ?int $activatedBy = null): User
    {
        $user->update(['status' => 'active']);

        ActivityLog::log(
            $activatedBy,
            'user_activated',
            "Activated user: {$user->name}",
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => request()->ip(),
            ]
        );

        return $user->fresh(['role']);
    }

    /**
     * Soft delete a user
     */
    public function deleteUser(User $user, ?int $deletedBy = null): bool
    {
        ActivityLog::log(
            $deletedBy,
            'user_deleted',
            "Deleted user: {$user->name}",
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => request()->ip(),
            ]
        );

        // Revoke all tokens
        $user->tokens()->delete();

        return $user->delete();
    }

    /**
     * Update user profile
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $changes = [];

        if (isset($data['name']) && $data['name'] !== $user->name) {
            $changes['name'] = ['from' => $user->name, 'to' => $data['name']];
        }

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'phone_number' => $data['phone_number'] ?? $user->phone_number,
        ]);

        if (! empty($changes)) {
            ActivityLog::log(
                $user->id,
                'profile_updated',
                'Updated own profile',
                [
                    'changes' => $changes,
                    'ip_address' => request()->ip(),
                ]
            );
        }

        return $user->fresh(['role']);
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $newPassword): bool
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all tokens except current
        $user->tokens()->where('id', '!=', $user->currentAccessToken()?->id)->delete();

        ActivityLog::log(
            $user->id,
            'password_changed',
            'Changed password',
            [
                'ip_address' => request()->ip(),
            ]
        );

        return true;
    }

    /**
     * Upload and resize user avatar
     */
    public function uploadAvatar(User $user, UploadedFile $file): User
    {
        // Delete old avatar if exists
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Create avatar directory if it doesn't exist
        if (! Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
        }

        // Generate unique filename
        $filename = 'avatar_'.$user->id.'_'.time().'.jpg';
        $path = 'avatars/'.$filename;

        // Resize and save image
        $image = Image::read($file);
        $image->cover(200, 200);
        $encodedImage = $image->toJpeg(90);

        Storage::disk('public')->put($path, (string) $encodedImage);

        $user->update(['avatar_path' => $path]);

        ActivityLog::log(
            $user->id,
            'avatar_uploaded',
            'Uploaded profile picture',
            [
                'ip_address' => request()->ip(),
            ]
        );

        return $user->fresh(['role']);
    }

    /**
     * Get user activity logs
     */
    public function getUserActivity(int $userId, int $perPage = 25): LengthAwarePaginator
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all activity logs with filters
     *
     * @param  array<string, mixed>  $filters
     */
    public function getActivityLogs(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = ActivityLog::with('user')
            ->when(isset($filters['user_id']), function ($q) use ($filters) {
                $q->where('user_id', $filters['user_id']);
            })
            ->when(isset($filters['activity_type']), function ($q) use ($filters) {
                $q->where('activity_type', $filters['activity_type']);
            })
            ->when(isset($filters['date_from']), function ($q) use ($filters) {
                $q->where('created_at', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($q) use ($filters) {
                $q->where('created_at', '<=', $filters['date_to']);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Bulk delete activity logs
     */
    public function bulkDeleteLogs(string $dateFrom, string $dateTo, ?int $deletedBy = null): int
    {
        // Convert date strings to full datetime ranges to properly match DATETIME columns
        $startDateTime = Carbon::parse($dateFrom)->startOfDay();
        $endDateTime = Carbon::parse($dateTo)->endOfDay();

        $count = ActivityLog::whereBetween('created_at', [$startDateTime, $endDateTime])->count();

        ActivityLog::whereBetween('created_at', [$startDateTime, $endDateTime])->delete();

        ActivityLog::log(
            $deletedBy,
            'logs_bulk_deleted',
            "Bulk deleted {$count} activity logs",
            [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'count' => $count,
                'ip_address' => request()->ip(),
            ]
        );

        return $count;
    }

    /**
     * Get unique office locations
     */
    public function getOfficeLocations(): \Illuminate\Support\Collection
    {
        return User::whereNotNull('office_location')
            ->distinct()
            ->pluck('office_location');
    }
}
