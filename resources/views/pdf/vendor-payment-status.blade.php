<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Vendor Payment Status Report</title>
    <style>
        @page {
            margin: 15px;
            size: landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #0891B2;
        }

        .header img {
            width: 100px;
            margin-bottom: 8px;
        }

        .header h1 {
            color: #0891B2;
            font-size: 22px;
            margin: 8px 0;
            font-weight: bold;
        }

        .header .period {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            gap: 10px;
        }

        .summary-card {
            flex: 1;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .summary-card.cyan {
            background-color: #CFFAFE;
            border-left: 4px solid #0891B2;
        }

        .summary-card.green {
            background-color: #D1FAE5;
            border-left: 4px solid #059669;
        }

        .summary-card.red {
            background-color: #FEE2E2;
            border-left: 4px solid #DC2626;
        }

        .summary-card.purple {
            background-color: #E9D5FF;
            border-left: 4px solid #7C3AED;
        }

        .summary-card .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 4px;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #111;
        }

        .vendor-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .vendor-header {
            background-color: #0891B2;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 3px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .vendor-stats {
            background-color: #F0F9FF;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-around;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }

        .stat-value {
            font-size: 11px;
            font-weight: bold;
            color: #111;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table thead th {
            background-color: #F3F4F6;
            color: #111;
            font-weight: bold;
            padding: 6px 8px;
            text-align: left;
            border-bottom: 2px solid #0891B2;
            font-size: 9px;
        }

        table thead th.right {
            text-align: right;
        }

        table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 9px;
        }

        table tbody td.right {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: bold;
        }

        .status-badge.approved {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-badge.received {
            background-color: #DBEAFE;
            color: #1E3A8A;
        }

        .status-badge.completed {
            background-color: #E9D5FF;
            color: #581C87;
        }

        .overdue-indicator {
            background-color: #FEE2E2;
            color: #991B1B;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            margin-left: 5px;
        }

        .vendor-total-row {
            background-color: #E0F2FE;
            font-weight: bold;
            font-size: 10px;
        }

        .grand-total-section {
            background-color: #0891B2;
            color: white;
            padding: 12px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .grand-total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 12px;
        }

        .grand-total-row.main {
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 2px solid white;
        }

        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 2px solid #E5E7EB;
            font-size: 8px;
            color: #666;
            text-align: center;
        }

        .footer .row {
            margin-bottom: 4px;
        }

        .footer .confidential {
            color: #DC2626;
            font-weight: bold;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('images/logo/canzim_logo.png') }}" alt="CANZIM Logo">
        <h1>VENDOR PAYMENT STATUS REPORT</h1>
        <div class="period">
            For the period <strong>{{ $date_from }}</strong> to <strong>{{ $date_to }}</strong> |
            Status: <strong>{{ $status_filter }}</strong>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card cyan">
            <div class="label">Total Vendors</div>
            <div class="value">{{ $total_vendors }}</div>
        </div>
        <div class="summary-card purple">
            <div class="label">Total Purchase Orders</div>
            <div class="value">{{ $total_pos }}</div>
        </div>
        <div class="summary-card green">
            <div class="label">Total PO Amount</div>
            <div class="value">${{ number_format($grand_total_po, 2) }}</div>
        </div>
        <div class="summary-card red">
            <div class="label">Total Outstanding</div>
            <div class="value">${{ number_format($grand_total_outstanding, 2) }}</div>
        </div>
    </div>

    <!-- Vendor Sections -->
    @foreach ($vendor_stats as $vendor_stat)
        <div class="vendor-section">
            <div class="vendor-header">
                <span>{{ $vendor_stat['vendor']->vendor_name }}</span>
                <span style="font-size: 10px;">
                    {{ $vendor_stat['po_count'] }} PO{{ $vendor_stat['po_count'] > 1 ? 's' : '' }}
                    @if ($vendor_stat['overdue_count'] > 0)
                        | <span class="overdue-indicator">{{ $vendor_stat['overdue_count'] }} OVERDUE</span>
                    @endif
                </span>
            </div>

            <!-- Vendor Statistics -->
            <div class="vendor-stats">
                <div class="stat-item">
                    <div class="stat-label">Total PO Amount</div>
                    <div class="stat-value">${{ number_format($vendor_stat['total_po_amount'], 2) }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Paid</div>
                    <div class="stat-value" style="color: #059669;">${{ number_format($vendor_stat['total_paid'], 2) }}
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Outstanding</div>
                    <div class="stat-value" style="color: #DC2626;">
                        ${{ number_format($vendor_stat['total_outstanding'], 2) }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Payment %</div>
                    <div class="stat-value">
                        {{ $vendor_stat['total_po_amount'] > 0 ? number_format(($vendor_stat['total_paid'] / $vendor_stat['total_po_amount']) * 100, 1) : 0 }}%
                    </div>
                </div>
            </div>

            <!-- Purchase Orders Table -->
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">PO Number</th>
                        <th style="width: 10%;">Order Date</th>
                        <th style="width: 10%;">Expected Delivery</th>
                        <th style="width: 18%;">Project</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 12%;" class="right">PO Amount</th>
                        <th style="width: 12%;" class="right">Amount Paid</th>
                        <th style="width: 12%;" class="right">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vendor_stat['purchase_orders'] as $po)
                        @php
                            $paidAmount = $po->expenses ? $po->expenses->sum('amount') : 0;
                            $outstanding = $po->total_amount - $paidAmount;
                            $isOverdue =
                                $po->expected_delivery_date &&
                                \Carbon\Carbon::parse($po->expected_delivery_date)->isPast() &&
                                $po->status !== 'completed';
                        @endphp
                        <tr>
                            <td>
                                {{ $po->po_number }}
                                @if ($isOverdue)
                                    <span class="overdue-indicator">OVERDUE</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</td>
                            <td>{{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M Y') : 'N/A' }}
                            </td>
                            <td>{{ $po->project->project_name }}</td>
                            <td>
                                <span class="status-badge {{ strtolower($po->status) }}">
                                    {{ ucfirst($po->status) }}
                                </span>
                            </td>
                            <td class="right">${{ number_format($po->total_amount, 2) }}</td>
                            <td class="right" style="color: #059669;">${{ number_format($paidAmount, 2) }}</td>
                            <td class="right" style="color: {{ $outstanding > 0 ? '#DC2626' : '#666' }};">
                                ${{ number_format($outstanding, 2) }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- Vendor Total -->
                    <tr class="vendor-total-row">
                        <td colspan="5" style="text-align: right; padding-right: 10px;">Vendor Total:</td>
                        <td class="right">${{ number_format($vendor_stat['total_po_amount'], 2) }}</td>
                        <td class="right" style="color: #059669;">${{ number_format($vendor_stat['total_paid'], 2) }}
                        </td>
                        <td class="right" style="color: #DC2626;">
                            ${{ number_format($vendor_stat['total_outstanding'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    @if (count($vendor_stats) === 0)
        <div style="text-align: center; padding: 40px; color: #999;">
            <p style="font-size: 14px;">No purchase orders found for the selected criteria.</p>
        </div>
    @endif

    <!-- Grand Total Section -->
    <div class="grand-total-section">
        <div class="grand-total-row">
            <span>GRAND TOTAL - All Vendors</span>
            <span></span>
        </div>
        <div class="grand-total-row">
            <span>Total PO Amount:</span>
            <span>${{ number_format($grand_total_po, 2) }}</span>
        </div>
        <div class="grand-total-row">
            <span>Total Amount Paid:</span>
            <span>${{ number_format($grand_total_paid, 2) }}</span>
        </div>
        <div class="grand-total-row main">
            <span>TOTAL OUTSTANDING:</span>
            <span>${{ number_format($grand_total_outstanding, 2) }}</span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="row">
            <strong>Generated by:</strong> {{ $generated_by->name }} | <strong>Date:</strong> {{ $generated_at }}
        </div>
        <div class="confidential">
            CONFIDENTIAL - For Internal Use Only
        </div>
        <div class="row" style="margin-top: 8px;">
            &copy; {{ date('Y') }} CAN-Zimbabwe. All rights reserved.
        </div>
        <div class="row">
            Developed with ❤️ by bguvava
        </div>
    </div>
</body>

</html>
