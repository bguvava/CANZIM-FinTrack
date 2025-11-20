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
                class="relative w-full max-w-2xl transform rounded-lg bg-white shadow-xl transition-all"
                @click.stop
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3 class="text-lg font-semibold text-gray-900">
                        Record Cash Outflow
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="px-6 py-6">
                    <div class="max-h-[70vh] overflow-y-auto space-y-6">
                        <!-- Date and Amount -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Transaction Date
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    v-model="form.transaction_date"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                    :class="{
                                        'border-red-500':
                                            errors.transaction_date,
                                    }"
                                    required
                                />
                                <p
                                    v-if="errors.transaction_date"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.transaction_date[0] }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Amount (USD)
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
                                        v-model="form.amount"
                                        step="0.01"
                                        min="0.01"
                                        class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                        :class="{
                                            'border-red-500': errors.amount,
                                        }"
                                        placeholder="0.00"
                                        required
                                    />
                                </div>
                                <p
                                    v-if="errors.amount"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.amount[0] }}
                                </p>
                            </div>
                        </div>

                        <!-- Bank Account -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Bank Account
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.bank_account_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                :class="{
                                    'border-red-500': errors.bank_account_id,
                                }"
                                required
                            >
                                <option value="">Select bank account</option>
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
                            <p
                                v-if="errors.bank_account_id"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.bank_account_id[0] }}
                            </p>
                            <p
                                v-if="
                                    selectedAccount &&
                                    parseFloat(form.amount) >
                                        parseFloat(
                                            selectedAccount.current_balance,
                                        )
                                "
                                class="mt-1 text-sm text-yellow-600"
                            >
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Warning: Insufficient funds (Available: ${{
                                    formatCurrency(
                                        selectedAccount.current_balance,
                                    )
                                }})
                            </p>
                        </div>

                        <!-- Project -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Project
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.project_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                :class="{
                                    'border-red-500': errors.project_id,
                                }"
                                required
                            >
                                <option value="">Select project</option>
                                <option
                                    v-for="project in projects"
                                    :key="project.id"
                                    :value="project.id"
                                >
                                    {{ project.name }}
                                </option>
                            </select>
                            <p
                                v-if="errors.project_id"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.project_id[0] }}
                            </p>
                        </div>

                        <!-- Expense Link (Optional) -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Link to Expense (Optional)
                            </label>
                            <select
                                v-model="form.expense_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                            >
                                <option value="">None - Manual Payment</option>
                                <option
                                    v-for="expense in unpaidExpenses"
                                    :key="expense.id"
                                    :value="expense.id"
                                >
                                    {{ expense.description }} - ${{
                                        formatCurrency(expense.amount)
                                    }}
                                    ({{ expense.category }})
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Link this outflow to an existing expense record
                            </p>
                        </div>

                        <!-- Reference Number -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Reference Number
                            </label>
                            <input
                                type="text"
                                v-model="form.reference_number"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                placeholder="e.g., Check #, Payment Voucher #"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Optional check number or payment reference
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Description
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                :class="{
                                    'border-red-500': errors.description,
                                }"
                                placeholder="Describe the purpose of this payment..."
                                required
                            ></textarea>
                            <p
                                v-if="errors.description"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.description[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-end gap-3 border-t border-gray-200 pt-4 mt-6"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            :disabled="submitting"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition disabled:opacity-50"
                            :disabled="submitting"
                        >
                            <i
                                v-if="submitting"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            {{ submitting ? "Recording..." : "Record Outflow" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useCashFlowStore } from "../../stores/cashFlowStore";
import { showSuccess, showError } from "../../plugins/sweetalert";
import api from "../../api";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["close", "outflow-recorded"]);

const cashFlowStore = useCashFlowStore();

const form = ref({
    transaction_date: new Date().toISOString().split("T")[0],
    amount: "",
    bank_account_id: "",
    project_id: "",
    expense_id: "",
    reference_number: "",
    description: "",
});

const errors = ref({});
const submitting = ref(false);
const projects = ref([]);
const unpaidExpenses = ref([]);

const selectedAccount = computed(() => {
    return cashFlowStore.activeBankAccounts.find(
        (acc) => acc.id === parseInt(form.value.bank_account_id),
    );
});

// Fetch projects and unpaid expenses
onMounted(async () => {
    try {
        const [projectsResponse, expensesResponse] = await Promise.all([
            api.get("/api/v1/projects", {
                params: { status: "active", per_page: 100 },
            }),
            api.get("/api/v1/expenses", {
                params: { payment_status: "unpaid", per_page: 100 },
            }),
        ]);
        projects.value = projectsResponse.data.data || projectsResponse.data;
        unpaidExpenses.value =
            expensesResponse.data.data || expensesResponse.data;
    } catch (error) {
        console.error("Failed to fetch data:", error);
    }
});

const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const closeModal = () => {
    if (!submitting.value) {
        resetForm();
        emit("close");
    }
};

const resetForm = () => {
    form.value = {
        transaction_date: new Date().toISOString().split("T")[0],
        amount: "",
        bank_account_id: "",
        project_id: "",
        expense_id: "",
        reference_number: "",
        description: "",
    };
    errors.value = {};
};

const handleSubmit = async () => {
    if (submitting.value) return;

    submitting.value = true;
    errors.value = {};

    try {
        await cashFlowStore.recordOutflow(form.value);
        showSuccess("Cash outflow recorded successfully!");
        emit("outflow-recorded");
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            showError("Please check the form for errors.");
        } else if (error.response?.status === 400) {
            showError(error.response.data.message || "Insufficient funds.");
        } else {
            showError("Failed to record cash outflow. Please try again.");
        }
    } finally {
        submitting.value = false;
    }
};
</script>
