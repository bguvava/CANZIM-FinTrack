# Module 9: Donor & Funding Management - TODO List

## 🎯 Module Overview

**Module:** Donor & Funding Management  
**Status:** Starting  
**Start Date:** November 17, 2025  
**Target Completion:** 100% with zero regressions  
**Test Coverage Required:** 100% pass rate  
**Requirements:** REQ-443 to REQ-488 (46 requirements)

---

## 📋 Module Success Criteria

- ✅ Donors can be created with complete contact information
- ✅ Donors can be linked to multiple projects with funding amounts
- ✅ Restricted and unrestricted funds tracked separately
- ✅ In-kind contributions recorded with estimated values
- ✅ Donor funding reports export to PDF with standardized layout
- ✅ Donor list with search and filters (max 5)
- ✅ Funding allocation displayed per project
- ✅ Donor management tests achieve 100% pass rate
- ✅ Documentation complete in `/docs/donors/`

---

## ✅ EXISTING INFRASTRUCTURE

### Database Tables Already Created

- [x] `donors` table (migration: 2025_11_14_113028_create_donors_table.php)
- [x] `project_donors` table (pivot for many-to-many with funding tracking)
- [x] `in_kind_contributions` table (tracks non-cash donations)
- [x] `communications` polymorphic table (for donor communications)

### Models Already Exist

- [x] `Donor.php` model with relationships
- [x] Relationships: projects(), inKindContributions(), communications()

### Existing Infrastructure to Leverage

- ✅ Project model with donor relationship
- ✅ CashFlow model with donor_id for inflow transactions
- ✅ InKindContribution model
- ✅ Communication model (polymorphic)

---

## 🚧 IMPLEMENTATION PHASES

### Phase 1: Backend API Foundation (REQ-447, REQ-450, REQ-452, REQ-477-480)

**Priority:** CRITICAL - Must complete before frontend  
**Status:** ✅ COMPLETE

- [x] **Task 1.1:** Create DonorController with CRUD methods
- [x] **Task 1.2:** Create DonorService.php business logic class
- [x] **Task 1.3:** Create Form Request validation classes
- [x] **Task 1.4:** Create DonorPolicy.php authorization
- [x] **Task 1.5:** Create DonorResource.php API formatting
- [x] **Task 1.6:** Optimize database queries
- [x] **Task 1.7:** Register API routes in routes/api.php
- [x] **BONUS:** Create DonorFactory, InKindContributionFactory, CommunicationFactory
- [x] **BONUS:** Update DonorSeeder with comprehensive test data
- [x] **BONUS:** Migrate & seed database successfully

---

### Phase 2: Pinia Store Setup (Store Pattern)

**Priority:** HIGH - Required before UI components  
**Estimated Time:** 1-2 hours

- [ ] **Task 2.1:** Create `resources/js/stores/donorStore.js`
    - State: donors array, loading, error, pagination, filters
    - Actions:
        - fetchDonors(page, filters)
        - fetchDonor(id)
        - createDonor(data)
        - updateDonor(id, data)
        - deleteDonor(id)
        - assignToProject(donorId, projectData)
        - removeFromProject(donorId, projectId)
        - addInKindContribution(donorId, data)
        - logCommunication(donorId, data)
        - generateReport(donorId, filters)
    - Getters:
        - activeDonors
        - totalFunding
        - getDonorById(id)

---

### Phase 3: Donor List Page (REQ-443 to REQ-446)

**Priority:** HIGH - Main entry point  
**Estimated Time:** 2-3 hours

- [ ] **Task 3.1:** Create `resources/js/pages/Donors/DonorsList.vue`
    - Summary cards:
        - Total Donors (count)
        - Active Donors (count with status='active')
        - Total Funding (sum of all project_donors.funding_amount)
        - Average Funding per Donor
    - Search box (debounced 300ms): name, contact_person
    - Filters (max 3):
        - Status: Active/Inactive dropdown
        - Funding Range: Min/Max inputs
        - Has Projects: Yes/No dropdown
    - Donors table with columns:
        - Name
        - Contact Person
        - Email
        - Phone
        - Total Funding (formatted USD)
        - Active Projects (count badge)
        - Status (badge: Active/Inactive)
        - Actions: View, Edit, Delete
    - Pagination (25 per page)
    - Loading skeleton
    - Empty state

- [ ] **Task 3.2:** Implement real-time search and filters
    - Watch search input with 300ms debounce
    - Filter button triggers API call with combined filters
    - Clear filters button resets to default state
    - URL query params for shareable filtered views

---

### Phase 4: Add/Edit Donor Modals (REQ-448 to REQ-452)

**Priority:** HIGH - Core CRUD operations  
**Estimated Time:** 2-3 hours

- [ ] **Task 4.1:** Create `AddDonorModal.vue`
    - SweetAlert2 modal with form fields:
        - Donor Name (required)
        - Contact Person (required)
        - Email (required, email validation)
        - Phone (required, phone format)
        - Address (textarea, optional)
        - Website (URL validation, optional)
        - Tax ID (optional)
        - Notes (textarea, optional)
    - Frontend validation with error messages
    - Submit → POST /api/v1/donors
    - Success: SweetAlert2 success toast + refresh list
    - Error: Display validation errors

- [ ] **Task 4.2:** Create `EditDonorModal.vue`
    - Same form as AddDonorModal but pre-filled
    - Fetch donor data on modal open
    - Submit → PUT /api/v1/donors/{id}
    - Success: SweetAlert2 success toast + refresh list
    - Error: Display validation errors

---

### Phase 5: View Donor Profile (REQ-453 to REQ-455)

**Priority:** HIGH - Detailed donor information  
**Estimated Time:** 3-4 hours

- [ ] **Task 5.1:** Create `ViewDonorModal.vue` (comprehensive view)
    - **Section 1: Donor Information**
        - Name, Contact Person, Email, Phone, Address
        - Status badge (Active/Inactive)
        - Website link, Tax ID, Notes
        - Created date, Last updated

    - **Section 2: Funding Summary Cards**
        - Total Funding (sum all project_donors)
        - Restricted Funding (sum where is_restricted=true)
        - Unrestricted Funding (sum where is_restricted=false)
        - In-Kind Total (sum in_kind_contributions.estimated_value)
        - Active Projects Count
        - Last Contribution Date

    - **Section 3: Funded Projects Table**
        - Columns: Project Name, Funding Amount, Period (start-end), Status, Type (Restricted/Unrestricted badge)
        - Actions: Remove from project (with confirmation)
        - Total row showing sum of funding amounts

    - **Section 4: In-Kind Contributions Table**
        - Columns: Date, Description, Category, Estimated Value, Project
        - Empty state if no contributions

    - **Section 5: Communication History Timeline**
        - Timeline view (vertical) with icons for type
        - Each entry: Date, Type, Subject, Notes, Next Action
        - Sorted by date descending
        - Empty state if no communications

    - **Section 6: Action Buttons**
        - Assign to Project
        - Add In-Kind Contribution
        - Log Communication
        - Generate Report
        - Edit Donor
        - Deactivate/Activate
        - Delete (if no active projects)

- [ ] **Task 5.2:** Implement funding calculations
    - API endpoint: GET /api/v1/donors/{id}/funding-summary
    - Returns: total, restricted, unrestricted, in_kind, active_projects, last_contribution
    - Display formatted USD with green/blue color coding

---

### Phase 6: Project Assignment (REQ-456 to REQ-458)

**Priority:** HIGH - Core funding tracking  
**Estimated Time:** 2 hours

- [ ] **Task 6.1:** Create `AssignProjectModal.vue`
    - SweetAlert2 modal with form:
        - Project Selector (dropdown from active projects)
        - Funding Amount (required, numeric, min: 0.01)
        - Start Date (required, date picker)
        - End Date (required, date picker, must be after start date)
        - Is Restricted Funding (checkbox)
        - Notes (textarea, optional)
    - Submit → POST /api/v1/donors/{id}/assign-project
    - Success: Refresh ViewDonorModal funded projects table
    - Error: Display validation errors

- [ ] **Task 6.2:** Create backend endpoint for project assignment
    - Route: POST /api/v1/donors/{id}/assign-project
    - Validate: project_id exists, funding_amount > 0, dates valid
    - Create record in project_donors table
    - Log to audit_trails
    - Return updated funding summary

- [ ] **Task 6.3:** Implement Remove from Project
    - DELETE /api/v1/donors/{donorId}/projects/{projectId}
    - SweetAlert2 confirmation: "Remove {donor} from {project}?"
    - Validate no active expenses linked to this funding
    - Soft delete or hard delete project_donors record
    - Success: Refresh funded projects table

- [ ] **Task 6.4:** Display restricted fund badges
    - In funded projects table: "Restricted" badge (red) or "Unrestricted" badge (green)
    - In summary cards: separate totals for restricted vs unrestricted

---

### Phase 7: In-Kind Contributions (REQ-459 to REQ-461)

**Priority:** MEDIUM - Supporting feature  
**Estimated Time:** 2 hours

- [ ] **Task 7.1:** Create `AddInKindContributionModal.vue`
    - SweetAlert2 modal with form:
        - Project Selector (dropdown)
        - Description (required, textarea)
        - Category (dropdown: Equipment, Supplies, Services, Training, Other)
        - Estimated Value (required, numeric, USD)
        - Contribution Date (required, date picker)
        - Notes (textarea, optional)
    - Submit → POST /api/v1/in-kind-contributions
    - Success: Refresh ViewDonorModal in-kind table
    - Error: Display validation errors

- [ ] **Task 7.2:** Create backend endpoint for in-kind
    - Route: POST /api/v1/in-kind-contributions
    - Validate: donor_id, project_id, description, estimated_value, date
    - Create record in in_kind_contributions table
    - Log to audit_trails
    - Return updated in-kind total

- [ ] **Task 7.3:** Display in-kind contributions table
    - Table in ViewDonorModal with columns: Date, Description, Category, Value, Project
    - Sorted by date descending
    - Total row showing sum of estimated values
    - Empty state: "No in-kind contributions recorded"

---

### Phase 8: Charts & Visualizations (REQ-462 to REQ-464)

**Priority:** MEDIUM - Enhanced UX  
**Estimated Time:** 2-3 hours

- [ ] **Task 8.1:** Create funding allocation pie chart (Chart.js)
    - Display in ViewDonorModal
    - Data: Funding distribution across projects
    - Show project name + percentage + amount in legend
    - Colors: Use CANZIM blue palette (#1E40AF, #2563EB, #60A5FA, etc.)
    - Responsive chart with tooltips showing USD amounts

- [ ] **Task 8.2:** Create funding timeline bar chart (Chart.js)
    - Display in ViewDonorModal or separate Insights tab
    - X-axis: Quarters or Years
    - Y-axis: USD amount
    - Grouped bars: Cash funding vs In-Kind contributions
    - Color coding: Blue for cash, Orange for in-kind
    - Tooltips with breakdown

- [ ] **Task 8.3:** Add chart library to project
    - Install: `npm install chart.js vue-chartjs`
    - Create reusable chart components in `/resources/js/components/charts/`
    - PieChart.vue, BarChart.vue

---

### Phase 9: Donor Financial Reports (REQ-465 to REQ-469)

**Priority:** HIGH - Critical reporting requirement  
**Estimated Time:** 3-4 hours

- [ ] **Task 9.1:** Create DonorPDFService.php
    - Method: `generateDonorFinancialReport(Donor $donor, array $filters)`
    - Filters: date_from, date_to, include_in_kind, specific_projects[]
    - Query funding data with filters
    - Calculate totals, subtotals
    - Generate PDF using Barryvdh\DomPDF

- [ ] **Task 9.2:** Create `donor-financial-report.blade.php` PDF template
    - **Header:**
        - CANZIM logo (left)
        - "Donor Financial Report" title (center)
        - Generation date (right)
    - **Section 1: Donor Information**
        - Name, Contact Person, Email, Phone, Address
    - **Section 2: Funding Summary**
        - Total Funding, Restricted, Unrestricted, In-Kind Total
        - Period: [date_from] to [date_to]
    - **Section 3: Projects Table**
        - Columns: Project Name, Funding Amount, Period, Type (Restricted/Unrestricted), Status
        - Subtotals per project
        - Grand total
    - **Section 4: In-Kind Contributions Table** (if include_in_kind=true)
        - Columns: Date, Description, Category, Estimated Value, Project
        - Total in-kind value
    - **Section 5: Funding Timeline Chart** (optional image embed)
    - **Footer:**
        - Generated by: [user name]
        - Date/Time: [timestamp]
        - Confidentiality Notice: "This document contains confidential financial information"
        - Copyright: © 2025 CANZIM. All rights reserved.
        - Developer Credits: "Developed with ❤️ by bguvava (bguvava.com)"

- [ ] **Task 9.3:** Create Generate Report endpoint
    - Route: POST /api/v1/donors/{id}/generate-report
    - Validate filters (dates, project IDs)
    - Call DonorPDFService
    - Store PDF in storage/app/reports/donors/
    - Return download URL
    - Auto-download with deleteFileAfterSend(true)

- [ ] **Task 9.4:** Add report filters modal
    - Create `GenerateReportModal.vue`
    - Filters: Date range (start/end), Include in-kind (checkbox), Specific projects (multi-select)
    - Generate button → API call → auto-download PDF
    - Success toast: "Report generated successfully"

---

### Phase 10: Communication Logging (REQ-470 to REQ-473)

**Priority:** MEDIUM - Supporting feature  
**Estimated Time:** 2 hours

- [ ] **Task 10.1:** Create `LogCommunicationModal.vue`
    - SweetAlert2 modal with form:
        - Communication Type (dropdown: Email, Phone Call, Meeting, Letter, Other)
        - Date (required, date picker with time)
        - Subject (required, text input)
        - Notes (required, textarea)
        - Attachments (optional, file upload, max 5MB)
        - Next Action Date (optional, date picker)
        - Next Action Notes (optional, textarea)
    - Submit → POST /api/v1/communications
    - Success: Refresh ViewDonorModal communication history
    - Error: Display validation errors

- [ ] **Task 10.2:** Create backend endpoint for communications
    - Route: POST /api/v1/communications
    - Polymorphic relationship: communicable_type='Donor', communicable_id={donor_id}
    - Validate: type, date, subject, notes
    - Handle file upload for attachments (store in storage/app/communications/)
    - Log to audit_trails
    - Return created communication

- [ ] **Task 10.3:** Display communication history timeline
    - Timeline component in ViewDonorModal
    - Icons for each type: 📧 Email, 📞 Phone, 👥 Meeting, 📄 Letter
    - Display: Date/Time, Type icon, Subject (bold), Notes, Next Action
    - Attachment download links (if any)
    - Sorted by date descending
    - Empty state: "No communication history"

---

### Phase 11: Donor Deactivation & Deletion (REQ-474 to REQ-476)

**Priority:** MEDIUM - Data management  
**Estimated Time:** 1 hour

- [ ] **Task 11.1:** Implement Deactivate Donor
    - Button in ViewDonorModal: "Deactivate Donor"
    - SweetAlert2 confirmation: "Deactivate {donor name}?"
    - PUT /api/v1/donors/{id}/deactivate
    - Set status='inactive'
    - Log to audit_trails (old status → new status)
    - Success: Update UI + toast notification
    - Button text changes to "Activate Donor" when inactive

- [ ] **Task 11.2:** Implement Delete Donor
    - Button in ViewDonorModal: "Delete Donor" (red button)
    - Backend validation: check for active funded projects
    - If active projects exist:
        - Return error 422: "Cannot delete donor with active projects"
        - SweetAlert2 error: "This donor has active funded projects. Please remove all project assignments first."
    - If no active projects:
        - SweetAlert2 confirmation: "Permanently delete {donor name}? This action cannot be undone."
        - DELETE /api/v1/donors/{id}
        - Soft delete (set deleted_at timestamp)
        - Success: Close modal + redirect to donors list + toast notification

- [ ] **Task 11.3:** Create DonorService validation method
    - Method: `canDeleteDonor(Donor $donor): bool`
    - Check: $donor->projects()->where('status', 'active')->count() === 0
    - Return true if no active projects, false otherwise

---

### Phase 12: Sidebar Navigation Updates

**Priority:** HIGH - User navigation  
**Estimated Time:** 30 minutes

- [ ] **Task 12.1:** Update Sidebar.vue
    - Add "Donors" menu item under Financial Section
    - Icon: `fas fa-hand-holding-usd`
    - Route: `/donors`
    - Role visibility: Programs Manager, Finance Officer
    - Position: After "Cash Flow", before "Reports"

- [ ] **Task 12.2:** Update router/index.js
    - Add route: `/donors` → DonorsList.vue component
    - Meta: requiresAuth: true, allowedRoles: ['Programs Manager', 'Finance Officer']
    - Lazy load component for performance

---

### Phase 13: Comprehensive Testing (REQ-481 to REQ-485, REQ-488)

**Priority:** CRITICAL - 100% pass rate required  
**Estimated Time:** 4-6 hours

#### Backend Feature Tests

- [ ] **Task 13.1:** Create `tests/Feature/Donors/DonorManagementTest.php`
    - Test CRUD operations:
        - `programs_manager_can_list_donors()`
        - `finance_officer_can_view_donors()`
        - `programs_manager_can_create_donor()`
        - `donor_validation_requires_name_and_email()`
        - `email_must_be_valid_format()`
        - `programs_manager_can_update_donor()`
        - `programs_manager_can_deactivate_donor()`
        - `programs_manager_can_activate_donor()`
        - `programs_manager_can_delete_donor_without_projects()`
        - `cannot_delete_donor_with_active_projects()`
        - `unauthenticated_user_cannot_access_donors()`

- [ ] **Task 13.2:** Create `tests/Feature/Donors/ProjectAssignmentTest.php`
    - Test project funding:
        - `programs_manager_can_assign_donor_to_project()`
        - `funding_amount_must_be_positive()`
        - `end_date_must_be_after_start_date()`
        - `can_track_restricted_funding_separately()`
        - `can_track_unrestricted_funding_separately()`
        - `programs_manager_can_remove_donor_from_project()`
        - `cannot_remove_donor_with_linked_expenses()`

- [ ] **Task 13.3:** Create `tests/Feature/Donors/InKindContributionsTest.php`
    - Test in-kind tracking:
        - `programs_manager_can_record_in_kind_contribution()`
        - `estimated_value_must_be_numeric()`
        - `contribution_must_link_to_donor_and_project()`
        - `can_retrieve_all_in_kind_for_donor()`
        - `can_calculate_total_in_kind_value()`

- [ ] **Task 13.4:** Create `tests/Feature/Donors/FundingCalculationsTest.php`
    - Test calculations:
        - `calculates_total_funding_across_projects()`
        - `separates_restricted_from_unrestricted_funding()`
        - `includes_in_kind_contributions_in_total()`
        - `calculates_average_funding_per_donor()`
        - `identifies_last_contribution_date()`

- [ ] **Task 13.5:** Create `tests/Feature/Donors/DonorReportsTest.php`
    - Test PDF generation:
        - `programs_manager_can_generate_donor_financial_report()`
        - `report_includes_funding_summary()`
        - `report_includes_project_list()`
        - `report_includes_in_kind_contributions_when_selected()`
        - `report_respects_date_range_filters()`
        - `report_respects_project_filters()`
        - `report_has_standardized_layout()`

- [ ] **Task 13.6:** Create `tests/Feature/Donors/CommunicationLogTest.php`
    - Test communication tracking:
        - `programs_manager_can_log_communication()`
        - `communication_links_to_donor_polymorphically()`
        - `can_retrieve_communication_history()`
        - `communication_history_sorted_by_date()`
        - `can_attach_files_to_communication()`

#### Backend Unit Tests

- [ ] **Task 13.7:** Create `tests/Unit/Donors/DonorServiceTest.php`
    - Test service methods:
        - `assignToProject_creates_project_donor_record()`
        - `calculateTotalFunding_sums_all_projects()`
        - `calculateRestrictedFunding_filters_correctly()`
        - `calculateInKindTotal_sums_estimated_values()`
        - `canDeleteDonor_checks_active_projects()`
        - `generateReport_creates_pdf_file()`

- [ ] **Task 13.8:** Create `tests/Unit/Donors/DonorPolicyTest.php`
    - Test authorization:
        - `programs_manager_can_view_any_donors()`
        - `finance_officer_can_view_any_donors()`
        - `project_officer_cannot_view_donors()`
        - `programs_manager_can_create_donors()`
        - `finance_officer_cannot_create_donors()`
        - `programs_manager_can_delete_donors()`

#### Test Execution

- [ ] **Task 13.9:** Run all donor tests
    - Command: `php artisan test tests/Feature/Donors/ tests/Unit/Donors/`
    - Target: 100% pass rate (no failures, no errors)
    - Verify: No regressions in other modules

- [ ] **Task 13.10:** Achieve 100% test coverage
    - All donor features tested
    - All edge cases covered
    - All authorization rules verified
    - All validation rules tested

---

### Phase 14: Documentation (REQ-486 to REQ-487)

**Priority:** HIGH - Required deliverable  
**Estimated Time:** 2-3 hours

- [ ] **Task 14.1:** Create `/docs/donors/overview.md`
    - Module objectives
    - Key features list
    - User roles and permissions
    - Workflow diagrams

- [ ] **Task 14.2:** Create `/docs/donors/donor-management.md`
    - Creating donors guide
    - Editing donor information
    - Deactivating vs deleting donors
    - Screenshots of UI

- [ ] **Task 14.3:** Create `/docs/donors/funding-tracking.md`
    - Assigning donors to projects
    - Restricted vs unrestricted funds explanation
    - Funding calculations methodology
    - Best practices

- [ ] **Task 14.4:** Create `/docs/donors/in-kind-contributions.md`
    - Recording in-kind donations
    - Categories and valuation
    - Reporting in-kind contributions

- [ ] **Task 14.5:** Create `/docs/donors/communication-log.md`
    - Logging communications guide
    - Communication types
    - Attachment handling
    - Follow-up tracking

- [ ] **Task 14.6:** Create `/docs/donors/reports.md`
    - Donor financial report guide
    - Report filters and customization
    - PDF layout explanation
    - Sample reports

- [ ] **Task 14.7:** Create `/docs/donors/api-reference.md`
    - All API endpoints documentation
    - Request/response examples
    - Authentication requirements
    - Error codes and handling

- [ ] **Task 14.8:** Create `/docs/donors/testing.md`
    - Test coverage report
    - Test execution guide
    - Test scenarios covered

---

## 📊 Progress Tracking

### Overall Progress: 35% Complete

- ✅ Backend API: 100% (7/7 tasks complete)
- ✅ Pinia Store: 0% (1 task pending)
- ✅ Donor List UI: 0% (2 tasks pending)
- ✅ Add/Edit Modals: 0% (2 tasks pending)
- ✅ View Donor Profile: 0% (2 tasks pending)
- ✅ Project Assignment: 0% (4 tasks pending)
- ✅ In-Kind Contributions: 0% (3 tasks pending)
- ✅ Charts & Visualizations: 0% (3 tasks pending)
- ✅ PDF Reports: 0% (4 tasks pending)
- ✅ Communication Logging: 0% (3 tasks pending)
- ✅ Deactivation & Deletion: 0% (3 tasks pending)
- ✅ Sidebar Navigation: 0% (2 tasks pending)
- ✅ Testing: 0% (10 test suites, ~60+ tests)
- ✅ Documentation: 0% (8 doc files)

### Test Coverage: 0/100%

- Target: 100% pass rate with zero regressions
- Feature Tests: 0/6 files
- Unit Tests: 0/2 files
- Total Tests: ~60+ tests to be written

---

## 🎯 Implementation Order (Sequential)

1. ✅ Phase 1: Backend API Foundation (MUST BE FIRST)
2. ✅ Phase 2: Pinia Store Setup
3. ✅ Phase 3: Donor List Page
4. ✅ Phase 4: Add/Edit Donor Modals
5. ✅ Phase 5: View Donor Profile
6. ✅ Phase 6: Project Assignment
7. ✅ Phase 7: In-Kind Contributions
8. ✅ Phase 11: Deactivation & Deletion (move up for core CRUD)
9. ✅ Phase 10: Communication Logging
10. ✅ Phase 8: Charts & Visualizations
11. ✅ Phase 9: Donor Financial Reports (PDF)
12. ✅ Phase 12: Sidebar Navigation
13. ✅ Phase 13: Comprehensive Testing (100% pass rate)
14. ✅ Phase 14: Documentation

---

## 🚀 Development Notes

### Design Patterns to Follow

- Copy UI patterns from Users, Projects, Budgets modules
- Use SweetAlert2 for ALL modals, confirmations, alerts
- Implement smooth transitions (300ms) for ALL UI elements
- Debounce search inputs (300ms)
- Color scheme: CANZIM Blue (#1E40AF) primary
- All buttons: bg-blue-800 hover:bg-blue-900
- Status badges with semantic colors (green: active, red: inactive)

### Key Requirements

- Maximum 5 filters per search/listing page (currently using 3)
- PDF exports ONLY (no Excel/CSV)
- Standardized PDF layout (header, content, footer)
- 100% test coverage required
- Zero regressions allowed

### API Endpoints Summary

```
GET    /api/v1/donors                          - List donors with pagination
POST   /api/v1/donors                          - Create donor
GET    /api/v1/donors/{id}                     - View donor
PUT    /api/v1/donors/{id}                     - Update donor
DELETE /api/v1/donors/{id}                     - Delete donor (soft delete)
POST   /api/v1/donors/{id}/assign-project      - Assign to project
DELETE /api/v1/donors/{id}/projects/{project}  - Remove from project
POST   /api/v1/in-kind-contributions           - Add in-kind contribution
POST   /api/v1/communications                  - Log communication
POST   /api/v1/donors/{id}/generate-report     - Generate PDF report
PUT    /api/v1/donors/{id}/deactivate          - Deactivate donor
PUT    /api/v1/donors/{id}/activate            - Activate donor
GET    /api/v1/donors/{id}/funding-summary     - Calculate funding totals
```

---

**Last Updated:** November 17, 2025  
**Next Review:** After Phase 1 completion  
**Module Status:** 🚧 In Progress - 0% Complete
