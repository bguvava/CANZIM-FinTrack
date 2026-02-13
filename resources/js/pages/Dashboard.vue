<template>
    <DashboardLayout>
        <!-- Loading State -->
        <div
            v-if="dashboardStore.loading && !dashboardStore.error"
            class="flex items-center justify-center py-12"
        >
            <div class="text-center">
                <div
                    class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
                ></div>
                <p class="mt-4 text-gray-600">Loading dashboard...</p>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="dashboardStore.error"
            class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6"
        >
            <div class="flex items-start gap-3">
                <i
                    class="fas fa-exclamation-circle text-red-600 text-xl mt-1"
                ></i>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">
                        Failed to Load Dashboard
                    </h3>
                    <p class="text-red-700 mb-4">
                        {{ dashboardStore.error }}
                    </p>
                    <button
                        @click="retryLoad"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        <i class="fas fa-redo mr-2"></i>Retry
                    </button>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div v-else>
            <!-- Welcome Section -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">
                    Welcome back, {{ userName }}!
                </h1>
                <p class="text-gray-600 mt-1">
                    Here's what's happening with your financial activities
                    today.
                </p>
                <p
                    v-if="dashboardStore.lastRefreshTime"
                    class="text-xs text-gray-500 mt-1"
                >
                    Last updated:
                    {{ formatTime(dashboardStore.lastRefreshTime) }}
                </p>
            </div>

            <!-- Programs Manager Dashboard -->
            <div v-if="authStore.isProgramsManager">
                <!-- KPI Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
                >
                    <KPICard
                        title="Total Budget"
                        :value="formatCurrency(kpis.total_budget || 0)"
                        icon="wallet"
                        color="blue"
                        description="Across all active projects"
                    />
                    <KPICard
                        title="YTD Spending"
                        :value="formatCurrency(kpis.ytd_spending || 0)"
                        icon="receipt"
                        color="red"
                        description="Year to date expenses"
                    />
                    <KPICard
                        title="Available Funds"
                        :value="formatCurrency(kpis.available_funds || 0)"
                        icon="money-bill-wave"
                        color="green"
                        description="Remaining budget"
                    />
                    <KPICard
                        title="Pending Approvals"
                        :value="
                            (typeof kpis.pending_approvals === 'object'
                                ? kpis.pending_approvals?.count
                                : kpis.pending_approvals) || 0
                        "
                        icon="clock"
                        color="yellow"
                        description="Awaiting your review"
                    />
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <ChartCard title="Budget Utilization">
                        <DoughnutChart
                            v-if="hasChartData('budget_utilization')"
                            chartId="budget-utilization-chart"
                            :data="charts.budget_utilization"
                        />
                        <EmptyState
                            v-else
                            icon="chart-pie"
                            message="No budget data available yet"
                        />
                    </ChartCard>

                    <ChartCard title="Expense Trends (12 Months)">
                        <LineChart
                            v-if="hasChartData('expense_trends')"
                            chartId="expense-trends-chart"
                            :data="charts.expense_trends"
                        />
                        <EmptyState
                            v-else
                            icon="chart-line"
                            message="No expense data available yet"
                        />
                    </ChartCard>

                    <ChartCard title="Donor Allocation">
                        <BarChart
                            v-if="hasChartData('donor_allocation')"
                            chartId="donor-allocation-chart"
                            :data="charts.donor_allocation"
                        />
                        <EmptyState
                            v-else
                            icon="chart-bar"
                            message="No donor data available yet"
                        />
                    </ChartCard>

                    <ChartCard title="Cash Flow Projection (6 Months)">
                        <LineChart
                            v-if="hasChartData('cash_flow_projection')"
                            chartId="cash-flow-chart"
                            :data="charts.cash_flow_projection"
                        />
                        <EmptyState
                            v-else
                            icon="chart-area"
                            message="No cash flow data available yet"
                        />
                    </ChartCard>
                </div>
            </div>

            <!-- Finance Officer Dashboard -->
            <div v-else-if="authStore.isFinanceOfficer">
                <!-- KPI Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
                >
                    <KPICard
                        title="Monthly Budget"
                        :value="formatCurrency(kpis.monthly_budget || 0)"
                        icon="calendar-alt"
                        color="blue"
                        description="Current month allocation"
                    />
                    <KPICard
                        title="Actual Expenses"
                        :value="formatCurrency(kpis.actual_expenses || 0)"
                        icon="receipt"
                        color="red"
                        description="This month"
                    />
                    <KPICard
                        title="Pending Expenses"
                        :value="formatCurrency(pendingExpensesAmount)"
                        icon="clock"
                        color="yellow"
                        :description="`${pendingExpensesCount} awaiting approval`"
                    />
                    <KPICard
                        title="Cash Balance"
                        :value="formatCurrency(kpis.cash_balance || 0)"
                        icon="money-bill-wave"
                        color="green"
                        description="Available funds"
                    />
                </div>

                <!-- Budget vs Actual Chart + Quick Actions (side by side) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="lg:col-span-2">
                        <ChartCard title="Budget vs Actual">
                            <BarChart
                                v-if="hasChartData('budget_vs_actual')"
                                chartId="budget-vs-actual-chart"
                                :data="charts.budget_vs_actual"
                            />
                            <EmptyState
                                v-else
                                icon="chart-bar"
                                message="No budget comparison data available yet"
                            />
                        </ChartCard>
                    </div>

                    <!-- FO Quick Actions -->
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Quick Actions
                        </h3>
                        <div class="space-y-3">
                            <button
                                @click="navigateTo('/expenses/create')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-receipt text-green-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >Submit Expense</span
                                >
                            </button>

                            <button
                                @click="navigateTo('/purchase-orders')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-file-invoice text-blue-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >Purchase Orders</span
                                >
                            </button>

                            <button
                                @click="navigateTo('/dashboard/reports')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-chart-bar text-purple-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >View Reports</span
                                >
                            </button>

                            <button
                                @click="navigateTo('/cash-flow')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-teal-50 hover:border-teal-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-money-bill-wave text-teal-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >Cash Flow</span
                                >
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Officer Dashboard -->
            <div v-else-if="authStore.isProjectOfficer">
                <!-- KPI Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
                >
                    <KPICard
                        title="Project Budget"
                        :value="formatCurrency(kpis.project_budget || 0)"
                        icon="project-diagram"
                        color="blue"
                        description="Total allocated budget"
                    />
                    <KPICard
                        title="Budget Used"
                        :value="formatCurrency(kpis.budget_used || 0)"
                        icon="receipt"
                        color="red"
                        description="Spent so far"
                    />
                    <KPICard
                        title="Remaining Budget"
                        :value="formatCurrency(kpis.remaining_budget || 0)"
                        icon="money-bill-wave"
                        color="green"
                        description="Available to spend"
                    />
                    <KPICard
                        title="Activities Completed"
                        :value="kpis.activities_completed || 0"
                        icon="check-circle"
                        color="purple"
                        description="Finished tasks"
                    />
                </div>

                <!-- Budget Summary Chart + Quick Actions (side by side) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Budget Summary Bar Chart -->
                    <div class="lg:col-span-2">
                        <ChartCard title="Budget Summary">
                            <BarChart
                                v-if="poBudgetChartData"
                                chartId="po-budget-summary-chart"
                                :data="poBudgetChartData"
                            />
                            <EmptyState
                                v-else
                                icon="chart-bar"
                                message="No budget data available yet"
                            />
                        </ChartCard>
                    </div>

                    <!-- PO Quick Actions -->
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Quick Actions
                        </h3>
                        <div class="space-y-3">
                            <button
                                @click="navigateTo('/expenses/create')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-receipt text-green-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >Submit Expense</span
                                >
                            </button>

                            <button
                                @click="navigateTo('/projects')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-project-diagram text-blue-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >My Projects</span
                                >
                            </button>

                            <button
                                @click="navigateTo('/dashboard/documents')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-folder-open text-purple-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >My Documents</span
                                >
                            </button>

                            <button
                                @click="navigateTo('/dashboard/help')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-amber-50 hover:border-amber-300 transition-colors w-full text-left"
                            >
                                <div
                                    class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center"
                                >
                                    <i
                                        class="fas fa-question-circle text-amber-600"
                                    ></i>
                                </div>
                                <span class="font-medium text-gray-700"
                                    >Help & Support</span
                                >
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Quick Actions (Programs Manager only) -->
            <div
                v-if="authStore.isProgramsManager"
                class="grid grid-cols-1 lg:grid-cols-3 gap-6"
            >
                <!-- Recent Activity -->
                <div
                    class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Recent Activity
                    </h3>
                    <div v-if="recentActivity.length > 0" class="space-y-4">
                        <div
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0"
                        >
                            <div
                                class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0"
                            >
                                <i
                                    :class="`fas ${activity.icon} text-blue-600`"
                                ></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ activity.title }}
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ activity.description }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ activity.time }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        icon="inbox"
                        message="No recent activity"
                    />
                </div>

                <!-- Quick Actions -->
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Quick Actions
                    </h3>
                    <div class="space-y-3">
                        <button
                            v-if="canCreateProject"
                            @click="navigateTo('/projects/create')"
                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors w-full text-left"
                        >
                            <div
                                class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                            >
                                <i class="fas fa-plus text-blue-600"></i>
                            </div>
                            <span class="font-medium text-gray-700"
                                >Create Project</span
                            >
                        </button>

                        <button
                            v-if="canSubmitExpense"
                            @click="navigateTo('/expenses/create')"
                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors w-full text-left"
                        >
                            <div
                                class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
                            >
                                <i class="fas fa-receipt text-green-600"></i>
                            </div>
                            <span class="font-medium text-gray-700"
                                >Submit Expense</span
                            >
                        </button>

                        <button
                            v-if="canViewReports"
                            @click="navigateTo('/dashboard/reports')"
                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors w-full text-left"
                        >
                            <div
                                class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"
                            >
                                <i class="fas fa-chart-bar text-purple-600"></i>
                            </div>
                            <span class="font-medium text-gray-700"
                                >View Reports</span
                            >
                        </button>

                        <button
                            @click="navigateTo('/dashboard/profile')"
                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors w-full text-left"
                        >
                            <div
                                class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"
                            >
                                <i class="fas fa-user text-gray-600"></i>
                            </div>
                            <span class="font-medium text-gray-700"
                                >My Profile</span
                            >
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from "vue";
import { useAuthStore } from "../stores/authStore";
import { useDashboardStore } from "../stores/dashboardStore";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import LineChart from "../components/charts/LineChart.vue";
import BarChart from "../components/charts/BarChart.vue";
import DoughnutChart from "../components/charts/DoughnutChart.vue";
import PieChart from "../components/charts/PieChart.vue";
import KPICard from "../components/KPICard.vue";
import ChartCard from "../components/ChartCard.vue";
import EmptyState from "../components/EmptyState.vue";

// Stores
const authStore = useAuthStore();
const dashboardStore = useDashboardStore();

// Fetch dashboard data on mount
onMounted(async () => {
    await dashboardStore.fetchDashboardData();
    dashboardStore.startAutoRefresh();
});

// Cleanup on unmount
onUnmounted(() => {
    dashboardStore.stopAutoRefresh();
});

// Computed properties
const userName = computed(() => {
    const user = authStore.currentUser;
    if (!user?.name) return "User";
    const firstName = user.name.split(" ")[0];
    return firstName;
});

const kpis = computed(() => dashboardStore.kpis || {});
const charts = computed(() => dashboardStore.charts || {});
const recentActivity = computed(() => dashboardStore.recentActivity || []);

// Handle pending_expenses which can be an object with count and total_amount
const pendingExpensesAmount = computed(() => {
    const pe = kpis.value?.pending_expenses;
    if (pe === null || pe === undefined) {
        return 0;
    }
    if (typeof pe === "object" && pe !== null) {
        const amount = pe.total_amount;
        // Ensure amount is a valid number before returning
        if (amount === null || amount === undefined || amount === "") {
            return 0;
        }
        const numAmount = parseFloat(amount);
        return Number.isFinite(numAmount) ? numAmount : 0;
    }
    // Handle case where pe is a direct number or string
    if (pe === "") {
        return 0;
    }
    const numPe = parseFloat(pe);
    return Number.isFinite(numPe) ? numPe : 0;
});

const pendingExpensesCount = computed(() => {
    const pe = kpis.value?.pending_expenses;
    if (pe === null || pe === undefined) {
        return 0;
    }
    if (typeof pe === "object" && pe !== null) {
        const count = pe.count;
        if (count === null || count === undefined || count === "") {
            return 0;
        }
        const numCount = parseInt(count, 10);
        return Number.isFinite(numCount) ? numCount : 0;
    }
    return 0;
});

const canCreateProject = computed(
    () => authStore.isProgramsManager || authStore.isFinanceOfficer,
);

const canSubmitExpense = computed(() => true);

const canViewReports = computed(
    () => authStore.isProgramsManager || authStore.isFinanceOfficer,
);

// Budget summary chart data for Project Officer
const poBudgetChartData = computed(() => {
    const budget = parseFloat(kpis.value?.project_budget) || 0;
    const used = parseFloat(kpis.value?.budget_used) || 0;
    const remaining = parseFloat(kpis.value?.remaining_budget) || 0;

    if (budget === 0 && used === 0 && remaining === 0) {
        return null;
    }

    return {
        labels: ["Budget Allocated", "Budget Used", "Remaining"],
        datasets: [
            {
                label: "Amount (USD)",
                data: [budget, used, remaining],
                backgroundColor: [
                    "rgba(59, 130, 246, 0.7)",
                    "rgba(239, 68, 68, 0.7)",
                    "rgba(34, 197, 94, 0.7)",
                ],
                borderColor: [
                    "rgb(59, 130, 246)",
                    "rgb(239, 68, 68)",
                    "rgb(34, 197, 94)",
                ],
                borderWidth: 1,
            },
        ],
    };
});

// Helper functions
const formatCurrency = (value) => {
    // Handle null, undefined, NaN, empty string, and non-numeric values
    if (value === null || value === undefined || value === "") {
        return "$0";
    }
    // Use parseFloat for better string-to-number conversion
    const numValue = typeof value === "number" ? value : parseFloat(value);
    // Use Number.isFinite to check for valid numbers (excludes NaN, Infinity, -Infinity)
    if (!Number.isFinite(numValue)) {
        return "$0";
    }
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numValue);
};

const formatTime = (time) => {
    if (!time) return "";
    const date = new Date(time);
    return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
    });
};

const hasChartData = (chartName) => {
    const chart = charts.value[chartName];
    if (!chart) return false;
    // Check if chart has labels or datasets with any data
    if (!chart.datasets || !Array.isArray(chart.datasets)) return false;
    // Return true if there's any data in any dataset (even if all values are 0)
    return chart.datasets.some(
        (dataset) =>
            dataset.data &&
            Array.isArray(dataset.data) &&
            dataset.data.length > 0,
    );
};

const retryLoad = async () => {
    await dashboardStore.fetchDashboardData();
};

// Navigation helper for quick action buttons
const navigateTo = (path) => {
    window.location.href = path;
};
</script>
