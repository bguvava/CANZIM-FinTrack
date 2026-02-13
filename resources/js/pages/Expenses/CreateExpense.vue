<template>
    <div class="create-expense-container">
        <!-- Page Header -->
        <div class="mb-6">
            <button
                @click="goBack"
                class="text-blue-600 hover:text-blue-800 mb-3 flex items-center gap-2 text-sm"
            >
                <i class="fas fa-arrow-left"></i>
                Back to Expenses
            </button>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ isEditMode ? "Edit Expense" : "Create New Expense" }}
            </h1>
            <p class="text-gray-600 mt-1">
                {{
                    isEditMode
                        ? "Update expense details"
                        : "Record a new project expense"
                }}
            </p>
        </div>

        <!-- Error Message -->
        <div
            v-if="error"
            class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
        >
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                <p class="text-red-800">{{ error }}</p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Basic Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Project -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Project <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.project_id"
                            @change="handleProjectChange"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select Project</option>
                            <option
                                v-for="project in projects"
                                :key="project.id"
                                :value="project.id"
                            >
                                {{ project.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Budget Item -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Budget Item <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.budget_item_id"
                            required
                            :disabled="!form.project_id || loading"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <option value="">Select Budget Item</option>
                            <option
                                v-for="item in budgetItems"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }} (Available: ${{
                                    formatAmount(item.available_amount)
                                }})
                            </option>
                        </select>
                        <p
                            v-if="form.project_id && budgetItems.length === 0"
                            class="mt-1 text-sm text-gray-500"
                        >
                            No budget items available for this project
                        </p>
                    </div>

                    <!-- Link to Purchase Order (Optional) -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Link to Purchase Order
                            <span class="text-gray-500">(Optional)</span>
                        </label>
                        <select
                            v-model="form.purchase_order_id"
                            :disabled="!form.project_id || loading"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <option value="">No Purchase Order</option>
                            <option
                                v-for="po in purchaseOrders"
                                :key="po.id"
                                :value="po.id"
                            >
                                {{ po.po_number }} - {{ po.vendor?.name }} (${{
                                    formatAmount(po.total_amount)
                                }})
                            </option>
                        </select>
                        <p
                            v-if="
                                !form.project_id || purchaseOrders.length === 0
                            "
                            class="mt-1 text-xs text-gray-500"
                        >
                            {{
                                !form.project_id
                                    ? "Select a project first"
                                    : "No approved POs available for this project"
                            }}
                        </p>
                        <div
                            v-if="poAmountWarning"
                            class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg"
                        >
                            <div class="flex items-center gap-2">
                                <i
                                    class="fas fa-exclamation-triangle text-red-600 text-sm"
                                ></i>
                                <p class="text-xs text-red-800">
                                    {{ poAmountWarning }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-else-if="selectedPO"
                            class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg"
                        >
                            <div class="flex items-center gap-2">
                                <i
                                    class="fas fa-info-circle text-blue-600 text-sm"
                                ></i>
                                <p class="text-xs text-blue-800">
                                    PO Total: ${{
                                        formatAmount(selectedPO.total_amount)
                                    }}
                                    | Vendor:
                                    {{ selectedPO.vendor?.vendor_name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Category -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Expense Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.expense_category_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select Category</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Expense Date -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Expense Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.expense_date"
                            type="date"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Amount -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-500"
                                >$</span
                            >
                            <input
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00"
                            />
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Provide a detailed description of the expense..."
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Receipt Upload -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Receipt/Documentation
                </h2>

                <div class="space-y-4">
                    <!-- Current Receipt -->
                    <div
                        v-if="currentReceipt"
                        class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg"
                    >
                        <i class="fas fa-file-pdf text-3xl text-red-500"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                Current Receipt
                            </p>
                            <a
                                :href="`/storage/${currentReceipt}`"
                                target="_blank"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                View Receipt
                            </a>
                        </div>
                        <button
                            type="button"
                            @click="removeReceipt"
                            class="text-red-600 hover:text-red-800"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Upload Receipt {{ !isEditMode ? "(Optional)" : "" }}
                        </label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition"
                        >
                            <div class="space-y-1 text-center">
                                <i
                                    class="fas fa-cloud-upload-alt text-4xl text-gray-400"
                                ></i>
                                <div class="flex text-sm text-gray-600">
                                    <label
                                        for="receipt-upload"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                                    >
                                        <span>Upload a file</span>
                                        <input
                                            id="receipt-upload"
                                            name="receipt"
                                            type="file"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            @change="handleFileUpload"
                                            class="sr-only"
                                        />
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PDF, PNG, JPG up to 5MB
                                </p>
                            </div>
                        </div>
                        <p
                            v-if="selectedFile"
                            class="mt-2 text-sm text-gray-600"
                        >
                            Selected: {{ selectedFile.name }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-4">
                <button
                    type="button"
                    @click="goBack"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2"
                >
                    <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-save"></i>
                    {{
                        loading
                            ? "Saving..."
                            : isEditMode
                              ? "Update Expense"
                              : "Save as Draft"
                    }}
                </button>
                <button
                    v-if="
                        !isEditMode || (isEditMode && expenseStatus === 'Draft')
                    "
                    type="button"
                    @click="handleSubmitAndSend"
                    :disabled="loading"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2"
                >
                    <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-paper-plane"></i>
                    {{ loading ? "Submitting..." : "Save & Submit for Review" }}
                </button>
                <button
                    v-if="isEditMode && expenseStatus === 'Draft'"
                    type="button"
                    @click="handleDirectSubmit"
                    :disabled="loading"
                    class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2"
                >
                    <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-share"></i>
                    {{ loading ? "Submitting..." : "Submit Without Changes" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import api from "@/api";
import Swal from "sweetalert2";
import { useExpenseStore } from "@/stores/expenseStore";
import { useProjectStore } from "@/stores/projectStore";
import { usePurchaseOrderStore } from "@/stores/purchaseOrderStore";

// Get expense ID from URL if in edit mode
const getExpenseIdFromUrl = () => {
    const pathParts = window.location.pathname.split("/");
    const expensesIndex = pathParts.indexOf("expenses");
    if (
        expensesIndex !== -1 &&
        pathParts[expensesIndex + 1] &&
        pathParts[expensesIndex + 1] !== "create"
    ) {
        return pathParts[expensesIndex + 1];
    }
    return null;
};

const expenseId = getExpenseIdFromUrl();
const expenseStore = useExpenseStore();
const projectStore = useProjectStore();
const purchaseOrderStore = usePurchaseOrderStore();

const loading = ref(false);
const error = ref(null);
const projects = ref([]);
const budgetItems = ref([]);
const categories = ref([]);
const purchaseOrders = ref([]);
const selectedFile = ref(null);
const currentReceipt = ref(null);
const expenseStatus = ref(null);

const form = ref({
    project_id: "",
    budget_item_id: "",
    purchase_order_id: "",
    expense_category_id: "",
    expense_date: new Date().toISOString().split("T")[0],
    amount: "",
    description: "",
    receipt: null,
});

const isEditMode = computed(() => !!expenseId);

const selectedPO = computed(() => {
    if (!form.value.purchase_order_id) return null;
    return purchaseOrders.value.find(
        (po) => po.id === parseInt(form.value.purchase_order_id),
    );
});

const poAmountWarning = computed(() => {
    if (!selectedPO.value || !form.value.amount) return null;

    const expenseAmount = parseFloat(form.value.amount);
    const poTotalAmount = parseFloat(selectedPO.value.total_amount);

    if (expenseAmount > poTotalAmount) {
        return `Expense amount ($${expenseAmount.toFixed(2)}) exceeds PO total ($${poTotalAmount.toFixed(2)})`;
    }

    return null;
});

// Methods
const loadProjects = async () => {
    try {
        await projectStore.fetchProjects();
        projects.value = projectStore.projects;
    } catch (err) {
        console.error("Error loading projects:", err);
    }
};

const loadCategories = async () => {
    try {
        await expenseStore.fetchCategories();
        categories.value = expenseStore.categories;
    } catch (err) {
        console.error("Error loading categories:", err);
    }
};

const loadExpense = async () => {
    if (!isEditMode.value) return;

    try {
        loading.value = true;
        const expense = await expenseStore.fetchExpense(expenseId);

        // Store expense status for conditional button display
        expenseStatus.value = expense.status;

        // Store values that will be needed after handleProjectChange
        const savedBudgetItemId = expense.budget_item_id;
        const savedPurchaseOrderId = expense.purchase_order_id || "";

        // Format expense_date to yyyy-MM-dd format for HTML date input
        let formattedDate = "";
        if (expense.expense_date) {
            const dateObj = new Date(expense.expense_date);
            if (!isNaN(dateObj.getTime())) {
                formattedDate = dateObj.toISOString().split("T")[0];
            }
        }

        form.value = {
            project_id: expense.project_id,
            budget_item_id: expense.budget_item_id,
            purchase_order_id: expense.purchase_order_id || "",
            expense_category_id: expense.expense_category_id,
            expense_date: formattedDate,
            amount: expense.amount,
            description: expense.description,
            receipt: null,
        };

        currentReceipt.value = expense.receipt_path;

        // Load budget items for the project (this clears budget_item_id and purchase_order_id)
        if (expense.project_id) {
            await handleProjectChange();
            // Restore the saved values after handleProjectChange loads the data
            form.value.budget_item_id = savedBudgetItemId;
            form.value.purchase_order_id = savedPurchaseOrderId;
        }
    } catch (err) {
        error.value = "Failed to load expense details";
        console.error("Error loading expense:", err);
    } finally {
        loading.value = false;
    }
};

const handleProjectChange = async () => {
    budgetItems.value = [];
    form.value.budget_item_id = "";
    purchaseOrders.value = [];
    form.value.purchase_order_id = "";

    if (!form.value.project_id) return;

    // Load budgets and POs independently so one failure doesn't block the other
    try {
        const response = await api.get(
            `/projects/${form.value.project_id}/budgets`,
            {
                params: { status: "approved" },
            },
        );

        if (response.data.success && response.data.data.length > 0) {
            const allItems = [];
            response.data.data.forEach((budget) => {
                if (budget.items && budget.items.length > 0) {
                    budget.items.forEach((item) => {
                        allItems.push({
                            ...item,
                            name: item.category,
                            available_amount:
                                parseFloat(item.allocated_amount || 0) -
                                parseFloat(item.spent_amount || 0),
                        });
                    });
                }
            });
            budgetItems.value = allItems;
        }
    } catch (err) {
        console.error("Error loading budget items:", err);
        budgetItems.value = [];
    }

    // Always load purchase orders regardless of budget loading result
    await loadPurchaseOrders();
};

const loadPurchaseOrders = async () => {
    try {
        if (!form.value.project_id) {
            purchaseOrders.value = [];
            return;
        }

        // Fetch approved POs for this specific project directly from API
        const response = await api.get("/purchase-orders", {
            params: {
                project_id: form.value.project_id,
                status: "Approved",
                per_page: 100,
            },
        });

        // Handle response data
        const poData = response.data?.data || response.data || [];
        purchaseOrders.value = Array.isArray(poData) ? poData : [];
    } catch (err) {
        console.error("Error loading purchase orders:", err);
        purchaseOrders.value = [];
    }
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert("File size must not exceed 5MB");
            event.target.value = "";
            return;
        }

        // Validate file type
        const allowedTypes = [
            "application/pdf",
            "image/jpeg",
            "image/jpg",
            "image/png",
        ];
        if (!allowedTypes.includes(file.type)) {
            alert("Only PDF, JPG, JPEG, and PNG files are allowed");
            event.target.value = "";
            return;
        }

        selectedFile.value = file;
        form.value.receipt = file;
    }
};

const removeReceipt = () => {
    currentReceipt.value = null;
    form.value.receipt = null;
    selectedFile.value = null;
};

const handleSubmit = async () => {
    try {
        loading.value = true;
        error.value = null;

        const formData = {
            ...form.value,
            receipt: form.value.receipt || undefined,
        };

        if (isEditMode.value) {
            await expenseStore.updateExpense(expenseId, formData);
        } else {
            await expenseStore.createExpense(formData);
        }

        window.location.href = "/expenses";
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to save expense";
        console.error("Error saving expense:", err);
    } finally {
        loading.value = false;
    }
};

const handleSubmitAndSend = async () => {
    try {
        loading.value = true;
        error.value = null;

        const formData = {
            ...form.value,
            receipt: form.value.receipt || undefined,
        };

        let expense;
        if (isEditMode.value) {
            // Update existing expense
            expense = await expenseStore.updateExpense(expenseId, formData);
        } else {
            // Create new expense
            expense = await expenseStore.createExpense(formData);
        }

        // Submit the expense
        const expenseToSubmit = expense?.id || expenseId;
        await expenseStore.submitExpense(expenseToSubmit);

        window.location.href = "/expenses";
    } catch (err) {
        error.value =
            err.response?.data?.message || "Failed to save and submit expense";
        console.error("Error submitting expense:", err);
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    window.location.href = "/expenses";
};

const handleDirectSubmit = async () => {
    const result = await Swal.fire({
        title: "Submit Expense?",
        text: "This will submit the draft expense for review without saving changes.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#1E40AF",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, Submit",
        cancelButtonText: "Cancel",
    });

    if (!result.isConfirmed) return;

    try {
        loading.value = true;
        error.value = null;

        await expenseStore.submitExpense(expenseId);

        Swal.fire({
            icon: "success",
            title: "Submitted!",
            text: "Expense has been submitted for review.",
            timer: 2000,
            showConfirmButton: false,
        });

        setTimeout(() => {
            window.location.href = "/expenses";
        }, 1500);
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to submit expense";
        Swal.fire({
            icon: "error",
            title: "Submission Failed",
            text: error.value,
        });
        console.error("Error submitting expense:", err);
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

onMounted(async () => {
    await loadProjects();
    await loadCategories();
    if (isEditMode.value) {
        await loadExpense();
    }
});
</script>
