@extends('reports.layouts.pdf')

@section('title', 'Donor Contributions Report')

@section('report-title', 'Donor Contributions Report')

@section('content')
    <h1>Donor Contributions Report</h1>

    <div class="info-section">
        <p><strong>Report Period:</strong> {{ $period ?? 'N/A' }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
    </div>

    <h2>Donor Contributions</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Donor</th>
                <th>Total Contributions</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items ?? [] as $item)
                <tr>
                    <td>{{ $item['donor_name'] ?? 'N/A' }}</td>
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
