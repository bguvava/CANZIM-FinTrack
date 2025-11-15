<template>
    <!-- View Purchase Order Modal -->
    <div
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300"
        @click.self="closeModal"
    >
        <div
            class="w-full max-w-5xl transform rounded-lg bg-white shadow-2xl transition-all duration-300"
        >
            <!-- Modal Header -->
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="rounded-lg bg-blue-100 p-2">
                            <i
                                class="fas fa-file-invoice-dollar text-xl text-blue-600"
                            ></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Purchase Order Details
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ purchaseOrder?.po_number || "N/A" }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-gray-400 transition-colors hover:text-gray-600"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-6">
                <!-- PO Header Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Vendor
                            </p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{
                                    purchaseOrder?.vendor?.vendor_name || "N/A"
                                }}
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                {{
                                    purchaseOrder?.vendor?.contact_person || ""
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Project
                            </p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{
                                    purchaseOrder?.project?.project_name ||
                                    "N/A"
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Status
                            </p>
                            <span
                                class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                :class="getStatusClass(purchaseOrder?.status)"
                            >
                                {{ purchaseOrder?.status || "N/A" }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Order Date
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDate(purchaseOrder?.created_at) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Total Amount
                            </p>
                            <p class="mt-1 text-sm font-bold text-gray-900">
                                ${{
                                    formatCurrency(purchaseOrder?.total_amount)
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Line Items Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h4
                        class="mb-4 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i class="fas fa-list mr-2 text-blue-600"></i>
                        Line Items
                    </h4>
                    <div
                        class="overflow-hidden rounded-lg border border-gray-200"
                    >
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Description
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Quantity
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Unit Price
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="item in purchaseOrder?.items"
                                    :key="item.id"
                                >
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ item.description }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        ${{ formatCurrency(item.unit_price) }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm font-medium text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                item.quantity * item.unit_price,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td
                                        colspan="3"
                                        class="px-4 py-3 text-right text-sm font-semibold text-gray-900"
                                    >
                                        Grand Total:
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm font-bold text-blue-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                purchaseOrder?.total_amount,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Notes Card (if present) -->
                <div
                    v-if="purchaseOrder?.notes"
                    class="rounded-lg border border-gray-200 bg-white p-6"
                >
                    <h4
                        class="mb-2 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i class="fas fa-sticky-note mr-2 text-blue-600"></i>
                        Notes
                    </h4>
                    <p class="text-sm text-gray-900">
                        {{ purchaseOrder.notes }}
                    </p>
                </div>

                <!-- Linked Expenses Card -->
                <div
                    v-if="
                        purchaseOrder?.expenses &&
                        purchaseOrder.expenses.length > 0
                    "
                    class="rounded-lg border border-gray-200 bg-white p-6"
                >
                    <h4
                        class="mb-4 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i class="fas fa-receipt mr-2 text-purple-600"></i>
                        Linked Expenses
                    </h4>
                    <div class="space-y-4">
                        <!-- Expenses Table -->
                        <div
                            class="overflow-hidden rounded-lg border border-gray-200"
                        >
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Expense #
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Date
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Description
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Amount
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="expense in purchaseOrder.expenses"
                                        :key="expense.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td
                                            class="px-4 py-3 text-sm font-medium text-gray-900"
                                        >
                                            {{ expense.expense_number }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{
                                                formatDate(expense.expense_date)
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{
                                                expense.description.length > 50
                                                    ? expense.description.substring(
                                                          0,
                                                          50,
                                                      ) + "..."
                                                    : expense.description
                                            }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                :class="
                                                    getExpenseStatusClass(
                                                        expense.status,
                                                    )
                                                "
                                            >
                                                {{ expense.status }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right text-sm font-semibold text-gray-900"
                                        >
                                            ${{
                                                formatCurrency(expense.amount)
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td
                                            colspan="4"
                                            class="px-4 py-3 text-right text-sm font-semibold text-gray-900"
                                        >
                                            Total Expenses:
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right text-sm font-bold text-purple-900"
                                        >
                                            ${{
                                                formatCurrency(
                                                    totalLinkedExpenses,
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- PO Amount Comparison -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="rounded-lg bg-blue-50 p-4">
                                <p class="text-xs font-medium text-blue-600">
                                    PO Total Amount
                                </p>
                                <p class="mt-1 text-lg font-bold text-blue-900">
                                    ${{
                                        formatCurrency(
                                            purchaseOrder.total_amount,
                                        )
                                    }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-purple-50 p-4">
                                <p class="text-xs font-medium text-purple-600">
                                    Total Paid
                                </p>
                                <p
                                    class="mt-1 text-lg font-bold text-purple-900"
                                >
                                    ${{ formatCurrency(totalLinkedExpenses) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg p-4"
                                :class="
                                    remainingBalance >= 0
                                        ? 'bg-green-50'
                                        : 'bg-red-50'
                                "
                            >
                                <p
                                    class="text-xs font-medium"
                                    :class="
                                        remainingBalance >= 0
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    Remaining Balance
                                </p>
                                <p
                                    class="mt-1 text-lg font-bold"
                                    :class="
                                        remainingBalance >= 0
                                            ? 'text-green-900'
                                            : 'text-red-900'
                                    "
                                >
                                    ${{
                                        formatCurrency(
                                            Math.abs(remainingBalance),
                                        )
                                    }}
                                    <span
                                        v-if="remainingBalance < 0"
                                        class="text-sm"
                                        >(Over)</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval History Card (if approved/rejected) -->
                <div
                    v-if="
                        purchaseOrder?.approved_by || purchaseOrder?.rejected_by
                    "
                    class="rounded-lg border border-gray-200 bg-white p-6"
                >
                    <h4
                        class="mb-4 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i class="fas fa-history mr-2 text-blue-600"></i>
                        Approval History
                    </h4>
                    <div class="space-y-3">
                        <div v-if="purchaseOrder.approved_by">
                            <p class="text-xs font-medium text-gray-500">
                                Approved By
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ purchaseOrder.approved_by?.name || "N/A" }}
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                {{ formatDate(purchaseOrder.approved_at) }}
                            </p>
                        </div>
                        <div v-if="purchaseOrder.rejected_by">
                            <p class="text-xs font-medium text-gray-500">
                                Rejected By
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ purchaseOrder.rejected_by?.name || "N/A" }}
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                {{ formatDate(purchaseOrder.rejected_at) }}
                            </p>
                            <p
                                v-if="purchaseOrder.rejection_reason"
                                class="mt-2 rounded-lg bg-red-50 p-2 text-sm text-red-700"
                            >
                                <strong>Reason:</strong>
                                {{ purchaseOrder.rejection_reason }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Audit Trail Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h4
                        class="mb-4 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i class="fas fa-clock mr-2 text-blue-600"></i>
                        Audit Trail
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Created By
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{
                                    purchaseOrder?.created_by?.name || "System"
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Created At
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDate(purchaseOrder?.created_at) }}
                            </p>
                        </div>
                        <div
                            v-if="
                                purchaseOrder?.updated_at &&
                                purchaseOrder.updated_at !==
                                    purchaseOrder.created_at
                            "
                        >
                            <p class="text-xs font-medium text-gray-500">
                                Last Updated
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDate(purchaseOrder.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with Status-Based Actions -->
            <div
                class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-4"
            >
                <button
                    @click="closeModal"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100"
                >
                    Close
                </button>

                <div class="flex items-center space-x-2">
                    <!-- Draft Status Actions -->
                    <template v-if="purchaseOrder?.status === 'draft'">
                        <button
                            @click="handleEdit"
                            class="flex items-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            <i class="fas fa-edit"></i>
                            <span>Edit</span>
                        </button>
                        <button
                            @click="handleSubmitForApproval"
                            class="flex items-center space-x-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700"
                        >
                            <i class="fas fa-paper-plane"></i>
                            <span>Submit for Approval</span>
                        </button>
                    </template>

                    <!-- Pending Status Actions (Programs Manager only) -->
                    <template v-if="purchaseOrder?.status === 'pending'">
                        <button
                            @click="handleReject"
                            class="flex items-center space-x-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
                        >
                            <i class="fas fa-times-circle"></i>
                            <span>Reject</span>
                        </button>
                        <button
                            @click="handleApprove"
                            class="flex items-center space-x-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700"
                        >
                            <i class="fas fa-check-circle"></i>
                            <span>Approve</span>
                        </button>
                    </template>

                    <!-- Approved Status Actions -->
                    <template v-if="purchaseOrder?.status === 'approved'">
                        <button
                            @click="handleMarkReceived"
                            class="flex items-center space-x-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700"
                        >
                            <i class="fas fa-box-check"></i>
                            <span>Mark as Received</span>
                        </button>
                    </template>

                    <!-- Received Status Actions -->
                    <template v-if="purchaseOrder?.status === 'received'">
                        <button
                            @click="handleComplete"
                            class="flex items-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            <i class="fas fa-check-double"></i>
                            <span>Complete PO</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from "vue";
import Swal from "sweetalert2";

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false,
    },
    purchaseOrder: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    "close",
    "edit",
    "submit-for-approval",
    "approve",
    "reject",
    "mark-received",
    "complete",
]);

// Computed properties for linked expenses
const totalLinkedExpenses = computed(() => {
    if (
        !props.purchaseOrder?.expenses ||
        props.purchaseOrder.expenses.length === 0
    ) {
        return 0;
    }
    return props.purchaseOrder.expenses.reduce((total, expense) => {
        return total + parseFloat(expense.amount || 0);
    }, 0);
});

const remainingBalance = computed(() => {
    const poTotal = parseFloat(props.purchaseOrder?.total_amount || 0);
    return poTotal - totalLinkedExpenses.value;
});

const closeModal = () => {
    emit("close");
};

const handleEdit = () => {
    emit("edit", props.purchaseOrder);
};

const handleSubmitForApproval = async () => {
    const result = await Swal.fire({
        title: "Submit for Approval?",
        text: "This will send the purchase order to the Programs Manager for approval.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#059669",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, Submit",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        emit("submit-for-approval", props.purchaseOrder.id);
    }
};

const handleApprove = async () => {
    const result = await Swal.fire({
        title: "Approve Purchase Order?",
        text: `Approve PO ${props.purchaseOrder.po_number} for $${formatCurrency(props.purchaseOrder.total_amount)}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#059669",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, Approve",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        emit("approve", props.purchaseOrder.id);
    }
};

const handleReject = async () => {
    const result = await Swal.fire({
        title: "Reject Purchase Order?",
        input: "textarea",
        inputLabel: "Rejection Reason",
        inputPlaceholder: "Enter reason for rejection...",
        inputAttributes: {
            "aria-label": "Enter reason for rejection",
        },
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Reject",
        cancelButtonText: "Cancel",
        inputValidator: (value) => {
            if (!value) {
                return "Please provide a reason for rejection";
            }
        },
    });

    if (result.isConfirmed) {
        emit("reject", {
            id: props.purchaseOrder.id,
            reason: result.value,
        });
    }
};

const handleMarkReceived = () => {
    emit("mark-received", props.purchaseOrder);
};

const handleComplete = async () => {
    const result = await Swal.fire({
        title: "Complete Purchase Order?",
        text: "This will mark the PO as completed. No further changes will be allowed.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#2563EB",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, Complete",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        emit("complete", props.purchaseOrder.id);
    }
};

const formatCurrency = (value) => {
    if (!value && value !== 0) return "0.00";
    return parseFloat(value).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getStatusClass = (status) => {
    const statusClasses = {
        draft: "bg-gray-100 text-gray-800",
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-blue-100 text-blue-800",
        received: "bg-purple-100 text-purple-800",
        completed: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        cancelled: "bg-gray-100 text-gray-800",
    };
    return statusClasses[status?.toLowerCase()] || "bg-gray-100 text-gray-800";
};

const getExpenseStatusClass = (status) => {
    const statusClasses = {
        Draft: "bg-gray-100 text-gray-800",
        Submitted: "bg-blue-100 text-blue-800",
        "Under Review": "bg-yellow-100 text-yellow-800",
        Approved: "bg-green-100 text-green-800",
        Rejected: "bg-red-100 text-red-800",
        Paid: "bg-purple-100 text-purple-800",
    };
    return statusClasses[status] || "bg-gray-100 text-gray-800";
};
</script>
