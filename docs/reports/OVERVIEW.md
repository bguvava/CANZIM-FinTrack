# Module 10: Reporting & Analytics Engine

## Overview & Architecture

**Module Status:** ✅ 100% Complete  
**Test Coverage:** 53/53 tests passing (100%)  
**Version:** 1.0.0  
**Last Updated:** November 19, 2025

---

## 📋 Module Description

The Reporting & Analytics Engine is a comprehensive financial reporting system that generates standardized PDF reports for CANZIM's financial operations, donor reporting, and project management needs.

### Primary Purpose

- Generate financial reports with standardized CANZIM layout
- Support donor-specific reporting requirements
- Provide audit-ready financial documentation
- Enable data-driven decision making with real-time analytics

### Key Features

✅ **6 Report Types** - Budget vs Actual, Cash Flow, Expense Summary, Project Status, Donor Contributions, Custom Reports  
✅ **Role-Based Access** - Programs Manager and Finance Officer only  
✅ **PDF Generation** - Standardized layout with CANZIM branding  
✅ **Report History** - Track all generated reports with metadata  
✅ **Advanced Filtering** - Up to 5 filters per report type  
✅ **File Management** - Download and view generated PDFs

---

## 🏗️ Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│  - Vue.js components (future implementation)                 │
│  - TailwindCSS styling                                       │
│  - SweetAlert2 confirmations                                 │
└─────────────────────────────────────────────────────────────┘
                            ↕ API (REST/JSON)
┌─────────────────────────────────────────────────────────────┐
│                    API LAYER                                 │
│  ┌──────────────────┐  ┌──────────────────┐                │
│  │ ReportController │  │   ReportPolicy   │                │
│  │  (API Routes)    │  │ (Authorization)  │                │
│  └──────────────────┘  └──────────────────┘                │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    BUSINESS LOGIC LAYER                      │
│  ┌──────────────────┐  ┌──────────────────┐                │
│  │  ReportService   │  │ ReportPDFService │                │
│  │ (Report Logic)   │  │ (PDF Generation) │                │
│  └──────────────────┘  └──────────────────┘                │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    DATA LAYER                                │
│  ┌──────────────────┐  ┌──────────────────┐                │
│  │  Report Model    │  │  File Storage    │                │
│  │   (Database)     │  │   (PDFs)         │                │
│  └──────────────────┘  └──────────────────┘                │
└─────────────────────────────────────────────────────────────┘
```

### MVC Pattern Implementation

**Models:**

- `Report` - Report metadata and file paths

**Controllers:**

- `ReportController` - API endpoints for report generation and management

**Services:**

- `ReportService` - Business logic for data aggregation and report generation
- `ReportPDFService` - PDF generation with standardized CANZIM layout

**Policies:**

- `ReportPolicy` - Role-based authorization for report access

**Resources:**

- `ReportResource` - API response formatting

**Requests:**

- `GenerateReportRequest` - Report generation validation
- `CustomReportRequest` - Custom report validation

---

## 📊 Report Types

### 1. Budget vs Actual Report

**Purpose:** Compare budgeted amounts with actual expenses  
**Access:** Programs Manager, Finance Officer  
**Filters:** Project IDs (max 5), Date Range  
**Key Metrics:** Total Budget, Total Spent, Variance, Utilization %

### 2. Cash Flow Report

**Purpose:** Track cash inflows and outflows  
**Access:** Programs Manager, Finance Officer  
**Filters:** Date Range, Grouping (month/quarter/year)  
**Key Metrics:** Total Inflow, Total Outflow, Net Cash Flow, Closing Balance

### 3. Expense Summary Report

**Purpose:** Summarize expenses by category or project  
**Access:** Programs Manager, Finance Officer  
**Filters:** Category IDs (max 5), Group By (category/project/month)  
**Key Metrics:** Total Expenses, Category Breakdown, Average Expense

### 4. Project Status Report

**Purpose:** Detailed project financial and progress overview  
**Access:** Programs Manager (only)  
**Filters:** Project ID (required)  
**Key Metrics:** Budget Utilization, Expenses by Category, Donors, Timeline

### 5. Donor Contributions Report

**Purpose:** Track donor funding and contributions  
**Access:** Programs Manager (only)  
**Filters:** Donor IDs (max 5), Date Range  
**Key Metrics:** Total Contributions, Donor Breakdown, Project Allocation

### 6. Custom Report

**Purpose:** User-defined report with custom parameters  
**Access:** Programs Manager, Finance Officer  
**Filters:** Custom parameters based on user needs

---

## 🔐 Security & Authorization

### Role-Based Access Control

| Report Type         | Programs Manager   | Finance Officer    | Project Officer |
| ------------------- | ------------------ | ------------------ | --------------- |
| Budget vs Actual    | ✅ Generate & View | ✅ Generate & View | ❌ No Access    |
| Cash Flow           | ✅ Generate & View | ✅ Generate & View | ❌ No Access    |
| Expense Summary     | ✅ Generate & View | ✅ Generate & View | ❌ No Access    |
| Project Status      | ✅ Generate & View | ❌ No Access       | ❌ No Access    |
| Donor Contributions | ✅ Generate & View | ❌ No Access       | ❌ No Access    |
| Custom Report       | ✅ Generate & View | ✅ Generate & View | ❌ No Access    |

### Authorization Rules

1. Users can only view/download their own generated reports
2. Users can only delete their own reports
3. Report generation requires specific policy permissions
4. File access checks ownership before allowing download

---

## 📁 Database Schema

### Reports Table Structure

```sql
CREATE TABLE reports (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    report_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    parameters JSON NOT NULL,
    file_path VARCHAR(255) NULL,
    file_size INT UNSIGNED NULL,
    generated_by BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_report_type (report_type),
    INDEX idx_generated_by_status (generated_by, status),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Field Descriptions

- `report_type` - Type of report (budget-vs-actual, cash-flow, expense-summary, etc.)
- `title` - Human-readable report title
- `parameters` - JSON object containing filter parameters
- `file_path` - Relative path to generated PDF file
- `file_size` - File size in bytes
- `generated_by` - User ID who generated the report
- `status` - Report generation status

---

## 🎨 PDF Layout Standard

All reports follow the standardized CANZIM PDF layout:

### Header Section

- **Logo:** CANZIM primary logo (left-aligned)
- **Organization Name:** Climate Action Network Zimbabwe
- **Report Title:** Dynamic title based on report type
- **Generation Date:** Current date in "F d, Y" format

### Content Section

- Report-specific data tables and charts
- Grouped by categories/projects as applicable
- Summary totals and key metrics
- Variance calculations where applicable

### Footer Section

- **Generated By:** User name and email
- **Generation Date & Time:** Full timestamp
- **Confidentiality Notice:** "CONFIDENTIAL - Internal Use Only"
- **Copyright:** "© 2025 Climate Action Network Zimbabwe. All rights reserved."
- **Developer Credits:** "Developed with ❤️ by bguvava (bguvava.com)"

---

## 📦 Files & Directories

### Backend Files

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   └── ReportController.php (11 endpoints)
│   ├── Requests/
│   │   ├── GenerateReportRequest.php
│   │   └── CustomReportRequest.php
│   └── Resources/
│       └── ReportResource.php
├── Models/
│   └── Report.php
├── Policies/
│   └── ReportPolicy.php
└── Services/
    ├── ReportService.php (6 generation methods)
    └── ReportPDFService.php (6 PDF methods)
```

### PDF Views

```
resources/views/reports/
├── layouts/
│   └── pdf.blade.php (standardized layout)
├── budget-vs-actual.blade.php
├── cash-flow.blade.php
├── expense-summary.blade.php
├── project-status.blade.php
└── donor-contributions.blade.php
```

### Tests

```
tests/Feature/Reports/
├── BudgetVsActualReportTest.php (10 tests)
├── CashFlowReportTest.php (8 tests)
├── DonorContributionsReportTest.php (7 tests)
├── ExpenseSummaryReportTest.php (9 tests)
├── ProjectStatusReportTest.php (7 tests)
└── ReportManagementTest.php (12 tests)
```

### Documentation

```
docs/reports/
├── OVERVIEW.md (this file)
├── API_ENDPOINTS.md
└── TESTING.md
```

---

## 🚀 Getting Started

### Prerequisites

- Laravel 12.x application installed
- Database migrated with reports table
- User authentication configured (Laravel Sanctum)
- PDF generation library installed (DomPDF)

### Quick Start

1. **Generate a Report:**

    ```bash
    POST /api/v1/reports/budget-vs-actual
    ```

2. **View Report History:**

    ```bash
    GET /api/v1/reports
    ```

3. **Download a Report:**
    ```bash
    GET /api/v1/reports/{id}/download
    ```

See `API_ENDPOINTS.md` for complete endpoint documentation.

---

## 📚 Additional Resources

- **API Documentation:** See `API_ENDPOINTS.md`
- **Testing Guide:** See `TESTING.md`
- **Laravel Documentation:** https://laravel.com/docs/12.x
- **DomPDF Documentation:** https://github.com/barryvdh/laravel-dompdf

---

**Module Completion Status:** ✅ 100% Complete  
**Last Verified:** November 19, 2025
