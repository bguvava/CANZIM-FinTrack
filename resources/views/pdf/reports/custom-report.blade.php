<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }} - CANZIM</title>
    @include('pdf.partials.styles')
</head>

<body>
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64,
        'organizationName' => $organizationName,
        'reportTitle' => $reportTitle,
    ])

    <div class="content">
        {{-- Aggregates Summary --}}
        @if (isset($aggregates) && count($aggregates) > 0)
            <table style="width: 100%; margin-bottom: 20px; border: none;">
                <tr>
                    @foreach ($aggregates as $key => $value)
                        <td
                            style="padding: 10px; background-color: #EFF6FF; border-left: 4px solid #1E40AF; text-align: center;">
                            <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">
                                {{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                            <div style="font-size: 16px; font-weight: bold; color: #1E40AF; margin-top: 5px;">
                                @if (str_contains($key, 'amount') || str_contains($key, 'total'))
                                    ${{ number_format($value, 2) }}
                                @else
                                    {{ number_format($value) }}
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        @endif

        {{-- Data Table --}}
        <h2 style="color: #1E40AF; margin-top: 20px; margin-bottom: 15px;">{{ $reportTitle }} Data</h2>
        <table class="data-table">
            <thead>
                <tr>
                    @if ($entity === 'expenses')
                        <th>Expense #</th>
                        <th>Description</th>
                        <th>Project</th>
                        <th>Category</th>
                        <th style="text-align: right;">Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    @elseif($entity === 'projects')
                        <th>Code</th>
                        <th>Name</th>
                        <th style="text-align: right;">Budget</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    @elseif($entity === 'budgets')
                        <th>Project</th>
                        <th>Period</th>
                        <th style="text-align: right;">Allocated</th>
                        <th style="text-align: right;">Spent</th>
                        <th>Status</th>
                    @elseif($entity === 'donors')
                        <th>Name</th>
                        <th>Type</th>
                        <th>Country</th>
                        <th>Email</th>
                        <th>Status</th>
                    @elseif($entity === 'purchase_orders')
                        <th>PO #</th>
                        <th>Vendor</th>
                        <th>Project</th>
                        <th style="text-align: right;">Total</th>
                        <th>Status</th>
                        <th>Order Date</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                    <tr>
                        @if ($entity === 'expenses')
                            <td>{{ $item->expense_number }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->project->name ?? 'N/A' }}</td>
                            <td>{{ $item->category->name ?? 'N/A' }}</td>
                            <td style="text-align: right; font-weight: bold;">${{ number_format($item->amount, 2) }}
                            </td>
                            <td><span class="badge badge-{{ strtolower($item->status) }}">{{ $item->status }}</span>
                            </td>
                            <td>{{ $item->expense_date->format('M d, Y') }}</td>
                        @elseif($entity === 'projects')
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->name }}</td>
                            <td style="text-align: right; font-weight: bold;">
                                ${{ number_format($item->total_budget ?? 0, 2) }}</td>
                            <td><span class="badge">{{ $item->status }}</span></td>
                            <td>{{ $item->start_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ $item->end_date?->format('M d, Y') ?? 'N/A' }}</td>
                        @elseif($entity === 'budgets')
                            <td>{{ $item->project->name ?? 'N/A' }}</td>
                            <td>{{ $item->period_start?->format('M Y') ?? 'N/A' }} -
                                {{ $item->period_end?->format('M Y') ?? 'N/A' }}</td>
                            <td style="text-align: right; font-weight: bold;">
                                ${{ number_format($item->total_allocated ?? 0, 2) }}</td>
                            <td style="text-align: right;">${{ number_format($item->total_spent ?? 0, 2) }}</td>
                            <td><span class="badge">{{ $item->status }}</span></td>
                        @elseif($entity === 'donors')
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->country ?? 'N/A' }}</td>
                            <td>{{ $item->email }}</td>
                            <td><span class="badge">{{ $item->status }}</span></td>
                        @elseif($entity === 'purchase_orders')
                            <td>{{ $item->po_number }}</td>
                            <td>{{ $item->vendor->name ?? 'N/A' }}</td>
                            <td>{{ $item->project->name ?? 'N/A' }}</td>
                            <td style="text-align: right; font-weight: bold;">
                                ${{ number_format($item->total_amount ?? 0, 2) }}</td>
                            <td><span class="badge">{{ $item->status }}</span></td>
                            <td>{{ $item->order_date?->format('M d, Y') ?? 'N/A' }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="color: #6B7280; font-style: italic;">No data
                            available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Filters Applied --}}
        @if (isset($filters) && count($filters) > 0)
            <div class="section-divider"></div>
            <div class="info-box" style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; margin-top: 20px;">
                <h4>Applied Filters:</h4>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    @foreach ($filters as $key => $value)
                        @if (!empty($value))
                            <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                @if (is_array($value))
                                    {{ implode(', ', $value) }}
                                @elseif($key === 'date_from' || $key === 'date_to')
                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y') }}
                                @else
                                    {{ $value }}
                                @endif
                            </li>
                        @endif
                    @endforeach
                    @if (isset($grouping))
                        <li><strong>Grouping:</strong> {{ ucfirst($grouping) }}</li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

    @include('pdf.partials.footer', [
        'generatedBy' => $generatedBy,
        'userRole' => $userRole,
        'generatedAt' => $generatedAt,
        'year' => $year,
        'organizationName' => $organizationName,
    ])
</body>

</html>
