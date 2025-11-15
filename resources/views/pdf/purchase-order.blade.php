<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchase_order->po_number }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #EA580C;
        }

        .header img {
            width: 120px;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #EA580C;
            font-size: 26px;
            margin: 10px 0;
            font-weight: bold;
        }

        .po-number {
            background-color: #FFEDD5;
            color: #9A3412;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
        }

        .two-column {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }

        .column {
            flex: 1;
        }

        .info-box {
            background-color: #F3F4F6;
            padding: 12px;
            border-radius: 5px;
            height: 100%;
        }

        .info-box h3 {
            color: #EA580C;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 13px;
            border-bottom: 2px solid #EA580C;
            padding-bottom: 5px;
        }

        .info-row {
            padding: 4px 0;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 40%;
        }

        .info-value {
            color: #111;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 5px;
        }

        .status-badge.pending {
            background-color: #FEF3C7;
            color: #92400E;
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

        .section-title {
            background-color: #EA580C;
            color: white;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-radius: 3px;
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
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #EA580C;
            font-size: 10px;
        }

        table thead th.right {
            text-align: right;
        }

        table tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 10px;
        }

        table tbody td.right {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .subtotal-row {
            background-color: #F9FAFB;
            font-weight: bold;
        }

        .total-row {
            background-color: #FED7AA;
            font-weight: bold;
            font-size: 12px;
        }

        .grand-total-row {
            background-color: #EA580C;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        .payment-summary {
            background-color: #F0FDF4;
            border-left: 4px solid #16A34A;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .payment-summary h3 {
            color: #166534;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
        }

        .payment-row.total {
            font-weight: bold;
            font-size: 13px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #16A34A;
        }

        .terms-section {
            background-color: #FFFBEB;
            border-left: 4px solid #F59E0B;
            padding: 12px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .terms-section h3 {
            color: #92400E;
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .terms-section ul {
            margin: 5px 0;
            padding-left: 20px;
        }

        .terms-section li {
            font-size: 9px;
            margin-bottom: 3px;
            color: #78350F;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            border-top: 2px solid #333;
            padding-top: 8px;
        }

        .signature-label {
            font-weight: bold;
            font-size: 10px;
            color: #555;
        }

        .signature-name {
            font-size: 11px;
            margin-top: 5px;
        }

        .signature-date {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #E5E7EB;
            font-size: 9px;
            color: #666;
            text-align: center;
        }

        .footer .row {
            margin-bottom: 5px;
        }

        .footer .confidential {
            color: #DC2626;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('images/logo/canzim_logo.png') }}" alt="CANZIM Logo">
        <h1>PURCHASE ORDER</h1>
        <div class="po-number">{{ $purchase_order->po_number }}</div>
    </div>

    <!-- Vendor and Order Information -->
    <div class="two-column">
        <!-- Vendor Details -->
        <div class="column">
            <div class="info-box">
                <h3>Vendor Information</h3>
                <div class="info-row">
                    <span class="info-label">Vendor Name:</span>
                    <span class="info-value">{{ $vendor->vendor_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value">{{ $vendor->contact_person ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $vendor->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $vendor->phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $vendor->address ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="column">
            <div class="info-box">
                <h3>Order Details</h3>
                <div class="info-row">
                    <span class="info-label">Order Date:</span>
                    <span
                        class="info-value">{{ \Carbon\Carbon::parse($purchase_order->order_date)->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Expected Delivery:</span>
                    <span
                        class="info-value">{{ $purchase_order->expected_delivery_date ? \Carbon\Carbon::parse($purchase_order->expected_delivery_date)->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Project:</span>
                    <span class="info-value">{{ $project->project_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge {{ strtolower($purchase_order->status) }}">
                        {{ ucfirst($purchase_order->status) }}
                    </span>
                </div>
                @if ($purchase_order->approved_at)
                    <div class="info-row">
                        <span class="info-label">Approved By:</span>
                        <span class="info-value">{{ $approver->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Approval Date:</span>
                        <span
                            class="info-value">{{ \Carbon\Carbon::parse($purchase_order->approved_at)->format('d M Y') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Line Items -->
    <div class="section-title">Order Items</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Description</th>
                <th style="width: 20%;">Specifications</th>
                <th style="width: 10%;" class="right">Qty</th>
                <th style="width: 15%;" class="right">Unit Price (USD)</th>
                <th style="width: 15%;" class="right">Total (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->specifications ?? 'N/A' }}</td>
                    <td class="right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach

            <!-- Subtotal -->
            <tr class="subtotal-row">
                <td colspan="5" style="text-align: right; padding-right: 10px;">Subtotal:</td>
                <td class="right">${{ number_format($subtotal, 2) }}</td>
            </tr>

            <!-- Grand Total -->
            <tr class="grand-total-row">
                <td colspan="5" style="text-align: right; padding-right: 10px;">GRAND TOTAL:</td>
                <td class="right" style="color: white;">${{ number_format($total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Payment Summary (if expenses linked) -->
    @if ($total_expenses > 0)
        <div class="payment-summary">
            <h3>Payment Summary</h3>
            <div class="payment-row">
                <span>Purchase Order Amount:</span>
                <span style="font-weight: bold;">${{ number_format($total_amount, 2) }}</span>
            </div>
            <div class="payment-row">
                <span>Total Paid (Linked Expenses):</span>
                <span style="color: #DC2626; font-weight: bold;">${{ number_format($total_expenses, 2) }}</span>
            </div>
            <div class="payment-row total">
                <span>Remaining Balance:</span>
                <span style="color: {{ $remaining_balance >= 0 ? '#16A34A' : '#DC2626' }};">
                    ${{ number_format(abs($remaining_balance), 2) }}
                    @if ($remaining_balance < 0)
                        <span style="font-size: 10px;">(OVERPAID)</span>
                    @endif
                </span>
            </div>
        </div>
    @endif

    <!-- Notes (if any) -->
    @if ($purchase_order->notes)
        <div class="section-title">Additional Notes</div>
        <div style="background-color: #F9FAFB; padding: 12px; border-radius: 5px; font-size: 10px;">
            {{ $purchase_order->notes }}
        </div>
    @endif

    <!-- Terms and Conditions -->
    <div class="terms-section">
        <h3>Terms and Conditions</h3>
        <ul>
            <li>Payment terms: Net 30 days from delivery date unless otherwise agreed</li>
            <li>All items must be delivered in good condition and match specifications</li>
            <li>Vendor must provide invoice and delivery note upon delivery</li>
            <li>CAN-Zimbabwe reserves the right to reject items that do not meet quality standards</li>
            <li>Any changes to this purchase order must be approved in writing</li>
            <li>This purchase order is subject to CAN-Zimbabwe's standard procurement policies</li>
        </ul>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Prepared By</div>
            <div class="signature-name">{{ $creator->name }}</div>
            <div class="signature-date">Date: {{ \Carbon\Carbon::parse($purchase_order->created_at)->format('d M Y') }}
            </div>
        </div>

        @if ($approver)
            <div class="signature-box">
                <div class="signature-label">Approved By</div>
                <div class="signature-name">{{ $approver->name }}</div>
                <div class="signature-date">Date:
                    {{ \Carbon\Carbon::parse($purchase_order->approved_at)->format('d M Y') }}</div>
            </div>
        @else
            <div class="signature-box">
                <div class="signature-label">Approved By</div>
                <div class="signature-name">_____________________</div>
                <div class="signature-date">Date: _________________</div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="row">
            <strong>Generated by:</strong> {{ $generated_by->name }} | <strong>Date:</strong> {{ $generated_at }}
        </div>
        <div class="confidential">
            CONFIDENTIAL - For Official Use Only
        </div>
        <div class="row" style="margin-top: 10px;">
            &copy; {{ date('Y') }} CAN-Zimbabwe. All rights reserved.
        </div>
        <div class="row">
            Developed with ❤️ by bguvava
        </div>
    </div>
</body>

</html>
