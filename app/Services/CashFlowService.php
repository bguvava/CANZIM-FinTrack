<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BankAccount;
use App\Models\CashFlow;
use Illuminate\Support\Facades\DB;

class CashFlowService
{
    /**
     * Record a cash inflow transaction.
     */
    public function recordInflow(array $data): CashFlow
    {
        return DB::transaction(function () use ($data) {
            $bankAccount = BankAccount::findOrFail($data['bank_account_id']);
            $balanceBefore = $bankAccount->current_balance;
            $amount = $data['amount'];
            $balanceAfter = $balanceBefore + $amount;

            // Create cash flow record
            $cashFlow = CashFlow::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => 'inflow',
                'bank_account_id' => $data['bank_account_id'],
                'project_id' => $data['project_id'] ?? null,
                'donor_id' => $data['donor_id'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $data['description'] ?? null,
                'reference' => $data['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update bank account balance
            $bankAccount->update(['current_balance' => $balanceAfter]);

            return $cashFlow;
        });
    }

    /**
     * Record a cash outflow transaction.
     */
    public function recordOutflow(array $data): CashFlow
    {
        return DB::transaction(function () use ($data) {
            $bankAccount = BankAccount::findOrFail($data['bank_account_id']);
            $balanceBefore = $bankAccount->current_balance;
            $amount = $data['amount'];
            $balanceAfter = $balanceBefore - $amount;

            // Validate sufficient balance
            if ($balanceAfter < 0) {
                throw new \Exception('Insufficient balance in bank account.');
            }

            // Create cash flow record
            $cashFlow = CashFlow::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => 'outflow',
                'bank_account_id' => $data['bank_account_id'],
                'project_id' => $data['project_id'] ?? null,
                'expense_id' => $data['expense_id'] ?? null,
                'transaction_date' => $data['transaction_date'],
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $data['description'] ?? null,
                'reference' => $data['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update bank account balance
            $bankAccount->update(['current_balance' => $balanceAfter]);

            return $cashFlow;
        });
    }

    /**
     * Reconcile a cash flow transaction.
     */
    public function reconcile(int $cashFlowId): CashFlow
    {
        $cashFlow = CashFlow::findOrFail($cashFlowId);

        if ($cashFlow->is_reconciled) {
            throw new \Exception('Transaction is already reconciled.');
        }

        $cashFlow->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => auth()->id(),
        ]);

        return $cashFlow->fresh();
    }

    /**
     * Calculate cash flow projection for specified months.
     */
    public function calculateProjection(int $bankAccountId, int $months = 3): array
    {
        $bankAccount = BankAccount::findOrFail($bankAccountId);
        $currentBalance = $bankAccount->current_balance;

        // Get historical data for last 6 months
        $sixMonthsAgo = now()->subMonths(6);
        $cashFlows = CashFlow::where('bank_account_id', $bankAccountId)
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->get();

        // Calculate average monthly inflows and outflows
        $avgMonthlyInflow = $cashFlows->where('type', 'inflow')->avg('amount') ?? 0;
        $avgMonthlyOutflow = $cashFlows->where('type', 'outflow')->avg('amount') ?? 0;
        $avgNetCashFlow = $avgMonthlyInflow - $avgMonthlyOutflow;

        // Project future balances
        $projections = [];
        $projectedBalance = $currentBalance;

        for ($i = 1; $i <= $months; $i++) {
            $projectedBalance += $avgNetCashFlow;
            $projections[] = [
                'month' => now()->addMonths($i)->format('Y-m'),
                'projected_balance' => round($projectedBalance, 2),
                'projected_inflow' => round($avgMonthlyInflow, 2),
                'projected_outflow' => round($avgMonthlyOutflow, 2),
                'net_cash_flow' => round($avgNetCashFlow, 2),
            ];
        }

        return [
            'current_balance' => $currentBalance,
            'avg_monthly_inflow' => round($avgMonthlyInflow, 2),
            'avg_monthly_outflow' => round($avgMonthlyOutflow, 2),
            'avg_net_cash_flow' => round($avgNetCashFlow, 2),
            'projections' => $projections,
        ];
    }

    /**
     * Generate a unique transaction number.
     */
    private function generateTransactionNumber(): string
    {
        $year = date('Y');
        $lastTransaction = CashFlow::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastTransaction ? (int) substr($lastTransaction->transaction_number, -4) + 1 : 1;

        return 'TXN-'.$year.'-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
