<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Donor Financial Report - {{ $donor->name }}</title>
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.header')

    <div class="content">
        {{-- Summary Cards --}}
        <table style="width: 100%; margin-bottom: 20px; border: none;">
            <tr>
                <td style="width: 25%; padding: 10px; background-color: #EFF6FF; border-left: 4px solid #1E40AF; text-align: center;">
                    <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">Total Funding</div>
                    <div style="font-size: 18px; font-weight: bold; color: #1E40AF; margin-top: 5px;">${{ number_format($total_funding, 2) }}</div>
                </td>
                <td style="width: 25%; padding: 10px; background-color: #F3E8FF; border-left: 4px solid #7C3AED; text-align: center;">
                    <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">In-Kind Value</div>
                    <div style="font-size: 18px; font-weight: bold; color: #7C3AED; margin-top: 5px;">${{ number_format($total_inkind_value, 2) }}</div>
                </td>
                <td style="width: 25%; padding: 10px; background-color: #D1FAE5; border-left: 4px solid #059669; text-align: center;">
                    <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">Active Projects</div>
                    <div style="font-size: 18px; font-weight: bold; color: #059669; margin-top: 5px;">{{ $active_projects_count }}</div>
                </td>
                <td style="width: 25%; padding: 10px; background-color: #FED7AA; border-left: 4px solid #EA580C; text-align: center;">
                    <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">Grand Total</div>
                    <div style="font-size: 18px; font-weight: bold; color: #EA580C; margin-top: 5px;">${{ number_format($grand_total_contribution, 2) }}</div>
                </td>
            </tr>
        </table>

        {{-- Donor Information --}}
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                    <div class="info-box">
                        <h3 style="color: #1E40AF; margin-top: 0; margin-bottom: 10px; font-size: 13px; border-bottom: 2px solid #1E40AF; padding-bottom: 5px;">Contact Information</h3>
                        <table class="summary-table">
                            <tr>
                                <td>Donor Name:</td>
                                <td>{{ $donor->name }}</td>
                            </tr>
                            <tr>
                                <td>Email:</td>
                                <td>{{ $donor->email }}</td>
                            </tr>
                            <tr>
                                <td>Phone:</td>
                                <td>{{ $donor->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Contact Person:</td>
                                <td>{{ $donor->contact_person ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Address:</td>
                                <td>{{ $donor->address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                    <div class="info-box">
                        <h3 style="color: #1E40AF; margin-top: 0; margin-bottom: 10px; font-size: 13px; border-bottom: 2px solid #1E40AF; padding-bottom: 5px;">Donor Details</h3>
                        <table class="summary-table">
                            <tr>
                                <td>Tax ID:</td>
                                <td>{{ $donor->tax_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Website:</td>
                                <td>{{ $donor->website ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($donor->status) }}">
                                        {{ ucfirst($donor->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Total Projects:</td>
                                <td>{{ $total_projects }}</td>
                            </tr>
                            <tr>
                                <td>Active Projects:</td>
                                <td>{{ $active_projects_count }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Funding Breakdown --}}
        <div class="info-box" style="background-color: #F0F9FF; border-left: 4px solid #0284C7; margin-bottom: 20px;">
            <h3 style="color: #075985; margin-top: 0; margin-bottom: 10px; font-size: 13px;">Funding Breakdown</h3>
            <table class="summary-table">
                <tr>
                    <td>Total Cash Funding:</td>
                    <td class="currency">${{ number_format($total_funding, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Restricted Funding:</td>
                    <td class="text-warning">${{ number_format($restricted_funding, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Unrestricted Funding:</td>
                    <td class="text-success">${{ number_format($unrestricted_funding, 2) }}</td>
                </tr>
                <tr style="border-top: 1px solid #E5E7EB; margin-top: 5px;">
                    <td>Total In-Kind Value:</td>
                    <td class="currency" style="color: #7C3AED;">${{ number_format($total_inkind_value, 2) }}</td>
                </tr>
                <tr style="border-top: 2px solid #0284C7; font-weight: bold;">
                    <td>GRAND TOTAL CONTRIBUTION:</td>
                    <td class="currency text-primary">${{ number_format($grand_total_contribution, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- Active Projects --}}
        @if($active_projects->count() > 0)
            <h2>Active Projects Funded</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 30%;">Project Name</th>
                        <th style="width: 15%; text-align: right;">Funding Amount</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 20%;">Funding Period</th>
                        <th style="width: 15%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($active_projects as $index => $project)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $project->name }}</td>
                            <td style="text-align: right; font-weight: bold;">${{ number_format($project->pivot->funding_amount, 2) }}</td>
                            <td>
                                @if($project->pivot->is_restricted)
                                    <span class="badge" style="background-color: #FEF3C7; color: #92400E;">Restricted</span>
                                @else
                                    <span class="badge badge-approved">Unrestricted</span>
                                @endif
                            </td>
                            <td>
                                {{ $project->pivot->funding_period_start ? \Carbon\Carbon::parse($project->pivot->funding_period_start)->format('M Y') : 'N/A' }}
                                -
                                {{ $project->pivot->funding_period_end ? \Carbon\Carbon::parse($project->pivot->funding_period_end)->format('M Y') : 'N/A' }}
                            </td>
                            <td><span class="badge">{{ ucfirst($project->status) }}</span></td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #BFDBFE; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding-right: 10px;">Active Projects Total:</td>
                        <td style="text-align: right;">${{ number_format($active_projects->sum('pivot.funding_amount'), 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tbody>
            </table>
        @else
            <h2>Active Projects Funded</h2>
            <div style="text-align: center; padding: 20px; background-color: #F9FAFB; border-radius: 5px; color: #6B7280; font-size: 10px; font-style: italic; margin-bottom: 20px;">
                No active projects currently funded
            </div>
        @endif

        {{-- Completed Projects --}}
        @if($completed_projects->count() > 0)
            <h2>Completed Projects</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 35%;">Project Name</th>
                        <th style="width: 20%; text-align: right;">Funding Amount</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 25%;">Funding Period</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completed_projects as $index => $project)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $project->name }}</td>
                            <td style="text-align: right; font-weight: bold;">${{ number_format($project->pivot->funding_amount, 2) }}</td>
                            <td>
                                @if($project->pivot->is_restricted)
                                    <span class="badge" style="background-color: #FEF3C7; color: #92400E;">Restricted</span>
                                @else
                                    <span class="badge badge-approved">Unrestricted</span>
                                @endif
                            </td>
                            <td>
                                {{ $project->pivot->funding_period_start ? \Carbon\Carbon::parse($project->pivot->funding_period_start)->format('M Y') : 'N/A' }}
                                -
                                {{ $project->pivot->funding_period_end ? \Carbon\Carbon::parse($project->pivot->funding_period_end)->format('M Y') : 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #F9FAFB; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding-right: 10px;">Completed Projects Total:</td>
                        <td style="text-align: right;">${{ number_format($completed_projects->sum('pivot.funding_amount'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        @endif

        {{-- In-Kind Contributions --}}
        @if($in_kind_contributions->count() > 0)
            <h2>In-Kind Contributions</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Category</th>
                        <th style="width: 40%;">Description</th>
                        <th style="width: 20%; text-align: right;">Estimated Value</th>
                        <th style="width: 20%;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($in_kind_contributions as $index => $contribution)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge" style="background-color: #E9D5FF; color: #581C87;">{{ ucfirst(str_replace('_', ' ', $contribution->category)) }}</span></td>
                            <td>{{ $contribution->description }}</td>
                            <td style="text-align: right; font-weight: bold;">${{ number_format($contribution->estimated_value, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($contribution->contribution_date)->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                    @foreach($in_kind_by_category as $category => $stats)
                        <tr style="background-color: #F9FAFB; font-weight: bold;">
                            <td colspan="2" style="text-align: right; padding-right: 10px;">
                                {{ ucfirst(str_replace('_', ' ', $category)) }} ({{ $stats['count'] }} items):
                            </td>
                            <td></td>
                            <td style="text-align: right;">${{ number_format($stats['total_value'], 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #BFDBFE; font-weight: bold;">
                        <td colspan="3" style="text-align: right; padding-right: 10px;">Total In-Kind Value:</td>
                        <td style="text-align: right;">${{ number_format($total_inkind_value, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        @else
            <h2>In-Kind Contributions</h2>
            <div style="text-align: center; padding: 20px; background-color: #F9FAFB; border-radius: 5px; color: #6B7280; font-size: 10px; font-style: italic; margin-bottom: 20px;">
                No in-kind contributions recorded
            </div>
        @endif

        {{-- Recent Communications --}}
        @if($recent_communications->count() > 0)
            <h2>Recent Communications (Last 10)</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 30%;">Subject</th>
                        <th style="width: 40%;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_communications as $communication)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($communication->communication_date)->format('d M Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $communication->type)) }}</td>
                            <td style="font-weight: bold;">{{ $communication->subject }}</td>
                            <td>{{ $communication->notes ? Str::limit($communication->notes, 100) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($total_communications > 10)
                <p style="text-align: center; font-size: 9px; color: #6B7280; margin-top: 10px;">
                    Showing 10 of {{ $total_communications }} total communications
                </p>
            @endif
        @else
            <h2>Recent Communications</h2>
            <div style="text-align: center; padding: 20px; background-color: #F9FAFB; border-radius: 5px; color: #6B7280; font-size: 10px; font-style: italic; margin-bottom: 20px;">
                No communications logged
            </div>
        @endif

        @if(!empty($filters))
            <div class="section-divider"></div>
            <div class="info-box" style="background-color: #FEF3C7; border-left: 4px solid #F59E0B;">
                <h4>Applied Filters:</h4>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    @if(!empty($filters['date_from']))
                        <li>Date From: {{ \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') }}</li>
                    @endif
                    @if(!empty($filters['date_to']))
                        <li>Date To: {{ \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') }}</li>
                    @endif
                    @if(!empty($filters['project_ids']))
                        <li>Filtered by {{ count($filters['project_ids']) }} specific project(s)</li>
                    @endif
                    @if(!empty($filters['include_in_kind']))
                        <li>In-Kind Contributions: Included</li>
                    @else
                        <li>In-Kind Contributions: Excluded</li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

    @include('pdf.partials.footer')
</body>
</html>
