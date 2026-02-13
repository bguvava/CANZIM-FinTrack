<template>
    <div class="view-expense-container">
        <!-- Loading State -->
        <div
            v-if="loading"
            class="bg-white rounded-lg shadow-sm p-8 text-center"
        >
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading expense details...</p>
        </div>

        <!-- Content -->
        <div v-else-if="expense">
            <!-- Page Header -->
            <div class="mb-6">
                <button
                    @click="goBack"
                    class="text-blue-600 hover:text-blue-800 mb-4 flex items-center gap-2"
                >
                    <i class="fas fa-arrow-left"></i>
                    Back to Expenses
                </button>
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ expense.expense_number }}
                        </h1>
                        <p class="text-gray-600 mt-1">Expense Details</p>
                    </div>
                    <span :class="getStatusClass(expense.status)">
                        {{ expense.status }}
                    </span>
                </div>

                <!-- Rejection Alert -->
                <div
                    v-if="
                        expense.status === 'Rejected' &&
                        expense.rejection_reason
                    "
                    class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg"
                >
                    <div class="flex items-start gap-3">
                        <i
                            class="fas fa-exclamation-circle text-red-600 mt-1"
                        ></i>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-900 mb-1">
                                Expense Rejected
                            </h3>
                            <p class="text-sm text-red-800">
                                {{ expense.rejection_reason }}
                            </p>
                            <p class="text-xs text-red-600 mt-2">
                                You can edit this expense and resubmit it for
                                review.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 mb-6">
                <button
                    v-if="canEdit"
                    @click="editExpense"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
                >
                    <i class="fas fa-edit"></i>
                    {{
                        expense.status === "Rejected"
                            ? "Edit & Resubmit"
                            : "Edit"
                    }}
                </button>
                <button
                    v-if="canSubmit"
                    @click="submitForReview"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2"
                >
                    <i class="fas fa-paper-plane"></i>
                    {{
                        expense.status === "Rejected"
                            ? "Resubmit for Review"
                            : "Submit for Review"
                    }}
                </button>
                <button
                    v-if="canReview"
                    @click="openReviewModal('approve')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2"
                >
                    <i class="fas fa-check"></i>
                    Approve Review
                </button>
                <button
                    v-if="canReview"
                    @click="openReviewModal('reject')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2"
                >
                    <i class="fas fa-times"></i>
                    Reject
                </button>
                <button
                    v-if="canApprove"
                    @click="openApprovalModal('approve')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2"
                >
                    <i class="fas fa-check-circle"></i>
                    Approve
                </button>
                <button
                    v-if="canApprove"
                    @click="openApprovalModal('reject')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2"
                >
                    <i class="fas fa-ban"></i>
                    Reject
                </button>
                <button
                    v-if="canMarkPaid"
                    @click="openPaymentModal"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2"
                >
                    <i class="fas fa-money-bill-wave"></i>
                    Mark as Paid
                </button>
            </div>

            <!-- Basic Information -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Expense Details -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            Expense Details
                        </h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Project
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.project?.name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Budget Item
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.budget_item?.category || "—" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Category
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.category?.name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Expense Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(expense.expense_date) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Amount
                                </dt>
                                <dd
                                    class="mt-1 text-lg font-semibold text-gray-900"
                                >
                                    ${{ formatAmount(expense.amount) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Submitted By
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.submitter?.name }}
                                </dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">
                                    Description
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.description }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Approval Timeline -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            Approval Timeline
                        </h2>
                        <div class="space-y-4">
                            <div
                                v-for="approval in expense.approvals"
                                :key="approval.id"
                                class="flex items-start gap-4 pb-4 border-b border-gray-200 last:border-0"
                            >
                                <div
                                    :class="[
                                        'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
                                        approval.action === 'Approved'
                                            ? 'bg-green-100'
                                            : 'bg-red-100',
                                    ]"
                                >
                                    <i
                                        :class="[
                                            'fas',
                                            approval.action === 'Approved'
                                                ? 'fa-check text-green-600'
                                                : 'fa-times text-red-600',
                                        ]"
                                    ></i>
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="flex justify-between items-start"
                                    >
                                        <div>
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    approval.user?.name ||
                                                    approval.approver?.name
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    approval.approval_level ||
                                                    approval.user?.role?.name ||
                                                    approval.approver?.role
                                                        ?.name
                                                }}
                                            </p>
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            {{
                                                formatDate(
                                                    approval.action_date ||
                                                        approval.approved_at,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ approval.action }}
                                        {{
                                            approval.approval_level
                                                ? `(${approval.approval_level})`
                                                : approval.stage === "review"
                                                  ? "(Review)"
                                                  : "(Final Approval)"
                                        }}
                                    </p>
                                    <p
                                        v-if="approval.comments"
                                        class="mt-2 text-sm text-gray-600 italic"
                                    >
                                        "{{ approval.comments }}"
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="
                                    !expense.approvals ||
                                    expense.approvals.length === 0
                                "
                                class="text-center text-gray-500"
                            >
                                No approvals yet
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Receipt -->
                    <div
                        v-if="expense.receipt_path"
                        class="bg-white rounded-lg shadow-sm p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Receipt
                        </h3>
                        <div class="text-center">
                            <i
                                class="fas fa-file-pdf text-6xl text-red-500 mb-4"
                            ></i>
                            <a
                                :href="`/storage/${expense.receipt_path}`"
                                target="_blank"
                                class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                <i class="fas fa-download mr-2"></i>
                                View Receipt
                            </a>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div
                        v-if="expense.status === 'Paid'"
                        class="bg-white rounded-lg shadow-sm p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Payment Information
                        </h3>
                        <dl class="space-y-3">
                            <div v-if="expense.cash_flow">
                                <dt class="text-sm font-medium text-gray-500">
                                    Bank Account
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        expense.cash_flow.bank_account
                                            ?.account_name
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Payment Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(expense.paid_at) }}
                                </dd>
                            </div>
                            <div v-if="expense.payment_method">
                                <dt class="text-sm font-medium text-gray-500">
                                    Payment Method
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.payment_method }}
                                </dd>
                            </div>
                            <div v-if="expense.payment_reference">
                                <dt class="text-sm font-medium text-gray-500">
                                    Reference
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.payment_reference }}
                                </dd>
                            </div>
                            <div v-if="expense.payment_notes">
                                <dt class="text-sm font-medium text-gray-500">
                                    Notes
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ expense.payment_notes }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Timestamps -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Timeline
                        </h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Created
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(expense.created_at) }}
                                </dd>
                            </div>
                            <div v-if="expense.submitted_at">
                                <dt class="text-sm font-medium text-gray-500">
                                    Submitted
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(expense.submitted_at) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Last Updated
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(expense.updated_at) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <CommentsSection
                    commentable-type="Expense"
                    :commentable-id="expense.id"
                />
            </div>
        </div>

        <!-- Review/Approval Modal -->
        <div
            v-if="showReviewModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeReviewModal"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    {{ modalAction === "approve" ? "Approve" : "Reject" }}
                    {{ modalStage === "review" ? "Review" : "Expense" }}
                </h3>
                <form @submit.prevent="handleReviewSubmit">
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Comments
                            {{
                                modalAction === "reject"
                                    ? "(Required)"
                                    : "(Optional)"
                            }}
                        </label>
                        <textarea
                            v-model="reviewComments"
                            :required="modalAction === 'reject'"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Add your comments..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeReviewModal"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="loading"
                            :class="[
                                'px-4 py-2 text-white rounded-lg transition disabled:opacity-50',
                                modalAction === 'approve'
                                    ? 'bg-green-600 hover:bg-green-700'
                                    : 'bg-red-600 hover:bg-red-700',
                            ]"
                        >
                            {{
                                loading
                                    ? "Processing..."
                                    : modalAction === "approve"
                                      ? "Approve"
                                      : "Reject"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Modal -->
        <div
            v-if="showPaymentModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closePaymentModal"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Mark as Paid
                </h3>
                <form @submit.prevent="handlePaymentSubmit">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Bank Account <span class="text-red-600">*</span>
                            </label>
                            <select
                                v-model="paymentForm.bank_account_id"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="" disabled>
                                    Select bank account...
                                </option>
                                <option
                                    v-for="account in cashFlowStore.activeBankAccounts"
                                    :key="account.id"
                                    :value="account.id"
                                >
                                    {{ account.account_name }} - ${{
                                        Number(
                                            account.current_balance,
                                        ).toLocaleString()
                                    }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Payment Reference
                            </label>
                            <input
                                v-model="paymentForm.payment_reference"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., CHQ-12345"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Payment Method
                            </label>
                            <input
                                v-model="paymentForm.payment_method"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Bank Transfer, Cheque"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Notes
                            </label>
                            <textarea
                                v-model="paymentForm.payment_notes"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Additional payment notes..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="closePaymentModal"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:opacity-50"
                        >
                            {{ loading ? "Processing..." : "Mark as Paid" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useExpenseStore } from "@/stores/expenseStore";
import { useAuthStore } from "@/stores/authStore";
import { useCashFlowStore } from "@/stores/cashFlowStore";
import CommentsSection from "../../components/comments/CommentsSection.vue";

// Get expense ID from URL
const getExpenseIdFromUrl = () => {
    const pathParts = window.location.pathname.split("/");
    const expensesIndex = pathParts.indexOf("expenses");
    if (expensesIndex !== -1 && pathParts[expensesIndex + 1]) {
        return pathParts[expensesIndex + 1];
    }
    return null;
};

const expenseId = getExpenseIdFromUrl();
const expenseStore = useExpenseStore();
const authStore = useAuthStore();
const cashFlowStore = useCashFlowStore();

const loading = ref(false);
const expense = ref(null);
const showReviewModal = ref(false);
const showPaymentModal = ref(false);
const reviewComments = ref("");
const modalAction = ref("");
const modalStage = ref("");
const paymentForm = ref({
    bank_account_id: "",
    payment_reference: "",
    payment_method: "",
    payment_notes: "",
});

// Permissions
const canEdit = computed(() => {
    if (!expense.value) return false;
    const role = authStore.user?.role?.slug;
    const userId = authStore.user?.id;
    return (
        role === "project-officer" &&
        expense.value.submitted_by === userId &&
        (expense.value.status === "Draft" ||
            expense.value.status === "Rejected")
    );
});

const canSubmit = computed(() => {
    if (!expense.value) return false;
    const role = authStore.user?.role?.slug;
    const userId = authStore.user?.id;
    return (
        role === "project-officer" &&
        expense.value.submitted_by === userId &&
        (expense.value.status === "Draft" ||
            expense.value.status === "Rejected")
    );
});

const canReview = computed(() => {
    if (!expense.value) return false;
    const role = authStore.user?.role?.slug;
    return role === "finance-officer" && expense.value.status === "Submitted";
});

const canApprove = computed(() => {
    if (!expense.value) return false;
    const role = authStore.user?.role?.slug;
    return role === "programs-manager" && expense.value.status === "Reviewed";
});

const canMarkPaid = computed(() => {
    if (!expense.value) return false;
    const role = authStore.user?.role?.slug;
    return role === "finance-officer" && expense.value.status === "Approved";
});

// Methods
const loadExpense = async () => {
    try {
        loading.value = true;
        expense.value = await expenseStore.fetchExpense(expenseId);
    } catch (error) {
        console.error("Error loading expense:", error);
        alert("Failed to load expense details");
        goBack();
    } finally {
        loading.value = false;
    }
};

const editExpense = () => {
    window.location.href = `/expenses/${expense.value.id}/edit`;
};

const submitForReview = async () => {
    if (!confirm("Submit this expense for review?")) return;

    try {
        loading.value = true;
        await expenseStore.submitExpense(expense.value.id);
        await loadExpense();
    } catch (error) {
        console.error("Error submitting expense:", error);
        alert("Failed to submit expense");
    } finally {
        loading.value = false;
    }
};

const openReviewModal = (action) => {
    modalAction.value = action;
    modalStage.value = "review";
    reviewComments.value = "";
    showReviewModal.value = true;
};

const openApprovalModal = (action) => {
    modalAction.value = action;
    modalStage.value = "approval";
    reviewComments.value = "";
    showReviewModal.value = true;
};

const closeReviewModal = () => {
    showReviewModal.value = false;
    reviewComments.value = "";
};

const handleReviewSubmit = async () => {
    try {
        loading.value = true;

        if (modalStage.value === "review") {
            await expenseStore.reviewExpense(
                expense.value.id,
                modalAction.value,
                reviewComments.value || null,
            );
        } else {
            await expenseStore.approveExpense(
                expense.value.id,
                modalAction.value,
                reviewComments.value || null,
            );
        }

        closeReviewModal();
        await loadExpense();
    } catch (error) {
        console.error("Error processing expense:", error);
        alert("Failed to process expense");
    } finally {
        loading.value = false;
    }
};

const openPaymentModal = () => {
    paymentForm.value = {
        bank_account_id: "",
        payment_reference: "",
        payment_method: "",
        payment_notes: "",
    };
    showPaymentModal.value = true;
    // Load bank accounts when modal opens
    cashFlowStore.fetchBankAccounts();
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
};

const handlePaymentSubmit = async () => {
    try {
        loading.value = true;
        await expenseStore.markAsPaid(expense.value.id, paymentForm.value);
        closePaymentModal();
        await loadExpense();
    } catch (error) {
        console.error("Error marking as paid:", error);
        alert("Failed to mark expense as paid");
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    window.location.href = "/expenses";
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
    const baseClasses = "px-3 py-1 text-sm font-semibold rounded-full";

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
    loadExpense();
});
</script>
