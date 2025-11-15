<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\CashFlow;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CashFlowPDFService
{
    /**
     * Generate Cash Flow Statement PDF.
     */
    public function generateCashFlowStatement(array $filters): string
    {
        $data = $this->prepareCashFlowStatementData($filters);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.cash-flow-statement', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateCashFlowFilename($filters);

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put("reports/cash-flow/{$filename}", $pdfContent);

        return $filename;
    }

    /**
     * Generate Bank Reconciliation Report PDF.
     */
    public function generateReconciliationReport(BankAccount $bankAccount, array $filters): string
    {
        $data = $this->prepareReconciliationData($bankAccount, $filters);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.bank-reconciliation', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateReconciliationFilename($bankAccount);

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put("reports/cash-flow/{$filename}", $pdfContent);

        return $filename;
    }

    /**
     * Prepare cash flow statement data.
     */
    protected function prepareCashFlowStatementData(array $filters): array
    {
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $bankAccountId = $filters['bank_account_id'] ?? null;

        // Build query
        $query = CashFlow::with(['bankAccount', 'project', 'donor', 'expense'])
            ->orderBy('transaction_date', 'asc');

        if ($dateFrom) {
            $query->where('transaction_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('transaction_date', '<=', $dateTo);
        }

        if ($bankAccountId) {
            $query->where('bank_account_id', $bankAccountId);
        }

        $transactions = $query->get();

        // Calculate opening balance
        $openingBalance = 0;
        if ($dateFrom && $bankAccountId) {
            $openingBalance = $this->calculateOpeningBalance($bankAccountId, $dateFrom);
        }

        // Separate inflows and outflows
        $inflows = $transactions->where('type', 'inflow');
        $outflows = $transactions->where('type', 'outflow');

        // Calculate totals
        $totalInflows = $inflows->sum('amount');
        $totalOutflows = $outflows->sum('amount');
        $netCashFlow = $totalInflows - $totalOutflows;
        $closingBalance = $openingBalance + $netCashFlow;

        // Get bank account details
        $bankAccount = null;
        if ($bankAccountId) {
            $bankAccount = BankAccount::find($bankAccountId);
        }

        return [
            'date_from' => $dateFrom ? Carbon::parse($dateFrom)->format('d M Y') : 'Beginning',
            'date_to' => $dateTo ? Carbon::parse($dateTo)->format('d M Y') : 'Today',
            'bank_account' => $bankAccount,
            'opening_balance' => $openingBalance,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'net_cash_flow' => $netCashFlow,
            'closing_balance' => $closingBalance,
            'generated_at' => now()->format('d M Y H:i:s'),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Prepare bank reconciliation report data.
     */
    protected function prepareReconciliationData(BankAccount $bankAccount, array $filters): array
    {
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        // Get reconciled transactions
        $query = CashFlow::where('bank_account_id', $bankAccount->id)
            ->where('is_reconciled', true)
            ->with(['project', 'donor', 'expense'])
            ->orderBy('reconciliation_date', 'desc');

        if ($dateFrom) {
            $query->where('reconciliation_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('reconciliation_date', '<=', $dateTo);
        }

        $reconciledTransactions = $query->get();

        // Group by reconciliation date
        $reconciliationGroups = $reconciledTransactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->reconciliation_date)->format('Y-m-d');
        });

        // Calculate totals
        $totalReconciled = $reconciledTransactions->count();
        $totalInflows = $reconciledTransactions->where('type', 'inflow')->sum('amount');
        $totalOutflows = $reconciledTransactions->where('type', 'outflow')->sum('amount');

        return [
            'bank_account' => $bankAccount,
            'date_from' => $dateFrom ? Carbon::parse($dateFrom)->format('d M Y') : 'Beginning',
            'date_to' => $dateTo ? Carbon::parse($dateTo)->format('d M Y') : 'Today',
            'reconciliation_groups' => $reconciliationGroups,
            'total_reconciled' => $totalReconciled,
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'system_balance' => $bankAccount->current_balance,
            'generated_at' => now()->format('d M Y H:i:s'),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Calculate opening balance for a bank account at a specific date.
     */
    protected function calculateOpeningBalance(int $bankAccountId, string $date): float
    {
        $transactions = CashFlow::where('bank_account_id', $bankAccountId)
            ->where('transaction_date', '<', $date)
            ->get();

        $inflows = $transactions->where('type', 'inflow')->sum('amount');
        $outflows = $transactions->where('type', 'outflow')->sum('amount');

        return $inflows - $outflows;
    }

    /**
     * Generate filename for cash flow statement.
     */
    protected function generateCashFlowFilename(array $filters): string
    {
        $dateFrom = $filters['date_from'] ?? 'beginning';
        $dateTo = $filters['date_to'] ?? 'today';

        if ($dateFrom !== 'beginning') {
            $dateFrom = Carbon::parse($dateFrom)->format('Ymd');
        }

        if ($dateTo !== 'today') {
            $dateTo = Carbon::parse($dateTo)->format('Ymd');
        }

        return sprintf(
            'cash-flow-statement-%s-to-%s-%s.pdf',
            $dateFrom,
            $dateTo,
            now()->format('YmdHis')
        );
    }

    /**
     * Generate filename for reconciliation report.
     */
    protected function generateReconciliationFilename(BankAccount $bankAccount): string
    {
        return sprintf(
            'bank-reconciliation-%s-%s.pdf',
            str_replace(' ', '-', strtolower($bankAccount->account_name)),
            now()->format('Ymd-His')
        );
    }

    /**
     * Get report download path.
     */
    public function getReportDownloadPath(string $filename): string
    {
        return Storage::disk('local')->path("reports/cash-flow/{$filename}");
    }
}
