<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Budget vs Actual Report - CANZIM</title>
    @include('pdf.partials.styles')
</head>

<body>
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64,
        'organizationName' => 'Climate Action Network Zimbabwe',
        'reportTitle' => 'Budget vs Actual Report',
    ])

    <div class="content">
        {{-- Summary Statistics --}}
        @if (isset($data['summary']))
            <table style="width: 100%; margin-bottom: 20px; border: none;">
                <tr>
                    <td
                        style="width: 33.33%; padding: 10px; background-color: #EFF6FF; border-left: 4px solid #1E40AF; text-align: center;">
                        <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">Total
                            Budget</div>
                        <div style="font-size: 16px; font-weight: bold; color: #1E40AF; margin-top: 5px;">
                            ${{ number_format($data['summary']['total_budget'] ?? 0, 2) }}</div>
                    </td>
                    <td
                        style="width: 33.33%; padding: 10px; background-color: #FEF3C7; border-left: 4px solid #F59E0B; text-align: center;">
                        <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">Total
                            Actual</div>
                        <div style="font-size: 16px; font-weight: bold; color: #F59E0B; margin-top: 5px;">
                            ${{ number_format($data['summary']['total_actual'] ?? 0, 2) }}</div>
                    </td>
                    <td
                        style="width: 33.33%; padding: 10px; background-color: #FEE2E2; border-left: 4px solid #DC2626; text-align: center;">
                        <div style="font-size: 9px; color: #6B7280; text-transform: uppercase; font-weight: bold;">
                            Variance</div>
                        <div style="font-size: 16px; font-weight: bold; color: #DC2626; margin-top: 5px;">
                            ${{ number_format($data['summary']['total_variance'] ?? 0, 2) }}</div>
                    </td>
                </tr>
            </table>
        @endif

        {{-- Budget vs Actual Data --}}
        <h2 style="color: #1E40AF; margin-top: 20px; margin-bottom: 15px;">Budget vs Actual by Project & Category</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Project</th>
                    <th style="width: 25%;">Category</th>
                    <th style="width: 15%; text-align: right;">Budget</th>
                    <th style="width: 15%; text-align: right;">Actual</th>
                    <th style="width: 10%; text-align: right;">Variance</th>
                    <th style="width: 10%; text-align: right;">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['data'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['project_name'] ?? 'N/A' }}</td>
                        <td>{{ $item['category_name'] ?? 'N/A' }}</td>
                        <td style="text-align: right; font-weight: bold;">${{ number_format($item['budget'] ?? 0, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: bold;">${{ number_format($item['actual'] ?? 0, 2) }}
                        </td>
                        <td
                            style="text-align: right; color: {{ ($item['variance'] ?? 0) >= 0 ? '#059669' : '#DC2626' }};">
                            ${{ number_format($item['variance'] ?? 0, 2) }}
                        </td>
                        <td
                            style="text-align: right; color: {{ ($item['variance_percentage'] ?? 0) >= 0 ? '#059669' : '#DC2626' }};">
                            {{ number_format($item['variance_percentage'] ?? 0, 2) }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: #6B7280; font-style: italic;">No budget
                            data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Filters Applied --}}
        @if (isset($data['parameters']) && !empty($data['parameters']))
            <div class="section-divider"></div>
            <div class="info-box" style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; margin-top: 20px;">
                <h4>Applied Filters:</h4>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    @if (!empty($data['parameters']['start_date']))
                        <li>Start Date:
                            {{ \Carbon\Carbon::parse($data['parameters']['start_date'])->format('M d, Y') }}</li>
                    @endif
                    @if (!empty($data['parameters']['end_date']))
                        <li>End Date: {{ \Carbon\Carbon::parse($data['parameters']['end_date'])->format('M d, Y') }}
                        </li>
                    @endif
                    @if (!empty($data['parameters']['project_ids']))
                        <li>Filtered by {{ count($data['parameters']['project_ids']) }} specific project(s)</li>
                    @endif
                    @if (!empty($data['parameters']['category_ids']))
                        <li>Filtered by {{ count($data['parameters']['category_ids']) }} specific category(ies)</li>
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
        'organizationName' => 'Climate Action Network Zimbabwe',
    ])
</body>

</html>
