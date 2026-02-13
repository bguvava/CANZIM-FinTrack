<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense List Report</title>
    @include('pdf.partials.styles')
</head>
<body>
    {{-- Header --}}
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64 ?? '',
        'organizationName' => 'Climate Action Network Zimbabwe',
        'reportTitle' => 'Expense List Report'
    ])

    {{-- Content --}}
    <div class="content">
        {{-- Report Filters Summary --}}
        <div class="info-box mb-20">
            <h4>Report Filters</h4>
            <p style="margin-bottom: 5px;">
                <strong>Date Range:</strong> 
                {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') : 'All' }} - 
                {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') : 'All' }}
            </p>
            @if(!empty($filters['status']))
            <p style="margin-bottom: 5px;"><strong>Status:</strong> {{ ucfirst($filters['status']) }}</p>
            @endif
            @if(!empty($filters['project_id']))
            <p style="margin-bottom: 5px;"><strong>Project:</strong> {{ $filters['project_name'] ?? 'N/A' }}</p>
            @endif
            @if(!empty($filters['category_id']))
            <p style="margin-bottom: 5px;"><strong>Category:</strong> {{ $filters['category_name'] ?? 'N/A' }}</p>
            @endif
            <p style="margin-bottom: 0;"><strong>Total Expenses:</strong> {{ $expenses->count() }}</p>
        </div>

        {{-- Summary Statistics --}}
        <table class="summary-table mb-20">
            <tr>
                <td style="width: 50%;">Total Amount:</td>
                <td class="currency text-primary" style="font-size: 14px;">${{ number_format($totalAmount, 2) }}</td>
            </tr>
            @if(isset($statistics))
            <tr>
                <td>Approved Amount:</td>
                <td class="currency positive">${{ number_format($statistics['approved_amount'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Paid Amount:</td>
                <td class="currency positive">${{ number_format($statistics['paid_amount'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Pending Amount:</td>
                <td class="currency">${{ number_format($statistics['pending_amount'] ?? 0, 2) }}</td>
            </tr>
            @endif
        </table>

        {{-- Expenses Table --}}
        <h3 class="mb-10">Expense Details</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Expense #</th>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 15%;">Project</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 23%;">Description</th>
                    <th style="width: 10%; text-align: right;">Amount</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 5%;">Receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
                    <td>{{ $expense->project->name ?? 'N/A' }}</td>
                    <td>{{ $expense->category->name ?? 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($expense->description, 50) }}</td>
                    <td style="text-align: right;" class="currency">${{ number_format($expense->amount, 2) }}</td>
                    <td><span class="badge badge-{{ strtolower($expense->status) }}">{{ $expense->status }}</span></td>
                    <td style="text-align: center;">{{ $expense->receipt_path ? '✓' : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #6B7280; padding: 20px;">
                        No expenses found matching the specified filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($expenses->count() > 0)
            <tfoot style="background-color: #F3F4F6; font-weight: bold;">
                <tr>
                    <td colspan="5" style="text-align: right; padding: 10px;">Total:</td>
                    <td style="text-align: right; padding: 10px;" class="currency text-primary">
                        ${{ number_format($totalAmount, 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- Status Breakdown --}}
        @if(isset($statusBreakdown) && count($statusBreakdown) > 0)
        <div class="section-divider"></div>
        <h3 class="mb-10">Status Breakdown</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th style="text-align: center;">Count</th>
                    <th style="text-align: right;">Total Amount</th>
                    <th style="text-align: right;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statusBreakdown as $status => $data)
                <tr>
                    <td><span class="badge badge-{{ strtolower($status) }}">{{ $status }}</span></td>
                    <td style="text-align: center;">{{ $data['count'] }}</td>
                    <td style="text-align: right;" class="currency">${{ number_format($data['amount'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($data['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Notes --}}
        <div class="warning-box mt-20">
            <p style="margin: 0; font-size: 9px;">
                <strong>Note:</strong> This report is generated for internal use only. 
                For detailed information about individual expenses, including approval history and payment details, 
                please refer to the individual expense detail reports.
            </p>
        </div>
    </div>

    {{-- Footer --}}
    @include('pdf.partials.footer', [
        'generatedBy' => auth()->user()->full_name ?? 'System',
        'userRole' => auth()->user()->role->name ?? 'Administrator',
        'generatedAt' => now()->format('F d, Y \a\t h:i A'),
        'year' => now()->year,
        'organizationName' => 'Climate Action Network Zimbabwe'
    ])
</body>
</html>
