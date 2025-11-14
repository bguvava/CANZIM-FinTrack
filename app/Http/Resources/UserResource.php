<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource
 *
 * Formats user data for API responses
 *
 * @property-read \App\Models\User $resource
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone_number' => $this->resource->phone_number,
            'office_location' => $this->resource->office_location,
            'avatar_path' => $this->resource->avatar_path,
            'avatar_url' => $this->resource->getAvatarUrl(),
            'initials' => $this->resource->getInitials(),
            'status' => $this->resource->status,
            'role' => [
                'id' => $this->resource->role->id,
                'name' => $this->resource->role->name,
                'slug' => $this->resource->role->slug,
            ],
            'last_login_at' => $this->resource->last_login_at?->toDateTimeString(),
            'last_login_ip' => $this->resource->last_login_ip,
            'email_verified_at' => $this->resource->email_verified_at?->toDateTimeString(),
            'created_at' => $this->resource->created_at->toDateTimeString(),
            'updated_at' => $this->resource->updated_at->toDateTimeString(),
        ];
    }
}
