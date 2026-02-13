<template>
    <div
        v-if="isOpen && bankAccount"
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
            @click="closeModal"
        ></div>

        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div
                class="relative w-full max-w-4xl transform rounded-lg bg-white shadow-xl transition-all"
                @click.stop
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3 class="text-lg font-semibold text-gray-900">
                        Bank Account Details
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="px-6 py-6">
                    <div class="max-h-[70vh] overflow-y-auto space-y-6">
                        <!-- Account Information Card -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4
                                class="text-sm font-semibold text-gray-900 mb-4"
                            >
                                Account Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Account Name
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{ bankAccount.account_name }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Bank Name
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{ bankAccount.bank_name }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Branch
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{ bankAccount.branch || "N/A" }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Account Number
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{ bankAccount.account_number }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Currency
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{ bankAccount.currency }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Status
                                    </p>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="
                                            bankAccount.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        "
                                    >
                                        {{
                                            bankAccount.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="bankAccount.description" class="mt-6">
                                <p class="text-sm text-gray-500 mb-1">
                                    Description
                                </p>
                                <p class="text-base text-gray-900">
                                    {{ bankAccount.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Balance Summary Card -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h4
                                class="text-sm font-semibold text-gray-900 mb-4"
                            >
                                Balance Summary
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        Opening Balance
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        ${{
                                            formatCurrency(
                                                bankAccount.opening_balance,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        Total Inflows
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-600"
                                    >
                                        +${{
                                            formatCurrency(
                                                summary.total_inflows,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        Total Outflows
                                    </p>
                                    <p class="text-2xl font-bold text-red-600">
                                        -${{
                                            formatCurrency(
                                                summary.total_outflows,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6 pt-6 border-t border-blue-200">
                                <p class="text-sm text-gray-600 mb-1">
                                    Current Balance
                                </p>
                                <p
                                    class="text-3xl font-bold"
                                    :class="
                                        bankAccount.current_balance >= 0
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    ${{
                                        formatCurrency(
                                            bankAccount.current_balance,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-semibold text-gray-900">
                                    Recent Transactions
                                </h4>
                                <p class="text-sm text-gray-500">
                                    Showing last 10 transactions
                                </p>
                            </div>
                            <div
                                v-if="loading"
                                class="text-center py-8 text-gray-500"
                            >
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Loading transactions...
                            </div>
                            <div
                                v-else-if="
                                    !summary.recent_transactions ||
                                    summary.recent_transactions.length === 0
                                "
                                class="text-center py-8 text-gray-500"
                            >
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>No transactions found</p>
                            </div>
                            <div
                                v-else
                                class="overflow-x-auto border border-gray-200 rounded-lg"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Date
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Type
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Description
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Amount
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Balance After
                                            </th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200"
                                    >
                                        <tr
                                            v-for="transaction in summary.recent_transactions"
                                            :key="transaction.id"
                                            class="hover:bg-gray-50"
                                        >
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"
                                            >
                                                {{
                                                    formatDate(
                                                        transaction.transaction_date,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm"
                                            >
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="
                                                        transaction.type ===
                                                        'inflow'
                                                            ? 'bg-green-100 text-green-800'
                                                            : 'bg-red-100 text-red-800'
                                                    "
                                                >
                                                    {{
                                                        transaction.type ===
                                                        "inflow"
                                                            ? "Inflow"
                                                            : "Outflow"
                                                    }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-sm text-gray-900"
                                            >
                                                {{
                                                    transaction.description ||
                                                    "N/A"
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium"
                                                :class="
                                                    transaction.type ===
                                                    'inflow'
                                                        ? 'text-green-600'
                                                        : 'text-red-600'
                                                "
                                            >
                                                {{
                                                    transaction.type ===
                                                    "inflow"
                                                        ? "+"
                                                        : "-"
                                                }}${{
                                                    formatCurrency(
                                                        transaction.amount,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900"
                                            >
                                                ${{
                                                    formatCurrency(
                                                        transaction.balance_after,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-center"
                                            >
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
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
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-between gap-3 border-t border-gray-200 pt-4 mt-6"
                    >
                        <button
                            v-if="bankAccount.is_active"
                            @click="handleDeactivate"
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition"
                            :disabled="actionLoading"
                        >
                            <i
                                v-if="actionLoading"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            <i
                                v-if="!actionLoading"
                                class="fas fa-ban mr-1.5"
                            ></i>
                            {{
                                actionLoading
                                    ? "Processing..."
                                    : "Deactivate Account"
                            }}
                        </button>
                        <button
                            v-else
                            @click="handleActivate"
                            class="px-4 py-2 border border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition"
                            :disabled="actionLoading"
                        >
                            <i
                                v-if="actionLoading"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            <i
                                v-if="!actionLoading"
                                class="fas fa-check-circle mr-1.5"
                            ></i>
                            {{
                                actionLoading
                                    ? "Processing..."
                                    : "Activate Account"
                            }}
                        </button>
                        <div class="flex gap-3">
                            <button
                                @click="closeModal"
                                class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition"
                            >
                                <i class="fas fa-times mr-1.5"></i>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useCashFlowStore } from "../../stores/cashFlowStore";
import { showSuccess, showError, showConfirm } from "../../plugins/sweetalert";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    bankAccount: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "edit", "status-changed"]);

const cashFlowStore = useCashFlowStore();

const loading = ref(false);
const actionLoading = ref(false);
const summary = ref({
    total_inflows: 0,
    total_outflows: 0,
    recent_transactions: [],
});

// Fetch account summary when modal opens
watch(
    () => [props.isOpen, props.bankAccount],
    async ([isOpen, account]) => {
        if (isOpen && account) {
            await fetchSummary();
        }
    },
    { immediate: true },
);

const fetchSummary = async () => {
    if (!props.bankAccount) return;

    loading.value = true;
    try {
        const data = await cashFlowStore.getBankAccountSummary(
            props.bankAccount.id,
        );
        summary.value = data;
    } catch (error) {
        showError("Failed to load account summary.");
    } finally {
        loading.value = false;
    }
};

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

const closeModal = () => {
    emit("close");
};

const handleEdit = () => {
    emit("edit", props.bankAccount);
};

const handleDeactivate = async () => {
    const confirmed = await showConfirm(
        "Are you sure you want to deactivate this bank account?",
        "This will prevent new transactions from being recorded.",
    );
    if (!confirmed) return;

    actionLoading.value = true;
    try {
        await cashFlowStore.deactivateBankAccount(props.bankAccount.id);
        showSuccess("Bank account deactivated successfully!");
        emit("status-changed");
    } catch (error) {
        showError("Failed to deactivate bank account.");
    } finally {
        actionLoading.value = false;
    }
};

const handleActivate = async () => {
    actionLoading.value = true;
    try {
        await cashFlowStore.activateBankAccount(props.bankAccount.id);
        showSuccess("Bank account activated successfully!");
        emit("status-changed");
    } catch (error) {
        showError("Failed to activate bank account.");
    } finally {
        actionLoading.value = false;
    }
};
</script>
