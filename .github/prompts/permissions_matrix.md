

# 🔒 CANZIM FinTrack - Comprehensive Permissions Matrix

## **User Roles Overview**

The system has **3 hierarchical roles**:

1. **Programs Manager** (slug: `programs-manager`) - Highest Authority
2. **Finance Officer** (slug: `finance-officer`) - Middle Authority  
3. **Project Officer** (slug: `project-officer`) - Base Authority

---

## **📊 Complete Permissions Matrix**

### **1. NAVIGATION & MENU ACCESS**

| Menu Item | Programs Manager | Finance Officer | Project Officer |
|-----------|------------------|-----------------|-----------------|
| Dashboard | ✅ Full Access | ✅ Full Access | ✅ Full Access |
| Projects | ✅ All Projects | ✅ All Projects | ✅ All Projects |
| Budgets | ✅ Full Access | ✅ Full Access | ❌ No Access |
| Expenses | ✅ Full Access | ✅ Full Access | ✅ Full Access |
| Cash Flow | ✅ Full Access | ✅ Full Access | ❌ No Access |
| Purchase Orders | ✅ Full Access | ✅ Full Access | ❌ No Access |
| Donors | ✅ Full Access | ✅ Full Access | ❌ No Access |
| Reports | ✅ Full Access | ✅ Full Access | ❌ No Access |
| Users | ✅ Full Access | ❌ No Access | ❌ No Access |
| Activity Logs | ✅ Full Access | ❌ No Access | ❌ No Access |
| Documents | ✅ Full Access | ❌ No Access | ✅ Limited Access |
| Audit Trail | ✅ Full Access | ❌ No Access | ❌ No Access |
| Settings | ✅ Full Access | ❌ No Access | ❌ No Access |

---

### **2. USER MANAGEMENT (Module 4)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View All Users** | ✅ Yes | ❌ No | ❌ No |
| **View Own Profile** | ✅ Yes | ✅ Yes | ✅ Yes |
| **View Other Profiles** | ✅ Yes | ❌ No | ❌ No |
| **Create Users** | ✅ Yes | ❌ No | ❌ No |
| **Update Any User** | ✅ Yes | ❌ No | ❌ No |
| **Update Own Profile** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Delete Users** | ✅ Yes (not self) | ❌ No | ❌ No |
| **Deactivate Users** | ✅ Yes (not self) | ❌ No | ❌ No |
| **Activate Users** | ✅ Yes | ❌ No | ❌ No |
| **Upload Own Avatar** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Change Own Password** | ✅ Yes | ✅ Yes | ✅ Yes |
| **View Activity Logs** | ✅ All Users | ❌ No | ❌ No |
| **View Own Activity** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Bulk Delete Logs** | ✅ Yes | ❌ No | ❌ No |

---

### **3. PROJECT MANAGEMENT (Module 6)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Projects List** | ✅ All Projects | ✅ All Projects | ✅ All Projects |
| **View Project Details** | ✅ All Projects | ✅ All Projects | ✅ All Projects |
| **Create Projects** | ✅ Yes | ❌ No | ❌ No |
| **Update Projects** | ✅ Yes | ❌ No | ❌ No |
| **Delete Projects** | ✅ Yes | ❌ No | ❌ No |
| **Archive Projects** | ✅ Yes | ❌ No | ❌ No |
| **Restore Projects** | ✅ Yes | ❌ No | ❌ No |
| **Force Delete** | ✅ Yes | ❌ No | ❌ No |
| **Generate Reports** | ✅ Yes | ✅ Yes | ❌ No |
| **View Statistics** | ✅ Yes | ✅ Yes | ✅ Limited |

---

### **4. BUDGET MANAGEMENT (Module 6)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Budgets List** | ✅ All Budgets | ✅ All Budgets | ❌ No Access |
| **View Budget Details** | ✅ Yes | ✅ Yes | ❌ No |
| **Create Budgets** | ✅ Yes | ❌ No | ❌ No |
| **Update Budgets** | ✅ Draft Only | ❌ No | ❌ No |
| **Delete Budgets** | ✅ Draft Only | ❌ No | ❌ No |
| **Approve Budgets** | ✅ Yes | ❌ No | ❌ No |
| **Request Reallocation** | ✅ Yes | ✅ Yes | ❌ No |
| **Approve Reallocation** | ✅ Yes | ❌ No | ❌ No |
| **View Categories** | ✅ Yes | ✅ Yes | ❌ No |

---

### **5. EXPENSE MANAGEMENT (Module 7)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Expenses List** | ✅ All Expenses | ✅ All Expenses | ✅ Own Only |
| **View Expense Details** | ✅ All Expenses | ✅ All Expenses | ✅ Own Only |
| **Create Expenses** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Update Expenses** | ✅ Draft/Rejected (Own) | ✅ Draft/Rejected (Own) | ✅ Draft/Rejected (Own) |
| **Delete Expenses** | ✅ Any Draft | ✅ Own Draft Only | ✅ Own Draft Only |
| **Submit Expenses** | ✅ Own Only | ✅ Own Only | ✅ Own Only |
| **Review Expenses** | ❌ No | ✅ Submitted Status | ❌ No |
| **Approve Expenses** | ✅ Under Review Status | ❌ No | ❌ No |
| **Mark as Paid** | ❌ No | ✅ Approved Status | ❌ No |
| **Link Purchase Orders** | ❌ No | ✅ Yes | ❌ No |
| **Unlink Purchase Orders** | ❌ No | ✅ Yes | ❌ No |
| **Restore Expenses** | ✅ Yes | ❌ No | ❌ No |
| **Force Delete** | ✅ Yes | ❌ No | ❌ No |

**Workflow:**
- Project Officer: Create → Submit
- Finance Officer: Review (Submitted → Under Review)
- Programs Manager: Approve (Under Review → Approved)
- Finance Officer: Mark as Paid (Approved → Paid)

---

### **6. CASH FLOW MANAGEMENT (Module 8)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Cash Flows** | ✅ Yes | ✅ Yes | ❌ No |
| **View Details** | ✅ Yes | ✅ Yes | ❌ No |
| **Create Inflows/Outflows** | ❌ No | ✅ Yes | ❌ No |
| **Update Transactions** | ❌ No | ✅ Unreconciled Only | ❌ No |
| **Delete Transactions** | ❌ No | ✅ Unreconciled Only | ❌ No |
| **Reconcile Transactions** | ❌ No | ✅ Yes | ❌ No |
| **Unreconcile** | ❌ No | ✅ Yes | ❌ No |
| **View Projections** | ✅ Yes | ✅ Yes | ❌ No |
| **Export Statement PDF** | ✅ Yes | ✅ Yes | ❌ No |
| **Export Reconciliation** | ✅ Yes | ✅ Yes | ❌ No |
| **View Statistics** | ✅ Yes | ✅ Yes | ❌ No |
| **Restore** | ❌ No | ✅ Yes | ❌ No |
| **Force Delete** | ❌ No | ❌ No | ❌ No |

---

### **7. BANK ACCOUNT MANAGEMENT (Module 8)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Bank Accounts** | ✅ Yes | ✅ Yes | ❌ No |
| **View Account Details** | ✅ Yes | ✅ Yes | ❌ No |
| **Create Accounts** | ❌ No | ✅ Yes | ❌ No |
| **Update Accounts** | ❌ No | ✅ Yes | ❌ No |
| **Delete Accounts** | ❌ No | ❌ No | ❌ No |
| **Deactivate Accounts** | ❌ No | ✅ Yes | ❌ No |
| **Activate Accounts** | ❌ No | ✅ Yes | ❌ No |
| **View Summary** | ✅ Yes | ✅ Yes | ❌ No |
| **Restore Accounts** | ❌ No | ✅ Yes | ❌ No |
| **Force Delete** | ❌ No | ❌ No | ❌ No |

---

### **8. PURCHASE ORDER MANAGEMENT (Module 8)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View PO List** | ✅ All POs | ✅ All POs | ✅ Own Only |
| **View PO Details** | ✅ All POs | ✅ All POs | ✅ Own Only |
| **Create POs** | ❌ No | ✅ Yes | ❌ No |
| **Update POs** | ❌ No | ✅ Draft/Rejected (Own) | ❌ No |
| **Delete POs** | ❌ No | ✅ Draft Only (Own) | ❌ No |
| **Submit POs** | ❌ No | ✅ Yes | ❌ No |
| **Approve POs** | ✅ Pending Status | ❌ No | ❌ No |
| **Reject POs** | ✅ Pending Status | ❌ No | ❌ No |
| **Receive Items** | ❌ No | ✅ Approved/Partial | ❌ No |
| **Complete POs** | ❌ No | ✅ Received Status | ❌ No |
| **Cancel POs** | ✅ Yes | ✅ Yes | ❌ No |
| **Export PDF** | ✅ Yes | ✅ Yes | ❌ No |
| **Export List PDF** | ✅ Yes | ✅ Yes | ❌ No |
| **View Expenses** | ✅ Yes | ✅ Yes | ❌ No |
| **View Statistics** | ✅ Yes | ✅ Yes | ❌ No |
| **Restore POs** | ✅ Yes | ❌ No | ❌ No |
| **Force Delete** | ✅ Yes | ❌ No | ❌ No |

**Workflow:**
- Finance Officer: Create → Submit
- Programs Manager: Approve/Reject (Pending)
- Finance Officer: Receive Items → Complete

---

### **9. VENDOR MANAGEMENT (Module 8)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Vendors** | ✅ Yes | ✅ Yes | ❌ No |
| **View Details** | ✅ Yes | ✅ Yes | ❌ No |
| **Create Vendors** | ❌ No | ✅ Yes | ❌ No |
| **Update Vendors** | ❌ No | ✅ Yes | ❌ No |
| **Delete Vendors** | ❌ No | ✅ Yes | ❌ No |
| **Deactivate Vendors** | ❌ No | ✅ Yes | ❌ No |
| **Activate Vendors** | ❌ No | ✅ Yes | ❌ No |
| **View Summary** | ✅ Yes | ✅ Yes | ❌ No |

---

### **10. DONOR MANAGEMENT (Module 9)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Donors List** | ✅ Yes | ✅ Yes | ❌ No |
| **View Donor Details** | ✅ Yes | ✅ Yes | ❌ No |
| **Create Donors** | ✅ Yes | ❌ No | ❌ No |
| **Update Donors** | ✅ Yes | ❌ No | ❌ No |
| **Delete Donors** | ✅ Yes | ❌ No | ❌ No |
| **Restore Donors** | ✅ Yes | ❌ No | ❌ No |
| **Assign to Project** | ✅ Yes | ✅ Yes | ❌ No |
| **Remove from Project** | ✅ Yes | ✅ Yes | ❌ No |
| **Generate Reports** | ✅ Yes | ✅ Yes | ❌ No |
| **View Statistics** | ✅ Yes | ✅ Yes | ❌ No |
| **View Funding Summary** | ✅ Yes | ✅ Yes | ❌ No |
| **Toggle Status** | ✅ Yes | ✅ Yes | ❌ No |
| **Add In-Kind Contributions** | ✅ Yes | ✅ Yes | ❌ No |
| **Add Communications** | ✅ Yes | ✅ Yes | ❌ No |

---

### **11. REPORTING & ANALYTICS (Module 10)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Reports List** | ✅ Own Reports | ✅ Own Reports | ❌ No |
| **View Report Details** | ✅ Own Reports | ✅ Own Reports | ❌ No |
| **Generate Reports** | ✅ Yes | ✅ Yes | ❌ No |
| **Delete Reports** | ✅ Own Only | ✅ Own Only | ❌ No |
| **Download PDF** | ✅ Yes | ✅ Yes | ❌ No |
| **Export PDF** | ✅ Yes | ✅ Yes | ❌ No |
| **Budget vs Actual** | ✅ Yes | ✅ Yes | ❌ No |
| **Cash Flow Report** | ✅ Yes | ✅ Yes | ❌ No |
| **Expense Summary** | ✅ Yes | ✅ Yes | ❌ No |
| **Project Status** | ✅ Yes | ❌ No | ❌ No |
| **Donor Contributions** | ✅ Yes | ❌ No | ❌ No |
| **Custom Reports** | ✅ Yes | ✅ Yes | ❌ No |
| **Restore Reports** | ✅ Own Only | ✅ Own Only | ❌ No |
| **Force Delete** | ✅ Own Only | ✅ Own Only | ❌ No |

---

### **12. DOCUMENT MANAGEMENT (Module 12)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Documents** | ✅ All | ✅ Financial Related | ✅ Project Related |
| **View Details** | ✅ Access by Parent | ✅ Access by Parent | ✅ Access by Parent |
| **Upload Documents** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Update Documents** | ✅ All | ✅ Own or Related | ✅ Own Only |
| **Delete Documents** | ✅ All | ✅ Own or Related | ✅ Own Only |
| **Download Documents** | ✅ Access by Parent | ✅ Access by Parent | ✅ Access by Parent |
| **Replace Documents** | ✅ All | ✅ Own or Related | ✅ Own Only |
| **View Versions** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Manage Categories** | ✅ Yes | ❌ No | ❌ No |
| **Restore Documents** | ✅ Yes | ❌ No | ❌ No |
| **Force Delete** | ✅ Yes | ❌ No | ❌ No |

**Document Access by Parent Entity:**
- **Project Documents**: Creator or Programs Manager
- **Budget Documents**: Creator, Programs Manager, or Finance Officer
- **Expense Documents**: Submitter, Programs Manager, or Finance Officer  
- **Donor Documents**: All authenticated users

---

### **13. COMMENT & COLLABORATION (Module 11)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Comments** | ✅ Yes | ✅ Yes | ✅ Yes |
| **View Comment Details** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Create Comments** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Update Comments** | ✅ Own Only | ✅ Own Only | ✅ Own Only |
| **Delete Comments** | ✅ Own Only | ✅ Own Only | ✅ Own Only |
| **Download Attachments** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Restore Comments** | ✅ Own Only | ✅ Own Only | ✅ Own Only |
| **Force Delete** | ✅ Own Only | ✅ Own Only | ✅ Own Only |

---

### **14. SYSTEM SETTINGS (Module 13)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Settings** | ✅ Yes | ❌ No | ❌ No |
| **View by Group** | ✅ Yes | ❌ No | ❌ No |
| **Update Organization** | ✅ Yes | ❌ No | ❌ No |
| **Update Financial** | ✅ Yes | ❌ No | ❌ No |
| **Update Email** | ✅ Yes | ❌ No | ❌ No |
| **Update Security** | ✅ Yes | ❌ No | ❌ No |
| **Update Notifications** | ✅ Yes | ❌ No | ❌ No |
| **Upload Logo** | ✅ Yes | ❌ No | ❌ No |
| **Clear Cache** | ✅ Yes | ❌ No | ❌ No |
| **System Health Check** | ✅ Yes | ❌ No | ❌ No |

**Gate:** `manage-settings` (Programs Manager Only)

---

### **15. AUDIT TRAIL (Module 13)**

| Permission | Programs Manager | Finance Officer | Project Officer |
|------------|------------------|-----------------|-----------------|
| **View Audit Trails** | ✅ Yes | ❌ No | ❌ No |
| **View Details** | ✅ Yes | ❌ No | ❌ No |
| **Filter Audit Trails** | ✅ Yes | ❌ No | ❌ No |
| **Search Audit Trails** | ✅ Yes | ❌ No | ❌ No |
| **Export to CSV** | ✅ Yes | ❌ No | ❌ No |
| **View Filter Options** | ✅ Yes | ❌ No | ❌ No |

**Gate:** `manage-settings` (Programs Manager Only)

---

## **📋 Summary by Role**

### **Programs Manager (Highest Authority)**
- **Full system access** across all modules
- **Approval authority** for budgets, expenses, purchase orders
- **User management** and system configuration
- **Audit trail** and activity log access
- **All reports** including donor and project status
- **Cannot**: Delete/deactivate self

### **Finance Officer (Middle Authority)**
- **Financial operations** focus
- **Review expenses** (Submit → Under Review)
- **Mark expenses as paid** (Approved → Paid)
- **Manage**: Cash flow, bank accounts, purchase orders, vendors
- **Create and receive** purchase orders
- **View/generate** financial reports (except Project Status & Donor Contributions)
- **Cannot**: Approve budgets, approve expenses, approve POs, manage users, access settings

### **Project Officer (Base Authority)**
- **Project-level access** only
- **Submit expenses** for own projects
- **View** all projects and expenses (own only for details)
- **Upload** project documents
- **Create comments** on accessible entities
- **Cannot**: Access financial modules (budgets, cash flow, POs, donors), approve anything, manage users, view reports

---

## **🔐 Authorization Implementation**

### **Laravel Policies (11 Files)**
1. UserPolicy.php - User management authorization
2. ProjectPolicy.php - Project CRUD operations
3. BudgetPolicy.php - Budget management & reallocation
4. ExpensePolicy.php - Expense workflow & approval
5. CashFlowPolicy.php - Cash flow transactions
6. BankAccountPolicy.php - Bank account management
7. PurchaseOrderPolicy.php - PO workflow & approval
8. DonorPolicy.php - Donor management
9. DocumentPolicy.php - Document access by parent entity
10. CommentPolicy.php - Comment ownership
11. ReportPolicy.php - Report generation & access

### **Gates (AppServiceProvider.php)**
- `manage-settings` - Programs Manager only (settings & audit trail)

### **Frontend Authorization (Sidebar.vue)**
All menu items use computed properties:
- `canAccessDashboard` - All roles
- `canAccessProjects` - All roles
- `canAccessBudgets` - PM, FO
- `canAccessExpenses` - All roles
- `canAccessCashFlow` - PM, FO
- `canAccessPurchaseOrders` - PM, FO
- `canAccessDonors` - PM, FO
- `canAccessReports` - PM, FO
- `canAccessUsers` - PM only
- `canAccessActivityLogs` - PM only
- `canAccessDocuments` - PM, PO
- `canAccessSettings` - PM only
- `canAccessAuditTrail` - PM only

---

