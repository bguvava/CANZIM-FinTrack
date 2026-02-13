<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Donor Contributions Report - CANZIM</title>
    @include('pdf.partials.styles')
</head>

<body>
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64,
        'organizationName' => 'Climate Action Network Zimbabwe',
        'reportTitle' => 'Donor Contributions Report',
    ])

    <div class="content">
        <h2 style="color: #1E40AF;">Donor Contributions</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th style="text-align: right;">Total Funding</th>
                    <th style="text-align: right;">In-Kind Value</th>
                    <th style="text-align: right;">Total Contribution</th>
                    <th style="text-align: center;">Projects</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['data'] ?? [] as $donor)
                    <tr>
                        <td>{{ $donor['donor_name'] ?? 'N/A' }}</td>
                        <td style="text-align: right; font-weight: bold;">
                            ${{ number_format($donor['total_funding'] ?? 0, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($donor['total_in_kind_value'] ?? 0, 2) }}</td>
                        <td style="text-align: right; font-weight: bold; color: #1E40AF;">
                            ${{ number_format($donor['total_contribution'] ?? 0, 2) }}</td>
                        <td style="text-align: center;">{{ $donor['projects_count'] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No donor data available</td>
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
