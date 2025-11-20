<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_type' => $this->type, // Database column is 'type', API returns 'report_type'
            'title' => $this->title,
            'parameters' => $this->parameters,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'file_size' => $this->human_file_size,
            'status' => $this->status,
            'generated_by' => [
                'id' => $this->generator->id,
                'name' => $this->generator->name,
                'email' => $this->generator->email,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
