<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cash Flow Statement</title>
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
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1E40AF;
        }

        .header img {
            width: 120px;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #1E40AF;
            font-size: 24px;
            margin: 10px 0;
            font-weight: bold;
        }

        .header .period {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .info-box {
            background-color: #F3F4F6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }

        .summary-card {
            flex: 1;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
        }

        .summary-card.blue {
            background-color: #DBEAFE;
            border-left: 4px solid #1E40AF;
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
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }

        .section-title {
            background-color: #1E40AF;
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
            margin-bottom: 20px;
        }

        table thead th {
            background-color: #F3F4F6;
            color: #111;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #1E40AF;
            font-size: 10px;
        }

        table tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 10px;
        }

        table tbody tr:hover {
            background-color: #F9FAFB;
        }

        .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .amount.positive {
            color: #059669;
        }

        .amount.negative {
            color: #DC2626;
        }

        .subtotal-row {
            background-color: #F9FAFB;
            font-weight: bold;
        }

        .total-row {
            background-color: #E5E7EB;
            font-weight: bold;
            font-size: 11px;
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
        <h1>CASH FLOW STATEMENT</h1>
        <div class="period">
            For the period <strong>{{ $date_from }}</strong> to <strong>{{ $date_to }}</strong>
        </div>
    </div>

    <!-- Bank Account Info -->
    @if ($bank_account)
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Bank Account:</span>
                <span>{{ $bank_account->account_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Bank:</span>
                <span>{{ $bank_account->bank_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Account Number:</span>
                <span>{{ $bank_account->account_number }}</span>
            </div>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card blue">
            <div class="label">Opening Balance</div>
            <div class="value">${{ number_format($opening_balance, 2) }}</div>
        </div>
        <div class="summary-card green">
            <div class="label">Total Inflows</div>
            <div class="value">${{ number_format($total_inflows, 2) }}</div>
        </div>
        <div class="summary-card red">
            <div class="label">Total Outflows</div>
            <div class="value">${{ number_format($total_outflows, 2) }}</div>
        </div>
        <div class="summary-card purple">
            <div class="label">Closing Balance</div>
            <div class="value">${{ number_format($closing_balance, 2) }}</div>
        </div>
    </div>

    <!-- Inflows Section -->
    @if ($inflows->count() > 0)
        <div class="section-title">Cash Inflows</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 15%;">Reference</th>
                    <th style="width: 18%;">Project</th>
                    <th style="width: 18%;">Donor</th>
                    <th style="width: 22%;">Description</th>
                    <th style="width: 15%;" class="amount">Amount (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inflows as $inflow)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($inflow->transaction_date)->format('d M Y') }}</td>
                        <td>{{ $inflow->reference_number ?? 'N/A' }}</td>
                        <td>{{ $inflow->project->project_name ?? 'N/A' }}</td>
                        <td>{{ $inflow->donor->name ?? 'N/A' }}</td>
                        <td>{{ $inflow->description ?? 'Cash Inflow' }}</td>
                        <td class="amount positive">+${{ number_format($inflow->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="5" style="text-align: right; padding-right: 10px;">Subtotal Inflows:</td>
                    <td class="amount positive">+${{ number_format($total_inflows, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Outflows Section -->
    @if ($outflows->count() > 0)
        <div class="section-title">Cash Outflows</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 15%;">Reference</th>
                    <th style="width: 18%;">Project</th>
                    <th style="width: 18%;">Expense</th>
                    <th style="width: 22%;">Description</th>
                    <th style="width: 15%;" class="amount">Amount (USD)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($outflows as $outflow)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($outflow->transaction_date)->format('d M Y') }}</td>
                        <td>{{ $outflow->reference_number ?? 'N/A' }}</td>
                        <td>{{ $outflow->project->project_name ?? 'N/A' }}</td>
                        <td>{{ $outflow->expense->expense_number ?? 'N/A' }}</td>
                        <td>{{ $outflow->description ?? 'Cash Outflow' }}</td>
                        <td class="amount negative">-${{ number_format($outflow->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="5" style="text-align: right; padding-right: 10px;">Subtotal Outflows:</td>
                    <td class="amount negative">-${{ number_format($total_outflows, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Net Cash Flow Summary -->
    <table>
        <tbody>
            <tr class="total-row">
                <td style="text-align: right; padding-right: 10px; width: 85%;">Opening Balance:</td>
                <td class="amount" style="width: 15%;">${{ number_format($opening_balance, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td style="text-align: right; padding-right: 10px;">Net Cash Flow:</td>
                <td class="amount {{ $net_cash_flow >= 0 ? 'positive' : 'negative' }}">
                    {{ $net_cash_flow >= 0 ? '+' : '' }}${{ number_format($net_cash_flow, 2) }}
                </td>
            </tr>
            <tr class="total-row" style="background-color: #1E40AF; color: white;">
                <td style="text-align: right; padding-right: 10px;">Closing Balance:</td>
                <td class="amount" style="color: white;">${{ number_format($closing_balance, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="row">
            <strong>Generated by:</strong> {{ $generated_by->name }} | <strong>Date:</strong> {{ $generated_at }}
        </div>
        <div class="confidential">
            CONFIDENTIAL - For Internal Use Only
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
