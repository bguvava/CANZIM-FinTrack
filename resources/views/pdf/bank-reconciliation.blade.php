<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bank Reconciliation Report</title>
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
            border-bottom: 3px solid #7C3AED;
        }

        .header img {
            width: 120px;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #7C3AED;
            font-size: 24px;
            margin: 10px 0;
            font-weight: bold;
        }

        .header .period {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .account-info {
            background-color: #F3F4F6;
            padding: 15px;
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

        .summary-box {
            background-color: #EDE9FE;
            border-left: 4px solid #7C3AED;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 12px;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #7C3AED;
        }

        .section-title {
            background-color: #7C3AED;
            color: white;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        .reconciliation-group {
            margin-bottom: 25px;
        }

        .group-header {
            background-color: #F3F4F6;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-left: 4px solid #7C3AED;
            font-weight: bold;
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
            border-bottom: 2px solid #7C3AED;
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

        .amount.inflow {
            color: #059669;
        }

        .amount.outflow {
            color: #DC2626;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge.inflow {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .badge.outflow {
            background-color: #FEE2E2;
            color: #991B1B;
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
        <h1>BANK RECONCILIATION REPORT</h1>
        <div class="period">
            For the period <strong>{{ $date_from }}</strong> to <strong>{{ $date_to }}</strong>
        </div>
    </div>

    <!-- Account Information -->
    <div class="account-info">
        <h3 style="margin-top: 0; color: #7C3AED;">Bank Account Details</h3>
        <div class="info-row">
            <span class="info-label">Account Name:</span>
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
        <div class="info-row">
            <span class="info-label">Current System Balance:</span>
            <span style="font-weight: bold;">${{ number_format($system_balance, 2) }}</span>
        </div>
    </div>

    <!-- Reconciliation Summary -->
    <div class="summary-box">
        <h3 style="margin-top: 0; color: #7C3AED;">Reconciliation Summary</h3>
        <div class="summary-row">
            <span>Total Reconciled Transactions:</span>
            <span style="font-weight: bold;">{{ $total_reconciled }}</span>
        </div>
        <div class="summary-row">
            <span>Total Reconciled Inflows:</span>
            <span style="color: #059669; font-weight: bold;">+${{ number_format($total_inflows, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Total Reconciled Outflows:</span>
            <span style="color: #DC2626; font-weight: bold;">-${{ number_format($total_outflows, 2) }}</span>
        </div>
        <div class="summary-row total">
            <span>Net Reconciled Amount:</span>
            <span style="color: {{ $total_inflows - $total_outflows >= 0 ? '#059669' : '#DC2626' }};">
                {{ $total_inflows - $total_outflows >= 0 ? '+' : '' }}${{ number_format($total_inflows - $total_outflows, 2) }}
            </span>
        </div>
    </div>

    <!-- Reconciled Transactions by Date -->
    <div class="section-title">Reconciled Transactions</div>

    @foreach ($reconciliation_groups as $date => $transactions)
        <div class="reconciliation-group">
            <div class="group-header">
                Reconciled on {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
                ({{ $transactions->count() }} transaction{{ $transactions->count() > 1 ? 's' : '' }})
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Transaction Date</th>
                        <th style="width: 10%;">Type</th>
                        <th style="width: 15%;">Reference</th>
                        <th style="width: 20%;">Project</th>
                        <th style="width: 28%;">Description</th>
                        <th style="width: 15%;" class="amount">Amount (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $transaction->type }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td>{{ $transaction->reference_number ?? 'N/A' }}</td>
                            <td>{{ $transaction->project->project_name ?? 'N/A' }}</td>
                            <td>{{ $transaction->description ?? 'N/A' }}</td>
                            <td class="amount {{ $transaction->type }}">
                                {{ $transaction->type === 'inflow' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    @if ($reconciliation_groups->count() === 0)
        <div style="text-align: center; padding: 40px; color: #999;">
            <p style="font-size: 14px;">No reconciled transactions found for the selected period.</p>
        </div>
    @endif

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
