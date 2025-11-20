<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentAttachmentResource extends JsonResource
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
            'file_name' => $this->resource->file_name,
            'file_size' => $this->resource->file_size,
            'file_type' => $this->resource->file_type,
            'mime_type' => $this->resource->file_type, // Alias for consistency
            'formatted_size' => $this->resource->formatted_size,
            'url' => $this->resource->url,
            'download_url' => $this->resource->download_url,
            'is_image' => $this->resource->isImage(),
            'is_pdf' => $this->resource->isPdf(),
        ];
    }
}
