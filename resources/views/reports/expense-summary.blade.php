@extends('reports.layouts.pdf')

@section('title', 'Expense Summary Report')

@section('report-title', 'Expense Summary Report')

@section('content')
    <h1>Expense Summary Report</h1>

    <div class="info-section">
        <p><strong>Report Period:</strong> {{ $period ?? 'N/A' }}</p>
        <p><strong>Group By:</strong> {{ ucfirst($group_by ?? 'category') }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
    </div>

    <h2>Expense Breakdown</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ ucfirst($group_by ?? 'Category') }}</th>
                <th>Total Amount</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items ?? [] as $item)
                <tr>
                    <td>{{ $item['name'] ?? 'N/A' }}</td>
                    <td>{{ number_format($item['total'] ?? 0, 2) }}</td>
                    <td>{{ $item['count'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
