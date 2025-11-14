<template>
    <DashboardLayout>
        <!-- Welcome Section -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Welcome back, {{ userName }}!
            </h1>
            <p class="text-gray-600 mt-1">
                Here's what's happening with your financial activities today.
            </p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Budget -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-wallet text-blue-600 text-xl"></i>
                    </div>
                    <span
                        class="text-green-600 text-sm font-medium flex items-center gap-1"
                    >
                        <i class="fas fa-arrow-up text-xs"></i>
                        12%
                    </span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium">Total Budget</h3>
                <p class="text-2xl font-bold text-gray-800 mt-1">$0.00</p>
                <p class="text-xs text-gray-500 mt-2">
                    Across all active projects
                </p>
            </div>

            <!-- Total Expenses -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-receipt text-red-600 text-xl"></i>
                    </div>
                    <span
                        class="text-red-600 text-sm font-medium flex items-center gap-1"
                    >
                        <i class="fas fa-arrow-up text-xs"></i>
                        8%
                    </span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium">
                    Total Expenses
                </h3>
                <p class="text-2xl font-bold text-gray-800 mt-1">$0.00</p>
                <p class="text-xs text-gray-500 mt-2">This month</p>
            </div>

            <!-- Active Projects -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
                    >
                        <i
                            class="fas fa-project-diagram text-green-600 text-xl"
                        ></i>
                    </div>
                    <span
                        class="text-green-600 text-sm font-medium flex items-center gap-1"
                    >
                        <i class="fas fa-arrow-up text-xs"></i>
                        3
                    </span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium">
                    Active Projects
                </h3>
                <p class="text-2xl font-bold text-gray-800 mt-1">0</p>
                <p class="text-xs text-gray-500 mt-2">Currently in progress</p>
            </div>

            <!-- Pending Approvals -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <span
                        class="text-yellow-600 text-sm font-medium flex items-center gap-1"
                    >
                        <i class="fas fa-minus text-xs"></i>
                        0%
                    </span>
                </div>
                <h3 class="text-gray-600 text-sm font-medium">
                    Pending Approvals
                </h3>
                <p class="text-2xl font-bold text-gray-800 mt-1">0</p>
                <p class="text-xs text-gray-500 mt-2">Awaiting your review</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Budget vs Actual Chart -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Budget vs Actual
                </h3>
                <div
                    class="h-64 flex items-center justify-center text-gray-400"
                >
                    <div class="text-center">
                        <i class="fas fa-chart-bar text-5xl mb-3"></i>
                        <p>No data available yet</p>
                    </div>
                </div>
            </div>

            <!-- Expense Trends Chart -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Expense Trends
                </h3>
                <div
                    class="h-64 flex items-center justify-center text-gray-400"
                >
                    <div class="text-center">
                        <i class="fas fa-chart-line text-5xl mb-3"></i>
                        <p>No data available yet</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity -->
            <div
                class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Recent Activity
                </h3>
                <div class="space-y-4">
                    <div
                        class="flex items-center justify-center py-12 text-gray-400"
                    >
                        <div class="text-center">
                            <i class="fas fa-inbox text-5xl mb-3"></i>
                            <p>No recent activity</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Quick Actions
                </h3>
                <div class="space-y-3">
                    <a
                        v-if="canCreateProject"
                        href="/dashboard/projects/create"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors"
                    >
                        <div
                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <i class="fas fa-plus text-blue-600"></i>
                        </div>
                        <span class="font-medium text-gray-700"
                            >Create Project</span
                        >
                    </a>

                    <a
                        v-if="canSubmitExpense"
                        href="/dashboard/expenses/create"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors"
                    >
                        <div
                            class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
                        >
                            <i class="fas fa-receipt text-green-600"></i>
                        </div>
                        <span class="font-medium text-gray-700"
                            >Submit Expense</span
                        >
                    </a>

                    <a
                        v-if="canViewReports"
                        href="/dashboard/reports"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition-colors"
                    >
                        <div
                            class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"
                        >
                            <i class="fas fa-chart-bar text-purple-600"></i>
                        </div>
                        <span class="font-medium text-gray-700"
                            >View Reports</span
                        >
                    </a>

                    <a
                        href="/dashboard/profile"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors"
                    >
                        <div
                            class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"
                        >
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                        <span class="font-medium text-gray-700"
                            >My Profile</span
                        >
                    </a>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from "vue";
import { useAuthStore } from "../stores/authStore";
import DashboardLayout from "../layouts/DashboardLayout.vue";

// Auth Store
const authStore = useAuthStore();

// Computed properties
const userName = computed(() => {
    const user = authStore.currentUser;
    if (!user?.name) return "User";

    const firstName = user.name.split(" ")[0];
    return firstName;
});

const canCreateProject = computed(
    () => authStore.isProgramsManager || authStore.isFinanceOfficer,
);

const canSubmitExpense = computed(() => true); // All roles can submit expenses

const canViewReports = computed(
    () => authStore.isProgramsManager || authStore.isFinanceOfficer,
);
</script>
