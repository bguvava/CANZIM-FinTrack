<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
            @click="closeModal"
        ></div>

        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div
                class="relative w-full max-w-4xl transform rounded-lg bg-white shadow-xl transition-all"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3
                        id="modal-title"
                        class="text-lg font-semibold text-gray-900"
                    >
                        Create New Budget
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm">
                    <div class="max-h-[70vh] overflow-y-auto px-6 py-4">
                        <div class="space-y-6">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Project -->
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700"
                                    >
                                        Project
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.project_id"
                                        required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        :class="{
                                            'border-red-500': errors.project_id,
                                        }"
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
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.project_id[0] }}
                                    </p>
                                </div>

                                <!-- Donor -->
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700"
                                    >
                                        Donor
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.donor_id"
                                        required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        :class="{
                                            'border-red-500': errors.donor_id,
                                        }"
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
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.donor_id[0] }}
                                    </p>
                                </div>

                                <!-- Period -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700"
                                    >
                                        Fiscal Year
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.fiscal_year"
                                        type="text"
                                        required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        :class="{
                                            'border-red-500':
                                                errors.fiscal_year,
                                        }"
                                        placeholder="2025"
                                    />
                                    <p
                                        v-if="errors.fiscal_year"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.fiscal_year[0] }}
                                    </p>
                                </div>

                                <!-- Quarter (Optional) -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700"
                                    >
                                        Quarter (Optional)
                                    </label>
                                    <select
                                        v-model="form.quarter"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                        <option value="">Full Year</option>
                                        <option value="Q1">Q1 (Jan-Mar)</option>
                                        <option value="Q2">Q2 (Apr-Jun)</option>
                                        <option value="Q3">Q3 (Jul-Sep)</option>
                                        <option value="Q4">Q4 (Oct-Dec)</option>
                                    </select>
                                </div>

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700"
                                    >
                                        Budget Notes
                                    </label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        placeholder="Enter budget notes..."
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Budget Line Items -->
                            <div>
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Budget Line Items
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <button
                                        type="button"
                                        @click="addLineItem"
                                        class="flex items-center gap-1 rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-800 hover:bg-blue-200 transition-colors"
                                    >
                                        <i class="fas fa-plus text-xs"></i>
                                        Add Line
                                    </button>
                                </div>

                                <div class="space-y-2">
                                    <div
                                        v-for="(item, index) in form.line_items"
                                        :key="index"
                                        class="flex items-start gap-2"
                                    >
                                        <input
                                            v-model="item.category"
                                            type="text"
                                            required
                                            placeholder="Category"
                                            class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        />
                                        <input
                                            v-model.number="
                                                item.allocated_amount
                                            "
                                            type="number"
                                            required
                                            min="0"
                                            step="0.01"
                                            placeholder="Amount"
                                            class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        />
                                        <button
                                            type="button"
                                            @click="removeLineItem(index)"
                                            :disabled="
                                                form.line_items.length === 1
                                            "
                                            class="rounded-lg border border-red-300 px-3 py-2 text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="mt-3 rounded-lg bg-blue-50 p-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                        >
                                            Total Budget:
                                        </span>
                                        <span
                                            class="text-lg font-bold text-blue-900"
                                        >
                                            ${{ totalBudget.toLocaleString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="
                                submitting || form.line_items.length === 0
                            "
                            class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="!submitting">Create Budget</span>
                            <span v-else class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                Creating...
                            </span>
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
    projects: {
        type: Array,
        default: () => [],
    },
    donors: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "budget-created"]);

const form = ref({
    project_id: "",
    donor_id: "",
    fiscal_year: new Date().getFullYear().toString(),
    quarter: "",
    notes: "",
    line_items: [{ category: "", allocated_amount: 0 }],
});

const errors = ref({});
const submitting = ref(false);

const totalBudget = computed(() => {
    return form.value.line_items.reduce(
        (sum, item) => sum + (parseFloat(item.allocated_amount) || 0),
        0,
    );
});

watch(
    () => props.isOpen,
    (newValue) => {
        if (newValue) {
            resetForm();
        }
    },
);

const resetForm = () => {
    form.value = {
        project_id: "",
        donor_id: "",
        fiscal_year: new Date().getFullYear().toString(),
        quarter: "",
        notes: "",
        line_items: [{ category: "", allocated_amount: 0 }],
    };
    errors.value = {};
};

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

const submitForm = async () => {
    if (submitting.value) return;

    submitting.value = true;
    errors.value = {};

    try {
        const response = await api.post("/budgets", form.value);

        if (response.data.status === "success") {
            showSuccess("Success!", "Budget created successfully.");
            emit("budget-created", response.data.data);
            closeModal();
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            showError("Validation Error", "Please check the form for errors.");
        } else {
            showError(
                "Error",
                error.response?.data?.message ||
                    "Failed to create budget. Please try again.",
            );
        }
    } finally {
        submitting.value = false;
    }
};
</script>
