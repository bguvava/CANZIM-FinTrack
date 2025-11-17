<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expense_number',
        'project_id',
        'budget_item_id',
        'purchase_order_id',
        'expense_category_id',
        'expense_date',
        'amount',
        'currency',
        'description',
        'receipt_path',
        'receipt_original_name',
        'receipt_file_size',
        'status',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_comments',
        'approved_by',
        'approved_at',
        'approval_comments',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'paid_by',
        'paid_at',
        'payment_reference',
        'payment_method',
        'payment_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'receipt_file_size' => 'integer',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the project that owns the expense.
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the budget item that owns the expense.
     */
    public function budgetItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BudgetItem::class);
    }

    /**
     * Get the purchase order linked to this expense.
     */
    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the category for this expense.
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Get the user who submitted the expense.
     */
    public function submitter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who reviewed the expense.
     */
    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who approved the expense.
     */
    public function approver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected the expense.
     */
    public function rejector(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the user who paid the expense.
     */
    public function payer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Get the approval history for the expense.
     */
    public function approvals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExpenseApproval::class);
    }

    /**
     * Scope a query to only include expenses with a specific status.
     */
    public function scopeStatus($query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope a query to only include submitted expenses.
     */
    public function scopeSubmitted($query): void
    {
        $query->where('status', 'Submitted');
    }

    /**
     * Scope a query to only include expenses under review.
     */
    public function scopeUnderReview($query): void
    {
        $query->where('status', 'Under Review');
    }

    /**
     * Scope a query to only include approved expenses.
     */
    public function scopeApproved($query): void
    {
        $query->where('status', 'Approved');
    }

    /**
     * Scope a query to only include paid expenses.
     */
    public function scopePaid($query): void
    {
        $query->where('status', 'Paid');
    }

    /**
     * Scope a query to only include rejected expenses.
     */
    public function scopeRejected($query): void
    {
        $query->where('status', 'Rejected');
    }

    /**
     * Scope a query for a specific project.
     */
    public function scopeForProject($query, int $projectId): void
    {
        $query->where('project_id', $projectId);
    }

    /**
     * Scope a query for a specific date range.
     */
    public function scopeDateRange($query, string $startDate, string $endDate): void
    {
        $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Check if expense can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['Draft', 'Rejected']);
    }

    /**
     * Check if expense can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->status, ['Draft', 'Rejected']);
    }

    /**
     * Check if expense can be reviewed by Finance Officer.
     */
    public function canBeReviewed(): bool
    {
        return $this->status === 'Submitted';
    }

    /**
     * Check if expense can be approved by Programs Manager.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'Under Review';
    }

    /**
     * Check if expense can be marked as paid.
     */
    public function canBePaid(): bool
    {
        return $this->status === 'Approved';
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Draft' => 'gray',
            'Submitted' => 'blue',
            'Under Review' => 'yellow',
            'Approved' => 'green',
            'Rejected' => 'red',
            'Paid' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get the receipt URL.
     */
    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? asset('storage/'.$this->receipt_path) : null;
    }
}
