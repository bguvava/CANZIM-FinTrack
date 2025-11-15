<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashFlow extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_number',
        'type',
        'bank_account_id',
        'project_id',
        'donor_id',
        'expense_id',
        'transaction_date',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference',
        'is_reconciled',
        'reconciled_at',
        'reconciled_by',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'is_reconciled' => 'boolean',
            'reconciled_at' => 'date',
        ];
    }

    /**
     * Get the bank account for this transaction.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the project for this transaction.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the donor for this transaction.
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get the expense for this transaction.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the user who created this transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who reconciled this transaction.
     */
    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    /**
     * Scope to get inflow transactions.
     */
    public function scopeInflow($query)
    {
        return $query->where('type', 'inflow');
    }

    /**
     * Scope to get outflow transactions.
     */
    public function scopeOutflow($query)
    {
        return $query->where('type', 'outflow');
    }

    /**
     * Scope to get reconciled transactions.
     */
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    /**
     * Scope to get unreconciled transactions.
     */
    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by bank account.
     */
    public function scopeForBankAccount($query, $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    /**
     * Scope to filter by project.
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
