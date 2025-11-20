<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'documentable_type' => $this->documentable_type,
            'documentable_id' => $this->documentable_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'formatted_file_size' => $this->formatted_file_size,
            'file_extension' => $this->file_extension,
            'version_number' => $this->version_number,
            'uploaded_by' => $this->uploaded_by,
            'uploader' => [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
                'email' => $this->uploader->email,
            ],
            'versions_count' => $this->versions_count ?? $this->versions()->count(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
