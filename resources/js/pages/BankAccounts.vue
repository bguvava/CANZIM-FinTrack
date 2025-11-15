<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Bank Accounts
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage bank accounts and monitor cash balances
                    </p>
                </div>
                <button
                    @click="openAddModal"
                    class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-900"
                >
                    <i class="fas fa-plus"></i>
                    Add Bank Account
                </button>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
                            placeholder="Account name or bank..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>
                        <select
                            v-model="statusFilter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Bank Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Bank
                        </label>
                        <select
                            v-model="bankFilter"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="applyFilters"
                        >
                            <option value="">All Banks</option>
                            <option
                                v-for="bank in uniqueBanks"
                                :key="bank"
                                :value="bank"
                            >
                                {{ bank }}
                            </option>
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
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Total Accounts
                            </p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ cashFlowStore.bankAccounts.length }}
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100"
                        >
                            <i class="fas fa-university text-blue-800"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Active Accounts
                            </p>
                            <p class="mt-2 text-3xl font-bold text-green-600">
                                {{ cashFlowStore.activeBankAccounts.length }}
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100"
                        >
                            <i class="fas fa-check-circle text-green-800"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Total Balance
                            </p>
                            <p
                                class="mt-2 text-3xl font-bold"
                                :class="
                                    cashFlowStore.totalBalance >= 0
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                ${{
                                    formatCurrency(cashFlowStore.totalBalance)
                                }}
                            </p>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100"
                        >
                            <i class="fas fa-wallet text-purple-800"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Accounts Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Loading State -->
                <div v-if="cashFlowStore.loading" class="p-8 text-center">
                    <i
                        class="fas fa-spinner fa-spin text-3xl text-blue-800"
                    ></i>
                    <p class="mt-2 text-sm text-gray-600">
                        Loading bank accounts...
                    </p>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="filteredAccounts.length === 0"
                    class="p-8 text-center"
                >
                    <i class="fas fa-university text-4xl text-gray-400"></i>
                    <p class="mt-2 text-sm font-medium text-gray-900">
                        No bank accounts found
                    </p>
                    <p class="text-sm text-gray-600">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters"
                                : "Get started by adding a bank account"
                        }}
                    </p>
                </div>

                <!-- Accounts Table -->
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Account Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Bank
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Account Number
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Current Balance
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
                                v-for="account in filteredAccounts"
                                :key="account.id"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100"
                                        >
                                            <i
                                                class="fas fa-university text-blue-800 text-sm"
                                            ></i>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ account.account_name }}
                                            </div>
                                            <div
                                                v-if="account.branch"
                                                class="text-sm text-gray-500"
                                            >
                                                {{ account.branch }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ account.bank_name }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ account.account_number }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold"
                                    :class="
                                        account.current_balance >= 0
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    ${{
                                        formatCurrency(account.current_balance)
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center"
                                >
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            account.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        "
                                    >
                                        {{
                                            account.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                                >
                                    <div
                                        class="flex items-center justify-center gap-3"
                                    >
                                        <button
                                            @click="openViewModal(account)"
                                            class="text-blue-800 hover:text-blue-900"
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button
                                            @click="openEditModal(account)"
                                            class="text-green-600 hover:text-green-700"
                                            title="Edit Account"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            v-if="account.is_active"
                                            @click="handleDeactivate(account)"
                                            class="text-red-600 hover:text-red-700"
                                            title="Deactivate"
                                        >
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <button
                                            v-else
                                            @click="handleActivate(account)"
                                            class="text-green-600 hover:text-green-700"
                                            title="Activate"
                                        >
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </div>
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
        <AddBankAccountModal
            :is-open="modals.add"
            @close="modals.add = false"
            @account-created="handleAccountCreated"
        />

        <EditBankAccountModal
            :is-open="modals.edit"
            :bank-account="selectedAccount"
            @close="modals.edit = false"
            @account-updated="handleAccountUpdated"
        />

        <ViewBankAccountModal
            :is-open="modals.view"
            :bank-account="selectedAccount"
            @close="modals.view = false"
            @edit="handleEditFromView"
            @status-changed="handleStatusChanged"
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useCashFlowStore } from "../stores/cashFlowStore";
import { showSuccess, showError, showConfirm } from "../plugins/sweetalert";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import AddBankAccountModal from "../components/modals/AddBankAccountModal.vue";
import EditBankAccountModal from "../components/modals/EditBankAccountModal.vue";
import ViewBankAccountModal from "../components/modals/ViewBankAccountModal.vue";

const cashFlowStore = useCashFlowStore();

// Filters
const searchQuery = ref("");
const statusFilter = ref("");
const bankFilter = ref("");
let searchTimeout = null;

// Modals
const modals = ref({
    add: false,
    edit: false,
    view: false,
});

const selectedAccount = ref(null);

// Computed
const hasActiveFilters = computed(() => {
    return searchQuery.value || statusFilter.value || bankFilter.value;
});

const uniqueBanks = computed(() => {
    const banks = cashFlowStore.bankAccounts.map((acc) => acc.bank_name);
    return [...new Set(banks)].sort();
});

const filteredAccounts = computed(() => {
    let accounts = cashFlowStore.bankAccounts;

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        accounts = accounts.filter(
            (acc) =>
                acc.account_name.toLowerCase().includes(query) ||
                acc.bank_name.toLowerCase().includes(query) ||
                acc.account_number.toLowerCase().includes(query),
        );
    }

    // Apply status filter
    if (statusFilter.value) {
        const isActive = statusFilter.value === "active";
        accounts = accounts.filter((acc) => acc.is_active === isActive);
    }

    // Apply bank filter
    if (bankFilter.value) {
        accounts = accounts.filter((acc) => acc.bank_name === bankFilter.value);
    }

    return accounts;
});

// Methods
const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
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
    statusFilter.value = "";
    bankFilter.value = "";
};

const loadPage = async (page) => {
    if (page < 1 || page > cashFlowStore.pagination.last_page) return;
    await cashFlowStore.fetchBankAccounts(page);
};

// Modal handlers
const openAddModal = () => {
    modals.value.add = true;
};

const openEditModal = (account) => {
    selectedAccount.value = account;
    modals.value.edit = true;
};

const openViewModal = (account) => {
    selectedAccount.value = account;
    modals.value.view = true;
};

const handleEditFromView = (account) => {
    modals.value.view = false;
    setTimeout(() => {
        selectedAccount.value = account;
        modals.value.edit = true;
    }, 300);
};

const handleAccountCreated = async () => {
    modals.value.add = false;
    await cashFlowStore.fetchBankAccounts();
};

const handleAccountUpdated = async () => {
    modals.value.edit = false;
    await cashFlowStore.fetchBankAccounts();
};

const handleStatusChanged = async () => {
    await cashFlowStore.fetchBankAccounts();
};

const handleDeactivate = async (account) => {
    const confirmed = await showConfirm(
        "Deactivate Bank Account",
        "Are you sure you want to deactivate this bank account? This will prevent new transactions.",
    );
    if (!confirmed) return;

    try {
        await cashFlowStore.deactivateBankAccount(account.id);
        showSuccess("Bank account deactivated successfully!");
        await cashFlowStore.fetchBankAccounts();
    } catch (error) {
        showError("Failed to deactivate bank account.");
    }
};

const handleActivate = async (account) => {
    try {
        await cashFlowStore.activateBankAccount(account.id);
        showSuccess("Bank account activated successfully!");
        await cashFlowStore.fetchBankAccounts();
    } catch (error) {
        showError("Failed to activate bank account.");
    }
};

// Lifecycle
onMounted(async () => {
    await cashFlowStore.fetchBankAccounts();
});
</script>
