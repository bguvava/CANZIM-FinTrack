<?php

namespace App\Services;

use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Service for generating expense-related PDF reports with standardized CANZIM layout.
 */
class ExpensePDFService
{
    /**
     * Generate PDF for a single expense with full details.
     */
    public function generateExpenseDetailsPDF(Expense $expense): string
    {
        // Eager load relationships
        $expense->load([
            'project',
            'budgetItem',
            'category',
            'submitter.role',
            'reviewer.role',
            'approver.role',
            'rejector.role',
            'payer.role',
            'approvals.user.role',
            'cashFlow.bankAccount',
        ]);

        // Get logo as base64
        $logoPath = public_path('images/canzim-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,'.$logoData;
        }

        $data = [
            'expense' => $expense,
            'logoBase64' => $logoBase64,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.expenses.expense-details', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateExpenseFilename($expense);

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('public')->put("expenses/{$filename}", $pdfContent);

        return "expenses/{$filename}";
    }

    /**
     * Generate PDF for a list of expenses with summary statistics.
     */
    public function generateExpenseListPDF(Collection $expenses, array $filters = []): string
    {
        // Calculate statistics
        $totalAmount = $expenses->sum('amount');

        $statistics = [
            'approved_amount' => $expenses->where('status', 'Approved')->sum('amount'),
            'paid_amount' => $expenses->where('status', 'Paid')->sum('amount'),
            'pending_amount' => $expenses->whereIn('status', ['Draft', 'Submitted', 'Under Review'])->sum('amount'),
        ];

        // Calculate status breakdown
        $statusBreakdown = [];
        $statusGroups = $expenses->groupBy('status');

        foreach ($statusGroups as $status => $group) {
            $amount = $group->sum('amount');
            $statusBreakdown[$status] = [
                'count' => $group->count(),
                'amount' => $amount,
                'percentage' => $totalAmount > 0 ? ($amount / $totalAmount) * 100 : 0,
            ];
        }

        // Get logo as base64
        $logoPath = public_path('images/canzim-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,'.$logoData;
        }

        // Prepare filter display names
        if (! empty($filters['project_id']) && $expenses->first()?->project) {
            $filters['project_name'] = $expenses->first()->project->name;
        }
        if (! empty($filters['category_id']) && $expenses->first()?->category) {
            $filters['category_name'] = $expenses->first()->category->name;
        }

        $data = [
            'expenses' => $expenses,
            'filters' => $filters,
            'totalAmount' => $totalAmount,
            'statistics' => $statistics,
            'statusBreakdown' => $statusBreakdown,
            'logoBase64' => $logoBase64,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.expenses.expense-list', $data)
            ->setPaper('a4', 'landscape') // Landscape for better table fit
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateListFilename($filters);

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('public')->put("expenses/{$filename}", $pdfContent);

        return "expenses/{$filename}";
    }

    /**
     * Generate filename for individual expense PDF.
     */
    protected function generateExpenseFilename(Expense $expense): string
    {
        $expenseNumber = str_replace('EXP-', '', $expense->expense_number);
        $timestamp = Carbon::now()->format('Ymd-His');

        return "expense-{$expenseNumber}-{$timestamp}.pdf";
    }

    /**
     * Generate filename for expense list PDF.
     */
    protected function generateListFilename(array $filters): string
    {
        $timestamp = Carbon::now()->format('Ymd-His');
        $status = ! empty($filters['status']) ? '-'.strtolower($filters['status']) : '';

        return "expense-list{$status}-{$timestamp}.pdf";
    }

    /**
     * Get PDF download URL.
     */
    public function getDownloadUrl(string $filePath): string
    {
        return Storage::disk('public')->url($filePath);
    }

    /**
     * Delete PDF file.
     */
    public function deletePDF(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }

        return false;
    }

    /**
     * Check if PDF exists.
     */
    public function pdfExists(string $filePath): bool
    {
        return Storage::disk('public')->exists($filePath);
    }

    /**
     * Stream PDF directly to browser.
     */
    public function streamExpenseDetailsPDF(Expense $expense): \Illuminate\Http\Response
    {
        $expense->load([
            'project', 'budgetItem', 'category', 'submitter.role',
            'reviewer.role', 'approver.role', 'rejector.role', 'payer.role',
            'approvals.user.role', 'cashFlow.bankAccount',
        ]);

        $logoPath = public_path('images/canzim-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,'.$logoData;
        }

        $data = [
            'expense' => $expense,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.expenses.expense-details', $data)
            ->setPaper('a4', 'portrait');

        $filename = "expense-{$expense->expense_number}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Stream expense list PDF directly to browser.
     */
    public function streamExpenseListPDF(Collection $expenses, array $filters = []): \Illuminate\Http\Response
    {
        $totalAmount = $expenses->sum('amount');

        $statistics = [
            'approved_amount' => $expenses->where('status', 'Approved')->sum('amount'),
            'paid_amount' => $expenses->where('status', 'Paid')->sum('amount'),
            'pending_amount' => $expenses->whereIn('status', ['Draft', 'Submitted', 'Under Review'])->sum('amount'),
        ];

        $statusBreakdown = [];
        $statusGroups = $expenses->groupBy('status');

        foreach ($statusGroups as $status => $group) {
            $amount = $group->sum('amount');
            $statusBreakdown[$status] = [
                'count' => $group->count(),
                'amount' => $amount,
                'percentage' => $totalAmount > 0 ? ($amount / $totalAmount) * 100 : 0,
            ];
        }

        $logoPath = public_path('images/canzim-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/png;base64,'.$logoData;
        }

        if (! empty($filters['project_id']) && $expenses->first()?->project) {
            $filters['project_name'] = $expenses->first()->project->name;
        }
        if (! empty($filters['category_id']) && $expenses->first()?->category) {
            $filters['category_name'] = $expenses->first()->category->name;
        }

        $data = [
            'expenses' => $expenses,
            'filters' => $filters,
            'totalAmount' => $totalAmount,
            'statistics' => $statistics,
            'statusBreakdown' => $statusBreakdown,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.expenses.expense-list', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->stream('expense-list.pdf');
    }
}
