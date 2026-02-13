{{-- Standardized PDF Header for CANZIM Reports --}}
<div class="pdf-header">
    <table style="width: 100%; border-bottom: 2px solid #1E40AF; padding-bottom: 10px; margin-bottom: 20px;">
        <tr>
            <td style="width: 20%; text-align: left; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="CANZIM Logo" style="max-width: 80px; height: auto;">
                @endif
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle;">
                <h3 style="margin: 0; color: #1E40AF; font-size: 14px; font-weight: bold;">
                    {{ $organizationName ?? 'Climate Action Network Zimbabwe' }}
                </h3>
                <h1 style="margin: 5px 0 0 0; color: #1F2937; font-size: 18px; font-weight: bold;">
                    {{ $reportTitle }}
                </h1>
            </td>
            <td style="width: 20%; text-align: right; vertical-align: middle;">
                <p style="margin: 0; font-size: 10px; color: #6B7280;">
                    Generated on:<br>
                    <strong>{{ now()->format('M d, Y') }}</strong>
                </p>
            </td>
        </tr>
    </table>
</div>
