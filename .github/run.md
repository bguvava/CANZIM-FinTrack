# Terminal 1: Laravel backend
cd C:\xampp\htdocs\CANZIM
$env:Path = "C:\xampp\php;" + $env:Path
php artisan serve

# Terminal 2: Vite frontend
cd C:\xampp\htdocs\CANZIM
npm run dev
---
php artisan migrate:fresh --seed



#### 1. **Test Users Created** 
| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Programs Manager** | `programs-manager@test.com` | `password123` | Full system access |
| **Finance Officer** | `finance-officer@test.com` | `password123` | Financial modules only |
| **Project Officer** | `project-officer@test.com` | `password123` | Project modules only |

**Additional Admin User:**
- **Email:** `admin@canzim.org.zw`
- **Password:** `canzim@2025`
- **Role:** Programs Manager (full access)


#### **Role-Based Navigation**
The sidebar menu will show different items based on the logged-in user's role:

**Programs Manager sees:**
- Dashboard, Projects, Budgets, Expenses, Cash Flow, Purchase Orders, Donors, Reports, Users, Documents, Settings, Profile, Logout

**Finance Officer sees:**
- Dashboard, Projects, Budgets, Expenses, Cash Flow, Purchase Orders, Donors, Reports, Profile, Logout

**Project Officer sees:**
- Dashboard, Projects, Expenses, Documents, Profile, Logout

---
Dashboard (All Roles)
├── Financial Section
│   ├── Projects (PM, FO, PO)
│   ├── Budgets (PM, FO)
│   ├── Expenses (PM, FO, PO) [with badge]
│   ├── Cash Flow (PM, FO)
│   ├── Purchase Orders (PM, FO)
│   └── Donors (PM, FO)
├── Management Section
│   ├── Reports (PM, FO)
│   ├── Users (PM only)
│   └── Documents (PM, PO)
└── System Section
    ├── Profile (All)
    ├── Settings (PM only)
    └── Logout (All)


