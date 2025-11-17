<?php

declare(strict_types=1);

/**
 * API Routes for CANZIM FinTrack
 *
 * All API routes are prefixed with /api/v1
 * All routes require authentication via Laravel Sanctum tokens
 *
 * @see https://laravel.com/docs/12.x/routing
 * @see https://laravel.com/docs/12.x/sanctum
 */

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'CANZIM FinTrack API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.auth.forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset-password');
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/profile', [AuthController::class, 'profile'])->name('api.auth.profile');
    });

    // User profile endpoint (deprecated - use /auth/profile instead)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('role'),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | - Bank Account Routes (/api/v1/bank-accounts)
    |--------------------------------------------------------------------------
    */

    Route::prefix('bank-accounts')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\BankAccountController::class, 'index'])->name('api.bank-accounts.index');
        Route::post('/', [\App\Http\Controllers\Api\BankAccountController::class, 'store'])->name('api.bank-accounts.store');
        Route::get('/{bankAccount}', [\App\Http\Controllers\Api\BankAccountController::class, 'show'])->name('api.bank-accounts.show');
        Route::put('/{bankAccount}', [\App\Http\Controllers\Api\BankAccountController::class, 'update'])->name('api.bank-accounts.update');
        Route::post('/{bankAccount}/deactivate', [\App\Http\Controllers\Api\BankAccountController::class, 'deactivate'])->name('api.bank-accounts.deactivate');
        Route::post('/{bankAccount}/activate', [\App\Http\Controllers\Api\BankAccountController::class, 'activate'])->name('api.bank-accounts.activate');
        Route::get('/{bankAccount}/reconciliation-report-pdf', [\App\Http\Controllers\Api\CashFlowController::class, 'exportReconciliation'])->name('api.bank-accounts.reconciliation-report-pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Module Routes (To be implemented in subsequent modules)
    |--------------------------------------------------------------------------
    |
    | The following route groups will be populated as modules are developed:
    | - Dashboard routes (/api/v1/dashboard)
    | - Project routes (/api/v1/projects)
    | - Budget routes (/api/v1/budgets)
    | - Expense routes (/api/v1/expenses)
    | - Cash flow routes (/api/v1/cash-flow)
    | - Purchase order routes (/api/v1/purchase-orders)
    | - Donor routes (/api/v1/donors)
    | - Report routes (/api/v1/reports)
    | - Document routes (/api/v1/documents)
    |
    */

    // Dashboard Routes (Module 5)
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('api.dashboard.index');
        Route::get('/notifications', [DashboardController::class, 'notifications'])->name('api.dashboard.notifications');
        Route::post('/notifications/{notification}/read', [DashboardController::class, 'markNotificationRead'])->name('api.dashboard.notifications.read');
    });

    // User Management Routes (Module 4)
    Route::prefix('users')->group(function () {
        // User CRUD operations
        Route::get('/', [UserController::class, 'index'])->name('api.users.index');
        Route::post('/', [UserController::class, 'store'])->name('api.users.store');
        Route::get('/{user}', [UserController::class, 'show'])->name('api.users.show');
        Route::put('/{user}', [UserController::class, 'update'])->name('api.users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('api.users.destroy');

        // User status management
        Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->name('api.users.deactivate');
        Route::post('/{user}/activate', [UserController::class, 'activate'])->name('api.users.activate');

        // Helper endpoints
        Route::get('/roles/list', [UserController::class, 'roles'])->name('api.users.roles');
        Route::get('/locations/list', [UserController::class, 'officeLocations'])->name('api.users.locations');

        // User activity logs
        Route::get('/{user}/activity', [ActivityLogController::class, 'userActivity'])->name('api.users.activity');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('api.users.activity-logs');
    });

    // User Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('api.profile.show');
        Route::put('/', [UserController::class, 'updateProfile'])->name('api.profile.update');
        Route::post('/change-password', [UserController::class, 'changePassword'])->name('api.profile.change-password');
        Route::post('/avatar', [UserController::class, 'uploadAvatar'])->name('api.profile.avatar');
    });

    // Activity Logs Routes (Programs Manager only)
    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('api.activity-logs.index');
        Route::post('/bulk-delete', [ActivityLogController::class, 'bulkDelete'])->name('api.activity-logs.bulk-delete');
    });

    // Project Management Routes (Module 6)
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('api.projects.index');
        Route::post('/', [ProjectController::class, 'store'])->name('api.projects.store');
        Route::get('/statistics', [ProjectController::class, 'statistics'])->name('api.projects.statistics');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('api.projects.show');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('api.projects.update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');
        Route::post('/{project}/archive', [ProjectController::class, 'archive'])->name('api.projects.archive');
        Route::post('/{project}/report', [ProjectController::class, 'generateReport'])->name('api.projects.report');

        // Budget routes for a specific project
        Route::get('/{project}/budgets', [BudgetController::class, 'index'])->name('api.projects.budgets.index');
    });

    // Budget Management Routes (Module 6)
    Route::prefix('budgets')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('api.budgets.index');
        Route::post('/', [BudgetController::class, 'store'])->name('api.budgets.store');
        Route::get('/categories', [BudgetController::class, 'categories'])->name('api.budgets.categories');
        Route::get('/{budget}', [BudgetController::class, 'show'])->name('api.budgets.show');
        Route::post('/{budget}/approve', [BudgetController::class, 'approve'])->name('api.budgets.approve');

        // Budget reallocation routes
        Route::post('/reallocations', [BudgetController::class, 'requestReallocation'])->name('api.budgets.reallocations.request');
        Route::post('/reallocations/{reallocation}/approve', [BudgetController::class, 'approveReallocation'])->name('api.budgets.reallocations.approve');
    });

    // Donor Management Routes (Module 9)
    Route::prefix('donors')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\DonorController::class, 'index'])->name('api.donors.index');
        Route::post('/', [\App\Http\Controllers\Api\DonorController::class, 'store'])->name('api.donors.store');
        Route::get('/statistics', [\App\Http\Controllers\Api\DonorController::class, 'statistics'])->name('api.donors.statistics');
        Route::get('/chart-data', [\App\Http\Controllers\Api\DonorController::class, 'chartData'])->name('api.donors.chart-data');
        Route::get('/{donor}', [\App\Http\Controllers\Api\DonorController::class, 'show'])->name('api.donors.show');
        Route::put('/{donor}', [\App\Http\Controllers\Api\DonorController::class, 'update'])->name('api.donors.update');
        Route::delete('/{donor}', [\App\Http\Controllers\Api\DonorController::class, 'destroy'])->name('api.donors.destroy');
        Route::post('/{id}/restore', [\App\Http\Controllers\Api\DonorController::class, 'restore'])->name('api.donors.restore');

        // Project assignment routes
        Route::post('/{donor}/assign-project', [\App\Http\Controllers\Api\DonorController::class, 'assignToProject'])->name('api.donors.assign-project');
        Route::delete('/{donor}/projects/{project}', [\App\Http\Controllers\Api\DonorController::class, 'removeFromProject'])->name('api.donors.remove-project');
        Route::get('/{donor}/funding-summary', [\App\Http\Controllers\Api\DonorController::class, 'fundingSummary'])->name('api.donors.funding-summary');
        Route::post('/{donor}/toggle-status', [\App\Http\Controllers\Api\DonorController::class, 'toggleStatus'])->name('api.donors.toggle-status');

        // PDF Report generation
        Route::get('/{donor}/report', [\App\Http\Controllers\Api\DonorController::class, 'generateReport'])->name('api.donors.report');
    });

    // In-Kind Contributions Routes (Module 9)
    Route::post('/in-kind-contributions', [\App\Http\Controllers\Api\DonorController::class, 'storeInKindContribution'])->name('api.in-kind.store');

    // Communications Routes (Module 9)
    Route::post('/communications', [\App\Http\Controllers\Api\DonorController::class, 'storeCommunication'])->name('api.communications.store');

    // Expense Management Routes (Module 7)
    Route::prefix('expenses')->group(function () {
        // Helper endpoints
        Route::get('/categories', [\App\Http\Controllers\Api\ExpenseController::class, 'categories'])->name('api.expenses.categories');
        Route::get('/pending-review', [\App\Http\Controllers\Api\ExpenseController::class, 'pendingReview'])->name('api.expenses.pending-review');
        Route::get('/pending-approval', [\App\Http\Controllers\Api\ExpenseController::class, 'pendingApproval'])->name('api.expenses.pending-approval');

        // Resource routes
        Route::get('/', [\App\Http\Controllers\Api\ExpenseController::class, 'index'])->name('api.expenses.index');
        Route::post('/', [\App\Http\Controllers\Api\ExpenseController::class, 'store'])->name('api.expenses.store');
        Route::get('/{expense}', [\App\Http\Controllers\Api\ExpenseController::class, 'show'])->name('api.expenses.show');
        Route::put('/{expense}', [\App\Http\Controllers\Api\ExpenseController::class, 'update'])->name('api.expenses.update');
        Route::delete('/{expense}', [\App\Http\Controllers\Api\ExpenseController::class, 'destroy'])->name('api.expenses.destroy');

        // Workflow routes
        Route::post('/{expense}/submit', [\App\Http\Controllers\Api\ExpenseController::class, 'submit'])->name('api.expenses.submit');
        Route::post('/{expense}/review', [\App\Http\Controllers\Api\ExpenseController::class, 'review'])->name('api.expenses.review');
        Route::post('/{expense}/approve', [\App\Http\Controllers\Api\ExpenseController::class, 'approve'])->name('api.expenses.approve');
        Route::post('/{expense}/mark-paid', [\App\Http\Controllers\Api\ExpenseController::class, 'markAsPaid'])->name('api.expenses.mark-paid');

        // Purchase Order linking routes
        Route::post('/{expense}/link-po', [\App\Http\Controllers\Api\ExpenseController::class, 'linkPurchaseOrder'])->name('api.expenses.link-po');
        Route::post('/{expense}/unlink-po', [\App\Http\Controllers\Api\ExpenseController::class, 'unlinkPurchaseOrder'])->name('api.expenses.unlink-po');
    });

    // Bank Account Management Routes (Module 8)
    Route::prefix('bank-accounts')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\BankAccountController::class, 'index'])->name('api.bank-accounts.index');
        Route::post('/', [\App\Http\Controllers\Api\BankAccountController::class, 'store'])->name('api.bank-accounts.store');
        Route::get('/{bankAccount}', [\App\Http\Controllers\Api\BankAccountController::class, 'show'])->name('api.bank-accounts.show');
        Route::put('/{bankAccount}', [\App\Http\Controllers\Api\BankAccountController::class, 'update'])->name('api.bank-accounts.update');
        Route::post('/{bankAccount}/deactivate', [\App\Http\Controllers\Api\BankAccountController::class, 'deactivate'])->name('api.bank-accounts.deactivate');
        Route::post('/{bankAccount}/activate', [\App\Http\Controllers\Api\BankAccountController::class, 'activate'])->name('api.bank-accounts.activate');
        Route::get('/{bankAccount}/summary', [\App\Http\Controllers\Api\BankAccountController::class, 'summary'])->name('api.bank-accounts.summary');
        Route::get('/{bankAccount}/reconciliation-report-pdf', [\App\Http\Controllers\Api\CashFlowController::class, 'exportReconciliation'])->name('api.bank-accounts.reconciliation-report-pdf');
    });

    // Cash Flow Management Routes (Module 8)
    Route::prefix('cash-flows')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\CashFlowController::class, 'index'])->name('api.cash-flows.index');
        Route::post('/inflows', [\App\Http\Controllers\Api\CashFlowController::class, 'storeInflow'])->name('api.cash-flows.inflow');
        Route::put('/inflows/{cashFlow}', [\App\Http\Controllers\Api\CashFlowController::class, 'update'])->name('api.cash-flows.update-inflow');
        Route::post('/outflows', [\App\Http\Controllers\Api\CashFlowController::class, 'storeOutflow'])->name('api.cash-flows.outflow');
        Route::put('/outflows/{cashFlow}', [\App\Http\Controllers\Api\CashFlowController::class, 'update'])->name('api.cash-flows.update-outflow');
        Route::get('/statistics', [\App\Http\Controllers\Api\CashFlowController::class, 'statistics'])->name('api.cash-flows.statistics');
        Route::get('/projections', [\App\Http\Controllers\Api\CashFlowController::class, 'projections'])->name('api.cash-flows.projections');
        Route::post('/export-statement', [\App\Http\Controllers\Api\CashFlowController::class, 'exportStatement'])->name('api.cash-flows.export-statement');
        Route::post('/export-reconciliation/{bankAccount}', [\App\Http\Controllers\Api\CashFlowController::class, 'exportReconciliation'])->name('api.cash-flows.export-reconciliation');
        Route::get('/{cashFlow}', [\App\Http\Controllers\Api\CashFlowController::class, 'show'])->name('api.cash-flows.show');
        Route::put('/{cashFlow}', [\App\Http\Controllers\Api\CashFlowController::class, 'update'])->name('api.cash-flows.update');
        Route::post('/{cashFlow}/reconcile', [\App\Http\Controllers\Api\CashFlowController::class, 'reconcile'])->name('api.cash-flows.reconcile');
        Route::post('/{cashFlow}/unreconcile', [\App\Http\Controllers\Api\CashFlowController::class, 'unreconcile'])->name('api.cash-flows.unreconcile');
        Route::delete('/{cashFlow}', [\App\Http\Controllers\Api\CashFlowController::class, 'destroy'])->name('api.cash-flows.destroy');
    });

    // Cash Flow route aliases (singular) for backward compatibility
    Route::prefix('cash-flow')->group(function () {
        Route::get('/projections', [\App\Http\Controllers\Api\CashFlowController::class, 'projections'])->name('api.cash-flow.projections');
        Route::get('/export-pdf', [\App\Http\Controllers\Api\CashFlowController::class, 'exportStatement'])->name('api.cash-flow.export-pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | - Purchase Order Routes (/api/v1/purchase-orders)
    |--------------------------------------------------------------------------
    */

    // Purchase Order Management Routes (Module 8)
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'index'])->name('api.purchase-orders.index');
        Route::post('/', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'store'])->name('api.purchase-orders.store');
        Route::get('/statistics', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'statistics'])->name('api.purchase-orders.statistics');
        Route::post('/export-vendor-payment-status', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'exportVendorPaymentStatus'])->name('api.purchase-orders.export-vendor-payment-status');
        Route::get('/export-list-pdf', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'exportListPDF'])->name('api.purchase-orders.export-list-pdf');
        Route::get('/{purchaseOrder}', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'show'])->name('api.purchase-orders.show');
        Route::put('/{purchaseOrder}', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'update'])->name('api.purchase-orders.update');
        Route::delete('/{purchaseOrder}', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'destroy'])->name('api.purchase-orders.destroy');

        // Workflow routes
        Route::post('/{purchaseOrder}/submit', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'submit'])->name('api.purchase-orders.submit');
        Route::post('/{purchaseOrder}/approve', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'approve'])->name('api.purchase-orders.approve');
        Route::post('/{purchaseOrder}/reject', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'reject'])->name('api.purchase-orders.reject');
        Route::post('/{purchaseOrder}/receive', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'receive'])->name('api.purchase-orders.receive');
        Route::post('/{purchaseOrder}/complete', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'complete'])->name('api.purchase-orders.complete');
        Route::post('/{purchaseOrder}/cancel', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'cancel'])->name('api.purchase-orders.cancel');

        // PDF Export routes
        Route::get('/{purchaseOrder}/export-pdf', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'exportPDF'])->name('api.purchase-orders.export-pdf');

        // Expense linking routes
        Route::get('/{purchaseOrder}/expenses', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'getExpenses'])->name('api.purchase-orders.expenses');
    });

    // Vendor Management Routes (Module 8)
    Route::prefix('vendors')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\VendorController::class, 'index'])->name('api.vendors.index');
        Route::post('/', [\App\Http\Controllers\Api\VendorController::class, 'store'])->name('api.vendors.store');
        Route::get('/{vendor}', [\App\Http\Controllers\Api\VendorController::class, 'show'])->name('api.vendors.show');
        Route::put('/{vendor}', [\App\Http\Controllers\Api\VendorController::class, 'update'])->name('api.vendors.update');
        Route::delete('/{vendor}', [\App\Http\Controllers\Api\VendorController::class, 'destroy'])->name('api.vendors.destroy');
        Route::post('/{vendor}/deactivate', [\App\Http\Controllers\Api\VendorController::class, 'deactivate'])->name('api.vendors.deactivate');
        Route::post('/{vendor}/activate', [\App\Http\Controllers\Api\VendorController::class, 'activate'])->name('api.vendors.activate');
        Route::get('/{vendor}/summary', [\App\Http\Controllers\Api\VendorController::class, 'summary'])->name('api.vendors.summary');
    });
});
