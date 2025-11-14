<template>
    <aside
        :class="[
            'fixed left-0 top-0 h-full bg-white border-r border-gray-200 transition-all duration-300 z-50',
            isCollapsed ? 'w-16' : 'w-64',
        ]"
    >
        <!-- Logo Section -->
        <div
            class="flex items-center justify-between h-16 px-4 border-b border-gray-200"
        >
            <div v-if="!isCollapsed" class="flex items-center gap-3">
                <i class="fas fa-chart-line text-2xl text-blue-600"></i>
                <span class="font-semibold text-lg text-gray-800"
                    >CANZIM FinTrack</span
                >
            </div>
            <div v-else class="flex items-center justify-center w-full">
                <i class="fas fa-chart-line text-2xl text-blue-600"></i>
            </div>
        </div>

        <!-- Toggle Button -->
        <button
            @click="toggleSidebar"
            class="absolute -right-3 top-20 bg-white border border-gray-200 rounded-full w-6 h-6 flex items-center justify-center hover:bg-gray-50 shadow-sm"
            :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        >
            <i
                :class="
                    isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left'
                "
                class="text-xs text-gray-600"
            ></i>
        </button>

        <!-- Navigation Menu -->
        <nav class="overflow-y-auto h-[calc(100vh-4rem)] py-4">
            <div class="space-y-1 px-3">
                <!-- Dashboard Menu Item -->
                <a
                    v-if="canAccessDashboard"
                    href="/dashboard"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                        isActive('/dashboard')
                            ? 'bg-blue-50 text-blue-700'
                            : 'text-gray-700 hover:bg-gray-50',
                    ]"
                    :title="isCollapsed ? 'Dashboard' : ''"
                >
                    <i class="fas fa-home w-5 text-center"></i>
                    <span v-if="!isCollapsed" class="font-medium"
                        >Dashboard</span
                    >
                </a>

                <!-- Financial Section -->
                <div v-if="canAccessFinancialMenu" class="pt-4">
                    <div v-if="!isCollapsed" class="px-3 mb-2">
                        <span
                            class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                            >Financial</span
                        >
                    </div>
                    <div v-else class="border-t border-gray-200 my-2"></div>

                    <!-- Projects -->
                    <a
                        v-if="canAccessProjects"
                        href="/dashboard/projects"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/projects')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Projects' : ''"
                    >
                        <i class="fas fa-project-diagram w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Projects</span
                        >
                    </a>

                    <!-- Budgets -->
                    <a
                        v-if="canAccessBudgets"
                        href="/dashboard/budgets"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/budgets')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Budgets' : ''"
                    >
                        <i class="fas fa-wallet w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Budgets</span
                        >
                    </a>

                    <!-- Expenses -->
                    <a
                        v-if="canAccessExpenses"
                        href="/dashboard/expenses"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/expenses')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Expenses' : ''"
                    >
                        <i class="fas fa-receipt w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Expenses</span
                        >
                        <span
                            v-if="!isCollapsed && pendingExpensesCount > 0"
                            class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full"
                        >
                            {{ pendingExpensesCount }}
                        </span>
                    </a>

                    <!-- Cash Flow -->
                    <a
                        v-if="canAccessCashFlow"
                        href="/dashboard/cash-flow"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/cash-flow')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Cash Flow' : ''"
                    >
                        <i class="fas fa-money-bill-wave w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Cash Flow</span
                        >
                    </a>

                    <!-- Purchase Orders -->
                    <a
                        v-if="canAccessPurchaseOrders"
                        href="/dashboard/purchase-orders"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/purchase-orders')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Purchase Orders' : ''"
                    >
                        <i class="fas fa-file-invoice w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Purchase Orders</span
                        >
                    </a>

                    <!-- Donors -->
                    <a
                        v-if="canAccessDonors"
                        href="/dashboard/donors"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/donors')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Donors' : ''"
                    >
                        <i class="fas fa-handshake w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Donors</span
                        >
                    </a>
                </div>

                <!-- Management Section -->
                <div v-if="canAccessManagementMenu" class="pt-4">
                    <div v-if="!isCollapsed" class="px-3 mb-2">
                        <span
                            class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                            >Management</span
                        >
                    </div>
                    <div v-else class="border-t border-gray-200 my-2"></div>

                    <!-- Reports -->
                    <a
                        v-if="canAccessReports"
                        href="/dashboard/reports"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/reports')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Reports' : ''"
                    >
                        <i class="fas fa-chart-bar w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Reports</span
                        >
                    </a>

                    <!-- Users -->
                    <a
                        v-if="canAccessUsers"
                        href="/dashboard/users"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/users')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Users' : ''"
                    >
                        <i class="fas fa-users w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Users</span
                        >
                    </a>

                    <!-- Documents -->
                    <a
                        v-if="canAccessDocuments"
                        href="/dashboard/documents"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/documents')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Documents' : ''"
                    >
                        <i class="fas fa-folder-open w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Documents</span
                        >
                    </a>
                </div>

                <!-- System Section -->
                <div class="pt-4">
                    <div v-if="!isCollapsed" class="px-3 mb-2">
                        <span
                            class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                            >System</span
                        >
                    </div>
                    <div v-else class="border-t border-gray-200 my-2"></div>

                    <!-- Profile -->
                    <a
                        href="/dashboard/profile"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/profile')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Profile' : ''"
                    >
                        <i class="fas fa-user-circle w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Profile</span
                        >
                    </a>

                    <!-- Settings (Programs Manager only) -->
                    <a
                        v-if="canAccessSettings"
                        href="/dashboard/settings"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isActive('/dashboard/settings')
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        :title="isCollapsed ? 'Settings' : ''"
                    >
                        <i class="fas fa-cog w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Settings</span
                        >
                    </a>

                    <!-- Logout -->
                    <button
                        @click="handleLogout"
                        :class="[
                            'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-red-50 hover:text-red-600',
                        ]"
                        :title="isCollapsed ? 'Logout' : ''"
                    >
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        <span v-if="!isCollapsed" class="font-medium"
                            >Logout</span
                        >
                    </button>
                </div>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "../stores/authStore";

// Props
const props = defineProps({
    pendingExpensesCount: {
        type: Number,
        default: 0,
    },
});

// State
const authStore = useAuthStore();
const isCollapsed = ref(false);

// Get current path for active state
const currentPath = ref(window.location.pathname);

// Toggle sidebar
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem(
        "sidebar_collapsed",
        isCollapsed.value ? "true" : "false",
    );
};

// Check if menu item is active
const isActive = (path) => {
    if (path === "/dashboard") {
        return currentPath.value === "/dashboard";
    }
    return currentPath.value.startsWith(path);
};

// Role-based access control
const userRole = computed(() => authStore.userRole);
const isProgramsManager = computed(() => authStore.isProgramsManager);
const isFinanceOfficer = computed(() => authStore.isFinanceOfficer);
const isProjectOfficer = computed(() => authStore.isProjectOfficer);

// Menu access permissions
const canAccessDashboard = computed(() => true); // All roles

const canAccessFinancialMenu = computed(
    () =>
        isProgramsManager.value ||
        isFinanceOfficer.value ||
        isProjectOfficer.value,
);

const canAccessProjects = computed(
    () =>
        isProgramsManager.value ||
        isFinanceOfficer.value ||
        isProjectOfficer.value,
);

const canAccessBudgets = computed(
    () => isProgramsManager.value || isFinanceOfficer.value,
);

const canAccessExpenses = computed(
    () =>
        isProgramsManager.value ||
        isFinanceOfficer.value ||
        isProjectOfficer.value,
);

const canAccessCashFlow = computed(
    () => isProgramsManager.value || isFinanceOfficer.value,
);

const canAccessPurchaseOrders = computed(
    () => isProgramsManager.value || isFinanceOfficer.value,
);

const canAccessDonors = computed(
    () => isProgramsManager.value || isFinanceOfficer.value,
);

const canAccessManagementMenu = computed(
    () =>
        isProgramsManager.value ||
        isFinanceOfficer.value ||
        isProjectOfficer.value,
);

const canAccessReports = computed(
    () => isProgramsManager.value || isFinanceOfficer.value,
);

const canAccessUsers = computed(() => isProgramsManager.value);

const canAccessDocuments = computed(
    () => isProgramsManager.value || isProjectOfficer.value,
);

const canAccessSettings = computed(() => isProgramsManager.value);

// Handle logout
const handleLogout = async () => {
    await authStore.logout();
};

// Initialize collapsed state from localStorage
const initializeSidebar = () => {
    const savedState = localStorage.getItem("sidebar_collapsed");
    if (savedState === "true") {
        isCollapsed.value = true;
    }
};

// Initialize on mount
initializeSidebar();
</script>
