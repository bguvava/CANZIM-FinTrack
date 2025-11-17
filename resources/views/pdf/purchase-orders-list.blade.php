<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Orders List - {{ $status_filter }}</title>
    <style>
        @page {
            margin: 15px;
            size: landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #EA580C;
        }

        .header h1 {
            color: #EA580C;
            font-size: 20px;
            margin: 5px 0;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-around;
            margin: 15px 0;
            gap: 10px;
        }

        .summary-card {
            flex: 1;
            background-color: #F3F4F6;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #EA580C;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table thead {
            background-color: #EA580C;
            color: white;
        }

        table th {
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }

        table td {
            padding: 6px 5px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 9px;
        }

        table tbody tr:hover {
            background-color: #F9FAFB;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }

        .status-draft {
            background-color: #F3F4F6;
            color: #6B7280;
        }

        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-approved {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .status-received {
            background-color: #E9D5FF;
            color: #6B21A8;
        }

        .status-completed {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-rejected,
        .status-cancelled {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #E5E7EB;
            font-size: 7px;
            color: #6B7280;
            text-align: center;
        }

        .footer-row {
            margin: 3px 0;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9CA3AF;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>PURCHASE ORDERS LIST</h1>
        <div class="subtitle">
            Period: {{ $date_from }} to {{ $date_to }}
            @if ($status_filter !== 'All')
                | Status: {{ $status_filter }}
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-label">Total Purchase Orders</div>
            <div class="summary-value">{{ $po_count }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Amount</div>
            <div class="summary-value">${{ number_format($total_amount, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Status Filter</div>
            <div class="summary-value">{{ $status_filter }}</div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    @if ($purchase_orders->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">PO Number</th>
                    <th style="width: 15%;">Vendor</th>
                    <th style="width: 15%;">Project</th>
                    <th style="width: 10%;">Order Date</th>
                    <th style="width: 10%;">Delivery Date</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 12%;" class="text-right">Total Amount</th>
                    <th style="width: 18%;">Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase_orders as $po)
                    <tr>
                        <td><strong>{{ $po->po_number }}</strong></td>
                        <td>{{ $po->vendor->name }}</td>
                        <td>{{ $po->project->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</td>
                        <td>
                            @if ($po->expected_delivery_date)
                                {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M Y') }}
                            @else
                                <span style="color: #9CA3AF;">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ strtolower($po->status) }}">
                                {{ ucfirst($po->status) }}
                            </span>
                        </td>
                        <td class="text-right"><strong>${{ number_format($po->total_amount, 2) }}</strong></td>
                        <td>{{ $po->items->count() }} item(s)</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #F3F4F6; font-weight: bold;">
                    <td colspan="6" class="text-right">GRAND TOTAL:</td>
                    <td class="text-right">${{ number_format($total_amount, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="empty-state">
            <p style="font-size: 14px; font-weight: bold;">No Purchase Orders Found</p>
            <p>No purchase orders match the selected criteria.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="footer-row">
            <strong>Generated by:</strong> {{ $generated_by->name ?? 'System' }} |
            <strong>Date:</strong> {{ $generated_at }}
        </div>
        <div class="footer-row">
            <strong>CONFIDENTIAL:</strong> This document contains proprietary information of CANZIM.
        </div>
        <div class="footer-row">
            &copy; {{ date('Y') }} CANZIM. All rights reserved. | Developed by Blessed Guvava
        </div>
    </div>
</body>

</html>
