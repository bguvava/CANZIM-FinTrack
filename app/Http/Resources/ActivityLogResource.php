<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Activity Log Resource
 *
 * Formats activity log data for API responses
 *
 * @property-read \App\Models\ActivityLog $resource
 */
class ActivityLogResource extends JsonResource
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
            'user' => $this->when($this->resource->user !== null, function () {
                return [
                    'id' => $this->resource->user->id,
                    'name' => $this->resource->user->name,
                    'email' => $this->resource->user->email,
                ];
            }),
            'user_id' => $this->resource->user_id,
            'user_name' => $this->resource->user?->name,
            'user_email' => $this->resource->user?->email,
            'activity_type' => $this->resource->activity_type,
            'description' => $this->resource->description,
            'properties' => $this->resource->properties,
            'ip_address' => $this->resource->properties['ip_address'] ?? null,
            'created_at' => $this->resource->created_at->toDateTimeString(),
            'created_at_human' => $this->resource->created_at->diffForHumans(),
        ];
    }
}
