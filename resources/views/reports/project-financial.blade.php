@extends('reports.layouts.pdf')

@section('title', 'Project Financial Report - ' . $project->name)

@section('report-title', 'Project Financial Report')

@section('content')
    <h1>{{ $project->name }}</h1>

    <!-- Project Information -->
    <h2>Project Information</h2>
    <table class="info-table">
        <tr>
            <td class="info-label">Project Code:</td>
            <td>{{ $project->code }}</td>
        </tr>
        <tr>
            <td class="info-label">Status:</td>
            <td>
                @php
                    $statusClass = match ($project->status) {
                        'active' => 'badge-success',
                        'planning' => 'badge-info',
                        'on_hold' => 'badge-warning',
                        'completed' => 'badge-success',
                        'cancelled' => 'badge-danger',
                        default => 'badge-info',
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
            </td>
        </tr>
        <tr>
            <td class="info-label">Start Date:</td>
            <td>{{ \Carbon\Carbon::parse($project->start_date)->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">End Date:</td>
            <td>{{ \Carbon\Carbon::parse($project->end_date)->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Location:</td>
            <td>{{ $project->location ?? 'Not specified' }}</td>
        </tr>
        <tr>
            <td class="info-label">Created By:</td>
            <td>{{ $project->creator->name ?? 'Unknown' }}</td>
        </tr>
    </table>

    @if ($project->description)
        <h3>Description</h3>
        <p>{{ $project->description }}</p>
    @endif

    <!-- Donors & Funding -->
    @if ($project->donors && $project->donors->count() > 0)
        <h2>Donors & Funding</h2>
        <table>
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th>Contact Person</th>
                    <th class="text-right">Funding Amount</th>
                    <th class="text-center">Type</th>
                </tr>
            </thead>
            <tbody>
                @php $totalFunding = 0; @endphp
                @foreach ($project->donors as $donor)
                    @php $totalFunding += $donor->pivot->funding_amount; @endphp
                    <tr>
                        <td>{{ $donor->name }}</td>
                        <td>{{ $donor->contact_person }}</td>
                        <td class="text-right">${{ number_format($donor->pivot->funding_amount, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $donor->pivot->is_restricted ? 'badge-warning' : 'badge-success' }}">
                                {{ $donor->pivot->is_restricted ? 'Restricted' : 'Unrestricted' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2">TOTAL FUNDING</td>
                    <td class="text-right">${{ number_format($totalFunding, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Budget Summary -->
    @if ($project->budget_summary)
        <h2>Budget Summary</h2>

        @if ($project->budget_summary['alert_level'])
            @php
                $alertClass = $project->budget_summary['alert_level'] === 'critical' ? 'alert-danger' : 'alert-warning';
                $alertMessage =
                    $project->budget_summary['alert_level'] === 'critical'
                        ? 'Budget utilization has exceeded 100%'
                        : 'Budget utilization is approaching 90%';
            @endphp
            <div class="alert {{ $alertClass }}">
                <strong>⚠ Alert:</strong> {{ $alertMessage }}
            </div>
        @endif

        <table class="info-table">
            <tr>
                <td class="info-label">Total Allocated:</td>
                <td class="text-right">
                    <strong>${{ number_format($project->budget_summary['total_allocated'], 2) }}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Total Spent:</td>
                <td class="text-right"><strong>${{ number_format($project->budget_summary['total_spent'], 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td class="info-label">Remaining:</td>
                <td class="text-right">
                    <strong>${{ number_format($project->budget_summary['total_allocated'] - $project->budget_summary['total_spent'], 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td class="info-label">Utilization:</td>
                <td class="text-right">
                    @php
                        $utilization = $project->budget_summary['utilization_percentage'];
                        $badgeClass =
                            $utilization >= 100
                                ? 'badge-danger'
                                : ($utilization >= 90
                                    ? 'badge-warning'
                                    : 'badge-success');
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $utilization }}%</span>
                </td>
            </tr>
        </table>
    @endif

    <!-- Budget Details -->
    @if ($project->budgets && $project->budgets->count() > 0)
        <div class="page-break"></div>
        <h2>Budget Details</h2>

        @foreach ($project->budgets as $budget)
            <h3>Budget #{{ $budget->id }}
                <span
                    class="badge {{ $budget->status === 'approved' ? 'badge-success' : ($budget->status === 'rejected' ? 'badge-danger' : 'badge-warning') }}">
                    {{ ucfirst($budget->status) }}
                </span>
            </h3>

            @if ($budget->items && $budget->items->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $budgetTotal = 0; @endphp
                        @foreach ($budget->items as $item)
                            @php $budgetTotal += $item->total_amount; @endphp
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $item->category)) }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="text-right">${{ number_format($item->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="4">TOTAL</td>
                            <td class="text-right">${{ number_format($budgetTotal, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            @if ($budget->notes)
                <p><strong>Notes:</strong> {{ $budget->notes }}</p>
            @endif
        @endforeach
    @endif

    <!-- Team Members -->
    @if ($project->teamMembers && $project->teamMembers->count() > 0)
        <h2>Team Members</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($project->teamMembers as $member)
                    <tr>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->email }}</td>
                        <td>{{ $member->role->name ?? 'Staff' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Report Summary -->
    <div class="page-break"></div>
    <h2>Financial Summary</h2>
    <table class="info-table">
        <tr>
            <td class="info-label">Total Donor Funding:</td>
            <td class="text-right"><strong>${{ number_format($totalFunding ?? 0, 2) }}</strong></td>
        </tr>
        @if ($project->budget_summary)
            <tr>
                <td class="info-label">Total Budget Allocated:</td>
                <td class="text-right">
                    <strong>${{ number_format($project->budget_summary['total_allocated'], 2) }}</strong></td>
            </tr>
            <tr>
                <td class="info-label">Total Expenses:</td>
                <td class="text-right"><strong>${{ number_format($project->budget_summary['total_spent'], 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td class="info-label">Budget Remaining:</td>
                <td class="text-right">
                    <strong>${{ number_format($project->budget_summary['total_allocated'] - $project->budget_summary['total_spent'], 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td class="info-label">Unallocated Funds:</td>
                <td class="text-right">
                    <strong>${{ number_format(($totalFunding ?? 0) - $project->budget_summary['total_allocated'], 2) }}</strong>
                </td>
            </tr>
        @endif
    </table>

    <p style="margin-top: 30px; font-size: 10pt; color: #666;">
        <strong>Note:</strong> This report was automatically generated by the CANZIM FinTrack system.
        All financial information is current as of {{ now()->format('F d, Y \a\t H:i:s') }}.
    </p>
@endsection
