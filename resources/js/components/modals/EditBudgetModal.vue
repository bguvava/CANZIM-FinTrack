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
                        Edit Budget
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
                        <!-- Project and Donor Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                    <option value="">Select Project</option>
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

                            <!-- Donor -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Donor
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.donor_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                    :class="{
                                        'border-red-500': errors.donor_id,
                                    }"
                                    required
                                >
                                    <option value="">Select Donor</option>
                                    <option
                                        v-for="donor in donors"
                                        :key="donor.id"
                                        :value="donor.id"
                                    >
                                        {{ donor.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.donor_id"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.donor_id[0] }}
                                </p>
                            </div>
                        </div>

                        <!-- Fiscal Year and Quarter -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fiscal Year -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Fiscal Year
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    v-model.number="form.fiscal_year"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                    :class="{
                                        'border-red-500': errors.fiscal_year,
                                    }"
                                    placeholder="e.g., 2025"
                                    required
                                />
                                <p
                                    v-if="errors.fiscal_year"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.fiscal_year[0] }}
                                </p>
                            </div>

                            <!-- Quarter -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Quarter
                                </label>
                                <select
                                    v-model="form.quarter"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                    :class="{
                                        'border-red-500': errors.quarter,
                                    }"
                                >
                                    <option value="">Full Year</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                                <p
                                    v-if="errors.quarter"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.quarter[0] }}
                                </p>
                            </div>
                        </div>

                        <!-- Budget Line Items -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Budget Line Items
                                    <span class="text-red-500">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="addLineItem"
                                    class="px-3 py-1 text-sm bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition"
                                >
                                    <i class="fas fa-plus mr-1"></i>
                                    Add Line Item
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="(item, index) in form.line_items"
                                    :key="index"
                                    class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start p-4 bg-gray-50 rounded-lg"
                                >
                                    <!-- Category -->
                                    <div class="md:col-span-7">
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Category
                                        </label>
                                        <select
                                            v-model="item.category"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                            :class="{
                                                'border-red-500':
                                                    errors[
                                                        `items.${index}.category`
                                                    ],
                                            }"
                                            required
                                        >
                                            <option value="">
                                                Select Category
                                            </option>
                                            <option value="Travel">
                                                Travel
                                            </option>
                                            <option value="Staff Salaries">
                                                Staff Salaries
                                            </option>
                                            <option
                                                value="Procurement/Supplies"
                                            >
                                                Procurement/Supplies
                                            </option>
                                            <option value="Consultants">
                                                Consultants
                                            </option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <p
                                            v-if="
                                                errors[
                                                    `items.${index}.category`
                                                ]
                                            "
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{
                                                errors[
                                                    `items.${index}.category`
                                                ][0]
                                            }}
                                        </p>
                                    </div>

                                    <!-- Allocated Amount -->
                                    <div class="md:col-span-4">
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Allocated Amount
                                        </label>
                                        <input
                                            type="number"
                                            v-model.number="
                                                item.allocated_amount
                                            "
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                            :class="{
                                                'border-red-500':
                                                    errors[
                                                        `items.${index}.allocated_amount`
                                                    ],
                                            }"
                                            placeholder="0.00"
                                            step="0.01"
                                            min="0"
                                            required
                                        />
                                        <p
                                            v-if="
                                                errors[
                                                    `items.${index}.allocated_amount`
                                                ]
                                            "
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{
                                                errors[
                                                    `items.${index}.allocated_amount`
                                                ][0]
                                            }}
                                        </p>
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="md:col-span-1 flex items-end">
                                        <button
                                            type="button"
                                            @click="removeLineItem(index)"
                                            :disabled="
                                                form.line_items.length === 1
                                            "
                                            class="w-full px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                                            title="Remove line item"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p
                                v-if="errors.line_items"
                                class="mt-2 text-sm text-red-500"
                            >
                                {{ errors.line_items[0] }}
                            </p>
                        </div>

                        <!-- Total Budget Display -->
                        <div
                            class="p-4 bg-blue-50 border border-blue-200 rounded-lg"
                        >
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700"
                                    >Total Budget:</span
                                >
                                <span class="text-xl font-bold text-blue-800">
                                    ${{ totalBudget.toLocaleString() }}
                                </span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Notes
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                :class="{ 'border-red-500': errors.notes }"
                                placeholder="Add any additional notes or comments..."
                            ></textarea>
                            <p
                                v-if="errors.notes"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.notes[0] }}
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
                                class="fas fa-save mr-1.5"
                            ></i>
                            {{ submitting ? "Updating..." : "Update Budget" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import api from "../../api";
import { showSuccess, showError } from "../../plugins/sweetalert";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    budget: {
        type: Object,
        default: null,
    },
    projects: {
        type: Array,
        default: () => [],
    },
    donors: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "budget-updated"]);

const form = ref({
    project_id: "",
    donor_id: "",
    fiscal_year: new Date().getFullYear(),
    quarter: "",
    line_items: [{ category: "", allocated_amount: 0 }],
    notes: "",
});

const errors = ref({});
const submitting = ref(false);

const totalBudget = computed(() => {
    return form.value.line_items.reduce(
        (sum, item) => sum + (parseFloat(item.allocated_amount) || 0),
        0,
    );
});

// Watch budget prop to populate form
watch(
    () => props.budget,
    (newBudget) => {
        if (newBudget) {
            // Donor comes from project relationship, not budget directly
            const projectDonorId = newBudget.project?.donors?.[0]?.id || "";

            form.value = {
                project_id: newBudget.project_id || "",
                donor_id: projectDonorId,
                fiscal_year: newBudget.fiscal_year || new Date().getFullYear(),
                quarter: newBudget.quarter || "",
                line_items:
                    newBudget.items && newBudget.items.length > 0
                        ? newBudget.items.map((item) => ({
                              category: item.category || "",
                              allocated_amount: item.allocated_amount || 0,
                          }))
                        : [{ category: "", allocated_amount: 0 }],
                notes: newBudget.notes || "",
            };
            errors.value = {};
        }
    },
    { immediate: true },
);

const addLineItem = () => {
    form.value.line_items.push({ category: "", allocated_amount: 0 });
};

const removeLineItem = (index) => {
    if (form.value.line_items.length > 1) {
        form.value.line_items.splice(index, 1);
    }
};

const closeModal = () => {
    if (!submitting.value) {
        emit("close");
    }
};

const handleSubmit = async () => {
    if (submitting.value || !props.budget) return;

    submitting.value = true;
    errors.value = {};

    try {
        // Transform line_items to items for backend
        const payload = {
            project_id: form.value.project_id,
            fiscal_year: form.value.fiscal_year.toString(),
            items: form.value.line_items.map((item) => ({
                category: item.category,
                allocated_amount: parseFloat(item.allocated_amount) || 0,
                description: item.description || null,
                cost_code: item.cost_code || null,
            })),
        };

        await api.put(`/budgets/${props.budget.id}`, payload);
        showSuccess("Budget updated successfully!");
        emit("budget-updated");
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            const message =
                error.response.data.message ||
                "Please check the form for errors.";
            showError(message);
        } else {
            showError("Failed to update budget. Please try again.");
        }
    } finally {
        submitting.value = false;
    }
};
</script>
