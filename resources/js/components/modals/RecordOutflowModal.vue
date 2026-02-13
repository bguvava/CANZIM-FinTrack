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
                                    v-for="account in safeBankAccounts"
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
                                Project (Optional)
                            </label>
                            <select
                                v-model="form.project_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                            >
                                <option value="">None</option>
                                <option
                                    v-for="project in safeProjects"
                                    :key="project.id"
                                    :value="project.id"
                                >
                                    {{ project.name }}
                                </option>
                            </select>
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
                                    v-for="expense in safeUnpaidExpenses"
                                    :key="expense.id"
                                    :value="expense.id"
                                >
                                    {{ expense.expense_number }} -
                                    {{
                                        expense.description || "No description"
                                    }}
                                    - ${{ formatCurrency(expense.amount) }} ({{
                                        expense.category?.name ||
                                        "Uncategorized"
                                    }})
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
                                v-model="form.reference"
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
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition"
                            :disabled="submitting"
                        >
                            <i class="fas fa-times mr-1.5"></i>Cancel
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
                            <i
                                v-if="!submitting"
                                class="fas fa-arrow-up mr-1.5"
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
import { Toast, showError } from "../../plugins/sweetalert";
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
    reference: "",
    description: "",
});

const errors = ref({});
const submitting = ref(false);
const projects = ref([]);
const unpaidExpenses = ref([]);

const selectedAccount = computed(() => {
    const accounts = cashFlowStore.activeBankAccounts;
    if (!accounts || !Array.isArray(accounts)) return null;
    return accounts.find(
        (acc) => acc && acc.id === parseInt(form.value.bank_account_id),
    );
});

// Safe computed properties to filter out null/undefined entries
const safeBankAccounts = computed(() => {
    const accounts = cashFlowStore.activeBankAccounts;
    if (!accounts || !Array.isArray(accounts)) return [];
    return accounts.filter((account) => account && account.id);
});

const safeProjects = computed(() => {
    if (!projects.value || !Array.isArray(projects.value)) return [];
    return projects.value.filter(
        (project) => project && project.id && project.name,
    );
});

const safeUnpaidExpenses = computed(() => {
    if (!unpaidExpenses.value || !Array.isArray(unpaidExpenses.value))
        return [];
    return unpaidExpenses.value.filter((expense) => expense && expense.id);
});

// Fetch projects and unpaid expenses
onMounted(async () => {
    try {
        const [projectsResponse, expensesResponse] = await Promise.all([
            api.get("/projects", {
                params: { status: "active", per_page: 100 },
            }),
            api.get("/expenses", {
                params: { status: "approved", per_page: 100 },
            }),
        ]);

        // Projects response: { success: true, data: { data: [...], ... } } (paginated)
        const projectsPayload = projectsResponse.data?.data;
        const projectsArray = projectsPayload?.data || projectsPayload;
        projects.value = Array.isArray(projectsArray) ? projectsArray : [];

        // Expenses response: raw paginator { data: [...], current_page: 1, ... }
        const expensesPayload = expensesResponse.data;
        const expensesArray = expensesPayload?.data || expensesPayload;
        unpaidExpenses.value = Array.isArray(expensesArray)
            ? expensesArray
            : [];
    } catch (error) {
        console.error("Failed to fetch data:", error);
        projects.value = [];
        unpaidExpenses.value = [];
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
        reference: "",
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
        resetForm();
        emit("outflow-recorded");
        submitting.value = false;
        closeModal();
        Toast.fire({
            icon: "success",
            title: "Cash outflow recorded successfully!",
        });
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
