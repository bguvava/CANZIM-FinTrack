<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseApproval extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expense_id',
        'approval_level',
        'action',
        'user_id',
        'comments',
        'action_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'action_date' => 'datetime',
    ];

    /**
     * Get the expense that owns the approval.
     */
    public function expense(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the user who took the action.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query for a specific approval level.
     */
    public function scopeLevel($query, string $level): void
    {
        $query->where('approval_level', $level);
    }

    /**
     * Scope a query for approved actions.
     */
    public function scopeApproved($query): void
    {
        $query->where('action', 'Approved');
    }

    /**
     * Scope a query for rejected actions.
     */
    public function scopeRejected($query): void
    {
        $query->where('action', 'Rejected');
    }
}
