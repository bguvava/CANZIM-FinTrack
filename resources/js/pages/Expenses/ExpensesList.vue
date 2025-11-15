<template>
    <div class="expenses-list-container">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Expenses</h1>
                <p class="text-gray-600 mt-1">
                    Track and manage project expenses
                </p>
            </div>
            <button
                v-if="canCreate"
                @click="goToCreateExpense"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
            >
                <i class="fas fa-plus"></i>
                New Expense
            </button>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search Expenses
                    </label>
                    <div class="relative">
                        <input
                            v-model="expenseStore.filters.search"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search by number or description..."
                            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                        <i
                            class="fas fa-search absolute left-3 top-3 text-gray-400"
                        ></i>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select
                        v-model="expenseStore.filters.status"
                        @change="handleFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="Draft">Draft</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Reviewed">Reviewed</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Paid">Paid</option>
                    </select>
                </div>

                <!-- Project Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Project
                    </label>
                    <select
                        v-model="expenseStore.filters.project_id"
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

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Category
                    </label>
                    <select
                        v-model="expenseStore.filters.expense_category_id"
                        @change="handleFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Categories</option>
                        <option
                            v-for="category in expenseStore.categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Clear Filters Button -->
            <div class="mt-4 flex justify-end">
                <button
                    @click="clearFilters"
                    class="px-4 py-2 text-gray-600 hover:text-gray-900 transition"
                >
                    <i class="fas fa-times mr-2"></i>
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div
            v-if="expenseStore.loading"
            class="bg-white rounded-lg shadow-sm p-8 text-center"
        >
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading expenses...</p>
        </div>

        <!-- Error State -->
        <div
            v-else-if="expenseStore.error"
            class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
        >
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                <p class="text-red-800">
                    {{ expenseStore.error }}
                </p>
            </div>
        </div>

        <!-- Expenses Table -->
        <div
            v-else-if="expenseStore.filteredExpenses.length > 0"
            class="bg-white rounded-lg shadow-sm overflow-hidden"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Expense #
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Project
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Category
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Amount
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="expense in expenseStore.filteredExpenses"
                            :key="expense.id"
                            class="hover:bg-gray-50 transition"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ expense.expense_number }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ expense.project?.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    {{ expense.category?.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div
                                    class="text-sm font-semibold text-gray-900"
                                >
                                    ${{ formatAmount(expense.amount) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">
                                    {{ formatDate(expense.expense_date) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(expense.status)">
                                    {{ expense.status }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                            >
                                <button
                                    @click="viewExpense(expense.id)"
                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                    title="View Details"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button
                                    v-if="canEdit(expense)"
                                    @click="goToCreateExpense(expense.id)"
                                    class="text-green-600 hover:text-green-900 mr-3"
                                    title="Edit"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    v-if="canDelete(expense)"
                                    @click="confirmDelete(expense)"
                                    class="text-red-600 hover:text-red-900"
                                    title="Delete"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200"
            >
                <div class="flex items-center">
                    <span class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ paginationStart }}</span>
                        to
                        <span class="font-medium">{{ paginationEnd }}</span>
                        of
                        <span class="font-medium">{{
                            expenseStore.pagination.total
                        }}</span>
                        results
                    </span>
                </div>

                <div class="flex gap-2">
                    <button
                        @click="
                            changePage(expenseStore.pagination.current_page - 1)
                        "
                        :disabled="expenseStore.pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        @click="changePage(page)"
                        :class="[
                            'px-3 py-1 border rounded-lg transition',
                            page === expenseStore.pagination.current_page
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'border-gray-300 hover:bg-gray-100',
                        ]"
                    >
                        {{ page }}
                    </button>

                    <button
                        @click="
                            changePage(expenseStore.pagination.current_page + 1)
                        "
                        :disabled="
                            expenseStore.pagination.current_page ===
                            expenseStore.pagination.last_page
                        "
                        class="px-3 py-1 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 transition"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                No expenses found
            </h3>
            <p class="text-gray-600 mb-6">
                {{
                    hasFilters
                        ? "Try adjusting your filters"
                        : "Get started by creating your first expense"
                }}
            </p>
            <button
                v-if="canCreate && !hasFilters"
                @click="goToCreateExpense"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                <i class="fas fa-plus mr-2"></i>
                Create First Expense
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useExpenseStore } from "@/stores/expenseStore";
import { useAuthStore } from "@/stores/authStore";
import { useProjectStore } from "@/stores/projectStore";

const router = useRouter();
const expenseStore = useExpenseStore();
const authStore = useAuthStore();
const projectStore = useProjectStore();

const projects = ref([]);

// Permissions
const canCreate = computed(() => {
    const role = authStore.user?.role?.slug;
    return role === "project-officer";
});

const canEdit = (expense) => {
    const role = authStore.user?.role?.slug;
    const userId = authStore.user?.id;

    // Project officers can edit their own draft expenses
    if (role === "project-officer" && expense.submitted_by === userId) {
        return expense.status === "Draft";
    }

    return false;
};

const canDelete = (expense) => {
    const role = authStore.user?.role?.slug;
    const userId = authStore.user?.id;

    // Project officers can delete their own draft expenses
    if (role === "project-officer" && expense.submitted_by === userId) {
        return expense.status === "Draft";
    }

    // Programs managers can delete any expense
    return role === "programs-manager";
};

// Computed properties
const hasFilters = computed(() => {
    return (
        expenseStore.filters.search ||
        expenseStore.filters.status ||
        expenseStore.filters.project_id ||
        expenseStore.filters.expense_category_id
    );
});

const paginationStart = computed(() => {
    return (
        (expenseStore.pagination.current_page - 1) *
            expenseStore.pagination.per_page +
        1
    );
});

const paginationEnd = computed(() => {
    return Math.min(
        expenseStore.pagination.current_page * expenseStore.pagination.per_page,
        expenseStore.pagination.total,
    );
});

const visiblePages = computed(() => {
    const current = expenseStore.pagination.current_page;
    const last = expenseStore.pagination.last_page;
    const delta = 2;
    const range = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        range.unshift("...");
    }
    if (current + delta < last - 1) {
        range.push("...");
    }

    range.unshift(1);
    if (last > 1) {
        range.push(last);
    }

    return range.filter(
        (p) => p !== "..." || range.indexOf(p) === range.lastIndexOf(p),
    );
});

// Methods
const loadData = async () => {
    try {
        await Promise.all([
            expenseStore.fetchExpenses(),
            expenseStore.fetchCategories(),
            loadProjects(),
        ]);
    } catch (error) {
        console.error("Error loading data:", error);
    }
};

const loadProjects = async () => {
    try {
        await projectStore.fetchProjects();
        projects.value = projectStore.projects;
    } catch (error) {
        console.error("Error loading projects:", error);
    }
};

const handleSearch = () => {
    expenseStore.fetchExpenses();
};

const handleFilter = () => {
    expenseStore.fetchExpenses();
};

const clearFilters = () => {
    expenseStore.clearFilters();
    expenseStore.fetchExpenses();
};

const changePage = (page) => {
    if (page >= 1 && page <= expenseStore.pagination.last_page) {
        expenseStore.fetchExpenses(page);
    }
};

const goToCreateExpense = (id = null) => {
    if (id) {
        router.push({ name: "CreateExpense", params: { id } });
    } else {
        router.push({ name: "CreateExpense" });
    }
};

const viewExpense = (id) => {
    router.push({ name: "ViewExpense", params: { id } });
};

const confirmDelete = async (expense) => {
    if (
        confirm(
            `Are you sure you want to delete expense ${expense.expense_number}?`,
        )
    ) {
        try {
            await expenseStore.deleteExpense(expense.id);
            // Success notification would go here
        } catch (error) {
            console.error("Error deleting expense:", error);
            alert("Failed to delete expense. Please try again.");
        }
    }
};

const formatAmount = (amount) => {
    return Number(amount).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getStatusClass = (status) => {
    const baseClasses = "px-2 py-1 text-xs font-semibold rounded-full";

    const statusColors = {
        Draft: "bg-gray-100 text-gray-800",
        Submitted: "bg-blue-100 text-blue-800",
        "Under Review": "bg-yellow-100 text-yellow-800",
        Reviewed: "bg-purple-100 text-purple-800",
        Approved: "bg-green-100 text-green-800",
        Rejected: "bg-red-100 text-red-800",
        Paid: "bg-emerald-100 text-emerald-800",
    };

    return `${baseClasses} ${statusColors[status] || statusColors["Draft"]}`;
};

onMounted(() => {
    loadData();
});
</script>
