<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The accessors to append to the model's array/JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'total_allocated',
        'total_spent',
        'total_remaining',
        'utilization_percentage',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'fiscal_year',
        'total_amount',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the project that owns the budget.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the budget.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the budget.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the budget items for the budget.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    /**
     * Get the budget reallocations for the budget.
     */
    public function reallocations(): HasMany
    {
        return $this->hasMany(BudgetReallocation::class);
    }

    /**
     * Scope a query to only include approved budgets.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include draft budgets.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the total allocated amount from budget items.
     */
    public function getTotalAllocatedAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum('allocated_amount');
        }

        return (float) $this->items()->sum('allocated_amount');
    }

    /**
     * Get the total spent amount from budget items.
     */
    public function getTotalSpentAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum('spent_amount');
        }

        return (float) $this->items()->sum('spent_amount');
    }

    /**
     * Get the total remaining amount from budget items.
     */
    public function getTotalRemainingAttribute(): float
    {
        return $this->total_allocated - $this->total_spent;
    }

    /**
     * Get the budget utilization percentage.
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->total_allocated == 0) {
            return 0;
        }

        return ($this->total_spent / $this->total_allocated) * 100;
    }

    /**
     * Check if budget has reached a specific threshold.
     */
    public function hasReachedThreshold(int $threshold): bool
    {
        return $this->utilization_percentage >= $threshold;
    }

    /**
     * Get the alert level based on utilization.
     */
    public function getAlertLevelAttribute(): string
    {
        $utilization = $this->utilization_percentage;

        if ($utilization >= 100) {
            return 'critical'; // Red
        } elseif ($utilization >= 90) {
            return 'warning'; // Orange
        } elseif ($utilization >= 50) {
            return 'caution'; // Yellow
        }

        return 'normal'; // Green
    }
}
