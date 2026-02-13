<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CANZIM FinTrack - Help & Support Manual</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --primary: #1E40AF;
            --primary-light: #3B82F6;
            --primary-lighter: #DBEAFE;
            --primary-dark: #1E3A8A;
            --accent: #059669;
            --accent-light: #D1FAE5;
            --warning: #D97706;
            --warning-light: #FEF3C7;
            --danger: #DC2626;
            --danger-light: #FEE2E2;
            --text: #1F2937;
            --text-muted: #6B7280;
            --bg: #F9FAFB;
            --white: #FFFFFF;
            --border: #E5E7EB;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
        }

        /* Top Bar */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: var(--primary);
            color: white;
            padding: 0 2rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .topbar h1 {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .topbar h1 i {
            font-size: 1.5rem;
        }

        .topbar .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
            transition: opacity 0.2s;
        }

        .topbar .back-link:hover {
            opacity: 1;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--white);
            border-right: 1px solid var(--border);
            overflow-y: auto;
            padding: 1.5rem 0;
            z-index: 50;
        }

        .sidebar-section {
            margin-bottom: 1.5rem;
        }

        .sidebar-section-title {
            padding: 0 1.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 1.25rem;
            color: var(--text);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.15s;
            border-left: 3px solid transparent;
        }

        .sidebar a:hover {
            background: var(--primary-lighter);
            color: var(--primary);
            border-left-color: var(--primary-light);
        }

        .sidebar a.active {
            background: var(--primary-lighter);
            color: var(--primary);
            font-weight: 600;
            border-left-color: var(--primary);
        }

        .sidebar a i {
            width: 20px;
            text-align: center;
            font-size: 0.85rem;
        }

        /* Search */
        .sidebar-search {
            padding: 0 1rem 1rem;
        }

        .sidebar-search input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.85rem;
            outline: none;
        }

        .sidebar-search input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Main Content */
        .main {
            margin-left: var(--sidebar-width);
            margin-top: 60px;
            padding: 2rem 3rem 4rem;
            max-width: 900px;
        }

        /* Section Styling */
        .section {
            margin-bottom: 3rem;
            scroll-margin-top: 80px;
        }

        .section h2 {
            font-size: 1.75rem;
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section h2 i {
            font-size: 1.5rem;
        }

        .section h3 {
            font-size: 1.25rem;
            color: var(--primary-dark);
            margin: 1.5rem 0 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section h3 i {
            font-size: 1rem;
            color: var(--primary-light);
        }

        .section h4 {
            font-size: 1.05rem;
            color: var(--text);
            margin: 1rem 0 0.5rem;
            font-weight: 600;
        }

        .section p {
            margin-bottom: 0.75rem;
        }

        .section ul,
        .section ol {
            margin: 0.5rem 0 1rem 1.5rem;
        }

        .section li {
            margin-bottom: 0.35rem;
        }

        /* Info Boxes */
        .info-box {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin: 1rem 0;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .info-box i {
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .info-box.tip {
            background: var(--accent-light);
            border-left: 4px solid var(--accent);
        }

        .info-box.tip i {
            color: var(--accent);
        }

        .info-box.warning {
            background: var(--warning-light);
            border-left: 4px solid var(--warning);
        }

        .info-box.warning i {
            color: var(--warning);
        }

        .info-box.danger {
            background: var(--danger-light);
            border-left: 4px solid var(--danger);
        }

        .info-box.danger i {
            color: var(--danger);
        }

        .info-box.info {
            background: var(--primary-lighter);
            border-left: 4px solid var(--primary);
        }

        .info-box.info i {
            color: var(--primary);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.9rem;
        }

        table th {
            background: var(--primary);
            color: white;
            padding: 0.6rem 0.75rem;
            text-align: left;
            font-weight: 600;
        }

        table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        table tr:hover td {
            background: #F3F4F6;
        }

        /* Steps */
        .steps {
            counter-reset: step;
            margin: 1rem 0;
        }

        .step {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: flex-start;
        }

        .step::before {
            counter-increment: step;
            content: counter(step);
            background: var(--primary);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .step-content {
            flex: 1;
        }

        .step-content strong {
            color: var(--primary-dark);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-pm {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .badge-fo {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-po {
            background: #FEF3C7;
            color: #92400E;
        }

        /* Workflow Diagram */
        .workflow {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin: 1rem 0;
        }

        .workflow-step {
            background: var(--primary-lighter);
            color: var(--primary);
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .workflow-arrow {
            color: var(--text-muted);
        }

        /* Keyboard shortcuts */
        kbd {
            background: #F3F4F6;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 0.1rem 0.4rem;
            font-family: monospace;
            font-size: 0.85rem;
        }

        /* Print */
        @media print {

            .sidebar,
            .topbar {
                display: none;
            }

            .main {
                margin: 0;
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main {
                margin-left: 0;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <!-- Top Bar -->
    <div class="topbar">
        <h1><i class="fas fa-book-open"></i> CANZIM FinTrack &mdash; User Manual</h1>
        <a href="/dashboard" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-search">
            <input type="text" id="search" placeholder="Search topics..." oninput="filterNav(this.value)">
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Getting Started</div>
            <a href="#overview"><i class="fas fa-info-circle"></i> System Overview</a>
            <a href="#roles"><i class="fas fa-user-shield"></i> User Roles</a>
            <a href="#login"><i class="fas fa-sign-in-alt"></i> Logging In</a>
            <a href="#navigation"><i class="fas fa-compass"></i> Navigation Guide</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Core Modules</div>
            <a href="#dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="#projects"><i class="fas fa-folder"></i> Projects</a>
            <a href="#budgets"><i class="fas fa-calculator"></i> Budget Management</a>
            <a href="#expenses"><i class="fas fa-receipt"></i> Expense Management</a>
            <a href="#cashflow"><i class="fas fa-money-bill-wave"></i> Cash Flow</a>
            <a href="#purchase-orders"><i class="fas fa-shopping-cart"></i> Purchase Orders</a>
            <a href="#donors"><i class="fas fa-hands-helping"></i> Donor Management</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Tools & Admin</div>
            <a href="#reports"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="#documents"><i class="fas fa-file-alt"></i> Documents</a>
            <a href="#comments"><i class="fas fa-comments"></i> Comments</a>
            <a href="#users"><i class="fas fa-users-cog"></i> User Management</a>
            <a href="#activity-logs"><i class="fas fa-history"></i> Activity Logs</a>
            <a href="#profile"><i class="fas fa-user-circle"></i> Profile Settings</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Help</div>
            <a href="#faq"><i class="fas fa-question-circle"></i> FAQ</a>
            <a href="#troubleshooting"><i class="fas fa-tools"></i> Troubleshooting</a>
            <a href="#support"><i class="fas fa-headset"></i> Contact Support</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main">

        <!-- OVERVIEW -->
        <div class="section" id="overview">
            <h2><i class="fas fa-info-circle"></i> System Overview</h2>
            <p><strong>CANZIM FinTrack</strong> is the Financial Management & Accounting platform for <strong>Climate
                    Action Network Zimbabwe</strong>. It provides end-to-end tracking of project budgets, expenses, cash
                flow, purchase orders, donor funding, and reporting.</p>

            <h3><i class="fas fa-star"></i> Key Features</h3>
            <ul>
                <li><strong>Project & Budget Management</strong> &mdash; Create projects, allocate budgets with line
                    items, track utilization in real-time</li>
                <li><strong>Expense Tracking</strong> &mdash; Submit, review, and approve expenses with 3-tier approval
                    workflow</li>
                <li><strong>Cash Flow Management</strong> &mdash; Manage bank accounts, record inflows/outflows,
                    reconcile transactions</li>
                <li><strong>Purchase Orders</strong> &mdash; Create POs with vendor management and item-level receiving
                </li>
                <li><strong>Donor Management</strong> &mdash; Track donor contributions, in-kind gifts, and
                    communication history</li>
                <li><strong>Reporting</strong> &mdash; Generate professional PDF reports: Budget vs Actual, Cash Flow,
                    Expense Summary, Donor Contributions, and more</li>
                <li><strong>Document Management</strong> &mdash; Attach, version, and organize documents across all
                    modules</li>
                <li><strong>Collaboration</strong> &mdash; Comment on projects, budgets, and expenses with @mentions and
                    file attachments</li>
            </ul>
        </div>

        <!-- ROLES -->
        <div class="section" id="roles">
            <h2><i class="fas fa-user-shield"></i> User Roles & Permissions</h2>
            <p>CANZIM FinTrack uses three distinct roles, each with specific capabilities:</p>

            <table>
                <thead>
                    <tr>
                        <th>Module</th>
                        <th><span class="badge badge-pm">Programs Manager</span></th>
                        <th><span class="badge badge-fo">Finance Officer</span></th>
                        <th><span class="badge badge-po">Project Officer</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Dashboard</td>
                        <td>Full org overview + 4 charts</td>
                        <td>Financial overview + 2 charts</td>
                        <td>Project-focused view</td>
                    </tr>
                    <tr>
                        <td>Projects</td>
                        <td>Full CRUD, assign teams/donors</td>
                        <td>View, financial details</td>
                        <td>View assigned projects only</td>
                    </tr>
                    <tr>
                        <td>Budgets</td>
                        <td>Create, approve, reallocate</td>
                        <td>View, track utilization</td>
                        <td>View assigned project budgets</td>
                    </tr>
                    <tr>
                        <td>Expenses</td>
                        <td>Final approval (Tier 2)</td>
                        <td>Review &amp; first approval (Tier 1), mark paid</td>
                        <td>Create, submit, edit drafts</td>
                    </tr>
                    <tr>
                        <td>Cash Flow</td>
                        <td>Full access</td>
                        <td>Full access</td>
                        <td>No access</td>
                    </tr>
                    <tr>
                        <td>Purchase Orders</td>
                        <td>Approve/reject POs</td>
                        <td>Create POs, receive items</td>
                        <td>Create POs for own projects</td>
                    </tr>
                    <tr>
                        <td>Donors</td>
                        <td>Full CRUD</td>
                        <td>View, generate reports</td>
                        <td>No access</td>
                    </tr>
                    <tr>
                        <td>Reports</td>
                        <td>All report types</td>
                        <td>All report types</td>
                        <td>No access</td>
                    </tr>
                    <tr>
                        <td>Documents</td>
                        <td>Full access</td>
                        <td>Financial documents</td>
                        <td>Own project documents</td>
                    </tr>
                    <tr>
                        <td>User Management</td>
                        <td>Full CRUD</td>
                        <td>No access</td>
                        <td>No access</td>
                    </tr>
                    <tr>
                        <td>Activity Logs</td>
                        <td>View &amp; manage</td>
                        <td>No access</td>
                        <td>No access</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- LOGIN -->
        <div class="section" id="login">
            <h2><i class="fas fa-sign-in-alt"></i> Logging In</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-content"><strong>Navigate to the login page</strong> &mdash; Open your browser and
                        go to the CANZIM FinTrack URL provided by your administrator.</div>
                </div>
                <div class="step">
                    <div class="step-content"><strong>Enter your credentials</strong> &mdash; Type your email address
                        and password. Check <em>"Remember Me"</em> for extended sessions (30 days).</div>
                </div>
                <div class="step">
                    <div class="step-content"><strong>Click "Login"</strong> &mdash; You will be redirected to your
                        role-specific dashboard.</div>
                </div>
            </div>

            <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div><strong>Account Lockout:</strong> After 5 failed login attempts, your account will be locked for 15
                    minutes. Contact your Programs Manager if you need immediate access.</div>
            </div>

            <h3><i class="fas fa-key"></i> Forgot Password</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Click <strong>"Forgot Password?"</strong> on the login page.</div>
                </div>
                <div class="step">
                    <div class="step-content">Enter the email address associated with your account.</div>
                </div>
                <div class="step">
                    <div class="step-content">Check your email for a password reset link (valid for 60 minutes).</div>
                </div>
                <div class="step">
                    <div class="step-content">Click the link, enter your new password (minimum 8 characters), and
                        confirm.</div>
                </div>
            </div>
        </div>

        <!-- NAVIGATION -->
        <div class="section" id="navigation">
            <h2><i class="fas fa-compass"></i> Navigation Guide</h2>
            <p>The interface consists of three main areas:</p>
            <ul>
                <li><strong>Sidebar (Left)</strong> &mdash; Main navigation menu with role-specific items. Click the
                    toggle arrow to collapse/expand. Menus are organized into sections: Financial, Management, and
                    Account.</li>
                <li><strong>Top Bar (Header)</strong> &mdash; Shows your name, role badge, notification bell, and user
                    dropdown menu with Profile, Help &amp; Support, and Logout options.</li>
                <li><strong>Breadcrumb Bar</strong> &mdash; Shows your current location in the application. Click any
                    breadcrumb to navigate back to that section.</li>
            </ul>

            <div class="info-box tip">
                <i class="fas fa-lightbulb"></i>
                <div><strong>Tip:</strong> Collapse the sidebar by clicking the chevron arrow on its right edge. This
                    gives you more screen space for data-heavy pages like reports and budgets.</div>
            </div>
        </div>

        <!-- DASHBOARD -->
        <div class="section" id="dashboard">
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
            <p>The dashboard is your home screen that auto-refreshes every 30 seconds with real-time financial data.
                Each role has a tailored view:</p>

            <h3><i class="fas fa-user-tie"></i> Programs Manager Dashboard</h3>
            <ul>
                <li><strong>Summary Cards:</strong> Total Budget, Year-to-Date Spending, Available Funds, Pending
                    Approvals</li>
                <li><strong>Charts:</strong> Budget Utilization (donut), Expense Trends (line), Donor Allocation (bar),
                    Cash Flow Projection (line)</li>
                <li><strong>Quick Actions:</strong> Access pending expense approvals, budget requests, and purchase
                    orders needing attention</li>
            </ul>

            <h3><i class="fas fa-user-tie"></i> Finance Officer Dashboard</h3>
            <ul>
                <li><strong>Summary Cards:</strong> Monthly Budget, Actual Expenses, Pending Expenses, Cash Balance</li>
                <li><strong>Charts:</strong> Budget vs Actual (bar), Expense Categories (pie)</li>
                <li><strong>Quick Actions:</strong> Pending expense reviews, cash flow actions</li>
            </ul>

            <h3><i class="fas fa-user"></i> Project Officer Dashboard</h3>
            <ul>
                <li><strong>Summary Cards:</strong> Project Budget, Budget Used, Remaining Budget, Activities Completed
                </li>
                <li><strong>Recent Activity:</strong> Your latest expense submissions and project updates</li>
            </ul>
        </div>

        <!-- PROJECTS -->
        <div class="section" id="projects">
            <h2><i class="fas fa-folder"></i> Project Management</h2>
            <p>Projects are the core organizational unit. Each project has a budget, team, donors, and associated
                financial records.</p>

            <h3><i class="fas fa-plus-circle"></i> Creating a Project</h3>
            <p><span class="badge badge-pm">Programs Manager</span></p>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Projects</strong> from the sidebar.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Add Project"</strong> in the top right.</div>
                </div>
                <div class="step">
                    <div class="step-content">Fill in project details: Name, Description, Start Date, End Date, Status
                        (Planning/Active/On Hold).</div>
                </div>
                <div class="step">
                    <div class="step-content">A unique project code (e.g., PROJ-2025-0001) is auto-generated.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Create Project"</strong> to save.</div>
                </div>
            </div>

            <h3><i class="fas fa-users"></i> Assigning Team & Donors</h3>
            <p>From the project detail page:</p>
            <ul>
                <li><strong>Team Members:</strong> Click "Manage Team" to add or remove users assigned to the project.
                </li>
                <li><strong>Donors:</strong> Click "Assign Donor" to link donors with funding amounts, period dates, and
                    restricted/unrestricted designation.</li>
            </ul>

            <h3><i class="fas fa-list"></i> Project Status Lifecycle</h3>
            <div class="workflow">
                <span class="workflow-step">Planning</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Active</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">On Hold</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Completed / Cancelled</span>
            </div>
        </div>

        <!-- BUDGETS -->
        <div class="section" id="budgets">
            <h2><i class="fas fa-calculator"></i> Budget Management</h2>
            <p>Budgets are tied to projects and consist of line items organized by category.</p>

            <h3><i class="fas fa-plus-circle"></i> Creating a Budget</h3>
            <p><span class="badge badge-pm">Programs Manager</span></p>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Budgets</strong> from the sidebar.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Create Budget"</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Select the project and fiscal year. Enter budget title and description.
                    </div>
                </div>
                <div class="step">
                    <div class="step-content">Add line items. For each: select category (Travel, Salaries, Procurement,
                        Consultants, Other), enter description, quantity, and unit cost. The total is auto-calculated.
                    </div>
                </div>
                <div class="step">
                    <div class="step-content">Submit the budget for approval. It enters <strong>Pending</strong>
                        status.</div>
                </div>
            </div>

            <h3><i class="fas fa-check-double"></i> Budget Approval</h3>
            <p><span class="badge badge-pm">Programs Manager</span></p>
            <p>From the budget detail page, review items and click <strong>"Approve"</strong> or
                <strong>"Reject"</strong> (with reason).</p>

            <h3><i class="fas fa-chart-pie"></i> Budget Utilization Tracking</h3>
            <p>The system automatically tracks budget utilization in real-time:</p>
            <ul>
                <li><strong style="color: #059669;">&lt; 50% used</strong> &mdash; Green indicator (healthy)</li>
                <li><strong style="color: #D97706;">50% &ndash; 90% used</strong> &mdash; Yellow indicator (caution)
                </li>
                <li><strong style="color: #DC2626;">&gt; 90% used</strong> &mdash; Red indicator (critical)</li>
            </ul>

            <h3><i class="fas fa-exchange-alt"></i> Budget Reallocations</h3>
            <p><span class="badge badge-pm">Programs Manager</span></p>
            <p>Move funds between budget categories. From the budget view, click <strong>"Reallocate"</strong>, select
                source and destination categories, enter the amount and reason.</p>

            <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div><strong>Important:</strong> Total budget cannot exceed the project's total donor funding. The
                    system will prevent over-allocation.</div>
            </div>
        </div>

        <!-- EXPENSES -->
        <div class="section" id="expenses">
            <h2><i class="fas fa-receipt"></i> Expense Management</h2>
            <p>The expense module features a 3-tier approval workflow ensuring proper financial oversight.</p>

            <h3><i class="fas fa-plus-circle"></i> Creating an Expense</h3>
            <p><span class="badge badge-po">Project Officer</span></p>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Expenses</strong> from the sidebar and click
                        <strong>"Create Expense"</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Select the <strong>Project</strong> and <strong>Budget Item</strong> the
                        expense relates to.</div>
                </div>
                <div class="step">
                    <div class="step-content">Choose an <strong>Expense Category</strong> (Travel, Accommodation,
                        Supplies, Communication, etc.).</div>
                </div>
                <div class="step">
                    <div class="step-content">Enter the <strong>amount</strong>, <strong>date</strong>, and
                        <strong>description</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Upload a <strong>receipt</strong> (PDF, JPG, or PNG, max 5MB).</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Save as Draft"</strong> to save for later, or
                        <strong>"Submit"</strong> to send for review.</div>
                </div>
            </div>

            <h3><i class="fas fa-tasks"></i> Expense Approval Workflow</h3>
            <div class="workflow">
                <span class="workflow-step">Draft</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Submitted</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Under Review</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Reviewed</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Approved</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Paid</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Stage</th>
                        <th>Who</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Draft</td>
                        <td><span class="badge badge-po">Project Officer</span></td>
                        <td>Create and edit the expense. Click "Submit" when ready.</td>
                    </tr>
                    <tr>
                        <td>Submitted &rarr; Under Review</td>
                        <td><span class="badge badge-fo">Finance Officer</span></td>
                        <td>Reviews expense details, receipt, and budget linkage. Approves or rejects.</td>
                    </tr>
                    <tr>
                        <td>Reviewed &rarr; Approved</td>
                        <td><span class="badge badge-pm">Programs Manager</span></td>
                        <td>Final approval. Approves or rejects with reason.</td>
                    </tr>
                    <tr>
                        <td>Approved &rarr; Paid</td>
                        <td><span class="badge badge-fo">Finance Officer</span></td>
                        <td>Processes payment, marks as paid. Cash outflow recorded automatically.</td>
                    </tr>
                    <tr>
                        <td>Rejected</td>
                        <td>Original submitter</td>
                        <td>Can edit and resubmit the expense with corrections.</td>
                    </tr>
                </tbody>
            </table>

            <div class="info-box tip">
                <i class="fas fa-lightbulb"></i>
                <div><strong>Tip:</strong> You can edit and resubmit a <strong>Draft</strong> expense at any time. Once
                    submitted, only the assigned reviewer can take action.</div>
            </div>
        </div>

        <!-- CASH FLOW -->
        <div class="section" id="cashflow">
            <h2><i class="fas fa-money-bill-wave"></i> Cash Flow Management</h2>
            <p><span class="badge badge-pm">Programs Manager</span> <span class="badge badge-fo">Finance
                    Officer</span></p>

            <h3><i class="fas fa-university"></i> Bank Accounts</h3>
            <p>Manage multiple bank accounts with multi-currency support (USD, EUR, GBP, ZMW).</p>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Cash Flow &gt; Bank Accounts</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Add Bank Account"</strong> and enter: Account Name,
                        Account Number, Bank Name, Currency, Opening Balance.</div>
                </div>
                <div class="step">
                    <div class="step-content">The account is now active and ready for transactions.</div>
                </div>
            </div>

            <h3><i class="fas fa-arrow-down"></i> Recording Inflows</h3>
            <p>Record money coming in (donations, grants, interest):</p>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Go to <strong>Cash Flow &gt; Transactions</strong> and click
                        <strong>"Record Inflow"</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Select the bank account, enter the amount, date, category, and
                        description.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Record"</strong>. The bank balance updates automatically.
                    </div>
                </div>
            </div>

            <h3><i class="fas fa-arrow-up"></i> Recording Outflows</h3>
            <p>Record money going out (payments, transfers, operational costs). The process is the same as inflows but
                uses outflow categories.</p>

            <div class="info-box danger">
                <i class="fas fa-exclamation-circle"></i>
                <div><strong>Negative Balance Prevention:</strong> The system will block any outflow that would cause a
                    bank account balance to go below zero.</div>
            </div>

            <h3><i class="fas fa-check-double"></i> Bank Reconciliation</h3>
            <p>Mark transactions as reconciled after verifying against bank statements. Use filter buttons to view
                reconciled, unreconciled, or all transactions.</p>

            <h3><i class="fas fa-chart-line"></i> Cash Flow Projections</h3>
            <p>View 3&ndash;12 month financial forecasts based on 6 months of historical averages. The projection chart
                shows expected inflows, outflows, and net cash position.</p>
        </div>

        <!-- PURCHASE ORDERS -->
        <div class="section" id="purchase-orders">
            <h2><i class="fas fa-shopping-cart"></i> Purchase Orders</h2>

            <h3><i class="fas fa-store"></i> Managing Vendors</h3>
            <p><span class="badge badge-fo">Finance Officer</span></p>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Purchase Orders &gt; Vendors</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Add Vendor"</strong>. Enter vendor name, contact details,
                        payment terms.</div>
                </div>
                <div class="step">
                    <div class="step-content">A vendor code (VEN-YYYY-NNNN) is auto-generated.</div>
                </div>
            </div>

            <h3><i class="fas fa-file-invoice"></i> Creating a Purchase Order</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Purchase Orders</strong> and click <strong>"Create
                            Purchase Order"</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Select <strong>Vendor</strong>, <strong>Project</strong>, delivery date,
                        and payment terms.</div>
                </div>
                <div class="step">
                    <div class="step-content">Add line items: description, quantity, unit price. Tax is
                        auto-calculated.</div>
                </div>
                <div class="step">
                    <div class="step-content"><strong>"Save as Draft"</strong> or <strong>"Submit for
                            Approval"</strong>.</div>
                </div>
            </div>

            <h3><i class="fas fa-tasks"></i> PO Approval Workflow</h3>
            <div class="workflow">
                <span class="workflow-step">Draft</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Pending Approval</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Approved</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Partially Received</span>
                <i class="fas fa-arrow-right workflow-arrow"></i>
                <span class="workflow-step">Received / Completed</span>
            </div>
            <p><span class="badge badge-pm">Programs Manager</span> reviews and approves or rejects. Once approved,
                <span class="badge badge-fo">Finance Officer</span> receives items (partial or full). Over-receiving is
                prevented by the system.</p>

            <div class="info-box info">
                <i class="fas fa-info-circle"></i>
                <div><strong>Auto-numbering:</strong> Purchase orders are automatically numbered PO-YYYY-NNNN (e.g.,
                    PO-2025-0012). No manual entry needed.</div>
            </div>
        </div>

        <!-- DONORS -->
        <div class="section" id="donors">
            <h2><i class="fas fa-hands-helping"></i> Donor Management</h2>
            <p><span class="badge badge-pm">Programs Manager</span> Full access &nbsp; | &nbsp; <span
                    class="badge badge-fo">Finance Officer</span> View &amp; reports</p>

            <h3><i class="fas fa-plus-circle"></i> Adding a Donor</h3>
            <p><span class="badge badge-pm">Programs Manager</span></p>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Donors</strong> from the sidebar.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Add Donor"</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Enter donor details: Name, Contact Person, Email, Phone, Address, Tax ID,
                        Website.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Create Donor"</strong>.</div>
                </div>
            </div>

            <h3><i class="fas fa-link"></i> Linking Donors to Projects</h3>
            <p>From the donor detail view or the project detail view, you can assign donors to projects with:</p>
            <ul>
                <li><strong>Funding Amount</strong> &mdash; How much the donor is contributing</li>
                <li><strong>Funding Period</strong> &mdash; Start and end dates</li>
                <li><strong>Restricted/Unrestricted</strong> &mdash; Whether funds are earmarked for specific activities
                </li>
            </ul>

            <h3><i class="fas fa-gift"></i> In-Kind Contributions</h3>
            <p>Record non-monetary contributions. From the donor detail view, click <strong>"Add In-Kind
                    Contribution"</strong> and enter the item description, estimated value, and date.</p>

            <h3><i class="fas fa-phone"></i> Communication Log</h3>
            <p>Track all interactions with donors. Click <strong>"Log Communication"</strong> and select the type
                (Email, Phone, Meeting, Letter), enter notes, and optionally attach files.</p>
        </div>

        <!-- REPORTS -->
        <div class="section" id="reports">
            <h2><i class="fas fa-chart-bar"></i> Reports & Analytics</h2>
            <p><span class="badge badge-pm">Programs Manager</span> <span class="badge badge-fo">Finance
                    Officer</span></p>

            <h3><i class="fas fa-file-pdf"></i> Available Report Types</h3>
            <table>
                <thead>
                    <tr>
                        <th>Report</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Budget vs Actual</strong></td>
                        <td>Compare budgeted amounts against actual spending per project</td>
                        <td>Date range, Project filter</td>
                    </tr>
                    <tr>
                        <td><strong>Cash Flow</strong></td>
                        <td>Inflows, outflows, and net cash movement by period</td>
                        <td>Date range, Grouping (monthly/quarterly)</td>
                    </tr>
                    <tr>
                        <td><strong>Expense Summary</strong></td>
                        <td>Expenses grouped by category, project, or time period</td>
                        <td>Date range, Project, Group by</td>
                    </tr>
                    <tr>
                        <td><strong>Project Status</strong></td>
                        <td>Project financial health &ndash; budget, spending, utilization</td>
                        <td>Date range, Project</td>
                    </tr>
                    <tr>
                        <td><strong>Donor Contributions</strong></td>
                        <td>Donor funding totals, in-kind values, projects funded</td>
                        <td>Date range</td>
                    </tr>
                    <tr>
                        <td><strong>Custom Report</strong></td>
                        <td>Build your own report by selecting entity, filters, and grouping</td>
                        <td>Entity, up to 5 filters, group by</td>
                    </tr>
                </tbody>
            </table>

            <h3><i class="fas fa-download"></i> Generating a Report</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Reports</strong> from the sidebar.</div>
                </div>
                <div class="step">
                    <div class="step-content">Select a report type by clicking its card or choosing from the dropdown.
                    </div>
                </div>
                <div class="step">
                    <div class="step-content">Set your parameters (date range, project, grouping).</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Generate Report"</strong>. The PDF downloads
                        automatically.</div>
                </div>
                <div class="step">
                    <div class="step-content">View generated reports in the <strong>History</strong> tab for later
                        downloads.</div>
                </div>
            </div>
        </div>

        <!-- DOCUMENTS -->
        <div class="section" id="documents">
            <h2><i class="fas fa-file-alt"></i> Document Management</h2>
            <p>Attach and manage documents across projects, budgets, expenses, and donors.</p>

            <h3><i class="fas fa-upload"></i> Uploading Documents</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>Documents</strong> from the sidebar or from a
                        specific project/expense page.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Upload Document"</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Select the file (PDF, Word, Excel, or Images &ndash; max 10MB).</div>
                </div>
                <div class="step">
                    <div class="step-content">Choose a <strong>Category</strong>: Budget Documents, Expense Receipts,
                        Project Reports, Donor Agreements, or Other.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Upload"</strong>.</div>
                </div>
            </div>

            <h3><i class="fas fa-code-branch"></i> Version Control</h3>
            <p>Upload a new version of a document using <strong>"Replace Document"</strong>. Previous versions are
                archived and remain accessible via <strong>"View Versions"</strong>.</p>

            <div class="info-box tip">
                <i class="fas fa-lightbulb"></i>
                <div><strong>Tip:</strong> Use the search bar and filters (category, file type, date range) to quickly
                    locate documents in large projects.</div>
            </div>
        </div>

        <!-- COMMENTS -->
        <div class="section" id="comments">
            <h2><i class="fas fa-comments"></i> Comments & Collaboration</h2>
            <p>Add comments to Projects, Budgets, and Expenses to discuss decisions and track conversations.</p>

            <h3><i class="fas fa-at"></i> @Mentions</h3>
            <p>Type <strong>@</strong> followed by a username to mention a colleague. They will receive a notification.
                Autocomplete suggestions appear as you type.</p>

            <h3><i class="fas fa-reply"></i> Threaded Replies</h3>
            <p>Click <strong>"Reply"</strong> on any comment to create a threaded discussion. Replies are visually
                nested under the parent comment.</p>

            <h3><i class="fas fa-paperclip"></i> Attachments</h3>
            <p>Attach up to 3 files per comment (max 2MB each). Supported formats: PDF, DOC, JPG, PNG.</p>
        </div>

        <!-- USER MANAGEMENT -->
        <div class="section" id="users">
            <h2><i class="fas fa-users-cog"></i> User Management</h2>
            <p><span class="badge badge-pm">Programs Manager Only</span></p>

            <h3><i class="fas fa-user-plus"></i> Adding a User</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Navigate to <strong>User Management</strong> from the sidebar.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Add User"</strong>.</div>
                </div>
                <div class="step">
                    <div class="step-content">Enter: Full Name, Email, Phone, Location/Office, Role (Programs Manager /
                        Finance Officer / Project Officer), and a Temporary Password.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Create User"</strong>. The user can now log in.</div>
                </div>
            </div>

            <h3><i class="fas fa-user-edit"></i> Managing Users</h3>
            <ul>
                <li><strong>Edit:</strong> Click the edit icon to modify user details or change their role.</li>
                <li><strong>Deactivate:</strong> Temporarily disable a user's access without deleting their account.
                </li>
                <li><strong>Delete:</strong> Permanently remove a user (soft delete &ndash; data is preserved for audit
                    trail).</li>
            </ul>

            <div class="info-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div><strong>Self-Protection:</strong> You cannot deactivate or delete your own account to prevent
                    accidental lockout.</div>
            </div>
        </div>

        <!-- ACTIVITY LOGS -->
        <div class="section" id="activity-logs">
            <h2><i class="fas fa-history"></i> Activity Logs</h2>
            <p><span class="badge badge-pm">Programs Manager Only</span></p>
            <p>The activity log records all significant actions in the system for audit purposes:</p>
            <ul>
                <li>User logins and logouts</li>
                <li>Expense creation, approvals, and rejections</li>
                <li>Budget creation and approvals</li>
                <li>Cash flow transactions (inflows and outflows)</li>
                <li>Purchase order actions (creation, approval, rejection)</li>
                <li>Donor and vendor creation/updates</li>
                <li>Document uploads and changes</li>
                <li>User management actions</li>
            </ul>
            <p>Use the filters (date range, action type, user) and search bar to find specific entries. Logs can be
                exported to CSV.</p>
        </div>

        <!-- PROFILE -->
        <div class="section" id="profile">
            <h2><i class="fas fa-user-circle"></i> Profile Settings</h2>
            <p>All users can manage their own profile:</p>

            <h3><i class="fas fa-id-card"></i> Personal Information</h3>
            <p>Click your name in the top-right dropdown and select <strong>"My Profile"</strong>. Edit your name,
                email, phone, and location.</p>

            <h3><i class="fas fa-camera"></i> Profile Photo</h3>
            <p>Upload an avatar image. It will be auto-resized to 200&times;200 pixels. Supported formats: JPG, PNG.</p>

            <h3><i class="fas fa-lock"></i> Change Password</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-content">Go to your profile page.</div>
                </div>
                <div class="step">
                    <div class="step-content">Scroll to the <strong>"Change Password"</strong> section.</div>
                </div>
                <div class="step">
                    <div class="step-content">Enter your <strong>current password</strong>, then your <strong>new
                            password</strong> and confirm it.</div>
                </div>
                <div class="step">
                    <div class="step-content">Click <strong>"Update Password"</strong>.</div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="section" id="faq">
            <h2><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>

            <h4>Q: I can't see certain menu items in the sidebar. Why?</h4>
            <p>Menu items are role-based. You only see modules you have access to. See the <a href="#roles">Roles &
                    Permissions</a> section for details.</p>

            <h4>Q: I submitted an expense but need to make changes. What do I do?</h4>
            <p>If your expense is still <strong>Submitted</strong> and hasn't been reviewed yet, contact the Finance
                Officer to reject it so you can edit and resubmit. If it was already rejected, you can edit and resubmit
                directly.</p>

            <h4>Q: How do I know if my expense was approved?</h4>
            <p>Check the expense detail page &ndash; the status badge will show <strong>Approved</strong> (green),
                <strong>Rejected</strong> (red), or the current stage. You also receive email notifications at each
                stage.</p>

            <h4>Q: Can I delete a budget that has been approved?</h4>
            <p>No. Approved budgets with linked expenses cannot be deleted to maintain financial integrity. Contact the
                Programs Manager for budget reallocations instead.</p>

            <h4>Q: Why does the Cash Flow page show "No Data"?</h4>
            <p>You may need to create a bank account first. Go to <strong>Cash Flow &gt; Bank Accounts</strong> and add
                at least one account before recording transactions.</p>

            <h4>Q: How do I generate a report for a specific donor?</h4>
            <p>Go to <strong>Donors</strong>, find the donor, click <strong>"View"</strong>, then click
                <strong>"Generate Financial Report"</strong> from the donor detail page. This creates a per-donor PDF
                report.</p>

            <h4>Q: What file types can I upload?</h4>
            <p>Documents: PDF, Word (DOC/DOCX), Excel (XLS/XLSX), Images (JPG, PNG). Maximum size: 10MB for documents,
                5MB for receipts, 2MB for comment attachments.</p>
        </div>

        <!-- TROUBLESHOOTING -->
        <div class="section" id="troubleshooting">
            <h2><i class="fas fa-tools"></i> Troubleshooting</h2>

            <h4><i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> Page not loading or showing
                errors</h4>
            <ul>
                <li>Try refreshing the page (<kbd>Ctrl</kbd>+<kbd>R</kbd> or <kbd>F5</kbd>).</li>
                <li>Clear your browser cache (<kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>Delete</kbd>).</li>
                <li>Check your internet connection.</li>
                <li>If the issue persists, log out and log back in.</li>
            </ul>

            <h4><i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> Session expired / logged out
                unexpectedly</h4>
            <p>Sessions expire after inactivity. Use <strong>"Remember Me"</strong> when logging in for extended 30-day
                sessions. Otherwise, the default session is 5 minutes.</p>

            <h4><i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> Account locked after failed
                login attempts</h4>
            <p>Wait 15 minutes for the lockout to expire, or contact your Programs Manager to unlock your account.</p>

            <h4><i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> PDF report not downloading
            </h4>
            <ul>
                <li>Disable any ad blockers or popup blockers for this site.</li>
                <li>Try downloading from the <strong>Report History</strong> tab instead.</li>
                <li>Use a different browser (Chrome or Firefox recommended).</li>
            </ul>

            <h4><i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> File upload fails</h4>
            <ul>
                <li>Check the file size (maximum varies by context: 10MB, 5MB, or 2MB).</li>
                <li>Ensure the file type is supported (PDF, DOC, XLS, JPG, PNG).</li>
                <li>Try a different browser or clear your cache.</li>
            </ul>

            <h4><i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> Budget showing incorrect
                utilization</h4>
            <p>The utilization is calculated from approved expenses linked to budget items. Check that expenses are
                properly linked to budget items and have been approved.</p>
        </div>

        <!-- SUPPORT -->
        <div class="section" id="support">
            <h2><i class="fas fa-headset"></i> Contact Support</h2>
            <p>If you encounter issues that cannot be resolved using this manual, please contact the system
                administrator:</p>

            <div class="info-box info">
                <i class="fas fa-envelope"></i>
                <div>
                    <p><strong>Email:</strong> info@blaxium.com or guvava.brian@gmail.com</p>
                    <p><strong>Phone/WhatsApp:</strong> +263773333660</p>
                    <p><strong>Organization:</strong> Climate Action Network Zimbabwe</p>
                    <p><strong>System:</strong> CANZIM FinTrack v1.0</p>
                    <p style="margin-top: 0.5rem;">When reporting an issue, please include:</p>
                    <ul>
                        <li>Your username and role</li>
                        <li>What you were trying to do</li>
                        <li>The exact error message (if any)</li>
                        <li>Steps to reproduce the issue</li>
                        <li>A screenshot (if possible)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div
            style="text-align: center; padding: 2rem 0; border-top: 1px solid var(--border); color: var(--text-muted); font-size: 0.85rem;">
            <p>&copy; {{ date('Y') }} Climate Action Network Zimbabwe. All rights reserved.</p>
            <p>CANZIM FinTrack v1.0 &mdash; User Manual</p>
        </div>

    </div>

    <script>
        // Sidebar active link highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('.sidebar a[href^="#"]');
            const sections = document.querySelectorAll('.section');

            // Smooth scroll
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                        // Update active state
                        links.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                    }
                });
            });

            // Active link on scroll
            window.addEventListener('scroll', function() {
                let current = '';
                sections.forEach(section => {
                    const top = section.offsetTop - 100;
                    if (window.scrollY >= top) {
                        current = section.getAttribute('id');
                    }
                });
                links.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            });
        });

        // Search / filter sidebar nav
        function filterNav(query) {
            const links = document.querySelectorAll('.sidebar a[href^="#"]');
            const sections = document.querySelectorAll('.sidebar-section');
            query = query.toLowerCase().trim();

            if (!query) {
                links.forEach(l => l.style.display = '');
                sections.forEach(s => s.style.display = '');
                return;
            }

            links.forEach(link => {
                const text = link.textContent.toLowerCase();
                link.style.display = text.includes(query) ? '' : 'none';
            });

            // Hide section titles if all their links are hidden
            sections.forEach(section => {
                const visibleLinks = section.querySelectorAll('a[href^="#"]:not([style*="none"])');
                section.style.display = visibleLinks.length > 0 ? '' : 'none';
            });
        }
    </script>

</body>

</html>
