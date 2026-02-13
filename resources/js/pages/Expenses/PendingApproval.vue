<template>
    <div class="pending-approval-container">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Pending Approval</h1>
            <p class="text-gray-600 mt-1">
                Expenses awaiting your final approval as Programs Manager
            </p>
        </div>

        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Pending Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium uppercase">
                            Total Pending
                        </p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ expenseStore.pendingApproval.length }}
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-lg p-3">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium uppercase">
                            Total Amount
                        </p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ formatCurrency(totalPendingAmount) }}
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-lg p-3">
                        <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Reviewed Today Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium uppercase">
                            Reviewed Today
                        </p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ reviewedTodayCount }}
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-lg p-3">
                        <i
                            class="fas fa-check-circle text-green-600 text-xl"
                        ></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div
            v-if="loading"
            class="bg-white rounded-lg shadow-sm p-8 text-center"
        >
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading pending approvals...</p>
        </div>

        <!-- Expenses List -->
        <div
            v-else-if="expenseStore.pendingApproval.length > 0"
            class="space-y-4"
        >
            <div
                v-for="expense in expenseStore.pendingApproval"
                :key="expense.id"
                class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition"
            >
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
                >
                    <!-- Expense Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ expense.expense_number }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ expense.project?.name }}
                                </p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800"
                            >
                                {{ expense.status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <p class="text-xs text-gray-500">Category</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ expense.category?.name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Amount</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    ${{ formatAmount(expense.amount) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Reviewed By</p>
                                <p class="text-sm text-gray-900">
                                    {{ getReviewer(expense) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Review Date</p>
                                <p class="text-sm text-gray-900">
                                    {{ getReviewDate(expense) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-xs text-gray-500">Description</p>
                            <p class="text-sm text-gray-700 mt-1 line-clamp-2">
                                {{ expense.description }}
                            </p>
                        </div>

                        <!-- Review Comments -->
                        <div
                            v-if="getReviewComments(expense)"
                            class="mt-3 p-3 bg-gray-50 rounded-lg"
                        >
                            <p class="text-xs text-gray-500 mb-1">
                                Finance Review Comments
                            </p>
                            <p class="text-sm text-gray-700 italic">
                                "{{ getReviewComments(expense) }}"
                            </p>
                        </div>

                        <div
                            class="mt-3 flex items-center gap-4 text-xs text-gray-500"
                        >
                            <span>
                                Submitted by:
                                <span class="text-gray-900 font-medium">
                                    {{ expense.submitter?.name }}
                                </span>
                            </span>
                            <span
                                v-if="expense.receipt_path"
                                class="flex items-center gap-1"
                            >
                                <i class="fas fa-paperclip text-blue-500"></i>
                                Has Receipt
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex lg:flex-col gap-2 lg:min-w-[160px]">
                        <button
                            @click="viewExpense(expense.id)"
                            class="flex-1 lg:flex-none px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-eye"></i>
                            View Details
                        </button>
                        <button
                            @click="openApprovalModal(expense, 'approve')"
                            class="flex-1 lg:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-check-circle"></i>
                            Approve
                        </button>
                        <button
                            @click="openApprovalModal(expense, 'reject')"
                            class="flex-1 lg:flex-none px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-ban"></i>
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-check-double text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                No Pending Approvals
            </h3>
            <p class="text-gray-600">
                All reviewed expenses have been approved. Check back later for
                new items.
            </p>
        </div>

        <!-- Approval Modal -->
        <div
            v-if="showApprovalModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeApprovalModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            >
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    {{
                        modalAction === "approve"
                            ? "Final Approval"
                            : "Reject Expense"
                    }}
                </h3>

                <!-- Expense Summary -->
                <div
                    v-if="selectedExpense"
                    class="bg-gray-50 rounded-lg p-4 mb-6"
                >
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Expense Number</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ selectedExpense.expense_number }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Amount</p>
                            <p class="text-sm font-semibold text-gray-900">
                                ${{ formatAmount(selectedExpense.amount) }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500">Project</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ selectedExpense.project?.name }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500">Budget Item</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ selectedExpense.budgetItem?.name || "—" }}
                            </p>
                        </div>
                    </div>

                    <!-- Finance Review Comments -->
                    <div
                        v-if="getReviewComments(selectedExpense)"
                        class="mt-4 pt-4 border-t border-gray-200"
                    >
                        <p class="text-xs text-gray-500 mb-1">
                            Finance Review Comments
                        </p>
                        <p class="text-sm text-gray-700 italic">
                            "{{ getReviewComments(selectedExpense) }}"
                        </p>
                    </div>
                </div>

                <form @submit.prevent="handleApprovalSubmit">
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Your Comments
                            {{
                                modalAction === "reject"
                                    ? "(Required)"
                                    : "(Optional)"
                            }}
                        </label>
                        <textarea
                            v-model="approvalComments"
                            :required="modalAction === 'reject'"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            :placeholder="
                                modalAction === 'approve'
                                    ? 'Add optional comments for final approval...'
                                    : 'Please provide reason for rejection...'
                            "
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeApprovalModal"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="loading"
                            :class="[
                                'px-6 py-2 text-white rounded-lg transition disabled:opacity-50 flex items-center gap-2',
                                modalAction === 'approve'
                                    ? 'bg-green-600 hover:bg-green-700'
                                    : 'bg-red-600 hover:bg-red-700',
                            ]"
                        >
                            <i
                                v-if="loading"
                                class="fas fa-spinner fa-spin"
                            ></i>
                            <i
                                v-else
                                :class="
                                    modalAction === 'approve'
                                        ? 'fas fa-check-circle'
                                        : 'fas fa-ban'
                                "
                            ></i>
                            {{
                                loading
                                    ? "Processing..."
                                    : modalAction === "approve"
                                      ? "Grant Final Approval"
                                      : "Reject Expense"
                            }}
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

const expenseStore = useExpenseStore();

const loading = ref(false);
const showApprovalModal = ref(false);
const selectedExpense = ref(null);
const modalAction = ref("");
const approvalComments = ref("");

// Computed
const totalPendingAmount = computed(() => {
    return expenseStore.pendingApproval.reduce(
        (sum, expense) => sum + parseFloat(expense.amount || 0),
        0,
    );
});

const reviewedTodayCount = computed(() => {
    const today = new Date().toDateString();
    return expenseStore.pendingApproval.filter((expense) => {
        if (expense.reviewed_at) {
            return new Date(expense.reviewed_at).toDateString() === today;
        }
        return false;
    }).length;
});

// Methods
const loadPendingExpenses = async () => {
    try {
        loading.value = true;
        await expenseStore.fetchPendingApproval();
    } catch (error) {
        console.error("Error loading pending approvals:", error);
    } finally {
        loading.value = false;
    }
};

const viewExpense = (id) => {
    window.location.href = `/expenses/${id}`;
};

const getReviewer = (expense) => {
    // Use the reviewer relationship loaded directly on the expense
    return expense.reviewer?.name || "N/A";
};

const getReviewDate = (expense) => {
    // Use the reviewed_at field directly from the expense
    return expense.reviewed_at ? formatDate(expense.reviewed_at) : "N/A";
};

const getReviewComments = (expense) => {
    // Check review_comments on the expense, or look up from approvals
    return expense.review_comments || "";
};

const openApprovalModal = (expense, action) => {
    selectedExpense.value = expense;
    modalAction.value = action;
    approvalComments.value = "";
    showApprovalModal.value = true;
};

const closeApprovalModal = () => {
    showApprovalModal.value = false;
    selectedExpense.value = null;
    approvalComments.value = "";
};

const handleApprovalSubmit = async () => {
    try {
        loading.value = true;
        await expenseStore.approveExpense(
            selectedExpense.value.id,
            modalAction.value,
            approvalComments.value || null,
        );
        closeApprovalModal();
        await loadPendingExpenses();
    } catch (error) {
        console.error("Error processing approval:", error);
        alert("Failed to process approval. Please try again.");
    } finally {
        loading.value = false;
    }
};

const formatAmount = (amount) => {
    return Number(amount).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatCurrency = (amount) => {
    return "$" + formatAmount(amount);
};

const formatDate = (date) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

onMounted(() => {
    loadPendingExpenses();
});
</script>
