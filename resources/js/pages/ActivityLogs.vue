<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Activity Logs
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Monitor system activity and user actions
                    </p>
                </div>
                <button
                    @click="openBulkDeleteModal"
                    class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700"
                >
                    <i class="fas fa-trash"></i>
                    Bulk Delete
                </button>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <!-- User Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            User
                        </label>
                        <select
                            v-model="filters.user_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadActivityLogs"
                        >
                            <option value="">All Users</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Activity Type Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Activity Type
                        </label>
                        <select
                            v-model="filters.activity_type"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadActivityLogs"
                        >
                            <option value="">All Types</option>
                            <option value="user_created">User Created</option>
                            <option value="user_updated">User Updated</option>
                            <option value="user_deactivated">
                                User Deactivated
                            </option>
                            <option value="user_activated">
                                User Activated
                            </option>
                            <option value="user_deleted">User Deleted</option>
                            <option value="password_changed">
                                Password Changed
                            </option>
                            <option value="profile_updated">
                                Profile Updated
                            </option>
                            <option value="avatar_uploaded">
                                Avatar Uploaded
                            </option>
                            <option value="logs_bulk_deleted">
                                Logs Bulk Deleted
                            </option>
                        </select>
                    </div>

                    <!-- Date From Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Date From
                        </label>
                        <input
                            v-model="filters.date_from"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadActivityLogs"
                        />
                    </div>

                    <!-- Date To Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Date To
                        </label>
                        <input
                            v-model="filters.date_to"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadActivityLogs"
                        />
                    </div>
                </div>

                <!-- Clear Filters -->
                <div v-if="hasActiveFilters" class="mt-3 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="text-sm text-blue-800 hover:text-blue-900 font-medium"
                    >
                        <i class="fas fa-times-circle mr-1"></i>
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Activity Logs Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Loading State -->
                <div v-if="loading" class="p-8 text-center">
                    <i
                        class="fas fa-spinner fa-spin text-3xl text-blue-800"
                    ></i>
                    <p class="mt-2 text-sm text-gray-600">
                        Loading activity logs...
                    </p>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="activityLogs.length === 0"
                    class="p-8 text-center"
                >
                    <i class="fas fa-history text-4xl text-gray-400"></i>
                    <p class="mt-2 text-sm font-medium text-gray-900">
                        No activity logs found
                    </p>
                    <p class="text-sm text-gray-600">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters"
                                : "Activity logs will appear here"
                        }}
                    </p>
                </div>

                <!-- Activity Logs Table -->
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    User
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Activity
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Description
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Date & Time
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="log in activityLogs"
                                :key="log.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ log.user_name || "System" }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ log.user_email || "N/A" }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                            getActivityTypeClass(
                                                log.activity_type,
                                            ),
                                        ]"
                                    >
                                        {{
                                            formatActivityType(
                                                log.activity_type,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ log.description }}
                                    </div>
                                    <div
                                        v-if="log.changes"
                                        class="mt-1 text-xs text-gray-500"
                                    >
                                        Changes:
                                        {{ formatChanges(log.changes) }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ formatDateTime(log.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.total > 0"
                    class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
                >
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{
                                pagination.from
                            }}</span>
                            to
                            <span class="font-medium">{{ pagination.to }}</span>
                            of
                            <span class="font-medium">{{
                                pagination.total
                            }}</span>
                            results
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="goToPage(pagination.current_page - 1)"
                                :disabled="pagination.current_page === 1"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <button
                                v-for="page in visiblePages"
                                :key="page"
                                @click="goToPage(page)"
                                :class="[
                                    'rounded-lg px-3 py-1.5 text-sm font-medium',
                                    page === pagination.current_page
                                        ? 'bg-blue-800 text-white'
                                        : 'border border-gray-300 text-gray-700 hover:bg-gray-50',
                                ]"
                            >
                                {{ page }}
                            </button>
                            <button
                                @click="goToPage(pagination.current_page + 1)"
                                :disabled="
                                    pagination.current_page ===
                                    pagination.last_page
                                "
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Delete Modal -->
        <div
            v-if="showBulkDeleteModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                @click="closeBulkDeleteModal"
            ></div>

            <!-- Modal -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div
                    class="relative w-full max-w-md transform rounded-lg bg-white shadow-xl transition-all"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                    >
                        <h3
                            id="modal-title"
                            class="text-lg font-semibold text-gray-900"
                        >
                            Bulk Delete Activity Logs
                        </h3>
                        <button
                            @click="closeBulkDeleteModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-600 mb-4">
                            Delete all activity logs within the specified date
                            range. This action cannot be undone.
                        </p>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Date From
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="bulkDeleteForm.date_from"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    required
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Date To
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="bulkDeleteForm.date_to"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    required
                                />
                            </div>
                        </div>

                        <div
                            v-if="bulkDeleteErrors.length > 0"
                            class="mt-4 rounded-lg bg-red-50 p-3"
                        >
                            <div class="flex">
                                <i
                                    class="fas fa-exclamation-circle text-red-400"
                                ></i>
                                <div class="ml-3">
                                    <h3
                                        class="text-sm font-medium text-red-800"
                                    >
                                        Please fix the following errors:
                                    </h3>
                                    <ul
                                        class="mt-2 text-sm text-red-700 list-disc list-inside"
                                    >
                                        <li
                                            v-for="(
                                                error, index
                                            ) in bulkDeleteErrors"
                                            :key="index"
                                        >
                                            {{ error }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4"
                    >
                        <button
                            @click="closeBulkDeleteModal"
                            type="button"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleBulkDelete"
                            type="button"
                            :disabled="bulkDeleteLoading"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i
                                v-if="bulkDeleteLoading"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            {{
                                bulkDeleteLoading
                                    ? "Deleting..."
                                    : "Delete Logs"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../api";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { showSuccess, showError } from "../plugins/sweetalert";

// State
const loading = ref(false);
const activityLogs = ref([]);
const users = ref([]);
const showBulkDeleteModal = ref(false);
const bulkDeleteLoading = ref(false);
const bulkDeleteErrors = ref([]);
const filters = ref({
    user_id: "",
    activity_type: "",
    date_from: "",
    date_to: "",
});
const bulkDeleteForm = ref({
    date_from: "",
    date_to: "",
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
    from: 0,
    to: 0,
});

// Computed
const hasActiveFilters = computed(() => {
    return (
        filters.value.user_id ||
        filters.value.activity_type ||
        filters.value.date_from ||
        filters.value.date_to
    );
});

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    if (last <= 5) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 3) {
            pages.push(1, 2, 3, 4, 5);
        } else if (current >= last - 2) {
            pages.push(last - 4, last - 3, last - 2, last - 1, last);
        } else {
            pages.push(
                current - 2,
                current - 1,
                current,
                current + 1,
                current + 2,
            );
        }
    }

    return pages;
});

// Methods
const loadActivityLogs = async (page = 1) => {
    loading.value = true;

    try {
        const params = {
            page,
            per_page: pagination.value.per_page,
            ...filters.value,
        };

        // Remove empty filters
        Object.keys(params).forEach((key) => {
            if (params[key] === "" || params[key] === null) {
                delete params[key];
            }
        });

        const response = await api.get("/users/activity-logs", {
            params,
        });

        activityLogs.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (error) {
        console.error("Error loading activity logs:", error);
        showError(
            "Error",
            error.response?.data?.message || "Failed to load activity logs",
        );
    } finally {
        loading.value = false;
    }
};

const loadUsers = async () => {
    try {
        const response = await api.get("/users", {
            params: { per_page: 1000 },
        });
        users.value = response.data.data;
    } catch (error) {
        console.error("Error loading users:", error);
    }
};

const clearFilters = () => {
    filters.value = {
        user_id: "",
        activity_type: "",
        date_from: "",
        date_to: "",
    };
    loadActivityLogs();
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadActivityLogs(page);
    }
};

const formatDateTime = (dateString) => {
    if (!dateString) return "N/A";

    const date = new Date(dateString);
    return date.toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatActivityType = (type) => {
    return type
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};

const getActivityTypeClass = (type) => {
    const typeMap = {
        user_created: "bg-green-100 text-green-800",
        user_updated: "bg-blue-100 text-blue-800",
        user_deactivated: "bg-orange-100 text-orange-800",
        user_activated: "bg-green-100 text-green-800",
        user_deleted: "bg-red-100 text-red-800",
        password_changed: "bg-purple-100 text-purple-800",
        profile_updated: "bg-blue-100 text-blue-800",
        avatar_uploaded: "bg-indigo-100 text-indigo-800",
        logs_bulk_deleted: "bg-red-100 text-red-800",
    };

    return typeMap[type] || "bg-gray-100 text-gray-800";
};

const formatChanges = (changes) => {
    if (!changes) return "N/A";

    try {
        const parsedChanges = JSON.parse(changes);
        return Object.keys(parsedChanges).join(", ");
    } catch {
        return changes;
    }
};

const openBulkDeleteModal = () => {
    showBulkDeleteModal.value = true;
    bulkDeleteErrors.value = [];
    bulkDeleteForm.value = {
        date_from: "",
        date_to: "",
    };
};

const closeBulkDeleteModal = () => {
    showBulkDeleteModal.value = false;
    bulkDeleteErrors.value = [];
};

const validateBulkDeleteForm = () => {
    bulkDeleteErrors.value = [];

    if (!bulkDeleteForm.value.date_from) {
        bulkDeleteErrors.value.push("Date From is required");
    }

    if (!bulkDeleteForm.value.date_to) {
        bulkDeleteErrors.value.push("Date To is required");
    }

    if (
        bulkDeleteForm.value.date_from &&
        bulkDeleteForm.value.date_to &&
        new Date(bulkDeleteForm.value.date_from) >
            new Date(bulkDeleteForm.value.date_to)
    ) {
        bulkDeleteErrors.value.push("Date From must be before Date To");
    }

    return bulkDeleteErrors.value.length === 0;
};

const handleBulkDelete = async () => {
    if (!validateBulkDeleteForm()) {
        return;
    }

    bulkDeleteLoading.value = true;

    try {
        await api.post("/users/activity-logs/bulk-delete", {
            date_from: bulkDeleteForm.value.date_from,
            date_to: bulkDeleteForm.value.date_to,
        });

        showSuccess(
            "Success",
            "Activity logs deleted successfully within the specified date range",
        );
        closeBulkDeleteModal();
        loadActivityLogs(pagination.value.current_page);
    } catch (error) {
        if (error.response?.data?.errors) {
            bulkDeleteErrors.value = Object.values(
                error.response.data.errors,
            ).flat();
        } else {
            showError(
                "Error",
                error.response?.data?.message ||
                    "Failed to delete activity logs",
            );
        }
    } finally {
        bulkDeleteLoading.value = false;
    }
};

// Lifecycle
onMounted(() => {
    loadActivityLogs();
    loadUsers();
});
</script>
