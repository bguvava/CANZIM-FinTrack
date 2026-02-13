<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cash Flow Report - CANZIM</title>
    @include('pdf.partials.styles')
</head>

<body>
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64,
        'organizationName' => 'Climate Action Network Zimbabwe',
        'reportTitle' => 'Cash Flow Report',
    ])

    <div class="content">
        <h2 style="color: #1E40AF;">Cash Flow Analysis</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th style="text-align: right;">Inflows</th>
                    <th style="text-align: right;">Outflows</th>
                    <th style="text-align: right;">Net Flow</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['data'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['period'] ?? 'N/A' }}</td>
                        <td style="text-align: right; color: #059669;">${{ number_format($item['inflow'] ?? 0, 2) }}</td>
                        <td style="text-align: right; color: #DC2626;">${{ number_format($item['outflow'] ?? 0, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: bold;">${{ number_format($item['net_flow'] ?? 0, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
