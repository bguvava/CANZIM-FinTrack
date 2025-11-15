<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'branch',
        'currency',
        'current_balance',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all cash flow transactions for this account.
     */
    public function cashFlows(): HasMany
    {
        return $this->hasMany(CashFlow::class);
    }

    /**
     * Get inflow transactions for this account.
     */
    public function inflows(): HasMany
    {
        return $this->hasMany(CashFlow::class)->where('type', 'inflow');
    }

    /**
     * Get outflow transactions for this account.
     */
    public function outflows(): HasMany
    {
        return $this->hasMany(CashFlow::class)->where('type', 'outflow');
    }

    /**
     * Scope to get only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive accounts.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
