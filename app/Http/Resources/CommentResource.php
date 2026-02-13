<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'content' => $this->resource->content,
            'user_id' => $this->resource->user_id,
            'user' => $this->resource->user ? [
                'id' => $this->resource->user->id,
                'name' => $this->resource->user->name,
                'avatar' => $this->resource->user->getAvatarUrl(),
                'initials' => $this->resource->user->getInitials(),
            ] : null,
            'parent_id' => $this->resource->parent_id,
            'attachments' => CommentAttachmentResource::collection($this->whenLoaded('attachments')),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'created_at_relative' => $this->resource->created_at?->diffForHumans(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
            'is_edited' => $this->resource->isEdited(),
            'can_edit' => $request->user() ? $request->user()->id === $this->resource->user_id : false,
            'can_delete' => $request->user() ? $request->user()->id === $this->resource->user_id : false,
        ];
    }
}
