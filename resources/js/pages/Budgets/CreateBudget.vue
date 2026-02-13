<template>
    <div class="p-6">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Budget</h1>
                <p class="mt-1 text-sm text-gray-600400">
                    Create a new budget for your project with detailed line
                    items
                </p>
            </div>
            <button
                @click="router.push('/budgets')"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2600800300"
            >
                <i class="fa fa-arrow-left mr-2"></i>
                Back to Budgets
            </button>
        </div>

        <!-- Error Alert -->
        <div
            v-if="error"
            class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4800900/20"
        >
            <div class="flex items-start">
                <i
                    class="fa fa-exclamation-circle mr-3 mt-0.5 text-red-600400"
                ></i>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-red-800300">
                        Error Creating Budget
                    </h3>
                    <p class="mt-1 text-sm text-red-700400">
                        {{ error }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Create Budget Form -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm700800">
            <form @submit.prevent="handleSubmit">
                <!-- Project Selection Section -->
                <div class="border-b border-gray-200 p-6700">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Project Selection
                    </h2>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Project Dropdown -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700300"
                            >
                                Project <span class="text-red-600">*</span>
                            </label>
                            <select
                                v-model="formData.project_id"
                                @change="loadProjectDonors"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500600700"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500':
                                        validationErrors.project_id,
                                }"
                                required
                            >
                                <option value="">Select a project...</option>
                                <option
                                    v-for="project in projects"
                                    :key="project.id"
                                    :value="project.id"
                                >
                                    {{ project.code }} - {{ project.name }}
                                </option>
                            </select>
                            <p
                                v-if="validationErrors.project_id"
                                class="mt-1 text-xs text-red-600400"
                            >
                                {{ validationErrors.project_id[0] }}
                            </p>
                        </div>

                        <!-- Donor Selection -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-gray-700300"
                            >
                                Donor Source <span class="text-red-600">*</span>
                            </label>
                            <select
                                v-model="formData.donor_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-gray-100600700"
                                :class="{
                                    'border-red-500 focus:border-red-500 focus:ring-red-500':
                                        validationErrors.donor_id,
                                }"
                                :disabled="
                                    !formData.project_id ||
                                    projectDonors.length === 0
                                "
                                required
                            >
                                <option value="">Select donor...</option>
                                <option
                                    v-for="donor in projectDonors"
                                    :key="donor.id"
                                    :value="donor.id"
                                >
                                    {{ donor.name }} - ${{
                                        parseFloat(
                                            donor.available_funding,
                                        ).toLocaleString()
                                    }}
                                    available
                                </option>
                            </select>
                            <p
                                v-if="validationErrors.donor_id"
                                class="mt-1 text-xs text-red-600400"
                            >
                                {{ validationErrors.donor_id[0] }}
                            </p>
                            <p
                                v-if="!formData.project_id"
                                class="mt-1 text-xs text-gray-500400"
                            >
                                Select a project first
                            </p>
                        </div>
                    </div>

                    <!-- Budget Notes -->
                    <div class="mt-6">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700300"
                        >
                            Budget Notes
                        </label>
                        <textarea
                            v-model="formData.notes"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500600700"
                            placeholder="Enter any additional notes or context for this budget..."
                        ></textarea>
                    </div>
                </div>

                <!-- Budget Line Items Section -->
                <div class="border-b border-gray-200 p-6700">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Budget Line Items
                        </h2>
                        <button
                            type="button"
                            @click="addLineItem"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2500"
                        >
                            <i class="fa fa-plus mr-2"></i>
                            Add Line Item
                        </button>
                    </div>

                    <p
                        v-if="lineItems.length === 0"
                        class="mb-4 text-sm text-gray-600400"
                    >
                        Click "Add Line Item" to start building your budget
                    </p>

                    <!-- Line Items Table -->
                    <div
                        v-if="lineItems.length > 0"
                        class="overflow-x-auto rounded-lg border border-gray-200700"
                    >
                        <table class="w-full">
                            <thead class="bg-gray-50900">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700300"
                                    >
                                        Category
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700300"
                                    >
                                        Description
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700300"
                                    >
                                        Quantity
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700300"
                                    >
                                        Unit Cost
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700300"
                                    >
                                        Total
                                    </th>
                                    <th class="w-20 px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 bg-white700800"
                            >
                                <tr
                                    v-for="(item, index) in lineItems"
                                    :key="index"
                                >
                                    <!-- Category -->
                                    <td class="px-4 py-3">
                                        <select
                                            v-model="item.category_id"
                                            class="w-full rounded border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500600700"
                                            required
                                        >
                                            <option value="">Select...</option>
                                            <option
                                                v-for="category in categories"
                                                :key="category.id"
                                                :value="category.id"
                                            >
                                                {{ category.name }}
                                            </option>
                                        </select>
                                    </td>

                                    <!-- Description -->
                                    <td class="px-4 py-3">
                                        <input
                                            v-model="item.description"
                                            type="text"
                                            class="w-full rounded border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500600700"
                                            placeholder="Item description..."
                                            required
                                        />
                                    </td>

                                    <!-- Quantity -->
                                    <td class="px-4 py-3">
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            step="1"
                                            class="w-24 rounded border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500600700"
                                            required
                                        />
                                    </td>

                                    <!-- Unit Cost -->
                                    <td class="px-4 py-3">
                                        <input
                                            v-model.number="item.unit_cost"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-32 rounded border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500600700"
                                            placeholder="0.00"
                                            required
                                        />
                                    </td>

                                    <!-- Total -->
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            ${{
                                                calculateItemTotal(
                                                    item,
                                                ).toLocaleString("en-US", {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                })
                                            }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            @click="removeLineItem(index)"
                                            class="text-red-600 transition-colors hover:text-red-800400"
                                            title="Remove item"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50900">
                                <tr>
                                    <td
                                        colspan="4"
                                        class="px-4 py-3 text-right text-sm font-semibold text-gray-900"
                                    >
                                        Budget Total:
                                    </td>
                                    <td
                                        colspan="2"
                                        class="px-4 py-3 text-sm font-bold text-gray-900"
                                    >
                                        ${{
                                            budgetTotal.toLocaleString(
                                                "en-US",
                                                {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                },
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p
                        v-if="validationErrors.line_items"
                        class="mt-2 text-xs text-red-600400"
                    >
                        {{ validationErrors.line_items[0] }}
                    </p>
                </div>

                <!-- Summary & Submit Section -->
                <div class="bg-gray-50 p-6900">
                    <div
                        class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4800900/20"
                    >
                        <h3 class="mb-3 text-sm font-semibold text-blue-900300">
                            Budget Summary
                        </h3>
                        <div class="grid gap-3 md:grid-cols-3">
                            <div>
                                <p class="text-xs text-blue-700400">
                                    Line Items
                                </p>
                                <p class="text-lg font-bold text-blue-900300">
                                    {{ lineItems.length }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-blue-700400">
                                    Total Budget
                                </p>
                                <p class="text-lg font-bold text-blue-900300">
                                    ${{
                                        budgetTotal.toLocaleString("en-US", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })
                                    }}
                                </p>
                            </div>
                            <div v-if="selectedDonor">
                                <p class="text-xs text-blue-700400">
                                    Available Funding
                                </p>
                                <p class="text-lg font-bold text-blue-900300">
                                    ${{
                                        parseFloat(
                                            selectedDonor.available_funding,
                                        ).toLocaleString("en-US", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Over Budget Warning -->
                        <div
                            v-if="
                                selectedDonor &&
                                budgetTotal >
                                    parseFloat(selectedDonor.available_funding)
                            "
                            class="mt-3 rounded border border-red-300 bg-red-100 p-3800900/30"
                        >
                            <div class="flex items-start">
                                <i
                                    class="fa fa-exclamation-triangle mr-2 mt-0.5 text-red-600400"
                                ></i>
                                <p class="text-xs text-red-800300">
                                    <strong>Warning:</strong> Budget total
                                    exceeds available donor funding by ${{
                                        (
                                            budgetTotal -
                                            parseFloat(
                                                selectedDonor.available_funding,
                                            )
                                        ).toLocaleString("en-US", {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        })
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="router.push('/budgets')"
                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2600800300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="loading || lineItems.length === 0"
                            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50500"
                        >
                            <i
                                v-if="loading"
                                class="fa fa-spinner fa-spin mr-2"
                            ></i>
                            <i v-else class="fa fa-check mr-2"></i>
                            {{
                                loading ? "Creating Budget..." : "Create Budget"
                            }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useBudgetStore } from "@/stores/budgetStore";
import { useProjectStore } from "@/stores/projectStore";
import Swal from "sweetalert2";

const budgetStore = useBudgetStore();
const projectStore = useProjectStore();
const router = useRouter();

// State
const loading = ref(false);
const error = ref(null);
const projects = ref([]);
const projectDonors = ref([]);
const categories = ref([]);
const validationErrors = ref({});

const formData = ref({
    project_id: "",
    donor_id: "",
    notes: "",
});

const lineItems = ref([]);

// Computed
const budgetTotal = computed(() => {
    return lineItems.value.reduce((sum, item) => {
        return sum + calculateItemTotal(item);
    }, 0);
});

const selectedDonor = computed(() => {
    return projectDonors.value.find((d) => d.id === formData.value.donor_id);
});

// Methods
const calculateItemTotal = (item) => {
    const quantity = parseFloat(item.quantity) || 0;
    const unitCost = parseFloat(item.unit_cost) || 0;
    return quantity * unitCost;
};

const addLineItem = () => {
    lineItems.value.push({
        category_id: "",
        description: "",
        quantity: 1,
        unit_cost: 0,
    });
};

const removeLineItem = (index) => {
    lineItems.value.splice(index, 1);
};

const loadProjectDonors = async () => {
    if (!formData.value.project_id) {
        projectDonors.value = [];
        formData.value.donor_id = "";
        return;
    }

    try {
        const project = projects.value.find(
            (p) => p.id === formData.value.project_id,
        );
        if (project && project.donors) {
            // Calculate available funding for each donor (total - already budgeted)
            projectDonors.value = project.donors.map((donor) => {
                const totalFunding =
                    parseFloat(donor.pivot.funding_amount) || 0;
                const budgeted = parseFloat(donor.pivot.budgeted_amount) || 0;
                return {
                    ...donor,
                    available_funding: totalFunding - budgeted,
                };
            });
        }
    } catch (err) {
        console.error("Error loading project donors:", err);
    }
};

const handleSubmit = async () => {
    if (lineItems.value.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No Line Items",
            text: "Please add at least one budget line item.",
            confirmButtonColor: "#3b82f6",
        });
        return;
    }

    loading.value = true;
    error.value = null;
    validationErrors.value = {};

    try {
        const payload = {
            project_id: formData.value.project_id,
            donor_id: formData.value.donor_id,
            notes: formData.value.notes || null,
            line_items: lineItems.value.map((item) => ({
                category_id: item.category_id,
                description: item.description,
                quantity: parseFloat(item.quantity),
                unit_cost: parseFloat(item.unit_cost),
            })),
        };

        await budgetStore.createBudget(payload);

        Swal.fire({
            icon: "success",
            title: "Budget Created",
            text: "The budget has been created successfully and submitted for approval.",
            confirmButtonColor: "#3b82f6",
        }).then(() => {
            router.push("/budgets");
        });
    } catch (err) {
        console.error("Error creating budget:", err);

        if (err.response?.data?.errors) {
            validationErrors.value = err.response.data.errors;
            error.value = "Please fix the validation errors below.";
        } else {
            error.value =
                err.response?.data?.message ||
                "Failed to create budget. Please try again.";
        }

        window.scrollTo({ top: 0, behavior: "smooth" });
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(async () => {
    try {
        // Load projects with donors
        await projectStore.fetchProjects({
            per_page: 100,
            status: "active,planning",
        });
        projects.value = projectStore.projects;

        // Load budget categories
        await budgetStore.fetchBudgetCategories();
        categories.value = budgetStore.categories;

        // Add initial line item
        addLineItem();
    } catch (err) {
        console.error("Error loading data:", err);
        error.value = "Failed to load required data. Please refresh the page.";
    }
});
</script>
