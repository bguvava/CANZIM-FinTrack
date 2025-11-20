@extends('reports.layouts.pdf')

@section('title', 'Cash Flow Report')

@section('report-title', 'Cash Flow Report')

@section('content')
    <h1>Cash Flow Report</h1>

    <div class="info-section">
        <p><strong>Report Period:</strong> {{ $period ?? 'N/A' }}</p>
        <p><strong>Grouping:</strong> {{ ucfirst($grouping ?? 'month') }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
    </div>

    <h2>Cash Flow Summary</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Period</th>
                <th>Inflows</th>
                <th>Outflows</th>
                <th>Net Cash Flow</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items ?? [] as $item)
                <tr>
                    <td>{{ $item['period'] ?? 'N/A' }}</td>
                    <td>{{ number_format($item['inflows'] ?? 0, 2) }}</td>
                    <td>{{ number_format($item['outflows'] ?? 0, 2) }}</td>
                    <td>{{ number_format($item['net'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
