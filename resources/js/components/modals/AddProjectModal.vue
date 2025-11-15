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
                class="relative w-full max-w-3xl transform rounded-lg bg-white shadow-xl transition-all"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3
                        id="modal-title"
                        class="text-lg font-semibold text-gray-900"
                    >
                        Add New Project
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
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Project Name -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Project Name
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.name,
                                    }"
                                    placeholder="Enter project name"
                                />
                                <p
                                    v-if="errors.name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.name[0] }}
                                </p>
                            </div>

                            <!-- Project Code -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Project Code
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.code"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.code,
                                    }"
                                    placeholder="PRJ-001"
                                />
                                <p
                                    v-if="errors.code"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.code[0] }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Status
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.status"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.status,
                                    }"
                                >
                                    <option value="">Select Status</option>
                                    <option value="planning">Planning</option>
                                    <option value="active">Active</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <p
                                    v-if="errors.status"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.status[0] }}
                                </p>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Start Date
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.start_date,
                                    }"
                                />
                                <p
                                    v-if="errors.start_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.start_date[0] }}
                                </p>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    End Date
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.end_date,
                                    }"
                                />
                                <p
                                    v-if="errors.end_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.end_date[0] }}
                                </p>
                            </div>

                            <!-- Location -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Location
                                </label>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.location,
                                    }"
                                    placeholder="Project location"
                                />
                                <p
                                    v-if="errors.location"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.location[0] }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.description,
                                    }"
                                    placeholder="Enter project description"
                                ></textarea>
                                <p
                                    v-if="errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.description[0] }}
                                </p>
                            </div>

                            <!-- Donors Selection -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Select Donors
                                </label>
                                <div
                                    class="grid grid-cols-1 gap-2 md:grid-cols-2"
                                >
                                    <label
                                        v-for="donor in donors"
                                        :key="donor.id"
                                        class="flex items-center gap-2 rounded-lg border border-gray-300 p-3 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="donor.id"
                                            v-model="form.donor_ids"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-700">{{
                                            donor.name
                                        }}</span>
                                    </label>
                                </div>
                                <p
                                    v-if="errors.donor_ids"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.donor_ids[0] }}
                                </p>
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
                            :disabled="submitting"
                            class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="!submitting">Create Project</span>
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
import { ref, watch } from "vue";
import api from "../../api";
import { showSuccess, showError } from "../../plugins/sweetalert";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    donors: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "project-created"]);

const form = ref({
    name: "",
    code: "",
    status: "",
    start_date: "",
    end_date: "",
    location: "",
    description: "",
    donor_ids: [],
});

const errors = ref({});
const submitting = ref(false);

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
        name: "",
        code: "",
        status: "",
        start_date: "",
        end_date: "",
        location: "",
        description: "",
        donor_ids: [],
    };
    errors.value = {};
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
        const response = await api.post("/projects", form.value);

        if (response.data.status === "success") {
            showSuccess("Success!", "Project created successfully.");
            emit("project-created", response.data.data);
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
                    "Failed to create project. Please try again.",
            );
        }
    } finally {
        submitting.value = false;
    }
};
</script>
