<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorResource extends JsonResource
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
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'tax_id' => $this->tax_id,
            'website' => $this->website,
            'status' => $this->status,
            'notes' => $this->notes,

            // Computed attributes
            'total_funding' => $this->when(
                $this->relationLoaded('projects'),
                fn () => (float) $this->projects->sum('pivot.funding_amount')
            ),
            'restricted_funding' => $this->when(
                $this->relationLoaded('projects'),
                fn () => (float) $this->projects->where('pivot.is_restricted', true)->sum('pivot.funding_amount')
            ),
            'unrestricted_funding' => $this->when(
                $this->relationLoaded('projects'),
                fn () => (float) $this->projects->where('pivot.is_restricted', false)->sum('pivot.funding_amount')
            ),
            'in_kind_total' => $this->when(
                $this->relationLoaded('inKindContributions'),
                fn () => (float) $this->inKindContributions->sum('estimated_value')
            ),
            'active_projects_count' => $this->when(
                $this->relationLoaded('projects'),
                fn () => $this->projects->where('status', 'active')->count()
            ),
            'total_projects_count' => $this->when(
                $this->relationLoaded('projects'),
                fn () => $this->projects->count()
            ),

            // Relationships
            'projects' => $this->when(
                $this->relationLoaded('projects'),
                fn () => $this->projects->map(fn ($project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'code' => $project->code,
                    'status' => $project->status,
                    'funding_amount' => (float) $project->pivot->funding_amount,
                    'funding_period_start' => $project->pivot->funding_period_start,
                    'funding_period_end' => $project->pivot->funding_period_end,
                    'is_restricted' => (bool) $project->pivot->is_restricted,
                    'notes' => $project->pivot->notes,
                ])
            ),
            'in_kind_contributions' => $this->when(
                $this->relationLoaded('inKindContributions'),
                fn () => $this->inKindContributions
            ),
            'communications' => $this->when(
                $this->relationLoaded('communications'),
                fn () => $this->communications
            ),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
