<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Project Status Report - CANZIM</title>
    @include('pdf.partials.styles')
</head>

<body>
    @include('pdf.partials.header', [
        'logoBase64' => $logoBase64,
        'organizationName' => 'Climate Action Network Zimbabwe',
        'reportTitle' => 'Project Status Report',
    ])

    <div class="content">
        @if (isset($data['project']))
            <div class="info-box" style="background-color: #EFF6FF; border-left: 4px solid #1E40AF;">
                <h3 style="color: #1E40AF; margin-top: 0;">Project Information</h3>
                <table class="summary-table">
                    <tr>
                        <td>Project:</td>
                        <td><strong>{{ $data['project']['name'] ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><strong>{{ ucfirst($data['project']['status'] ?? 'N/A') }}</strong></td>
                    </tr>
                </table>
            </div>

            @if (isset($data['financial_summary']))
                <h2 style="color: #1E40AF; margin-top: 20px;">Financial Summary</h2>
                <table class="summary-table">
                    <tr>
                        <td>Total Budget:</td>
                        <td class="currency">${{ number_format($data['financial_summary']['total_budget'] ?? 0, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td>Total Spent:</td>
                        <td class="currency">${{ number_format($data['financial_summary']['total_spent'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Remaining:</td>
                        <td class="currency">
                            ${{ number_format($data['financial_summary']['remaining_budget'] ?? 0, 2) }}</td>
                    </tr>
                </table>
            @endif
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
