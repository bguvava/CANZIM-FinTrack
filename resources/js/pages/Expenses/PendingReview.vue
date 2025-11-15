<template>
    <div class="pending-review-container">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Pending Review</h1>
            <p class="text-gray-600 mt-1">
                Expenses awaiting your review as Finance Officer
            </p>
        </div>

        <!-- Stats Card -->
        <div
            class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg shadow-lg p-6 mb-6 text-white"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">
                        Total Pending
                    </p>
                    <p class="text-3xl font-bold mt-1">
                        {{ expenseStore.pendingReview.length }}
                    </p>
                    <p class="text-yellow-100 text-sm mt-2">
                        {{ formatCurrency(totalPendingAmount) }} in total value
                    </p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-clipboard-check text-4xl"></i>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div
            v-if="loading"
            class="bg-white rounded-lg shadow-sm p-8 text-center"
        >
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading pending expenses...</p>
        </div>

        <!-- Expenses List -->
        <div
            v-else-if="expenseStore.pendingReview.length > 0"
            class="space-y-4"
        >
            <div
                v-for="expense in expenseStore.pendingReview"
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
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                            >
                                {{ expense.status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
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
                                <p class="text-xs text-gray-500">Submitted</p>
                                <p class="text-sm text-gray-900">
                                    {{ formatDate(expense.submitted_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-xs text-gray-500">Description</p>
                            <p class="text-sm text-gray-700 mt-1 line-clamp-2">
                                {{ expense.description }}
                            </p>
                        </div>

                        <div class="mt-3">
                            <p class="text-xs text-gray-500">
                                Submitted by:
                                <span class="text-gray-900 font-medium">
                                    {{ expense.submitter?.name }}
                                </span>
                            </p>
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
                            @click="openReviewModal(expense, 'approve')"
                            class="flex-1 lg:flex-none px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-check"></i>
                            Approve
                        </button>
                        <button
                            @click="openReviewModal(expense, 'reject')"
                            class="flex-1 lg:flex-none px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-times"></i>
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-clipboard-check text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                No Pending Reviews
            </h3>
            <p class="text-gray-600">
                All expenses have been reviewed. Check back later for new
                submissions.
            </p>
        </div>

        <!-- Review Modal -->
        <div
            v-if="showReviewModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeReviewModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            >
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    {{ modalAction === "approve" ? "Approve" : "Reject" }}
                    Review
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
                    </div>
                </div>

                <form @submit.prevent="handleReviewSubmit">
                    <div class="mb-6">
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
                            :placeholder="
                                modalAction === 'approve'
                                    ? 'Add optional comments for approval...'
                                    : 'Please provide reason for rejection...'
                            "
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeReviewModal"
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
                                        ? 'fas fa-check'
                                        : 'fas fa-times'
                                "
                            ></i>
                            {{
                                loading
                                    ? "Processing..."
                                    : modalAction === "approve"
                                      ? "Approve Review"
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
import { useRouter } from "vue-router";
import { useExpenseStore } from "@/stores/expenseStore";

const router = useRouter();
const expenseStore = useExpenseStore();

const loading = ref(false);
const showReviewModal = ref(false);
const selectedExpense = ref(null);
const modalAction = ref("");
const reviewComments = ref("");

// Computed
const totalPendingAmount = computed(() => {
    return expenseStore.pendingReview.reduce(
        (sum, expense) => sum + parseFloat(expense.amount || 0),
        0,
    );
});

// Methods
const loadPendingExpenses = async () => {
    try {
        loading.value = true;
        await expenseStore.fetchPendingReview();
    } catch (error) {
        console.error("Error loading pending expenses:", error);
    } finally {
        loading.value = false;
    }
};

const viewExpense = (id) => {
    router.push({ name: "ViewExpense", params: { id } });
};

const openReviewModal = (expense, action) => {
    selectedExpense.value = expense;
    modalAction.value = action;
    reviewComments.value = "";
    showReviewModal.value = true;
};

const closeReviewModal = () => {
    showReviewModal.value = false;
    selectedExpense.value = null;
    reviewComments.value = "";
};

const handleReviewSubmit = async () => {
    try {
        loading.value = true;
        await expenseStore.reviewExpense(
            selectedExpense.value.id,
            modalAction.value,
            reviewComments.value || null,
        );
        closeReviewModal();
        await loadPendingExpenses();
    } catch (error) {
        console.error("Error reviewing expense:", error);
        alert("Failed to process review. Please try again.");
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
