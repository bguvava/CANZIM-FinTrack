<?php

declare(strict_types=1);

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Base class for standardized PDF report generation.
 *
 * This service provides common functionality for all report PDF services including:
 * - Standardized header/footer data
 * - Logo loading as base64
 * - Common filter application
 * - Currency formatting
 * - PDF generation and streaming
 *
 * Can be used directly for simple reports or extended for more complex scenarios.
 */
class ReportPDFService
{
    /**
     * Load CANZIM logo as base64 encoded string for embedding in PDFs.
     */
    protected function loadLogoAsBase64(): string
    {
        $logoPath = public_path('images/canzim-logo.png');

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));

            return 'data:image/png;base64,'.$logoData;
        }

        return '';
    }

    /**
     * Get standardized header data for PDF reports.
     *
     * @param  string  $reportTitle  The title of the report to display in the header
     * @return array Header data array containing logo, organization name, and report title
     */
    protected function getStandardizedHeaderData(string $reportTitle): array
    {
        return [
            'logoBase64' => $this->loadLogoAsBase64(),
            'organizationName' => 'Climate Action Network Zimbabwe',
            'reportTitle' => $reportTitle,
        ];
    }

    /**
     * Get standardized footer data for PDF reports.
     *
     * @return array Footer data including user info, timestamp, and copyright
     */
    protected function getStandardizedFooterData(): array
    {
        $user = auth()->user();

        return [
            'generatedBy' => $user?->full_name ?? $user?->name ?? 'System',
            'userRole' => $user?->role?->name ?? 'User',
            'generatedAt' => now()->format('F d, Y \a\t h:i A'),
            'year' => now()->year,
            'organizationName' => 'Climate Action Network Zimbabwe',
        ];
    }

    /**
     * Apply common filters to a query builder.
     *
     * Supports date range filtering, status filtering, and category filtering.
     *
     * @param  Builder  $query  The Eloquent query builder
     * @param  array  $filters  Array of filters to apply
     * @return Builder The modified query builder
     */
    protected function applyFiltersToQuery(Builder $query, array $filters): Builder
    {
        // Date range filters
        if (! empty($filters['date_from'])) {
            $query->where(function ($q) use ($filters) {
                $dateColumn = $filters['date_column'] ?? 'created_at';
                $q->where($dateColumn, '>=', $filters['date_from']);
            });
        }

        if (! empty($filters['date_to'])) {
            $query->where(function ($q) use ($filters) {
                $dateColumn = $filters['date_column'] ?? 'created_at';
                $q->where($dateColumn, '<=', $filters['date_to']);
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Category filter
        if (! empty($filters['category_id'])) {
            if (is_array($filters['category_id'])) {
                $query->whereIn('expense_category_id', $filters['category_id']);
            } else {
                $query->where('expense_category_id', $filters['category_id']);
            }
        }

        // Project filter
        if (! empty($filters['project_id'])) {
            if (is_array($filters['project_id'])) {
                $query->whereIn('project_id', $filters['project_id']);
            } else {
                $query->where('project_id', $filters['project_id']);
            }
        }

        return $query;
    }

    /**
     * Format currency amount as USD with proper formatting.
     *
     * @param  float  $amount  The amount to format
     * @return string Formatted currency string (e.g., "$1,234.56")
     */
    protected function formatCurrency(float $amount): string
    {
        return '$'.number_format($amount, 2);
    }

    /**
     * Generate PDF using DomPDF facade with standardized settings.
     *
     * @param  string  $view  The Blade view name
     * @param  array  $data  Data to pass to the view
     * @return DomPDF Generated PDF instance
     */
    protected function generatePDF(string $view, array $data): DomPDF
    {
        return Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 25)
            ->setOption('margin-bottom', 30)
            ->setOption('margin-left', 20)
            ->setOption('margin-right', 20);
    }

    /**
     * Stream PDF to browser as a download response.
     *
     * @param  DomPDF  $pdf  The PDF instance to stream
     * @param  string  $filename  The filename for the download
     * @return Response PDF stream response
     */
    protected function streamPDF(DomPDF $pdf, string $filename): Response
    {
        return $pdf->stream($filename);
    }

    /**
     * Generate unique filename for report with timestamp.
     *
     * @param  string  $reportType  The type of report (e.g., 'budget-vs-actual')
     * @return string Generated filename
     */
    protected function generateFilename(string $reportType): string
    {
        $timestamp = Carbon::now()->format('Ymd-His');
        $randomString = substr(md5(uniqid((string) rand(), true)), 0, 8);

        return "{$reportType}-{$timestamp}-{$randomString}.pdf";
    }

    /**
     * Save PDF to storage and return the file path.
     *
     * @param  DomPDF  $pdf  The PDF to save
     * @param  string  $filename  The filename to use
     * @return string The storage path of the saved PDF
     */
    protected function savePDF(DomPDF $pdf, string $filename): string
    {
        $pdfContent = $pdf->output();
        $filePath = "reports/{$filename}";
        Storage::disk('public')->put($filePath, $pdfContent);

        return $filePath;
    }

    /**
     * Get PDF download URL.
     */
    public function getDownloadUrl(string $filePath): string
    {
        return Storage::disk('public')->url($filePath);
    }

    /**
     * Delete PDF file from storage.
     */
    public function deletePDF(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }

        return false;
    }

    /**
     * Get PDF file size in bytes.
     */
    public function getFileSize(string $filePath): ?int
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->size($filePath);
        }

        return null;
    }

    /**
     * Check if PDF exists in storage.
     */
    public function pdfExists(string $filePath): bool
    {
        return Storage::disk('public')->exists($filePath);
    }

    /**
     * Generate PDF for Budget vs Actual Report.
     */
    public function generateBudgetVsActualPDF(array $reportData, int $userId): string
    {
        // Structure for blade template: $data contains report data, additional vars at root
        $pdfData = [
            'data' => $reportData,  // Contains 'data', 'summary', 'parameters', 'type'
            'title' => 'Budget vs Actual Report',
            'organization' => 'Climate Action Network Zimbabwe',
            'report_date' => Carbon::now()->format('F d, Y'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];

        $pdfData = array_merge($pdfData, $this->getStandardizedHeaderData('Budget vs Actual Report'));
        $pdfData = array_merge($pdfData, $this->getStandardizedFooterData());

        $pdf = $this->generatePDF('reports.budget-vs-actual', $pdfData);
        $filename = $this->generateFilename('budget-vs-actual');

        return $this->savePDF($pdf, $filename);
    }

    /**
     * Generate PDF for Cash Flow Report.
     */
    public function generateCashFlowPDF(array $reportData, int $userId): string
    {
        // Structure for blade template: $data contains report data, additional vars at root
        $pdfData = [
            'data' => $reportData,  // Contains 'data', 'summary', 'parameters', 'type'
            'title' => 'Cash Flow Report',
            'organization' => 'Climate Action Network Zimbabwe',
            'report_date' => Carbon::now()->format('F d, Y'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];

        $pdfData = array_merge($pdfData, $this->getStandardizedHeaderData('Cash Flow Report'));
        $pdfData = array_merge($pdfData, $this->getStandardizedFooterData());

        $pdf = $this->generatePDF('reports.cash-flow', $pdfData);
        $filename = $this->generateFilename('cash-flow');

        return $this->savePDF($pdf, $filename);
    }

    /**
     * Generate PDF for Expense Summary Report.
     */
    public function generateExpenseSummaryPDF(array $reportData, int $userId): string
    {
        // Structure for blade template: $data contains report data, additional vars at root
        $pdfData = [
            'data' => $reportData,  // Contains 'data', 'summary', 'parameters', 'type'
            'title' => 'Expense Summary Report',
            'organization' => 'Climate Action Network Zimbabwe',
            'report_date' => Carbon::now()->format('F d, Y'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];

        $pdfData = array_merge($pdfData, $this->getStandardizedHeaderData('Expense Summary Report'));
        $pdfData = array_merge($pdfData, $this->getStandardizedFooterData());

        $pdf = $this->generatePDF('reports.expense-summary', $pdfData);
        $filename = $this->generateFilename('expense-summary');

        return $this->savePDF($pdf, $filename);
    }

    /**
     * Generate PDF for Project Status Report.
     */
    public function generateProjectStatusPDF(array $reportData, int $userId): string
    {
        // Structure for blade template: $data contains report data, additional vars at root
        $pdfData = [
            'data' => $reportData,  // Contains 'data', 'summary', 'parameters', 'type'
            'title' => 'Project Status Report',
            'organization' => 'Climate Action Network Zimbabwe',
            'report_date' => Carbon::now()->format('F d, Y'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];

        $pdfData = array_merge($pdfData, $this->getStandardizedHeaderData('Project Status Report'));
        $pdfData = array_merge($pdfData, $this->getStandardizedFooterData());

        $pdf = $this->generatePDF('reports.project-status', $pdfData);
        $filename = $this->generateFilename('project-status');

        return $this->savePDF($pdf, $filename);
    }

    /**
     * Generate PDF for Donor Contributions Report.
     */
    public function generateDonorContributionsPDF(array $reportData, int $userId): string
    {
        // Structure for blade template: $data contains report data, additional vars at root
        $pdfData = [
            'data' => $reportData,  // Contains 'data', 'summary', 'parameters', 'type'
            'title' => 'Donor Contributions Report',
            'organization' => 'Climate Action Network Zimbabwe',
            'report_date' => Carbon::now()->format('F d, Y'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];

        $pdfData = array_merge($pdfData, $this->getStandardizedHeaderData('Donor Contributions Report'));
        $pdfData = array_merge($pdfData, $this->getStandardizedFooterData());

        $pdf = $this->generatePDF('reports.donor-contributions', $pdfData);
        $filename = $this->generateFilename('donor-contributions');

        return $this->savePDF($pdf, $filename);
    }
}
