<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donor extends Model
{
    /** @use HasFactory<\Database\Factories\DonorFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'website',
        'status',
        'notes',
        'funding_total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'funding_total' => 'decimal:2',
    ];

    /**
     * Get the projects associated with the donor.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_donors')
            ->withPivot('funding_amount', 'funding_period_start', 'funding_period_end', 'is_restricted', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the in-kind contributions associated with the donor.
     */
    public function inKindContributions()
    {
        return $this->hasMany(InKindContribution::class);
    }

    /**
     * Get all communications for the donor (polymorphic).
     */
    public function communications()
    {
        return $this->morphMany(Communication::class, 'communicable');
    }

    /**
     * Get the total cash funding provided to all projects.
     */
    public function getTotalFundingAttribute(): float
    {
        return $this->projects()->sum('project_donors.funding_amount');
    }

    /**
     * Get the total restricted funding.
     */
    public function getRestrictedFundingAttribute(): float
    {
        return $this->projects()
            ->wherePivot('is_restricted', true)
            ->sum('project_donors.funding_amount');
    }

    /**
     * Get the total unrestricted funding.
     */
    public function getUnrestrictedFundingAttribute(): float
    {
        return $this->projects()
            ->wherePivot('is_restricted', false)
            ->sum('project_donors.funding_amount');
    }

    /**
     * Get the total in-kind contribution value.
     */
    public function getInKindTotalAttribute(): float
    {
        return $this->inKindContributions()->sum('estimated_value');
    }

    /**
     * Get the count of active projects.
     */
    public function getActiveProjectsCountAttribute(): int
    {
        return $this->projects()->where('status', 'active')->count();
    }

    /**
     * Get the last contribution date (cash or in-kind).
     */
    public function getLastContributionDateAttribute(): ?string
    {
        $lastProject = $this->projects()->latest('project_donors.created_at')->first();
        $lastInKind = $this->inKindContributions()->latest('contribution_date')->first();

        if (! $lastProject && ! $lastInKind) {
            return null;
        }

        if (! $lastProject) {
            return $lastInKind->contribution_date;
        }

        if (! $lastInKind) {
            return $lastProject->pivot->created_at->format('Y-m-d');
        }

        return max(
            $lastProject->pivot->created_at->format('Y-m-d'),
            $lastInKind->contribution_date
        );
    }
}
