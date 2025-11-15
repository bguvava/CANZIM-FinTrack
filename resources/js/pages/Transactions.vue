<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Cash Flow Transactions
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Track and manage all cash inflow and outflow
                        transactions
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="modals.reconcile = true"
                        class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-900"
                    >
                        <i class="fas fa-balance-scale"></i>
                        Reconcile
                    </button>
                    <button
                        @click="modals.inflow = true"
                        class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-700"
                    >
                        <i class="fas fa-arrow-down"></i>
                        Record Inflow
                    </button>
                    <button
                        @click="modals.outflow = true"
                        class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700"
                    >
                        <i class="fas fa-arrow-up"></i>
                        Record Outflow
                    </button>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Search
                        </label>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Description or project..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Type
                        </label>
                        <select
                            v-model="typeFilter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option value="inflow">Inflow</option>
                            <option value="outflow">Outflow</option>
                        </select>
                    </div>

                    <!-- Bank Account Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Bank Account
                        </label>
                        <select
                            v-model="bankAccountFilter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="applyFilters"
                        >
                            <option value="">All Accounts</option>
                            <option
                                v-for="account in cashFlowStore.bankAccounts"
                                :key="account.id"
                                :value="account.id"
                            >
                                {{ account.account_name }}
                            </option>
                        </select>
                    </div>

                    <!-- Reconciliation Status Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>
                        <select
                            v-model="reconciliationFilter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="reconciled">Reconciled</option>
                            <option value="unreconciled">Unreconciled</option>
                        </select>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div v-if="hasActiveFilters" class="mt-3 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="text-sm text-blue-800 hover:text-blue-900 font-medium"
                    >
                        <i class="fas fa-times-circle mr-1"></i>
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Total Inflows
                            </p>
                            <p class="mt-2 text-2xl font-bold text-green-600">
                                ${{ formatCurrency(totalInflows) }}
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100"
                        >
                            <i class="fas fa-arrow-down text-green-800"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Total Outflows
                            </p>
                            <p class="mt-2 text-2xl font-bold text-red-600">
                                ${{ formatCurrency(totalOutflows) }}
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100"
                        >
                            <i class="fas fa-arrow-up text-red-800"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Net Cash Flow
                            </p>
                            <p
                                class="mt-2 text-2xl font-bold"
                                :class="
                                    netCashFlow >= 0
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                ${{ formatCurrency(netCashFlow) }}
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100"
                        >
                            <i class="fas fa-exchange-alt text-blue-800"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Unreconciled
                            </p>
                            <p class="mt-2 text-2xl font-bold text-yellow-600">
                                {{ unreconciledCount }}
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100"
                        >
                            <i class="fas fa-clock text-yellow-800"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Loading State -->
                <div v-if="cashFlowStore.loading" class="p-8 text-center">
                    <i
                        class="fas fa-spinner fa-spin text-3xl text-blue-800"
                    ></i>
                    <p class="mt-2 text-sm text-gray-600">
                        Loading transactions...
                    </p>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="filteredTransactions.length === 0"
                    class="p-8 text-center"
                >
                    <i class="fas fa-exchange-alt text-4xl text-gray-400"></i>
                    <p class="mt-2 text-sm font-medium text-gray-900">
                        No transactions found
                    </p>
                    <p class="text-sm text-gray-600">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters"
                                : "Get started by recording a transaction"
                        }}
                    </p>
                </div>

                <!-- Transactions Table -->
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Description
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Bank Account
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Amount
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Balance After
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="transaction in filteredTransactions"
                                :key="transaction.id"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{
                                        formatDate(transaction.transaction_date)
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            transaction.type === 'inflow'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800'
                                        "
                                    >
                                        <i
                                            class="mr-1"
                                            :class="
                                                transaction.type === 'inflow'
                                                    ? 'fas fa-arrow-down'
                                                    : 'fas fa-arrow-up'
                                            "
                                        ></i>
                                        {{
                                            transaction.type === "inflow"
                                                ? "Inflow"
                                                : "Outflow"
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs">
                                        <p class="truncate font-medium">
                                            {{ transaction.description }}
                                        </p>
                                        <p
                                            v-if="transaction.project"
                                            class="text-gray-500 truncate"
                                        >
                                            {{ transaction.project.name }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ transaction.bank_account?.account_name }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold"
                                    :class="
                                        transaction.type === 'inflow'
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{
                                        transaction.type === "inflow"
                                            ? "+"
                                            : "-"
                                    }}${{ formatCurrency(transaction.amount) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900"
                                >
                                    ${{
                                        formatCurrency(
                                            transaction.balance_after,
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center"
                                >
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            transaction.is_reconciled
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-yellow-100 text-yellow-800'
                                        "
                                    >
                                        {{
                                            transaction.is_reconciled
                                                ? "Reconciled"
                                                : "Unreconciled"
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                                >
                                    <button
                                        @click="openViewModal(transaction)"
                                        class="text-blue-800 hover:text-blue-900"
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="
                        cashFlowStore.pagination &&
                        cashFlowStore.pagination.last_page > 1
                    "
                    class="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-3"
                >
                    <div class="flex flex-1 justify-between sm:hidden">
                        <button
                            @click="
                                loadPage(
                                    cashFlowStore.pagination.current_page - 1,
                                )
                            "
                            :disabled="
                                cashFlowStore.pagination.current_page === 1
                            "
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="
                                loadPage(
                                    cashFlowStore.pagination.current_page + 1,
                                )
                            "
                            :disabled="
                                cashFlowStore.pagination.current_page ===
                                cashFlowStore.pagination.last_page
                            "
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                    <div
                        class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{
                                    (cashFlowStore.pagination.current_page -
                                        1) *
                                        cashFlowStore.pagination.per_page +
                                    1
                                }}</span>
                                to
                                <span class="font-medium">{{
                                    Math.min(
                                        cashFlowStore.pagination.current_page *
                                            cashFlowStore.pagination.per_page,
                                        cashFlowStore.pagination.total,
                                    )
                                }}</span>
                                of
                                <span class="font-medium">{{
                                    cashFlowStore.pagination.total
                                }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav
                                class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                            >
                                <button
                                    @click="
                                        loadPage(
                                            cashFlowStore.pagination
                                                .current_page - 1,
                                        )
                                    "
                                    :disabled="
                                        cashFlowStore.pagination
                                            .current_page === 1
                                    "
                                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-50"
                                >
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button
                                    @click="
                                        loadPage(
                                            cashFlowStore.pagination
                                                .current_page + 1,
                                        )
                                    "
                                    :disabled="
                                        cashFlowStore.pagination
                                            .current_page ===
                                        cashFlowStore.pagination.last_page
                                    "
                                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-50"
                                >
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <RecordInflowModal
            :is-open="modals.inflow"
            @close="modals.inflow = false"
            @inflow-recorded="handleTransactionRecorded"
        />

        <RecordOutflowModal
            :is-open="modals.outflow"
            @close="modals.outflow = false"
            @outflow-recorded="handleTransactionRecorded"
        />

        <ViewTransactionModal
            :is-open="modals.view"
            :transaction="selectedTransaction"
            @close="modals.view = false"
        />

        <BankReconciliationModal
            :is-open="modals.reconcile"
            @close="modals.reconcile = false"
            @reconciled="handleReconciled"
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useCashFlowStore } from "../stores/cashFlowStore";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import RecordInflowModal from "../components/modals/RecordInflowModal.vue";
import RecordOutflowModal from "../components/modals/RecordOutflowModal.vue";
import ViewTransactionModal from "../components/modals/ViewTransactionModal.vue";
import BankReconciliationModal from "../components/modals/BankReconciliationModal.vue";

const cashFlowStore = useCashFlowStore();

// Filters
const searchQuery = ref("");
const typeFilter = ref("");
const bankAccountFilter = ref("");
const reconciliationFilter = ref("");
let searchTimeout = null;

// Modals
const modals = ref({
    inflow: false,
    outflow: false,
    view: false,
    reconcile: false,
});

const selectedTransaction = ref(null);

// Computed
const hasActiveFilters = computed(() => {
    return (
        searchQuery.value ||
        typeFilter.value ||
        bankAccountFilter.value ||
        reconciliationFilter.value
    );
});

const filteredTransactions = computed(() => {
    let transactions = cashFlowStore.cashFlows;

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        transactions = transactions.filter(
            (t) =>
                t.description?.toLowerCase().includes(query) ||
                t.project?.name?.toLowerCase().includes(query) ||
                t.donor?.toLowerCase().includes(query),
        );
    }

    // Apply type filter
    if (typeFilter.value) {
        transactions = transactions.filter((t) => t.type === typeFilter.value);
    }

    // Apply bank account filter
    if (bankAccountFilter.value) {
        transactions = transactions.filter(
            (t) => t.bank_account_id === parseInt(bankAccountFilter.value),
        );
    }

    // Apply reconciliation filter
    if (reconciliationFilter.value) {
        const isReconciled = reconciliationFilter.value === "reconciled";
        transactions = transactions.filter(
            (t) => t.is_reconciled === isReconciled,
        );
    }

    return transactions;
});

const totalInflows = computed(() => {
    return filteredTransactions.value
        .filter((t) => t.type === "inflow")
        .reduce((sum, t) => sum + parseFloat(t.amount), 0);
});

const totalOutflows = computed(() => {
    return filteredTransactions.value
        .filter((t) => t.type === "outflow")
        .reduce((sum, t) => sum + parseFloat(t.amount), 0);
});

const netCashFlow = computed(() => {
    return totalInflows.value - totalOutflows.value;
});

const unreconciledCount = computed(() => {
    return filteredTransactions.value.filter((t) => !t.is_reconciled).length;
});

// Methods
const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        // Search is done client-side via computed property
    }, 300);
};

const applyFilters = () => {
    // Filters applied via computed property
};

const clearFilters = () => {
    searchQuery.value = "";
    typeFilter.value = "";
    bankAccountFilter.value = "";
    reconciliationFilter.value = "";
};

const loadPage = async (page) => {
    if (page < 1 || page > cashFlowStore.pagination.last_page) return;
    await cashFlowStore.fetchCashFlows(page);
};

// Modal handlers
const openViewModal = (transaction) => {
    selectedTransaction.value = transaction;
    modals.value.view = true;
};

const handleTransactionRecorded = async () => {
    await Promise.all([
        cashFlowStore.fetchCashFlows(),
        cashFlowStore.fetchBankAccounts(),
    ]);
};

const handleReconciled = async () => {
    await cashFlowStore.fetchCashFlows();
};

// Lifecycle
onMounted(async () => {
    await Promise.all([
        cashFlowStore.fetchCashFlows(),
        cashFlowStore.fetchBankAccounts(),
    ]);
});
</script>
