<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Audit Trail
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Monitor system changes and user activities
                    </p>
                </div>
                <button
                    @click="exportToCSV"
                    :disabled="exporting"
                    class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-700 disabled:opacity-50"
                >
                    <i
                        :class="
                            exporting
                                ? 'fas fa-spinner fa-spin'
                                : 'fas fa-file-csv'
                        "
                    ></i>
                    {{ exporting ? "Exporting..." : "Export to CSV" }}
                </button>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
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
                            @change="loadAuditTrails"
                        >
                            <option value="">All Users</option>
                            <option
                                v-for="user in filterOptions.users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Action
                        </label>
                        <select
                            v-model="filters.action"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadAuditTrails"
                        >
                            <option value="">All Actions</option>
                            <option
                                v-for="action in filterOptions.actions"
                                :key="action"
                                :value="action"
                            >
                                {{ formatAction(action) }}
                            </option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Type
                        </label>
                        <select
                            v-model="filters.auditable_type"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadAuditTrails"
                        >
                            <option value="">All Types</option>
                            <option
                                v-for="type in filterOptions.auditable_types"
                                :key="type"
                                :value="type"
                            >
                                {{ formatType(type) }}
                            </option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Date From
                        </label>
                        <input
                            v-model="filters.start_date"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadAuditTrails"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Date To
                        </label>
                        <input
                            v-model="filters.end_date"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadAuditTrails"
                        />
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="mt-4">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Search
                    </label>
                    <div class="relative">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by description or action..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 pl-10 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @input="debounceSearch"
                        />
                        <i
                            class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                        ></i>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div v-if="hasActiveFilters" class="mt-3 flex justify-end">
                    <button
                        @click="clearFilters"
                        class="text-sm font-medium text-blue-800 hover:text-blue-900"
                    >
                        <i class="fas fa-times-circle mr-1"></i>
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Audit Trail Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Loading State -->
                <div v-if="loading" class="p-8 text-center">
                    <i
                        class="fas fa-spinner fa-spin text-3xl text-blue-800"
                    ></i>
                    <p class="mt-2 text-sm text-gray-600">
                        Loading audit trails...
                    </p>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="auditTrails.length === 0"
                    class="p-8 text-center"
                >
                    <i class="fas fa-clipboard-list text-4xl text-gray-400"></i>
                    <p class="mt-2 text-sm font-medium text-gray-900">
                        No audit trails found
                    </p>
                    <p class="text-sm text-gray-600">
                        Try adjusting your filters to see more results
                    </p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    Date & Time
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    User
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    Action
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    Description
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    IP Address
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="trail in auditTrails"
                                :key="trail.id"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{ formatDateTime(trail.created_at) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{ trail.user?.name || "N/A" }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="
                                            getActionBadgeClass(trail.action)
                                        "
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ formatAction(trail.action) }}
                                    </span>
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                >
                                    {{ formatType(trail.auditable_type) }}
                                </td>
                                <td
                                    class="max-w-xs truncate px-6 py-4 text-sm text-gray-600"
                                    :title="trail.description"
                                >
                                    {{ trail.description }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                >
                                    {{ trail.ip_address }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right text-sm"
                                >
                                    <button
                                        @click="viewDetails(trail)"
                                        class="text-blue-800 hover:text-blue-900"
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.total > pagination.per_page"
                    class="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-3"
                >
                    <div class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ pagination.from }}</span>
                        to
                        <span class="font-medium">{{ pagination.to }}</span>
                        of
                        <span class="font-medium">{{ pagination.total }}</span>
                        results
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page === pagination.last_page
                            "
                            class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import api from "../api.js";
import Swal from "sweetalert2";

const auditTrails = ref([]);
const filterOptions = ref({
    users: [],
    actions: [],
    auditable_types: [],
});
const filters = ref({
    user_id: "",
    action: "",
    auditable_type: "",
    start_date: "",
    end_date: "",
    search: "",
    page: 1,
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 50,
    total: 0,
    from: 0,
    to: 0,
});
const loading = ref(false);
const exporting = ref(false);
let searchTimeout = null;

const hasActiveFilters = computed(() => {
    return (
        filters.value.user_id ||
        filters.value.action ||
        filters.value.auditable_type ||
        filters.value.start_date ||
        filters.value.end_date ||
        filters.value.search
    );
});

const loadAuditTrails = async () => {
    loading.value = true;
    try {
        const params = { ...filters.value };
        Object.keys(params).forEach(
            (key) => params[key] === "" && delete params[key],
        );

        const response = await api.get("/audit-trails", { params });
        auditTrails.value = response.data.data.data;
        pagination.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            per_page: response.data.data.per_page,
            total: response.data.data.total,
            from: response.data.data.from,
            to: response.data.data.to,
        };
    } catch (error) {
        console.error("Error loading audit trails:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to load audit trails. Please try again.",
        });
    } finally {
        loading.value = false;
    }
};

const loadFilterOptions = async () => {
    try {
        const response = await api.get("/audit-trails/filters");
        filterOptions.value = response.data.data;
    } catch (error) {
        console.error("Error loading filter options:", error);
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadAuditTrails();
    }, 300);
};

const clearFilters = () => {
    filters.value = {
        user_id: "",
        action: "",
        auditable_type: "",
        start_date: "",
        end_date: "",
        search: "",
        page: 1,
    };
    loadAuditTrails();
};

const changePage = (page) => {
    filters.value.page = page;
    loadAuditTrails();
};

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatAction = (action) => {
    return action
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};

const formatType = (type) => {
    return type ? type.split("\\").pop() : "N/A";
};

const getActionBadgeClass = (action) => {
    const classes = {
        created: "bg-green-100 text-green-800",
        updated: "bg-blue-100 text-blue-800",
        deleted: "bg-red-100 text-red-800",
        restored: "bg-purple-100 text-purple-800",
    };
    return classes[action] || "bg-gray-100 text-gray-800";
};

const viewDetails = async (trail) => {
    try {
        const response = await api.get(`/api/v1/audit-trails/${trail.id}`);
        const details = response.data.data;

        let html = `
            <div class="text-left space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">User</p>
                    <p class="text-sm text-gray-900">${details.user?.name || "N/A"}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Action</p>
                    <p class="text-sm text-gray-900">${formatAction(details.action)}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Type</p>
                    <p class="text-sm text-gray-900">${formatType(details.auditable_type)}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Description</p>
                    <p class="text-sm text-gray-900">${details.description}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">IP Address</p>
                    <p class="text-sm text-gray-900">${details.ip_address}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">User Agent</p>
                    <p class="text-sm text-gray-900 break-all">${details.user_agent || "N/A"}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">URL</p>
                    <p class="text-sm text-gray-900 break-all">${details.request_url || "N/A"}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Method</p>
                    <p class="text-sm text-gray-900">${details.request_method || "N/A"}</p>
                </div>
        `;

        if (details.old_values && Object.keys(details.old_values).length > 0) {
            html += `
                <div>
                    <p class="text-sm font-medium text-gray-700">Old Values</p>
                    <pre class="mt-1 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(details.old_values, null, 2)}</pre>
                </div>
            `;
        }

        if (details.new_values && Object.keys(details.new_values).length > 0) {
            html += `
                <div>
                    <p class="text-sm font-medium text-gray-700">New Values</p>
                    <pre class="mt-1 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(details.new_values, null, 2)}</pre>
                </div>
            `;
        }

        html += `</div>`;

        Swal.fire({
            title: "Audit Trail Details",
            html,
            width: "600px",
            confirmButtonColor: "#1E40AF",
        });
    } catch (error) {
        console.error("Error loading audit trail details:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to load details. Please try again.",
        });
    }
};

const exportToCSV = async () => {
    exporting.value = true;
    try {
        const params = { ...filters.value };
        Object.keys(params).forEach(
            (key) => params[key] === "" && delete params[key],
        );

        const response = await api.get("/audit-trails/export", {
            params,
        });

        // Decode base64 and download - API returns { data: { filename, content } }
        const csvContent = response.data.data.content;
        const filename =
            response.data.data.filename ||
            `audit-trails-${new Date().toISOString().split("T")[0]}.csv`;

        // Decode base64 content
        const csvData = atob(csvContent);
        const blob = new Blob([csvData], { type: "text/csv;charset=utf-8" });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Audit trails exported successfully!",
            timer: 2000,
            showConfirmButton: false,
        });
    } catch (error) {
        console.error("Error exporting audit trails:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to export audit trails. Please try again.",
        });
    } finally {
        exporting.value = false;
    }
};

onMounted(() => {
    loadAuditTrails();
    loadFilterOptions();
});
</script>
