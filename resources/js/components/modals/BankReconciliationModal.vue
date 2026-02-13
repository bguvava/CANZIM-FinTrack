<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
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
                        Bank Reconciliation
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
                        <!-- Bank Account Selection -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Select Bank Account
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="selectedBankAccountId"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                @change="loadUnreconciledTransactions"
                            >
                                <option value="">
                                    Choose account to reconcile
                                </option>
                                <option
                                    v-for="account in cashFlowStore.activeBankAccounts"
                                    :key="account.id"
                                    :value="account.id"
                                >
                                    {{ account.account_name }} -
                                    {{ account.bank_name }} (Balance: ${{
                                        formatCurrency(account.current_balance)
                                    }})
                                </option>
                            </select>
                        </div>

                        <!-- Bank Statement Balance Input -->
                        <div
                            v-if="selectedBankAccountId"
                            class="bg-gray-50 p-4 rounded-lg"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        System Balance
                                    </label>
                                    <div
                                        class="text-2xl font-bold text-gray-900"
                                    >
                                        ${{ formatCurrency(systemBalance) }}
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Current balance in system
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Bank Statement Balance
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"
                                        >
                                            $
                                        </span>
                                        <input
                                            type="number"
                                            v-model="bankStatementBalance"
                                            step="0.01"
                                            class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Balance shown on bank statement
                                    </p>
                                </div>
                            </div>

                            <!-- Difference Display -->
                            <div
                                v-if="bankStatementBalance"
                                class="mt-4 p-3 rounded border"
                                :class="
                                    difference === 0
                                        ? 'bg-green-50 border-green-200'
                                        : 'bg-yellow-50 border-yellow-200'
                                "
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Difference:
                                    </span>
                                    <span
                                        class="text-lg font-bold"
                                        :class="
                                            difference === 0
                                                ? 'text-green-600'
                                                : 'text-yellow-600'
                                        "
                                    >
                                        ${{
                                            formatCurrency(Math.abs(difference))
                                        }}
                                        {{
                                            difference > 0
                                                ? "(System Higher)"
                                                : difference < 0
                                                  ? "(Bank Higher)"
                                                  : "(Matched)"
                                        }}
                                    </span>
                                </div>
                                <p
                                    v-if="difference !== 0"
                                    class="text-xs text-gray-600 mt-1"
                                >
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Select unreconciled transactions below to
                                    match balances
                                </p>
                            </div>
                        </div>

                        <!-- Unreconciled Transactions -->
                        <div v-if="selectedBankAccountId">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-gray-900">
                                    Unreconciled Transactions ({{
                                        unreconciledTransactions.length
                                    }})
                                </h4>
                                <button
                                    v-if="unreconciledTransactions.length > 0"
                                    @click="toggleSelectAll"
                                    class="text-sm text-blue-800 hover:text-blue-900 font-medium flex items-center gap-1"
                                >
                                    <i
                                        :class="
                                            allSelected
                                                ? 'fas fa-times-circle'
                                                : 'fas fa-check-double'
                                        "
                                    ></i>
                                    {{
                                        allSelected
                                            ? "Deselect All"
                                            : "Select All"
                                    }}
                                </button>
                            </div>

                            <!-- Loading State -->
                            <div
                                v-if="loading"
                                class="text-center py-8 text-gray-500"
                            >
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Loading transactions...
                            </div>

                            <!-- Empty State -->
                            <div
                                v-else-if="
                                    unreconciledTransactions.length === 0
                                "
                                class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg"
                            >
                                <i
                                    class="fas fa-check-circle text-4xl text-green-500 mb-2"
                                ></i>
                                <p class="text-sm font-medium text-gray-900">
                                    All transactions are reconciled
                                </p>
                                <p class="text-sm text-gray-600">
                                    No pending transactions for this account
                                </p>
                            </div>

                            <!-- Transactions List -->
                            <div
                                v-else
                                class="border border-gray-200 rounded-lg overflow-hidden"
                            >
                                <div class="max-h-80 overflow-y-auto">
                                    <table
                                        class="min-w-full divide-y divide-gray-200"
                                    >
                                        <thead class="bg-gray-50 sticky top-0">
                                            <tr>
                                                <th class="w-12 px-4 py-3">
                                                    <input
                                                        type="checkbox"
                                                        :checked="allSelected"
                                                        @change="
                                                            toggleSelectAll
                                                        "
                                                        class="rounded border-gray-300 text-blue-800 focus:ring-blue-500"
                                                    />
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                                >
                                                    Date
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                                >
                                                    Type
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                                >
                                                    Description
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase"
                                                >
                                                    Amount
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white divide-y divide-gray-200"
                                        >
                                            <tr
                                                v-for="transaction in unreconciledTransactions"
                                                :key="transaction.id"
                                                class="hover:bg-gray-50 transition"
                                            >
                                                <td class="px-4 py-3">
                                                    <input
                                                        type="checkbox"
                                                        v-model="
                                                            selectedTransactionIds
                                                        "
                                                        :value="transaction.id"
                                                        class="rounded border-gray-300 text-blue-800 focus:ring-blue-500"
                                                    />
                                                </td>
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
                                                    class="px-4 py-3 whitespace-nowrap"
                                                >
                                                    <span
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
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
                                                    class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate"
                                                >
                                                    {{
                                                        transaction.description
                                                    }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold"
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
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Selected Summary -->
                            <div
                                v-if="selectedTransactionIds.length > 0"
                                class="mt-3 p-3 bg-blue-50 rounded border border-blue-200"
                            >
                                <p class="text-sm text-gray-700">
                                    <i
                                        class="fas fa-info-circle mr-1 text-blue-600"
                                    ></i>
                                    <strong>{{
                                        selectedTransactionIds.length
                                    }}</strong>
                                    transaction(s) selected for reconciliation
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-end gap-3 border-t border-gray-200 pt-4 mt-6"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition"
                            :disabled="submitting"
                        >
                            <i class="fas fa-times mr-1.5"></i>Cancel
                        </button>
                        <button
                            @click="handleReconcile"
                            class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition disabled:opacity-50"
                            :disabled="!canReconcile || submitting"
                        >
                            <i
                                v-if="submitting"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            <i
                                v-if="!submitting"
                                class="fas fa-check-double mr-1.5"
                            ></i>
                            {{
                                submitting
                                    ? "Reconciling..."
                                    : "Reconcile Selected"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useCashFlowStore } from "../../stores/cashFlowStore";
import { Toast, showError, showConfirm } from "../../plugins/sweetalert";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["close", "reconciled"]);

const cashFlowStore = useCashFlowStore();

const selectedBankAccountId = ref("");
const bankStatementBalance = ref("");
const selectedTransactionIds = ref([]);
const unreconciledTransactions = ref([]);
const loading = ref(false);
const submitting = ref(false);

const systemBalance = computed(() => {
    const account = cashFlowStore.activeBankAccounts.find(
        (acc) => acc.id === parseInt(selectedBankAccountId.value),
    );
    return account ? parseFloat(account.current_balance) : 0;
});

const difference = computed(() => {
    if (!bankStatementBalance.value) return 0;
    return systemBalance.value - parseFloat(bankStatementBalance.value);
});

const allSelected = computed(() => {
    return (
        unreconciledTransactions.value.length > 0 &&
        selectedTransactionIds.value.length ===
            unreconciledTransactions.value.length
    );
});

const canReconcile = computed(() => {
    return (
        selectedBankAccountId.value &&
        selectedTransactionIds.value.length > 0 &&
        bankStatementBalance.value
    );
});

// Watch for modal opening to reset state
watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            resetForm();
        }
    },
);

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

const loadUnreconciledTransactions = async () => {
    if (!selectedBankAccountId.value) {
        unreconciledTransactions.value = [];
        return;
    }

    loading.value = true;
    try {
        // Filter unreconciled transactions for selected account
        const allTransactions = cashFlowStore.cashFlows;
        unreconciledTransactions.value = allTransactions.filter(
            (t) =>
                !t.is_reconciled &&
                t.bank_account_id === parseInt(selectedBankAccountId.value),
        );
    } catch (error) {
        showError("Failed to load unreconciled transactions.");
    } finally {
        loading.value = false;
    }
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedTransactionIds.value = [];
    } else {
        selectedTransactionIds.value = unreconciledTransactions.value.map(
            (t) => t.id,
        );
    }
};

const handleReconcile = async () => {
    if (!canReconcile.value) return;

    const confirmed = await showConfirm(
        "Reconcile Transactions",
        `Are you sure you want to reconcile ${selectedTransactionIds.value.length} transaction(s)?`,
    );
    if (!confirmed) return;

    submitting.value = true;
    try {
        const reconciliationDate = new Date().toISOString().split("T")[0];
        // Reconcile each selected transaction
        for (const transactionId of selectedTransactionIds.value) {
            await cashFlowStore.reconcileCashFlow(
                transactionId,
                reconciliationDate,
            );
        }

        const count = selectedTransactionIds.value.length;
        resetForm();
        emit("reconciled");
        closeModal();
        Toast.fire({
            icon: "success",
            title: `Successfully reconciled ${count} transaction(s)!`,
        });
    } catch (error) {
        showError("Failed to reconcile transactions. Please try again.");
    } finally {
        submitting.value = false;
    }
};

const resetForm = () => {
    selectedBankAccountId.value = "";
    bankStatementBalance.value = "";
    selectedTransactionIds.value = [];
    unreconciledTransactions.value = [];
};

const closeModal = () => {
    if (!submitting.value) {
        resetForm();
        emit("close");
    }
};
</script>
