<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Expense Summary Report - CANZIM</title>
    @include('pdf.partials.styles')
</head>

<body>
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64,
        'organizationName' => 'Climate Action Network Zimbabwe',
        'reportTitle' => 'Expense Summary Report',
    ])

    <div class="content">
        <h2 style="color: #1E40AF;">Expense Breakdown</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Group</th>
                    <th style="text-align: right;">Amount</th>
                    <th style="text-align: center;">Count</th>
                    <th style="text-align: right;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['data'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['category_name'] ?? ($item['project_name'] ?? ($item['month'] ?? 'N/A')) }}</td>
                        <td style="text-align: right; font-weight: bold;">${{ number_format($item['amount'] ?? 0, 2) }}
                        </td>
                        <td style="text-align: center;">{{ $item['count'] ?? 0 }}</td>
                        <td style="text-align: right;">{{ number_format($item['percentage'] ?? 0, 2) }}%</td>
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
