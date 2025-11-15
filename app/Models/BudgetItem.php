<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'budget_id',
        'category',
        'description',
        'cost_code',
        'allocated_amount',
        'spent_amount',
        'remaining_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    /**
     * Get the budget that owns the budget item.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the utilization percentage for this budget item.
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->allocated_amount == 0) {
            return 0;
        }

        return ($this->spent_amount / $this->allocated_amount) * 100;
    }

    /**
     * Update the remaining amount based on spent amount.
     */
    public function updateRemainingAmount(): void
    {
        $this->remaining_amount = $this->allocated_amount - $this->spent_amount;
        $this->save();
    }

    /**
     * Check if budget item is over budget.
     */
    public function isOverBudget(): bool
    {
        return $this->spent_amount > $this->allocated_amount;
    }

    /**
     * Get the overspent amount.
     */
    public function getOverspentAmountAttribute(): float
    {
        return max(0, $this->spent_amount - $this->allocated_amount);
    }
}
