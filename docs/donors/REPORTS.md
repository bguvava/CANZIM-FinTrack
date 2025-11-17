# Donor Financial Reports - Documentation

**Module:** Donor & Funding Management (Module 9)  
**Feature:** PDF Financial Report Generation  
**Last Updated:** November 17, 2025

---

## Overview

The Donor Financial Reports feature provides comprehensive PDF reports for individual donors, summarizing their funding contributions, in-kind donations, active projects, and recent communications with CANZIM.

**Key Features:**

- ✅ Comprehensive financial summary (cash funding + in-kind value)
- ✅ Funding breakdown (restricted vs unrestricted)
- ✅ Active and completed projects list
- ✅ In-kind contributions by category
- ✅ Recent communications timeline (last 10)
- ✅ CANZIM branding and professional formatting
- ✅ Auto-download with unique filename
- ✅ Role-based access (Programs Manager, Finance Officer)

---

## Architecture

### Backend Components

**1. DonorPDFService** (`app/Services/DonorPDFService.php`)

- **Purpose:** Generate PDF reports using DomPDF library
- **Methods:**
    - `generateDonorFinancialReport(Donor $donor): string` - Main report generation
    - `generateDonorsSummaryReport(array $filters): string` - Multiple donors summary
    - `prepareDonorFinancialData(Donor $donor): array` - Data preparation for single donor
    - `prepareDonorsSummaryData(array $filters): array` - Data preparation for multiple donors
    - `generateDonorReportFilename(Donor $donor): string` - Unique filename generation
    - `getReportDownloadPath(string $filename): string` - Storage path retrieval

**2. DonorController** (`app/Http/Controllers/Api/DonorController.php`)

- **Endpoint:** `GET /api/v1/donors/{donor}/report`
- **Method:** `generateReport(Donor $donor): BinaryFileResponse|JsonResponse`
- **Authorization:** `$this->authorize('view', $donor)`
- **Response:** PDF file download or JSON error

**3. Blade Template** (`resources/views/pdf/donor-financial-report.blade.php`)

- **Format:** A4 portrait
- **Margins:** 10mm all sides
- **Sections:**
    1. Header with CANZIM logo
    2. Donor name banner
    3. Summary cards (4 metrics)
    4. Contact information
    5. Funding breakdown
    6. Active projects table
    7. Completed projects table
    8. In-kind contributions table (with category breakdown)
    9. Recent communications timeline
    10. Footer with generation info

### Frontend Components

**1. DonorStore** (`resources/js/stores/donorStore.js`)

- **Action:** `generateReport(donorId)`
- **Endpoint:** `/api/v1/donors/${donorId}/report`
- **Response Type:** `blob` (binary PDF)
- **File Handling:**
    - Creates Blob from response
    - Generates download link
    - Triggers automatic download
    - Cleans up resources

**2. ViewDonorModal** (`resources/js/pages/Donors/Modals/ViewDonorModal.vue`)

- **Button:** "Generate Report" (blue theme, PDF icon)
- **Location:** Modal footer, between "Edit" and "Assign Project"
- **States:**
    - Normal: "Generate Report" with PDF icon
    - Loading: "Generating..." with spinner icon
    - Disabled during generation
- **Feedback:**
    - Success: SweetAlert with 2-second auto-close
    - Error: SweetAlert with error message

---

## Report Content

### Summary Cards (Top Section)

1. **Total Funding** (Blue)
    - Sum of all cash funding across all projects
    - Format: `$1,234,567.89`

2. **In-Kind Value** (Purple)
    - Sum of all in-kind contribution estimated values
    - Format: `$12,345.67`

3. **Active Projects** (Green)
    - Count of projects with status ≠ cancelled/completed
    - Format: `5`

4. **Total Contribution** (Orange)
    - Total Funding + In-Kind Value
    - Format: `$1,246,913.56`

### Contact Information

**Left Column:**

- Donor Name
- Email
- Phone
- Contact Person
- Address

**Right Column:**

- Tax ID
- Website
- Status (active/inactive badge)
- Total Projects count
- Active Projects count

### Funding Breakdown

**Detailed Breakdown:**

- **Total Cash Funding:** Sum of all project funding
    - **Restricted Funding:** Projects with `is_restricted = true` (yellow)
    - **Unrestricted Funding:** Projects with `is_restricted = false` (green)
- **Total In-Kind Value:** Sum of in-kind contributions (purple)
- **GRAND TOTAL CONTRIBUTION:** Cash + In-Kind (blue, bold)

### Active Projects Table

**Columns:**

1. # (index)
2. Project Name
3. Funding Amount (USD, right-aligned)
4. Type (Restricted/Unrestricted badge)
5. Funding Period (MMM YYYY - MMM YYYY)
6. Status

**Features:**

- Restricted: Yellow badge with lock icon
- Unrestricted: Green badge with unlock icon
- Subtotal row at bottom
- Only shows projects with status ≠ cancelled/completed

### Completed Projects Table

**Columns:**

1. # (index)
2. Project Name
3. Funding Amount (USD, right-aligned)
4. Type (Restricted/Unrestricted badge)
5. Funding Period (MMM YYYY - MMM YYYY)

**Features:**

- Subtotal row at bottom
- Only shows projects with status = completed

### In-Kind Contributions Table

**Columns:**

1. # (index)
2. Category (purple badge)
3. Description
4. Estimated Value (USD, right-aligned)
5. Date (DD MMM YYYY)

**Category Breakdown:**

- Subtotal for each category (equipment, supplies, services, training, other)
- Shows count and total value per category
- Grand total row at bottom

**Categories:**

- Equipment
- Supplies
- Services
- Training
- Other

### Recent Communications Timeline

**Display:**

- Last 10 communications (chronologically)
- Timeline design with blue vertical line
- Blue dot markers for each entry

**Per Communication:**

- Date and Time (DD MMM YYYY HH:MM)
- Type (Email/Phone Call/Meeting/Letter/Other)
- Attachment indicator (📎 if file attached)
- Subject
- Notes (truncated to 120 chars)

**Footer Note:**

- If total communications > 10: "Showing 10 of X total communications"

---

## File Storage

**Directory:** `storage/app/reports/donors/`

**Filename Format:** `donor-financial-report-{donor-slug}-{timestamp}.pdf`

**Example:** `donor-financial-report-world-vision-20251117-143052.pdf`

**Components:**

- `donor-slug`: Lowercase donor name with spaces replaced by hyphens
- `timestamp`: YmdHis format (20251117-143052)

**Cleanup:** Files are auto-deleted after download (`deleteFileAfterSend(true)`)

---

## API Endpoint

### Generate Donor Financial Report

**Endpoint:** `GET /api/v1/donors/{donor}/report`

**Authorization:**

- Must be authenticated (Laravel Sanctum)
- Must have `view` permission for donor (via DonorPolicy)
- Programs Manager: ✅ Full access
- Finance Officer: ✅ Full access
- Project Officer: ❌ No access

**Path Parameters:**

- `{donor}` - Donor ID (integer)

**Response (Success):**

```http
HTTP/1.1 200 OK
Content-Type: application/pdf
Content-Disposition: attachment; filename="donor-financial-report-world-vision-20251117-143052.pdf"

[Binary PDF Content]
```

**Response (Error):**

```json
{
    "success": false,
    "message": "Failed to generate report: [Error details]"
}
```

**Status Codes:**

- `200 OK` - PDF generated successfully
- `403 Forbidden` - User lacks permission
- `404 Not Found` - Donor not found
- `500 Internal Server Error` - Report generation failed

---

## Frontend Usage

### Trigger Report Generation

```javascript
import { useDonorStore } from "@/stores/donorStore";

const donorStore = useDonorStore();

// Generate report for donor ID 5
try {
    await donorStore.generateReport(5);
    // Success - file automatically downloaded
} catch (error) {
    console.error("Report generation failed:", error);
}
```

### From ViewDonorModal

User clicks "Generate Report" button → Modal shows loading state → PDF downloads → Success notification appears → Modal returns to normal state

**User Flow:**

1. User views donor details in ViewDonorModal
2. User clicks "Generate Report" button (blue, PDF icon)
3. Button shows spinner: "Generating..."
4. Backend generates PDF and returns binary file
5. Frontend creates download link and triggers download
6. SweetAlert shows: "Report Generated - PDF report downloaded successfully"
7. Button returns to normal state

---

## Styling & Branding

### Color Scheme

**Primary (Blue):**

- Headers: `#1E40AF`
- Backgrounds: `#DBEAFE`
- Text: `#1E3A8A`

**Secondary Colors:**

- Purple (In-Kind): `#7C3AED` / `#E9D5FF`
- Green (Unrestricted): `#16A34A` / `#D1FAE5`
- Orange (Total Contribution): `#EA580C` / `#FED7AA`
- Yellow (Restricted): `#D97706` / `#FEF3C7`

### Typography

**Fonts:**

- Body: Arial, sans-serif
- Monospace (amounts): 'Courier New', monospace

**Sizes:**

- H1 (Title): 26px
- H3 (Section): 13px
- Body: 11px
- Small: 10px
- Tiny: 9px

### Layout

**Sections:**

- Border radius: 5px
- Padding: 12-15px
- Margins: 15-20px between sections

**Tables:**

- Header: Gray background, blue bottom border
- Rows: 1px gray bottom border
- Alternating row colors for readability

**Badges:**

- Border radius: 10px
- Padding: 2-3px 8-10px
- Font size: 8-9px
- Bold weight

---

## Error Handling

### Backend Errors

**1. Donor Not Found**

```json
{
    "success": false,
    "message": "Donor not found"
}
```

**2. Permission Denied**

```json
{
    "success": false,
    "message": "This action is unauthorized"
}
```

**3. Generation Failed**

```json
{
    "success": false,
    "message": "Failed to generate report: [Specific error]"
}
```

### Frontend Errors

**1. Network Error**

```javascript
Swal.fire({
    icon: "error",
    title: "Generation Failed",
    text: "Network error - please try again",
});
```

**2. API Error**

```javascript
Swal.fire({
    icon: "error",
    title: "Generation Failed",
    text: error.message || "Failed to generate report",
});
```

---

## Performance Considerations

**Eager Loading:**

```php
$donor->load([
    'projects' => function($query) {
        $query->withPivot(['funding_amount', 'funding_start_date', 'funding_end_date', 'is_restricted', 'notes']);
    },
    'projects.budgets',
    'inKindContributions',
    'communications' => function($query) {
        $query->orderBy('communication_date', 'desc');
    },
]);
```

**Optimization:**

- Single database query with eager loading
- Limit communications to 10 most recent
- No N+1 query issues
- Efficient Eloquent collections for grouping/filtering

**File Size:**

- Typical PDF size: 50-150 KB
- Max size: ~500 KB (for donors with extensive data)
- Logo: 120px width (optimized)

---

## Testing Checklist

### Backend Tests

- [ ] Test PDF generation for donor with all data types
- [ ] Test PDF generation for donor with minimal data
- [ ] Test authorization (PM: yes, FO: yes, PO: no)
- [ ] Test with non-existent donor ID (404)
- [ ] Test filename generation (unique, valid characters)
- [ ] Test data calculation accuracy
- [ ] Test eager loading (no N+1 queries)
- [ ] Test file cleanup after download

### Frontend Tests

- [ ] Test report generation from ViewDonorModal
- [ ] Test loading state during generation
- [ ] Test success notification
- [ ] Test error handling
- [ ] Test file download trigger
- [ ] Test button disabled state during loading
- [ ] Test for all user roles (PM, FO, PO)

### Integration Tests

- [ ] Test end-to-end flow (button click → PDF download)
- [ ] Test with various donor data scenarios
- [ ] Test concurrent report generations
- [ ] Test browser compatibility (Chrome, Firefox, Edge)

---

## Future Enhancements

**Potential Improvements:**

1. **Filters:** Allow date range filtering for reports
2. **Donors Summary Report:** Generate multi-donor comparison report
3. **Email Reports:** Send PDF via email instead of download
4. **Report Scheduling:** Automatic monthly/quarterly reports
5. **Custom Templates:** Allow customization of report layout
6. **Excel Export:** Alternative to PDF (CSV, XLSX)
7. **Charts in PDF:** Include funding charts in the report
8. **Report History:** Store generated reports for audit trail

---

## Related Documentation

- **API Documentation:** `/docs/donors/API.md`
- **Models Documentation:** `/docs/donors/MODELS.md`
- **TODO List:** `/docs/donors/TODO.md`
- **Main Progress:** `/docs/donors/PROGRESS.md`

---

## Changelog

**v1.0.0 - November 17, 2025**

- ✅ Initial implementation of DonorPDFService
- ✅ Created donor-financial-report.blade.php template
- ✅ Added GET /api/v1/donors/{donor}/report endpoint
- ✅ Integrated generateReport() in donorStore
- ✅ Added "Generate Report" button in ViewDonorModal
- ✅ Implemented auto-download functionality
- ✅ Added comprehensive error handling
- ✅ Applied CANZIM branding and styling

---

**Status:** ✅ Phase 6 Complete - PDF reports fully functional
