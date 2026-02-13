<template>
    <div class="budgets-list-container">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Budgets</h1>
                <p class="text-gray-600 mt-1">
                    Manage project budgets and allocations
                </p>
            </div>
            <button
                v-if="canCreate"
                @click="openAddBudgetModal"
                class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition flex items-center gap-2"
            >
                <i class="fas fa-plus"></i>
                Add Budget
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status Filter
                    </label>
                    <select
                        v-model="statusFilter"
                        @change="handleFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Project Filter
                    </label>
                    <select
                        v-model="projectFilter"
                        @change="handleFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Projects</option>
                        <option
                            v-for="project in projects"
                            :key="project.id"
                            :value="project.id"
                        >
                            {{ project.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div
                class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
            ></div>
        </div>

        <!-- Budgets Grid -->
        <div
            v-else-if="budgets.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <div
                v-for="budget in budgets"
                :key="budget.id"
                class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition cursor-pointer"
                @click="viewBudget(budget)"
            >
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">
                            Budget #{{ budget.id }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ budget.project?.name || "Unknown Project" }}
                        </p>
                    </div>
                    <span
                        :class="getStatusClass(budget.status)"
                        class="px-2 py-1 rounded-full text-xs font-medium"
                    >
                        {{ getStatusLabel(budget.status) }}
                    </span>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Allocated</span>
                        <span class="font-semibold text-gray-900"
                            >${{ formatNumber(budget.total_allocated) }}</span
                        >
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Spent</span>
                        <span class="font-semibold text-gray-900"
                            >${{ formatNumber(budget.total_spent) }}</span
                        >
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div
                            :class="
                                getUtilizationColorClass(
                                    budget.utilization_percentage,
                                )
                            "
                            :style="{
                                width:
                                    Math.min(
                                        budget.utilization_percentage,
                                        100,
                                    ) + '%',
                            }"
                            class="h-2 rounded-full transition-all"
                        ></div>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{
                            Number(budget.utilization_percentage || 0).toFixed(
                                1,
                            )
                        }}% utilized
                    </div>
                </div>

                <div v-if="budget.items" class="mt-4 text-sm text-gray-600">
                    <i class="fas fa-list mr-1"></i>
                    {{ budget.items.length }} line items
                </div>

                <!-- Approval Buttons -->
                <div
                    v-if="canApprove(budget)"
                    class="mt-4 pt-4 border-t border-gray-200 flex gap-2"
                    @click.stop
                >
                    <button
                        @click="handleApproveBudget(budget)"
                        class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium"
                    >
                        <i class="fas fa-check mr-1"></i>
                        Approve
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-calculator text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">
                No Budgets Found
            </h3>
            <p class="text-gray-500 mb-4">
                {{
                    hasActiveFilters
                        ? "Try adjusting your filters"
                        : "Get started by creating your first budget"
                }}
            </p>
            <button
                v-if="canCreate && !hasActiveFilters"
                @click="openAddBudgetModal"
                class="inline-block px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition"
            >
                <i class="fas fa-plus mr-2"></i>
                Create First Budget
            </button>
        </div>

        <!-- Pagination -->
        <div
            v-if="pagination.total > pagination.per_page"
            class="mt-6 flex justify-center"
        >
            <div class="flex gap-2">
                <button
                    @click="goToPage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="px-3 py-1 text-sm text-gray-600">
                    Page {{ pagination.current_page }} of
                    {{ pagination.last_page }}
                </span>
                <button
                    @click="goToPage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Add Budget Modal -->
        <AddBudgetModal
            :is-open="showAddBudgetModal"
            :projects="projects"
            :donors="donors"
            @close="showAddBudgetModal = false"
            @budget-created="handleBudgetCreated"
        />

        <!-- Edit Budget Modal -->
        <EditBudgetModal
            :is-open="showEditBudgetModal"
            :budget="selectedBudget"
            :projects="projects"
            :donors="donors"
            @close="showEditBudgetModal = false"
            @budget-updated="handleBudgetUpdated"
        />

        <!-- View Budget Modal -->
        <ViewBudgetModal
            :is-open="showViewBudgetModal"
            :budget="selectedBudget"
            @close="showViewBudgetModal = false"
            @edit="handleViewEdit"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useBudgetStore } from "../../stores/budgetStore";
import { useProjectStore } from "../../stores/projectStore";
import { useAuthStore } from "../../stores/authStore";
import { confirmAction, showSuccess, showError } from "@/plugins/sweetalert";
import AddBudgetModal from "../../components/modals/AddBudgetModal.vue";
import EditBudgetModal from "../../components/modals/EditBudgetModal.vue";
import ViewBudgetModal from "../../components/modals/ViewBudgetModal.vue";

const budgetStore = useBudgetStore();
const projectStore = useProjectStore();
const authStore = useAuthStore();

const statusFilter = ref("");
const projectFilter = ref("");

// Modal state
const showAddBudgetModal = ref(false);
const showEditBudgetModal = ref(false);
const showViewBudgetModal = ref(false);
const selectedBudget = ref(null);

const budgets = computed(() => budgetStore.budgets || []);
const projects = computed(() => projectStore.projects || []);
const donors = computed(() => projectStore.donors);
const loading = computed(() => budgetStore.loading);
const pagination = computed(() => budgetStore.pagination);

const hasActiveFilters = computed(
    () => statusFilter.value || projectFilter.value,
);

const canCreate = computed(() => authStore.hasPermission("create-budget"));

const canApprove = (budget) => {
    // Only show approve button for pending budgets
    // and if user has approval permission
    return (
        (budget.status === "pending" || budget.status === "draft") &&
        authStore.hasPermission("approve-budget")
    );
};

const handleApproveBudget = async (budget) => {
    const confirmed = await confirmAction(
        "Approve Budget?",
        `Are you sure you want to approve Budget #${budget.id} for ${budget.project?.name}?`,
        "Yes, Approve",
    );

    if (confirmed) {
        try {
            await budgetStore.approveBudget(budget.id, "");
            showSuccess("Budget approved successfully!");
            // Reload budgets to show updated status
            await loadBudgets();
        } catch (error) {
            console.error("Error approving budget:", error);
            showError(
                error.response?.data?.message || "Failed to approve budget",
            );
        }
    }
};

const handleFilter = () => {
    budgetStore.setStatusFilter(statusFilter.value);
    budgetStore.setProjectFilter(projectFilter.value);
    loadBudgets(1);
};

const loadBudgets = async (page = 1) => {
    await budgetStore.fetchBudgets(page);
};

const goToPage = (page) => {
    loadBudgets(page);
};

// Modal methods
const openAddBudgetModal = () => {
    showAddBudgetModal.value = true;
};

const viewBudget = (budget) => {
    selectedBudget.value = budget;
    showViewBudgetModal.value = true;
};

const editBudget = (budget) => {
    selectedBudget.value = budget;
    showEditBudgetModal.value = true;
};

const handleViewEdit = (budget) => {
    showViewBudgetModal.value = false;
    selectedBudget.value = budget;
    showEditBudgetModal.value = true;
};

const handleBudgetCreated = () => {
    showAddBudgetModal.value = false;
    loadBudgets();
};

const handleBudgetUpdated = () => {
    showEditBudgetModal.value = false;
    selectedBudget.value = null;
    loadBudgets();
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
    };
    return classes[status] || classes.pending;
};

const getStatusLabel = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const getUtilizationColorClass = (percentage) => {
    if (percentage >= 100) return "bg-red-600";
    if (percentage >= 90) return "bg-orange-500";
    if (percentage >= 50) return "bg-yellow-500";
    return "bg-green-500";
};

const formatNumber = (num) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num || 0);
};

onMounted(async () => {
    // Prevent data fetching if not authenticated or session is locked
    if (
        !authStore.isAuthenticated ||
        authStore.isSessionLocked ||
        window.isLoggingOut
    ) {
        console.warn(
            "Skipping data load - user not authenticated or session locked",
        );
        return;
    }

    await loadBudgets();
    await projectStore.fetchProjects();
    await projectStore.fetchDonors();
});
</script>
