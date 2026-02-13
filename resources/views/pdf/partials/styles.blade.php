{{-- Standardized PDF Styles for CANZIM Reports --}}
<style>
    /* Reset and Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 11px;
        line-height: 1.6;
        color: #1F2937;
        background: #FFFFFF;
    }

    /* Typography */
    h1, h2, h3, h4, h5, h6 {
        color: #1F2937;
        font-weight: bold;
        margin-bottom: 10px;
    }

    h1 { font-size: 18px; color: #1E40AF; }
    h2 { font-size: 16px; color: #1E40AF; }
    h3 { font-size: 14px; }
    h4 { font-size: 12px; }

    p {
        margin-bottom: 8px;
        line-height: 1.5;
    }

    /* Links */
    a {
        color: #1E40AF;
        text-decoration: none;
    }

    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table.data-table {
        border: 1px solid #E5E7EB;
    }

    table.data-table thead {
        background-color: #1E40AF;
        color: #FFFFFF;
    }

    table.data-table th {
        padding: 10px;
        text-align: left;
        font-weight: bold;
        font-size: 11px;
        border: 1px solid #1E40AF;
    }

    table.data-table td {
        padding: 8px 10px;
        border: 1px solid #E5E7EB;
        font-size: 10px;
    }

    table.data-table tbody tr:nth-child(even) {
        background-color: #F9FAFB;
    }

    table.data-table tbody tr:hover {
        background-color: #EFF6FF;
    }

    /* Summary Table (no borders, key-value pairs) */
    table.summary-table td {
        padding: 5px 10px;
        border: none;
    }

    table.summary-table td:first-child {
        font-weight: bold;
        color: #4B5563;
        width: 40%;
    }

    /* Lists */
    ul, ol {
        margin-left: 20px;
        margin-bottom: 15px;
    }

    li {
        margin-bottom: 5px;
        line-height: 1.5;
    }

    /* Content Area */
    .content {
        padding: 0 10px;
        margin-bottom: 80px; /* Space for footer */
    }

    /* Info Boxes */
    .info-box {
        background-color: #EFF6FF;
        border-left: 4px solid #1E40AF;
        padding: 10px 15px;
        margin-bottom: 15px;
    }

    .warning-box {
        background-color: #FEF3C7;
        border-left: 4px solid #F59E0B;
        padding: 10px 15px;
        margin-bottom: 15px;
    }

    .success-box {
        background-color: #D1FAE5;
        border-left: 4px solid #10B981;
        padding: 10px 15px;
        margin-bottom: 15px;
    }

    .error-box {
        background-color: #FEE2E2;
        border-left: 4px solid #EF4444;
        padding: 10px 15px;
        margin-bottom: 15px;
    }

    /* Status Badges */
    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .badge-draft { background-color: #E5E7EB; color: #4B5563; }
    .badge-submitted { background-color: #FEF3C7; color: #92400E; }
    .badge-under-review { background-color: #DBEAFE; color: #1E40AF; }
    .badge-approved { background-color: #D1FAE5; color: #065F46; }
    .badge-paid { background-color: #D1FAE5; color: #065F46; }
    .badge-rejected { background-color: #FEE2E2; color: #991B1B; }

    /* Currency and Numbers */
    .currency {
        font-weight: bold;
        color: #1F2937;
    }

    .negative {
        color: #DC2626;
    }

    .positive {
        color: #059669;
    }

    /* Section Divider */
    .section-divider {
        border-top: 1px solid #E5E7EB;
        margin: 20px 0;
    }

    /* Page Break */
    .page-break {
        page-break-after: always;
    }

    /* Text Alignment */
    .text-left { text-align: left; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }

    /* Font Weights */
    .font-bold { font-weight: bold; }
    .font-normal { font-weight: normal; }

    /* Text Colors */
    .text-primary { color: #1E40AF; }
    .text-secondary { color: #6B7280; }
    .text-success { color: #059669; }
    .text-warning { color: #D97706; }
    .text-danger { color: #DC2626; }

    /* Margins and Padding */
    .mt-10 { margin-top: 10px; }
    .mt-20 { margin-top: 20px; }
    .mb-10 { margin-bottom: 10px; }
    .mb-20 { margin-bottom: 20px; }
    .p-10 { padding: 10px; }
    .p-20 { padding: 20px; }

    /* Print-specific */
    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    /* Page Numbers */
    .pagenum:before {
        content: counter(page);
    }
</style>
