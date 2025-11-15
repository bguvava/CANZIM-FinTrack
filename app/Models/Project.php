<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'total_budget',
        'status',
        'office_location',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_budget' => 'decimal:2',
    ];

    /**
     * Get the user who created the project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the budgets for the project.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get the expenses for the project.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the donors associated with the project.
     */
    public function donors(): BelongsToMany
    {
        return $this->belongsToMany(Donor::class, 'project_donors')
            ->withPivot('funding_amount', 'funding_period_start', 'funding_period_end', 'is_restricted')
            ->withTimestamps();
    }

    /**
     * Get the team members assigned to the project.
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the documents associated with the project.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scope a query to only include active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed projects.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get the total spent amount for the project.
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Get the remaining budget for the project.
     */
    public function getRemainingBudgetAttribute(): float
    {
        return $this->total_budget - $this->total_spent;
    }

    /**
     * Get the budget utilization percentage.
     */
    public function getBudgetUtilizationAttribute(): float
    {
        if ($this->total_budget == 0) {
            return 0;
        }

        return ($this->total_spent / $this->total_budget) * 100;
    }
}
