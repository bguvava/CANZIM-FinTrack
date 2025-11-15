<template>
    <div
        v-if="isOpen && project"
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
                    <div>
                        <h3
                            id="modal-title"
                            class="text-lg font-semibold text-gray-900"
                        >
                            {{ project.name }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ project.code }}
                        </p>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="max-h-[70vh] overflow-y-auto px-6 py-4">
                    <div class="space-y-6">
                        <!-- Status Badge -->
                        <div>
                            <span
                                :class="getStatusClass(project.status)"
                                class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold"
                            >
                                <i
                                    :class="getStatusIcon(project.status)"
                                    class="mr-1.5"
                                ></i>
                                {{ getStatusLabel(project.status) }}
                            </span>
                        </div>

                        <!-- Project Details -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500"
                                >
                                    Start Date
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(project.start_date) }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500"
                                >
                                    End Date
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(project.end_date) }}
                                </p>
                            </div>

                            <div v-if="project.location" class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-500"
                                >
                                    Location
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ project.location }}
                                </p>
                            </div>

                            <div
                                v-if="project.description"
                                class="md:col-span-2"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-500"
                                >
                                    Description
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ project.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Donors -->
                        <div v-if="project.donors && project.donors.length > 0">
                            <label
                                class="block text-sm font-medium text-gray-500 mb-2"
                            >
                                Donors
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="donor in project.donors"
                                    :key="donor.id"
                                    class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800"
                                >
                                    {{ donor.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Budget Summary -->
                        <div
                            v-if="project.budgets && project.budgets.length > 0"
                        >
                            <label
                                class="block text-sm font-medium text-gray-500 mb-2"
                            >
                                Budget Summary
                            </label>
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Total Budget
                                        </p>
                                        <p
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            ${{
                                                project.total_budget?.toLocaleString() ||
                                                "0.00"
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Spent
                                        </p>
                                        <p
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            ${{
                                                project.total_spent?.toLocaleString() ||
                                                "0.00"
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Remaining
                                        </p>
                                        <p
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            ${{
                                                project.total_remaining?.toLocaleString() ||
                                                "0.00"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Metadata -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="grid grid-cols-1 gap-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Created:</span>
                                    <span class="text-gray-900">{{
                                        formatDate(project.created_at)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500"
                                        >Last Updated:</span
                                    >
                                    <span class="text-gray-900">{{
                                        formatDate(project.updated_at)
                                    }}</span>
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
                        Close
                    </button>
                    <button
                        type="button"
                        @click="handleEdit"
                        class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 transition-colors"
                    >
                        <i class="fas fa-edit mr-1.5"></i>
                        Edit
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    project: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "edit"]);

const closeModal = () => {
    emit("close");
};

const handleEdit = () => {
    emit("edit", props.project);
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const getStatusClass = (status) => {
    const classes = {
        planning: "bg-gray-100 text-gray-800",
        active: "bg-green-100 text-green-800",
        on_hold: "bg-yellow-100 text-yellow-800",
        completed: "bg-blue-100 text-blue-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getStatusIcon = (status) => {
    const icons = {
        planning: "fas fa-clock",
        active: "fas fa-play-circle",
        on_hold: "fas fa-pause-circle",
        completed: "fas fa-check-circle",
        cancelled: "fas fa-times-circle",
    };
    return icons[status] || "fas fa-circle";
};

const getStatusLabel = (status) => {
    const labels = {
        planning: "Planning",
        active: "Active",
        on_hold: "On Hold",
        completed: "Completed",
        cancelled: "Cancelled",
    };
    return labels[status] || status;
};
</script>
