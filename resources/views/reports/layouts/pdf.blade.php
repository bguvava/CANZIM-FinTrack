<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Report')</title>
    <style>
        @page {
            margin: 100px 50px 80px 50px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 80px;
            background: #f8f9fa;
            border-bottom: 3px solid #0066cc;
            padding: 15px 20px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30%;
        }

        .logo {
            max-height: 50px;
            max-width: 150px;
        }

        .org-name {
            font-size: 18pt;
            font-weight: bold;
            color: #0066cc;
            margin: 0;
        }

        .report-title {
            font-size: 14pt;
            color: #666;
            margin: 5px 0 0 0;
        }

        /* Footer Styles */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 60px;
            background: #f8f9fa;
            border-top: 2px solid #0066cc;
            padding: 10px 20px;
            font-size: 9pt;
            color: #666;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30%;
        }

        .confidential {
            font-weight: bold;
            color: #dc3545;
        }

        /* Content Styles */
        .content {
            margin-top: 20px;
        }

        h1 {
            font-size: 20pt;
            color: #0066cc;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 16pt;
            color: #333;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        h3 {
            font-size: 13pt;
            color: #555;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th {
            background: #0066cc;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .info-table td {
            width: 50%;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background: #e9ecef !important;
            border-top: 2px solid #0066cc;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .alert {
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }

        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .alert-danger {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="header-left">
                @if (file_exists(public_path('images/logo/canzim-logo.png')))
                    <img src="{{ public_path('images/logo/canzim-logo.png') }}" alt="CANZIM Logo" class="logo">
                @else
                    <h2 class="org-name">CANZIM</h2>
                @endif
                <p class="report-title">@yield('report-title', 'Financial Report')</p>
            </div>
            <div class="header-right">
                <strong>Report Date:</strong><br>
                {{ date('F d, Y') }}
            </div>
        </div>
    </header>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div>Generated by: {{ auth()->user()->name }} ({{ auth()->user()->role->name ?? 'Staff' }})</div>
                <div>Generated on: {{ now()->format('F d, Y \a\t H:i:s') }}</div>
                <div class="confidential">CONFIDENTIAL - For Internal Use Only</div>
            </div>
            <div class="footer-right">
                <div>&copy; {{ date('Y') }} CANZIM</div>
                <div>Page <span class="page-number"></span></div>
            </div>
        </div>
    </footer>

    <!-- Main Content -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Page numbering script -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans");
            $size = 9;
            $pageText = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
            $y = 800;
            $x = 520;
            $pdf->text($x, $y, $pageText, $font, $size);
        }
    </script>
</body>

</html>
