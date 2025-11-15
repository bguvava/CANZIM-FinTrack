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
            ->withPivot('funding_amount', 'funding_period_start', 'funding_period_end', 'is_restricted')
            ->withTimestamps();
    }

    /**
     * Get the total funding provided to all projects.
     */
    public function getTotalFundingProvidedAttribute(): float
    {
        return $this->projects()->sum('project_donors.funding_amount');
    }
}
