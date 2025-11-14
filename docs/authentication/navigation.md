# Navigation & Dashboard Components - Technical Documentation

## Overview

This document provides comprehensive technical documentation for the CANZIM FinTrack navigation and dashboard components, including the sidebar menu, dashboard layout, and role-based access control system.

---

## Table of Contents

1. [Component Architecture](#component-architecture)
2. [Components Overview](#components-overview)
3. [Role-Based Access Control](#role-based-access-control)
4. [Component Props & State](#component-props--state)
5. [Features & Functionality](#features--functionality)
6. [Usage Examples](#usage-examples)
7. [Styling & Theming](#styling--theming)
8. [Testing](#testing)
9. [Future Enhancements](#future-enhancements)

---

## Component Architecture

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     Browser Window                          │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              DashboardLayout.vue                     │  │
│  │  ┌────────────┐  ┌────────────────────────────────┐ │  │
│  │  │            │  │  Top Header Bar                │ │  │
│  │  │            │  │  - Global Search               │ │  │
│  │  │  Sidebar   │  │  - Notifications Dropdown      │ │  │
│  │  │  .vue      │  │  - User Menu Dropdown          │ │  │
│  │  │            │  └────────────────────────────────┘ │  │
│  │  │  - Logo    │  ┌────────────────────────────────┐ │  │
│  │  │  - Toggle  │  │  Breadcrumb Navigation         │ │  │
│  │  │  - Menu    │  └────────────────────────────────┘ │  │
│  │  │    Items   │  ┌────────────────────────────────┐ │  │
│  │  │  - Logout  │  │                                │ │  │
│  │  │            │  │  Main Content Area (<slot>)    │ │  │
│  │  │            │  │  - Dashboard.vue               │ │  │
│  │  │            │  │  - Other Module Components     │ │  │
│  │  │            │  │                                │ │  │
│  │  │            │  │                                │ │  │
│  │  │            │  └────────────────────────────────┘ │  │
│  │  └────────────┘                                      │  │
│  └──────────────────────────────────────────────────────┘  │
│              ▲                                              │
│              │ Uses authStore (Pinia)                      │
│              ▼                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  authStore.js (Pinia State Management)              │  │
│  │  - user.role.slug                                   │  │
│  │  - isProgramsManager, isFinanceOfficer,             │  │
│  │    isProjectOfficer computed properties             │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Component Hierarchy

```
app.js
├── Dashboard.vue (mounted on #dashboard-app)
    └── DashboardLayout.vue
        ├── Sidebar.vue
        │   └── Uses: authStore (role-based menu rendering)
        └── <slot> (Main content area)
            └── Dashboard content or other module components
```

---

## Components Overview

### 1. Sidebar.vue

**Location:** `resources/js/components/Sidebar.vue`

**Purpose:** Provides persistent left sidebar navigation with role-based menu items, collapsible functionality, and active state highlighting.

**Key Features:**

- **Collapsible sidebar** (64px collapsed, 256px expanded)
- **Role-based menu visibility** (3 roles: Programs Manager, Finance Officer, Project Officer)
- **Active route highlighting** (blue background for current page)
- **Badge counter support** (e.g., pending expenses count)
- **LocalStorage persistence** (remembers collapsed state)
- **Font Awesome icons** for all menu items
- **Responsive transitions** (300ms smooth animations)

**Props:**

```javascript
{
  pendingExpensesCount: {
    type: Number,
    default: 0
  }
}
```

**Menu Structure:**

```
Dashboard (All Roles)
─────────────────────
Financial Section
  ├── Projects (PM, FO, PO)
  ├── Budgets (PM, FO)
  ├── Expenses (PM, FO, PO) [with badge counter]
  ├── Cash Flow (PM, FO)
  ├── Purchase Orders (PM, FO)
  └── Donors (PM, FO)

Management Section
  ├── Reports (PM, FO)
  ├── Users (PM only)
  └── Documents (PM, PO)

System Section
  ├── Profile (All Roles)
  ├── Settings (PM only)
  └── Logout (All Roles)
```

**Legend:**

- PM = Programs Manager
- FO = Finance Officer
- PO = Project Officer

---

### 2. DashboardLayout.vue

**Location:** `resources/js/layouts/DashboardLayout.vue`

**Purpose:** Main dashboard shell providing the SPA container with header, breadcrumbs, and content area.

**Key Features:**

- **Top header bar** (64px height, fixed position)
    - Global search with keyboard shortcut (Ctrl+K)
    - Notifications dropdown with badge counter
    - User profile menu with avatar initials
- **Breadcrumb navigation** (auto-generated from route path)
- **Main content area** (white background, 24px padding)
- **Responsive layout** (adjusts to sidebar collapsed/expanded state)
- **Click-outside detection** (closes dropdowns when clicking elsewhere)
- **Keyboard shortcuts** (Ctrl+K for search focus)

**State Management:**

```javascript
{
  searchQuery: ref(''),
  showNotifications: ref(false),
  showUserMenu: ref(false),
  sidebarCollapsed: ref(false),
  pendingExpensesCount: ref(0),
  notificationCount: ref(0),
  notifications: ref([]),
  breadcrumbs: ref([])
}
```

**Computed Properties:**

```javascript
userName: User's display name (first word of full name)
userInitials: 2-letter initials from user's full name
userRoleName: User's role display name
```

---

### 3. Dashboard.vue

**Location:** `resources/js/pages/Dashboard.vue`

**Purpose:** Default dashboard home page with KPI cards, charts, recent activity, and quick actions.

**Key Features:**

- **Welcome message** with user's first name
- **4 KPI cards** (Total Budget, Total Expenses, Active Projects, Pending Approvals)
- **2 chart placeholders** (Budget vs Actual, Expense Trends)
- **Recent activity feed** (placeholder)
- **Quick action buttons** (role-based visibility)
    - Create Project (PM, FO)
    - Submit Expense (All Roles)
    - View Reports (PM, FO)
    - My Profile (All Roles)

**Card Color Scheme:**

- Blue: Budget-related (Total Budget)
- Red: Expense-related (Total Expenses)
- Green: Project-related (Active Projects)
- Yellow: Approval-related (Pending Approvals)

---

## Role-Based Access Control

### Role Definitions

#### 1. Programs Manager (programs-manager)

- **Full system access** to all modules
- **Menu access:** All menu items visible
- **Permissions:**
    - Create/edit/delete projects, budgets, expenses
    - Manage users and system settings
    - View all reports
    - Approve expenses (all levels)
    - Manage donors and documents

#### 2. Finance Officer (finance-officer)

- **Financial modules access**
- **Menu access:** Dashboard, Projects, Budgets, Expenses, Cash Flow, Purchase Orders, Donors, Reports, Profile
- **Permissions:**
    - View/edit projects and budgets
    - Submit and approve expenses (level 2)
    - Manage cash flow and purchase orders
    - View financial reports
    - Manage donors

#### 3. Project Officer (project-officer)

- **Project-focused access**
- **Menu access:** Dashboard, Projects (their own), Expenses (their own), Documents, Profile
- **Permissions:**
    - View assigned projects only
    - Submit expenses for their projects
    - Upload/view project documents
    - Add comments to projects

### Access Control Implementation

**Sidebar.vue:**

```javascript
// Computed properties for menu visibility
const canAccessProjects = computed(
    () =>
        isProgramsManager.value ||
        isFinanceOfficer.value ||
        isProjectOfficer.value,
);

const canAccessBudgets = computed(
    () => isProgramsManager.value || isFinanceOfficer.value,
);

const canAccessUsers = computed(() => isProgramsManager.value);
```

**Usage in Template:**

```vue
<a v-if="canAccessUsers" href="/dashboard/users">
  <!-- Users menu item -->
</a>
```

---

## Component Props & State

### Sidebar Component

**Props:**
| Prop | Type | Default | Description |
|------------------------|--------|---------|--------------------------------------|
| pendingExpensesCount | Number | 0 | Badge count for Expenses menu item |

**State:**
| State | Type | Description |
|----------------|---------|------------------------------------------|
| isCollapsed | Boolean | Sidebar collapsed state (true/false) |
| currentPath | String | Current URL path for active highlighting |

**Computed:**
| Property | Returns | Description |
|-----------------------|---------|-------------------------------------|
| userRole | String | Current user's role slug |
| isProgramsManager | Boolean | True if user is Programs Manager |
| isFinanceOfficer | Boolean | True if user is Finance Officer |
| isProjectOfficer | Boolean | True if user is Project Officer |
| canAccess[MenuItem] | Boolean | Menu item visibility permissions |

### DashboardLayout Component

**Props:** None (uses slot for content)

**State:**
| State | Type | Default | Description |
|-----------------------|---------|---------|---------------------------------------|
| searchQuery | String | '' | Global search input value |
| showNotifications | Boolean | false | Notifications dropdown visible |
| showUserMenu | Boolean | false | User menu dropdown visible |
| sidebarCollapsed | Boolean | false | Sidebar state (synced from localStorage) |
| pendingExpensesCount | Number | 0 | Pending expenses badge count |
| notificationCount | Number | 0 | Notifications badge count |
| notifications | Array | [] | List of notifications |
| breadcrumbs | Array | [] | Breadcrumb trail items |

**Computed:**
| Property | Returns | Description |
|-----------------|---------|--------------------------------------|
| userName | String | User's display name |
| userInitials | String | 2-letter initials for avatar |
| userRoleName | String | User's role display name |

---

## Features & Functionality

### 1. Collapsible Sidebar

**Behavior:**

- **Toggle button:** Positioned on the right edge of sidebar (absolute positioning)
- **Collapsed width:** 64px (shows only icons)
- **Expanded width:** 256px (shows icons + labels)
- **State persistence:** Saved to `localStorage` with key `sidebar_collapsed`
- **Smooth transitions:** 300ms CSS transition for width changes
- **Icon rotation:** Chevron icon flips direction (left/right) based on state

**Implementation:**

```javascript
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem(
        "sidebar_collapsed",
        isCollapsed.value ? "true" : "false",
    );
};
```

**CSS:**

```vue
:class="[ 'fixed left-0 top-0 h-full bg-white border-r border-gray-200
transition-all duration-300 z-50', isCollapsed ? 'w-16' : 'w-64', ]"
```

### 2. Active Route Highlighting

**Behavior:**

- **Current page:** Blue background (bg-blue-50) with blue text (text-blue-700)
- **Other pages:** Gray text (text-gray-700) with gray hover (hover:bg-gray-50)
- **Active detection:** Checks if current path starts with menu item path
- **Dashboard special case:** Exact match for `/dashboard` to avoid highlighting all items

**Implementation:**

```javascript
const isActive = (path) => {
    if (path === "/dashboard") {
        return currentPath.value === "/dashboard";
    }
    return currentPath.value.startsWith(path);
};
```

### 3. Badge Counters

**Purpose:** Display pending items count on menu items (e.g., expenses awaiting approval)

**Styling:**

- **Background:** Red (bg-red-500)
- **Text:** White, 12px, bold
- **Shape:** Rounded pill (rounded-full)
- **Padding:** 2px vertical, 6px horizontal
- **Minimum width:** 10px (for single digits)

**Usage:**

```vue
<span
    v-if="!isCollapsed && pendingExpensesCount > 0"
    class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full"
>
  {{ pendingExpensesCount }}
</span>
```

### 4. Global Search

**Features:**

- **Keyboard shortcut:** Ctrl+K to focus search input
- **Icon:** Font Awesome search icon (absolute positioned left)
- **Placeholder:** "Search... (Ctrl+K)"
- **Styling:** Rounded border with focus ring (blue)

**Implementation:**

```javascript
const handleKeyboardShortcuts = (event) => {
    if (event.ctrlKey && event.key === "k") {
        event.preventDefault();
        focusSearch();
    }
};
```

### 5. Notifications Dropdown

**Features:**

- **Bell icon** with badge counter (red circle, "9+" for counts > 9)
- **Dropdown menu:** 320px width, max-height 384px (scrollable)
- **Empty state:** "No new notifications" with bell-slash icon
- **Footer link:** "View all notifications" link to dedicated page

**Structure:**

```
┌─────────────────────────────┐
│ Notifications               │ Header
├─────────────────────────────┤
│ [Icon] Notification Title   │
│        Notification message │
│        2 hours ago          │ Notification Item
├─────────────────────────────┤
│ View all notifications →    │ Footer Link
└─────────────────────────────┘
```

### 6. User Profile Menu

**Features:**

- **Avatar:** Circular with user initials, blue background
- **User info:** Name and role (hidden on mobile)
- **Dropdown menu items:**
    - My Profile
    - Settings (Programs Manager only)
    - Messages
    - Logout (red hover state)

**Dropdown Structure:**

```
┌─────────────────────────┐
│ [Icon] My Profile       │
│ [Icon] Settings         │
│ [Icon] Messages         │
├─────────────────────────┤ Separator
│ [Icon] Logout           │ Red hover
└─────────────────────────┘
```

### 7. Breadcrumb Navigation

**Features:**

- **Auto-generation:** Created from URL path segments
- **Home icon:** Links to `/dashboard`
- **Chevron separators:** Between breadcrumb items
- **Last item:** Non-clickable, bold text
- **Hover states:** Blue text on hover for clickable items

**Algorithm:**

```javascript
const generateBreadcrumbs = () => {
    const path = window.location.pathname;
    const segments = path.split("/").filter((segment) => segment !== "");
    const filteredSegments = segments.filter(
        (segment) => segment !== "dashboard",
    );

    breadcrumbs.value = filteredSegments.map((segment, index) => {
        const path =
            "/dashboard/" + filteredSegments.slice(0, index + 1).join("/");
        const label = segment
            .split("-")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(" ");

        return { path, label };
    });
};
```

**Example:**

```
Path: /dashboard/expenses/create
Breadcrumb: Home > Expenses > Create
```

---

## Usage Examples

### 1. Creating a New Dashboard Page

**Step 1:** Create the page component

```vue
<!-- resources/js/pages/MyNewPage.vue -->
<template>
    <DashboardLayout>
        <h1>My New Page</h1>
        <p>Content goes here</p>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from "../layouts/DashboardLayout.vue";
</script>
```

**Step 2:** Add route in `web.php`

```php
Route::get('/dashboard/my-new-page', function () {
  return view('dashboard'); // Uses same blade file
})->name('dashboard.my-new-page');
```

**Step 3:** Add menu item in `Sidebar.vue`

```vue
<a
    v-if="canAccessNewPage"
    href="/dashboard/my-new-page"
    :class="[
        'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
        isActive('/dashboard/my-new-page')
            ? 'bg-blue-50 text-blue-700'
            : 'text-gray-700 hover:bg-gray-50',
    ]"
>
  <i class="fas fa-star w-5 text-center"></i>
  <span v-if="!isCollapsed">My New Page</span>
</a>
```

**Step 4:** Add permission check (if needed)

```javascript
const canAccessNewPage = computed(
    () => isProgramsManager.value || isFinanceOfficer.value,
);
```

### 2. Updating Badge Counters

**From parent component (Dashboard.vue):**

```vue
<DashboardLayout :pending-expenses-count="5">
  <!-- Content -->
</DashboardLayout>
```

**From API (recommended):**

```javascript
// In DashboardLayout.vue onMounted
onMounted(async () => {
    const response = await api.get("/api/v1/dashboard/counts");
    pendingExpensesCount.value = response.data.pending_expenses;
});
```

### 3. Adding a New Role

**Step 1:** Add role check in authStore.js

```javascript
const isNewRole = computed(() => hasRole("new-role-slug"));

return {
    // ... existing returns
    isNewRole,
};
```

**Step 2:** Update Sidebar.vue permissions

```javascript
const canAccessMenuItem = computed(
    () => isProgramsManager.value || isFinanceOfficer.value || isNewRole.value,
);
```

---

## Styling & Theming

### Color Scheme

| Usage           | Tailwind Class  | Hex Value | Description             |
| --------------- | --------------- | --------- | ----------------------- |
| Primary         | bg-blue-600     | #2563EB   | Logo, active states     |
| Primary Dark    | bg-blue-800     | #1E40AF   | Buttons, emphasis       |
| Success         | bg-green-600    | #16A34A   | Positive indicators     |
| Warning         | bg-yellow-600   | #CA8A04   | Warnings, pending items |
| Danger          | bg-red-600      | #DC2626   | Errors, delete actions  |
| Background      | bg-gray-50      | #F9FAFB   | Page background         |
| Card Background | bg-white        | #FFFFFF   | Cards, sidebar, header  |
| Text Primary    | text-gray-800   | #1F2937   | Main text content       |
| Text Secondary  | text-gray-600   | #4B5563   | Supporting text         |
| Border          | border-gray-200 | #E5E7EB   | Dividers, card borders  |

### Typography

| Element         | Font Family     | Font Size | Font Weight    | Line Height |
| --------------- | --------------- | --------- | -------------- | ----------- |
| H1 (Page Title) | Instrument Sans | 30px      | 700 (Bold)     | 1.2         |
| H2 (Section)    | Instrument Sans | 24px      | 600 (SemiBold) | 1.3         |
| H3 (Card Title) | Instrument Sans | 18px      | 600 (SemiBold) | 1.4         |
| Body Text       | Instrument Sans | 16px      | 400 (Regular)  | 1.5         |
| Small Text      | Instrument Sans | 14px      | 400 (Regular)  | 1.4         |
| Tiny Text       | Instrument Sans | 12px      | 400 (Regular)  | 1.4         |
| Menu Items      | Instrument Sans | 14px      | 500 (Medium)   | 1.4         |
| Section Labels  | Instrument Sans | 12px      | 600 (SemiBold) | 1.4         |

### Spacing System

| Token | Value | Usage Example                 |
| ----- | ----- | ----------------------------- |
| xs    | 4px   | Small gaps, icon padding      |
| sm    | 8px   | Compact spacing               |
| base  | 16px  | Standard gap between elements |
| md    | 24px  | Content padding, card spacing |
| lg    | 32px  | Section spacing               |
| xl    | 48px  | Large section spacing         |

### Responsive Breakpoints

| Breakpoint | Min Width | Max Width | Target Devices |
| ---------- | --------- | --------- | -------------- |
| sm         | 640px     | 767px     | Large phones   |
| md         | 768px     | 1023px    | Tablets        |
| lg         | 1024px    | 1279px    | Small laptops  |
| xl         | 1280px    | 1535px    | Laptops        |
| 2xl        | 1536px    | -         | Desktops       |

### Component-Specific Styling

#### Sidebar

```css
Width (Expanded): 256px (w-64)
Width (Collapsed): 64px (w-16)
Background: White (#FFFFFF)
Border: Right, 1px, Gray 200 (#E5E7EB)
Z-index: 50 (above content, below modals)
Transition: All properties, 300ms, ease
```

#### Header Bar

```css
Height: 64px (h-16)
Background: White (#FFFFFF)
Border: Bottom, 1px, Gray 200 (#E5E7EB)
Z-index: 40 (below sidebar)
Position: Fixed top
```

#### Menu Items

```css
Padding: 10px 12px (py-2.5 px-3)
Border Radius: 8px (rounded-lg)
Transition: Colors, 150ms, ease

Active State:
  Background: Blue 50 (#EFF6FF)
  Text: Blue 700 (#1D4ED8)

Hover State:
  Background: Gray 50 (#F9FAFB)
  Text: Gray 700 (#374151)
```

#### Badge Counters

```css
Background: Red 500 (#EF4444)
Text: White (#FFFFFF)
Font Size: 12px (text-xs)
Font Weight: Bold (font-semibold)
Padding: 2px 6px (py-0.5 px-2)
Border Radius: 9999px (rounded-full)
Minimum Width: 10px
```

---

## Testing

### Component Testing Strategy

#### 1. Sidebar.vue Tests

**Test File:** `tests/Feature/Dashboard/SidebarTest.php`

**Test Cases:**

```
✓ Sidebar renders with correct logo and title
✓ Sidebar shows all menu items for Programs Manager
✓ Sidebar hides restricted menu items for Finance Officer
✓ Sidebar hides restricted menu items for Project Officer
✓ Sidebar toggle button works correctly
✓ Sidebar remembers collapsed state from localStorage
✓ Sidebar highlights active menu item
✓ Pending expenses badge displays correct count
✓ Logout button triggers authStore.logout()
✓ Menu items have correct Font Awesome icons
✓ Menu sections have correct labels
✓ Collapsible state persists across page reloads
```

#### 2. DashboardLayout.vue Tests

**Test File:** `tests/Feature/Dashboard/DashboardLayoutTest.php`

**Test Cases:**

```
✓ Dashboard layout renders with header and sidebar
✓ Global search input is present
✓ Ctrl+K keyboard shortcut focuses search
✓ Notifications dropdown toggles correctly
✓ User menu dropdown toggles correctly
✓ Breadcrumbs generate correctly from URL path
✓ Sidebar width adjusts when collapsed/expanded
✓ Main content area has correct padding
✓ User initials display correctly (first + last name)
✓ User role name displays correctly
✓ Clicking outside closes dropdowns
✓ Notifications badge shows correct count
```

#### 3. Dashboard.vue Tests

**Test File:** `tests/Feature/Dashboard/DashboardTest.php`

**Test Cases:**

```
✓ Dashboard renders with welcome message
✓ Welcome message uses user's first name
✓ All 4 KPI cards render
✓ Chart placeholders display correctly
✓ Recent activity shows empty state
✓ Quick actions show correct buttons for Programs Manager
✓ Quick actions show correct buttons for Finance Officer
✓ Quick actions show correct buttons for Project Officer
✓ Quick action links navigate to correct routes
```

### Manual Testing Checklist

#### Visual Testing

- [ ] Sidebar collapses/expands smoothly (no glitches)
- [ ] Active menu item highlights correctly on page load
- [ ] Active menu item highlights correctly after navigation
- [ ] Badge counters display properly (not cut off)
- [ ] User avatar initials are centered and readable
- [ ] Breadcrumbs wrap correctly on narrow screens
- [ ] Dropdowns position correctly (no overflow)
- [ ] Hover states transition smoothly
- [ ] Icons align properly in all states

#### Functional Testing

- [ ] Sidebar state persists after browser refresh
- [ ] Ctrl+K focuses search input
- [ ] Clicking notification bell opens dropdown
- [ ] Clicking user avatar opens menu
- [ ] Clicking outside closes all dropdowns
- [ ] Logout button confirms and logs out
- [ ] All menu items navigate to correct routes
- [ ] Role-based menu items show/hide correctly
- [ ] Breadcrumbs update after route change

#### Responsive Testing

- [ ] Sidebar is accessible on mobile (drawer overlay)
- [ ] Header adapts to narrow screens (hide text, keep icons)
- [ ] User info hides on mobile (< 768px)
- [ ] Notifications dropdown fits on small screens
- [ ] Breadcrumbs truncate on narrow screens
- [ ] Touch targets are at least 44x44px
- [ ] No horizontal scrolling on any screen size

#### Accessibility Testing

- [ ] All interactive elements focusable via keyboard
- [ ] Tab order is logical (sidebar → search → notifications → user menu)
- [ ] Active menu item announced by screen reader
- [ ] Dropdowns have proper ARIA labels
- [ ] Icons have accessible titles (when text hidden)
- [ ] Color contrast meets WCAG AA standards (4.5:1 minimum)
- [ ] Focus indicators visible on all interactive elements

### Performance Testing

#### Metrics to Monitor

- **Initial Load Time:** < 2 seconds
- **Sidebar Toggle Animation:** 300ms (no jank)
- **Menu Item Hover Response:** < 50ms
- **Dropdown Open/Close:** < 100ms
- **Route Navigation:** < 500ms (client-side)

#### Performance Optimizations Implemented

- **Lazy loading:** Dashboard page lazy-loaded via Vue
- **LocalStorage caching:** Sidebar state cached client-side
- **CSS transitions:** Hardware-accelerated (transform, opacity)
- **Event delegation:** Click handlers on parent elements
- **Debounced search:** (when search API implemented)
- **Computed caching:** Role checks computed once, cached by Vue

---

## Future Enhancements

### Planned Features (Module 5+)

#### 1. Mobile Responsive Sidebar

- **Overlay drawer** on mobile screens (< 768px)
- **Hamburger menu** in top-left corner
- **Swipe gesture** to open/close sidebar
- **Backdrop overlay** (semi-transparent black) when open
- **Touch-friendly** menu items (48px minimum height)

**Implementation Notes:**

- Use `@media (max-width: 768px)` for mobile styles
- Add `transform: translateX(-100%)` for hidden state
- Use `position: fixed` with `z-index: 60` for overlay
- Add touch event listeners for swipe gestures

#### 2. Multi-Level Menu (Nested Items)

- **Expandable sections** (e.g., Projects > My Projects, All Projects)
- **Chevron icons** to indicate expandable items
- **Smooth accordion animation** when expanding/collapsing
- **Persist expanded state** in localStorage

**Example Structure:**

```
Projects ▼
  ├── All Projects
  ├── My Projects
  └── Archived Projects
```

#### 3. Search Functionality

- **Real-time search** across all modules
- **Quick navigation** to search results
- **Keyboard shortcuts** (Ctrl+K already implemented)
- **Search suggestions** dropdown
- **Recent searches** (localStorage)

**Features:**

- Debounced input (300ms delay)
- Search projects, expenses, documents, users
- Highlight matching text in results
- "See all results" link to dedicated search page

#### 4. Notification System

- **Real-time notifications** via WebSockets (Laravel Echo + Pusher)
- **Mark as read/unread** functionality
- **Notification categories** (Expense Approvals, Comments, Mentions)
- **Desktop notifications** (browser push API)
- **Email digest** option (daily/weekly summary)

**Notification Types:**

- Expense submitted for approval
- Expense approved/rejected
- Comment added to project
- Budget threshold exceeded (80%, 100%)
- New document uploaded
- User mentioned in comment

#### 5. Customizable Dashboard

- **Drag-and-drop widgets** (KPI cards, charts)
- **User preferences** (save layout in database)
- **Widget library** (add/remove widgets)
- **Dashboard templates** (default layouts per role)

**Widgets:**

- KPI cards (customizable metrics)
- Charts (line, bar, pie, donut)
- Recent activity feed
- Calendar/events
- Quick actions
- Announcements

#### 6. Favorites/Shortcuts

- **Pin favorite pages** to top of sidebar
- **Custom menu order** (drag-and-drop)
- **Quick links section** (user-defined)
- **Keyboard shortcuts** (customizable)

**Implementation:**

- Add "star" icon next to menu items
- Store favorites in database (user_preferences table)
- Display favorites section at top of sidebar
- Allow drag-and-drop reordering

#### 7. Dark Mode Support

- **Toggle switch** in user menu or settings
- **System preference detection** (prefers-color-scheme)
- **Smooth transition** between themes (CSS variables)
- **Persist choice** in localStorage or database

**Color Scheme (Dark Mode):**

- Background: Gray 900 (#111827)
- Sidebar: Gray 800 (#1F2937)
- Text: Gray 100 (#F3F4F6)
- Border: Gray 700 (#374151)
- Active: Blue 500 (#3B82F6)

#### 8. Multi-Tenancy Support

- **Organization switcher** in header
- **Separate data** per organization
- **Shared user accounts** (one user, multiple orgs)
- **Role per organization** (admin in one, user in another)

#### 9. Activity Timeline

- **User activity log** in sidebar footer
- **Recent actions** (last 5 items)
- **Timestamps** (e.g., "2 minutes ago")
- **Action icons** (created, updated, deleted)

**Example:**

```
Recent Activity
───────────────
📄 Created expense "Office Supplies"
   2 minutes ago

✓ Approved expense "Travel Reimbursement"
   15 minutes ago

📝 Updated project "Clean Water Initiative"
   1 hour ago
```

#### 10. Tour/Onboarding

- **Interactive tour** for new users
- **Step-by-step guide** (highlight elements)
- **Skip option** (don't show again)
- **Role-specific tours** (different guides per role)

**Tour Steps:**

1. Welcome message
2. Sidebar navigation overview
3. Global search demo
4. Notifications demo
5. Quick actions demo
6. First task (e.g., "Create your first project")

---

## Troubleshooting

### Common Issues & Solutions

#### Issue 1: Sidebar Not Collapsing

**Symptoms:** Toggle button doesn't work, sidebar stuck in one state

**Solutions:**

1. Check localStorage for corrupted value:
    ```javascript
    localStorage.removeItem("sidebar_collapsed");
    ```
2. Clear browser cache and reload
3. Check console for Vue reactivity errors
4. Verify `isCollapsed` ref is properly initialized

#### Issue 2: Active Menu Item Not Highlighting

**Symptoms:** Menu items don't highlight when on corresponding page

**Solutions:**

1. Check `currentPath.value` matches `window.location.pathname`
2. Verify route exists in `isActive()` logic
3. Check for trailing slashes in path (normalize paths)
4. Ensure CSS classes apply correctly (inspect element)

#### Issue 3: Badge Counter Not Updating

**Symptoms:** Pending expenses count shows 0 or stale value

**Solutions:**

1. Verify `pendingExpensesCount` prop is passed to Sidebar
2. Check API endpoint returns correct count
3. Ensure prop is reactive (ref/computed)
4. Add console.log to debug prop value:
    ```javascript
    watchEffect(() => console.log("Count:", props.pendingExpensesCount));
    ```

#### Issue 4: Dropdowns Not Closing

**Symptoms:** Notifications/user menu stays open after clicking outside

**Solutions:**

1. Check `handleClickOutside` is attached to document:
    ```javascript
    document.addEventListener("click", handleClickOutside);
    ```
2. Verify event listener is removed on unmount
3. Ensure dropdown refs are properly scoped
4. Add `@click.stop` to dropdown content to prevent closing when clicking inside

#### Issue 5: Role-Based Menu Not Working

**Symptoms:** Users see menu items they shouldn't have access to

**Solutions:**

1. Verify authStore has correct user role:
    ```javascript
    console.log(authStore.userRole);
    ```
2. Check `v-if` conditions on menu items
3. Ensure user object has `role.slug` property
4. Re-fetch user profile if role changed:
    ```javascript
    await authStore.fetchProfile();
    ```

#### Issue 6: Breadcrumbs Not Generating

**Symptoms:** Breadcrumbs empty or show wrong path

**Solutions:**

1. Check `window.location.pathname` is correct
2. Verify `generateBreadcrumbs()` is called on mount and route change
3. Ensure path segments are properly filtered (remove 'dashboard')
4. Check capitalization logic for labels

#### Issue 7: Keyboard Shortcuts Not Working

**Symptoms:** Ctrl+K doesn't focus search

**Solutions:**

1. Verify event listener is attached:
    ```javascript
    document.addEventListener("keydown", handleKeyboardShortcuts);
    ```
2. Check `event.preventDefault()` is called
3. Ensure input element exists in DOM
4. Use `document.querySelector('input[type="text"]')` selector

---

## API Integration

### Expected API Endpoints (Future Implementation)

#### 1. Dashboard Counts

**GET** `/api/v1/dashboard/counts`

**Response:**

```json
{
    "status": "success",
    "data": {
        "pending_expenses": 5,
        "pending_approvals": 3,
        "active_projects": 12,
        "unread_notifications": 7
    }
}
```

#### 2. Notifications

**GET** `/api/v1/notifications?limit=5`

**Response:**

```json
{
    "status": "success",
    "data": {
        "notifications": [
            {
                "id": 1,
                "title": "Expense Approved",
                "message": "Your expense 'Office Supplies' has been approved.",
                "time": "2 hours ago",
                "read": false,
                "url": "/dashboard/expenses/123"
            }
        ],
        "unread_count": 7
    }
}
```

#### 3. Mark Notification as Read

**POST** `/api/v1/notifications/{id}/read`

**Response:**

```json
{
    "status": "success",
    "message": "Notification marked as read"
}
```

#### 4. Search

**GET** `/api/v1/search?q={query}&limit=10`

**Response:**

```json
{
    "status": "success",
    "data": {
        "results": [
            {
                "type": "project",
                "id": 1,
                "title": "Clean Water Initiative",
                "description": "Providing clean water to rural communities",
                "url": "/dashboard/projects/1"
            }
        ]
    }
}
```

---

## Conclusion

The CANZIM FinTrack navigation and dashboard components provide a robust, role-based, and user-friendly interface for the financial management system. This documentation covers all aspects of the implementation, from component architecture to future enhancements, ensuring maintainability and scalability as the system grows.

For questions or issues, please refer to the troubleshooting section or contact the development team.

**Last Updated:** {{ current_date }}
**Version:** 1.0.0
**Author:** CANZIM Development Team
