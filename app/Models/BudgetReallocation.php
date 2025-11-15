<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetReallocation extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetReallocationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'budget_id',
        'from_budget_item_id',
        'to_budget_item_id',
        'amount',
        'justification',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the budget that owns the reallocation.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the budget item funds are being moved from.
     */
    public function fromBudgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class, 'from_budget_item_id');
    }

    /**
     * Get the budget item funds are being moved to.
     */
    public function toBudgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class, 'to_budget_item_id');
    }

    /**
     * Get the user who requested the reallocation.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the reallocation.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending reallocations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved reallocations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
