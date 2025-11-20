@extends('reports.layouts.pdf')

@section('title', 'Project Status Report')

@section('report-title', 'Project Status Report')

@section('content')
    <h1>Project Status Report</h1>

    <div class="info-section">
        <p><strong>Project:</strong> {{ $project['name'] ?? 'N/A' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($project['status'] ?? 'N/A') }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
    </div>

    <h2>Project Details</h2>
    <table class="info-table">
        <tr>
            <td class="info-label">Budget:</td>
            <td>{{ number_format($project['budget'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="info-label">Expenses:</td>
            <td>{{ number_format($project['expenses'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="info-label">Remaining:</td>
            <td>{{ number_format($project['remaining'] ?? 0, 2) }}</td>
        </tr>
    </table>
@endsection
