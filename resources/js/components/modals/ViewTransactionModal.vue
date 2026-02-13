<template>
    <div
        v-if="isOpen && transaction"
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
                class="relative w-full max-w-3xl transform rounded-lg bg-white shadow-xl transition-all"
                @click.stop
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Transaction Details
                        </h3>
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                            :class="
                                transaction.type === 'inflow'
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-800'
                            "
                        >
                            {{
                                transaction.type === "inflow"
                                    ? "Inflow"
                                    : "Outflow"
                            }}
                        </span>
                    </div>
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
                        <!-- Transaction Summary Card -->
                        <div
                            class="p-6 rounded-lg"
                            :class="
                                transaction.type === 'inflow'
                                    ? 'bg-green-50'
                                    : 'bg-red-50'
                            "
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        Transaction Date
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{
                                            formatDate(
                                                transaction.transaction_date,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        Amount
                                    </p>
                                    <p
                                        class="text-2xl font-bold"
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
                                        }}${{
                                            formatCurrency(transaction.amount)
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        Balance Before
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                transaction.balance_before,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        Balance After
                                    </p>
                                    <p
                                        class="text-base font-bold"
                                        :class="
                                            transaction.balance_after >= 0
                                                ? 'text-green-600'
                                                : 'text-red-600'
                                        "
                                    >
                                        ${{
                                            formatCurrency(
                                                transaction.balance_after,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Details -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4
                                class="text-sm font-semibold text-gray-900 mb-4"
                            >
                                Transaction Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Bank Account
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{
                                            transaction.bank_account
                                                ?.account_name
                                        }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            transaction.bank_account?.bank_name
                                        }}
                                    </p>
                                </div>
                                <div v-if="transaction.project">
                                    <p class="text-sm text-gray-500 mb-1">
                                        Project
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{ transaction.project.name }}
                                    </p>
                                </div>
                                <div
                                    v-if="
                                        transaction.donor &&
                                        transaction.type === 'inflow'
                                    "
                                >
                                    <p class="text-sm text-gray-500 mb-1">
                                        Donor/Source
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{
                                            typeof transaction.donor ===
                                            "object"
                                                ? transaction.donor?.name
                                                : transaction.donor || "N/A"
                                        }}
                                    </p>
                                </div>
                                <div v-if="transaction.reference">
                                    <p class="text-sm text-gray-500 mb-1">
                                        Reference Number
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{
                                            transaction.reference ||
                                            transaction.reference_number
                                        }}
                                    </p>
                                </div>
                                <div
                                    v-if="transaction.expense"
                                    class="md:col-span-2"
                                >
                                    <p class="text-sm text-gray-500 mb-1">
                                        Linked Expense
                                    </p>
                                    <div
                                        class="bg-white p-3 rounded border border-gray-200"
                                    >
                                        <p
                                            class="text-base font-medium text-gray-900"
                                        >
                                            {{
                                                transaction.expense.description
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Category:
                                            {{
                                                typeof transaction.expense
                                                    .category === "object"
                                                    ? transaction.expense
                                                          .category?.name
                                                    : transaction.expense
                                                          .category || "N/A"
                                            }}
                                            • Amount: ${{
                                                formatCurrency(
                                                    transaction.expense.amount,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <p class="text-sm text-gray-500 mb-1">
                                    Description
                                </p>
                                <p class="text-base text-gray-900">
                                    {{ transaction.description || "N/A" }}
                                </p>
                            </div>
                        </div>

                        <!-- Reconciliation Status -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4
                                class="text-sm font-semibold text-gray-900 mb-4"
                            >
                                Reconciliation Status
                            </h4>
                            <div class="flex items-center gap-4">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                    :class="
                                        transaction.is_reconciled
                                            ? 'bg-blue-100 text-blue-800'
                                            : 'bg-yellow-100 text-yellow-800'
                                    "
                                >
                                    <i
                                        class="mr-2"
                                        :class="
                                            transaction.is_reconciled
                                                ? 'fas fa-check-circle'
                                                : 'fas fa-clock'
                                        "
                                    ></i>
                                    {{
                                        transaction.is_reconciled
                                            ? "Reconciled"
                                            : "Unreconciled"
                                    }}
                                </span>
                                <span
                                    v-if="transaction.reconciled_at"
                                    class="text-sm text-gray-600"
                                >
                                    on
                                    {{ formatDate(transaction.reconciled_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Audit Information -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h4
                                class="text-sm font-semibold text-gray-900 mb-4"
                            >
                                Audit Trail
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">
                                        Created By
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{
                                            transaction.created_by?.name ||
                                            "System"
                                        }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ formatDate(transaction.created_at) }}
                                    </p>
                                </div>
                                <div
                                    v-if="
                                        transaction.updated_at !==
                                        transaction.created_at
                                    "
                                >
                                    <p class="text-sm text-gray-500 mb-1">
                                        Last Updated
                                    </p>
                                    <p
                                        class="text-base font-medium text-gray-900"
                                    >
                                        {{ formatDate(transaction.updated_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-end gap-3 border-t border-gray-200 pt-4 mt-6"
                    >
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
</template>

<script setup>
const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    transaction: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close"]);

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
        month: "long",
        day: "numeric",
    });
};

const closeModal = () => {
    emit("close");
};
</script>
