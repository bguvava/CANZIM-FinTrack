@extends('reports.layouts.pdf')

@section('title', 'Budget vs Actual Report')

@section('report-title', 'Budget vs Actual Report')

@section('content')
    <h1>Budget vs Actual Report</h1>

    <div class="info-section">
        <p><strong>Report Period:</strong> {{ $period ?? 'N/A' }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
    </div>

    <h2>Summary</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Budgeted Amount</th>
                <th>Actual Amount</th>
                <th>Variance</th>
                <th>Variance %</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items ?? [] as $item)
                <tr>
                    <td>{{ $item['category'] ?? 'N/A' }}</td>
                    <td>{{ number_format($item['budgeted'] ?? 0, 2) }}</td>
                    <td>{{ number_format($item['actual'] ?? 0, 2) }}</td>
                    <td>{{ number_format($item['variance'] ?? 0, 2) }}</td>
                    <td>{{ number_format($item['variance_percent'] ?? 0, 2) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
