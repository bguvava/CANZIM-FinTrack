<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Donor Financial Report - {{ $donor->donor_name }}</title>
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
            border-bottom: 3px solid #1E40AF;
        }

        .header img {
            width: 120px;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #1E40AF;
            font-size: 26px;
            margin: 10px 0;
            font-weight: bold;
        }

        .donor-name {
            background-color: #DBEAFE;
            color: #1E3A8A;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
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

        .summary-card.purple {
            background-color: #E9D5FF;
            border-left: 4px solid #7C3AED;
        }

        .summary-card.green {
            background-color: #D1FAE5;
            border-left: 4px solid #16A34A;
        }

        .summary-card.orange {
            background-color: #FED7AA;
            border-left: 4px solid #EA580C;
        }

        .summary-card .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #111;
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
            color: #1E40AF;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 13px;
            border-bottom: 2px solid #1E40AF;
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

        .status-badge.active {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-badge.inactive {
            background-color: #FEE2E2;
            color: #991B1B;
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
            margin-bottom: 15px;
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
            background-color: #BFDBFE;
            font-weight: bold;
            font-size: 12px;
        }

        .grand-total-row {
            background-color: #1E40AF;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        .funding-breakdown {
            background-color: #F0F9FF;
            border-left: 4px solid #0284C7;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .funding-breakdown h3 {
            color: #075985;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 11px;
        }

        .breakdown-row.total {
            font-weight: bold;
            font-size: 13px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #0284C7;
        }

        .restriction-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .restriction-badge.restricted {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .restriction-badge.unrestricted {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .category-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            background-color: #E9D5FF;
            color: #581C87;
        }

        .timeline-item {
            border-left: 3px solid #BFDBFE;
            padding-left: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background-color: #1E40AF;
        }

        .timeline-date {
            font-size: 9px;
            color: #666;
            font-weight: bold;
        }

        .timeline-type {
            font-size: 10px;
            color: #1E40AF;
            font-weight: bold;
            margin-top: 2px;
        }

        .timeline-subject {
            font-size: 10px;
            color: #111;
            margin-top: 3px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            background-color: #F9FAFB;
            border-radius: 5px;
            color: #6B7280;
            font-size: 10px;
            font-style: italic;
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
        <h1>DONOR FINANCIAL REPORT</h1>
        <div class="donor-name">{{ $donor->donor_name }}</div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card blue">
            <div class="label">Total Funding</div>
            <div class="value">${{ number_format($total_funding, 2) }}</div>
        </div>
        <div class="summary-card purple">
            <div class="label">In-Kind Value</div>
            <div class="value">${{ number_format($total_inkind_value, 2) }}</div>
        </div>
        <div class="summary-card green">
            <div class="label">Active Projects</div>
            <div class="value">{{ $active_projects_count }}</div>
        </div>
        <div class="summary-card orange">
            <div class="label">Total Contribution</div>
            <div class="value">${{ number_format($grand_total_contribution, 2) }}</div>
        </div>
    </div>

    <!-- Donor Information -->
    <div class="two-column">
        <!-- Contact Details -->
        <div class="column">
            <div class="info-box">
                <h3>Contact Information</h3>
                <div class="info-row">
                    <span class="info-label">Donor Name:</span>
                    <span class="info-value">{{ $donor->donor_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $donor->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $donor->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value">{{ $donor->contact_person ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $donor->address ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="column">
            <div class="info-box">
                <h3>Donor Details</h3>
                <div class="info-row">
                    <span class="info-label">Tax ID:</span>
                    <span class="info-value">{{ $donor->tax_id ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Website:</span>
                    <span class="info-value">{{ $donor->website ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge {{ strtolower($donor->status) }}">
                        {{ ucfirst($donor->status) }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Projects:</span>
                    <span class="info-value">{{ $total_projects }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Active Projects:</span>
                    <span class="info-value">{{ $active_projects_count }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Funding Breakdown -->
    <div class="funding-breakdown">
        <h3>Funding Breakdown</h3>
        <div class="breakdown-row">
            <span>Total Cash Funding:</span>
            <span style="font-weight: bold;">${{ number_format($total_funding, 2) }}</span>
        </div>
        <div class="breakdown-row">
            <span style="padding-left: 15px;">Restricted Funding:</span>
            <span style="color: #D97706;">${{ number_format($restricted_funding, 2) }}</span>
        </div>
        <div class="breakdown-row">
            <span style="padding-left: 15px;">Unrestricted Funding:</span>
            <span style="color: #16A34A;">${{ number_format($unrestricted_funding, 2) }}</span>
        </div>
        <div class="breakdown-row" style="margin-top: 10px;">
            <span>Total In-Kind Value:</span>
            <span style="font-weight: bold; color: #7C3AED;">${{ number_format($total_inkind_value, 2) }}</span>
        </div>
        <div class="breakdown-row total">
            <span>GRAND TOTAL CONTRIBUTION:</span>
            <span style="color: #1E40AF;">${{ number_format($grand_total_contribution, 2) }}</span>
        </div>
    </div>

    <!-- Active Projects -->
    @if ($active_projects->count() > 0)
        <div class="section-title">Active Projects Funded</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Project Name</th>
                    <th style="width: 15%;" class="right">Funding Amount</th>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 20%;">Funding Period</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($active_projects as $index => $project)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $project->project_name }}</td>
                        <td class="right">${{ number_format($project->pivot->funding_amount, 2) }}</td>
                        <td>
                            @if ($project->pivot->is_restricted)
                                <span class="restriction-badge restricted">Restricted</span>
                            @else
                                <span class="restriction-badge unrestricted">Unrestricted</span>
                            @endif
                        </td>
                        <td style="font-size: 9px;">
                            {{ $project->pivot->funding_start_date ? \Carbon\Carbon::parse($project->pivot->funding_start_date)->format('M Y') : 'N/A' }}
                            -
                            {{ $project->pivot->funding_end_date ? \Carbon\Carbon::parse($project->pivot->funding_end_date)->format('M Y') : 'N/A' }}
                        </td>
                        <td style="font-size: 9px;">{{ ucfirst($project->status) }}</td>
                    </tr>
                @endforeach

                <!-- Total -->
                <tr class="total-row">
                    <td colspan="2" style="text-align: right; padding-right: 10px;">Active Projects Total:</td>
                    <td class="right">${{ number_format($active_projects->sum('pivot.funding_amount'), 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="section-title">Active Projects Funded</div>
        <div class="no-data">No active projects currently funded</div>
    @endif

    <!-- Completed Projects -->
    @if ($completed_projects->count() > 0)
        <div class="section-title">Completed Projects</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Project Name</th>
                    <th style="width: 20%;" class="right">Funding Amount</th>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 25%;">Funding Period</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($completed_projects as $index => $project)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $project->project_name }}</td>
                        <td class="right">${{ number_format($project->pivot->funding_amount, 2) }}</td>
                        <td>
                            @if ($project->pivot->is_restricted)
                                <span class="restriction-badge restricted">Restricted</span>
                            @else
                                <span class="restriction-badge unrestricted">Unrestricted</span>
                            @endif
                        </td>
                        <td style="font-size: 9px;">
                            {{ $project->pivot->funding_start_date ? \Carbon\Carbon::parse($project->pivot->funding_start_date)->format('M Y') : 'N/A' }}
                            -
                            {{ $project->pivot->funding_end_date ? \Carbon\Carbon::parse($project->pivot->funding_end_date)->format('M Y') : 'N/A' }}
                        </td>
                    </tr>
                @endforeach

                <!-- Total -->
                <tr class="subtotal-row">
                    <td colspan="2" style="text-align: right; padding-right: 10px;">Completed Projects Total:</td>
                    <td class="right">${{ number_format($completed_projects->sum('pivot.funding_amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- In-Kind Contributions -->
    @if ($in_kind_contributions->count() > 0)
        <div class="section-title">In-Kind Contributions</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 20%;" class="right">Estimated Value</th>
                    <th style="width: 20%;">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($in_kind_contributions as $index => $contribution)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span
                                class="category-badge">{{ ucfirst(str_replace('_', ' ', $contribution->category)) }}</span>
                        </td>
                        <td>{{ $contribution->description }}</td>
                        <td class="right">${{ number_format($contribution->estimated_value, 2) }}</td>
                        <td style="font-size: 9px;">
                            {{ \Carbon\Carbon::parse($contribution->contribution_date)->format('d M Y') }}</td>
                    </tr>
                @endforeach

                <!-- Category Breakdown -->
                @foreach ($in_kind_by_category as $category => $stats)
                    <tr class="subtotal-row">
                        <td colspan="2" style="text-align: right; padding-right: 10px;">
                            {{ ucfirst(str_replace('_', ' ', $category)) }} ({{ $stats['count'] }} items):
                        </td>
                        <td></td>
                        <td class="right">${{ number_format($stats['total_value'], 2) }}</td>
                        <td></td>
                    </tr>
                @endforeach

                <!-- Grand Total -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; padding-right: 10px;">Total In-Kind Value:</td>
                    <td class="right">${{ number_format($total_inkind_value, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="section-title">In-Kind Contributions</div>
        <div class="no-data">No in-kind contributions recorded</div>
    @endif

    <!-- Recent Communications -->
    @if ($recent_communications->count() > 0)
        <div class="section-title">Recent Communications (Last 10)</div>
        <div style="padding: 10px;">
            @foreach ($recent_communications as $communication)
                <div class="timeline-item">
                    <div class="timeline-date">
                        {{ \Carbon\Carbon::parse($communication->communication_date)->format('d M Y H:i') }}
                    </div>
                    <div class="timeline-type">
                        {{ ucfirst(str_replace('_', ' ', $communication->type)) }}
                        @if ($communication->attachment_path)
                            <span style="color: #7C3AED; font-size: 8px;">(📎 Attachment)</span>
                        @endif
                    </div>
                    <div class="timeline-subject">{{ $communication->subject }}</div>
                    @if ($communication->notes)
                        <div style="font-size: 9px; color: #666; margin-top: 2px;">
                            {{ Str::limit($communication->notes, 120) }}</div>
                    @endif
                </div>
            @endforeach
        </div>
        @if ($total_communications > 10)
            <div style="text-align: center; font-size: 9px; color: #666; margin-top: 10px;">
                Showing 10 of {{ $total_communications }} total communications
            </div>
        @endif
    @else
        <div class="section-title">Recent Communications</div>
        <div class="no-data">No communications logged</div>
    @endif

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
