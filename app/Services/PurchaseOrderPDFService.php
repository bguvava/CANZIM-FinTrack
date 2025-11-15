<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderPDFService
{
    /**
     * Generate Purchase Order PDF.
     */
    public function generatePurchaseOrderPDF(PurchaseOrder $purchaseOrder): string
    {
        $data = $this->preparePurchaseOrderData($purchaseOrder);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.purchase-order', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generatePOFilename($purchaseOrder);

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put("reports/purchase-orders/{$filename}", $pdfContent);

        return $filename;
    }

    /**
     * Generate Vendor Payment Status Report PDF.
     */
    public function generateVendorPaymentStatusReport(array $filters = []): string
    {
        $data = $this->prepareVendorPaymentStatusData($filters);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.vendor-payment-status', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        // Generate filename
        $filename = $this->generateVendorPaymentFilename();

        // Store PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put("reports/purchase-orders/{$filename}", $pdfContent);

        return $filename;
    }

    /**
     * Prepare purchase order data for PDF.
     */
    protected function preparePurchaseOrderData(PurchaseOrder $purchaseOrder): array
    {
        $purchaseOrder->load([
            'vendor',
            'project',
            'items',
            'creator',
            'approver',
            'expenses',
        ]);

        // Calculate totals
        $subtotal = $purchaseOrder->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $totalExpenses = $purchaseOrder->expenses ? $purchaseOrder->expenses->sum('amount') : 0;
        $remainingBalance = $purchaseOrder->total_amount - $totalExpenses;

        return [
            'purchase_order' => $purchaseOrder,
            'vendor' => $purchaseOrder->vendor,
            'project' => $purchaseOrder->project,
            'items' => $purchaseOrder->items,
            'creator' => $purchaseOrder->creator,
            'approver' => $purchaseOrder->approver,
            'subtotal' => $subtotal,
            'total_amount' => $purchaseOrder->total_amount,
            'total_expenses' => $totalExpenses,
            'remaining_balance' => $remainingBalance,
            'generated_at' => now()->format('d M Y H:i:s'),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Prepare vendor payment status data for PDF.
     */
    protected function prepareVendorPaymentStatusData(array $filters): array
    {
        $vendorId = $filters['vendor_id'] ?? null;
        $status = $filters['status'] ?? 'approved';
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        // Build query for purchase orders
        $query = PurchaseOrder::with(['vendor', 'project', 'expenses'])
            ->where('status', $status);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        if ($dateFrom) {
            $query->where('order_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('order_date', '<=', $dateTo);
        }

        $purchaseOrders = $query->orderBy('order_date', 'desc')->get();

        // Group by vendor
        $vendorGroups = $purchaseOrders->groupBy('vendor_id');

        // Calculate statistics
        $vendorStats = [];
        foreach ($vendorGroups as $vendorId => $pos) {
            $vendor = $pos->first()->vendor;

            $totalPOAmount = $pos->sum('total_amount');
            $totalPaid = 0;
            $overdueCount = 0;

            foreach ($pos as $po) {
                $paidAmount = $po->expenses ? $po->expenses->sum('amount') : 0;
                $totalPaid += $paidAmount;

                // Check if overdue (expected delivery date passed and not completed)
                if (
                    $po->expected_delivery_date &&
                    Carbon::parse($po->expected_delivery_date)->isPast() &&
                    $po->status !== 'completed'
                ) {
                    $overdueCount++;
                }
            }

            $vendorStats[] = [
                'vendor' => $vendor,
                'purchase_orders' => $pos,
                'total_po_amount' => $totalPOAmount,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalPOAmount - $totalPaid,
                'po_count' => $pos->count(),
                'overdue_count' => $overdueCount,
            ];
        }

        // Sort by total outstanding (highest first)
        usort($vendorStats, function ($a, $b) {
            return $b['total_outstanding'] <=> $a['total_outstanding'];
        });

        // Calculate overall totals
        $grandTotalPO = array_sum(array_column($vendorStats, 'total_po_amount'));
        $grandTotalPaid = array_sum(array_column($vendorStats, 'total_paid'));
        $grandTotalOutstanding = $grandTotalPO - $grandTotalPaid;

        return [
            'vendor_stats' => $vendorStats,
            'grand_total_po' => $grandTotalPO,
            'grand_total_paid' => $grandTotalPaid,
            'grand_total_outstanding' => $grandTotalOutstanding,
            'total_vendors' => count($vendorStats),
            'total_pos' => $purchaseOrders->count(),
            'date_from' => $dateFrom ? Carbon::parse($dateFrom)->format('d M Y') : 'Beginning',
            'date_to' => $dateTo ? Carbon::parse($dateTo)->format('d M Y') : 'Today',
            'status_filter' => ucfirst($status),
            'generated_at' => now()->format('d M Y H:i:s'),
            'generated_by' => auth()->user(),
        ];
    }

    /**
     * Generate filename for purchase order PDF.
     */
    protected function generatePOFilename(PurchaseOrder $purchaseOrder): string
    {
        return sprintf(
            'purchase-order-%s-%s.pdf',
            $purchaseOrder->po_number,
            now()->format('Ymd-His')
        );
    }

    /**
     * Generate filename for vendor payment status report.
     */
    protected function generateVendorPaymentFilename(): string
    {
        return sprintf(
            'vendor-payment-status-%s.pdf',
            now()->format('Ymd-His')
        );
    }

    /**
     * Get report download path.
     */
    public function getReportDownloadPath(string $filename): string
    {
        return Storage::disk('local')->path("reports/purchase-orders/{$filename}");
    }
}
