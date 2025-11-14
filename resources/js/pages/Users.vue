<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        User Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage system users, roles, and permissions
                    </p>
                </div>
                <button
                    @click="openAddUserModal"
                    class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-900"
                >
                    <i class="fas fa-plus"></i>
                    Add User
                </button>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Search
                        </label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Name or email..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Role
                        </label>
                        <select
                            v-model="filters.role_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadUsers"
                        >
                            <option value="">All Roles</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>
                        <select
                            v-model="filters.status"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadUsers"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Office Location Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Office Location
                        </label>
                        <select
                            v-model="filters.office_location"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadUsers"
                        >
                            <option value="">All Locations</option>
                            <option
                                v-for="location in officeLocations"
                                :key="location"
                                :value="location"
                            >
                                {{ location }}
                            </option>
                        </select>
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

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Loading State -->
                <div v-if="loading" class="p-8 text-center">
                    <i
                        class="fas fa-spinner fa-spin text-3xl text-blue-800"
                    ></i>
                    <p class="mt-2 text-sm text-gray-600">Loading users...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="users.length === 0" class="p-8 text-center">
                    <i class="fas fa-users text-4xl text-gray-400"></i>
                    <p class="mt-2 text-sm font-medium text-gray-900">
                        No users found
                    </p>
                    <p class="text-sm text-gray-600">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters"
                                : "Get started by adding a user"
                        }}
                    </p>
                </div>

                <!-- Users Table -->
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
                                    Role
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Office
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Last Login
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="user in users"
                                :key="user.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 shrink-0">
                                            <div
                                                v-if="user.avatar_url"
                                                class="h-10 w-10 rounded-full bg-cover bg-center"
                                                :style="{
                                                    backgroundImage: `url(${user.avatar_url})`,
                                                }"
                                            ></div>
                                            <div
                                                v-else
                                                class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-800 text-sm font-medium text-white"
                                            >
                                                {{ user.initials }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ user.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ user.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800"
                                    >
                                        {{ user.role.name }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ user.office_location || "N/A" }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                            user.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{
                                            user.status === "active"
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ formatDate(user.last_login_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <button
                                            @click="viewUser(user)"
                                            class="text-blue-800 hover:text-blue-900"
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button
                                            @click="editUser(user)"
                                            class="text-blue-800 hover:text-blue-900"
                                            title="Edit User"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            v-if="user.status === 'active'"
                                            @click="deactivateUser(user)"
                                            class="text-orange-600 hover:text-orange-700"
                                            title="Deactivate User"
                                        >
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <button
                                            v-else
                                            @click="activateUser(user)"
                                            class="text-green-600 hover:text-green-700"
                                            title="Activate User"
                                        >
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button
                                            @click="deleteUser(user)"
                                            class="text-red-600 hover:text-red-700"
                                            title="Delete User"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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

        <!-- Add User Modal -->
        <AddUserModal
            :is-open="showAddUserModal"
            :roles="roles"
            :office-locations="officeLocations"
            @close="showAddUserModal = false"
            @user-created="handleUserCreated"
        />

        <!-- Edit User Modal -->
        <EditUserModal
            :is-open="showEditUserModal"
            :user="selectedUser"
            :roles="roles"
            :office-locations="officeLocations"
            @close="showEditUserModal = false"
            @user-updated="handleUserUpdated"
        />

        <!-- View User Modal -->
        <ViewUserModal
            :is-open="showViewUserModal"
            :user="selectedUser"
            @close="showViewUserModal = false"
            @edit="handleViewEdit"
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../api";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import AddUserModal from "../components/modals/AddUserModal.vue";
import EditUserModal from "../components/modals/EditUserModal.vue";
import ViewUserModal from "../components/modals/ViewUserModal.vue";
import {
    confirmAction,
    showSuccess,
    showError,
    Toast,
} from "../plugins/sweetalert";

// State
const loading = ref(false);
const users = ref([]);
const roles = ref([]);
const officeLocations = ref([]);
const showAddUserModal = ref(false);
const showEditUserModal = ref(false);
const showViewUserModal = ref(false);
const selectedUser = ref(null);
const filters = ref({
    search: "",
    role_id: "",
    status: "",
    office_location: "",
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
        filters.value.search ||
        filters.value.role_id ||
        filters.value.status ||
        filters.value.office_location
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

// Debounce search
let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadUsers();
    }, 300);
};

// Methods
const loadUsers = async (page = 1) => {
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

        const response = await api.get("/users", { params });

        users.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (error) {
        console.error("Error loading users:", error);
        showError(
            "Error",
            error.response?.data?.message || "Failed to load users",
        );
    } finally {
        loading.value = false;
    }
};

const loadRoles = async () => {
    try {
        const response = await api.get("/users/roles/list");
        roles.value = response.data.data;
    } catch (error) {
        console.error("Error loading roles:", error);
    }
};

const loadOfficeLocations = async () => {
    try {
        const response = await api.get("/users/locations/list");
        officeLocations.value = response.data.data;
    } catch (error) {
        console.error("Error loading office locations:", error);
    }
};

const clearFilters = () => {
    filters.value = {
        search: "",
        role_id: "",
        status: "",
        office_location: "",
    };
    loadUsers();
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadUsers(page);
    }
};

const formatDate = (dateString) => {
    if (!dateString) return "Never";

    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins} min ago`;
    if (diffHours < 24)
        return `${diffHours} hour${diffHours > 1 ? "s" : ""} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? "s" : ""} ago`;

    return date.toLocaleDateString();
};

const openAddUserModal = () => {
    showAddUserModal.value = true;
};

const handleUserCreated = () => {
    showAddUserModal.value = false;
    loadUsers();
};

const viewUser = (user) => {
    selectedUser.value = user;
    showViewUserModal.value = true;
};

const handleViewEdit = (user) => {
    showViewUserModal.value = false;
    selectedUser.value = user;
    showEditUserModal.value = true;
};

const editUser = (user) => {
    selectedUser.value = user;
    showEditUserModal.value = true;
};

const handleUserUpdated = () => {
    showEditUserModal.value = false;
    selectedUser.value = null;
    loadUsers();
};

const deactivateUser = async (user) => {
    const confirmed = await confirmAction(
        "Deactivate User",
        `Are you sure you want to deactivate ${user.name}? They will no longer be able to access the system.`,
        "Deactivate",
    );

    if (!confirmed) return;

    try {
        await api.post(`/users/${user.id}/deactivate`);
        showSuccess("Success", `${user.name} has been deactivated`);
        loadUsers(pagination.value.current_page);
    } catch (error) {
        showError(
            "Error",
            error.response?.data?.message || "Failed to deactivate user",
        );
    }
};

const activateUser = async (user) => {
    const confirmed = await confirmAction(
        "Activate User",
        `Are you sure you want to activate ${user.name}?`,
        "Activate",
    );

    if (!confirmed) return;

    try {
        await api.post(`/users/${user.id}/activate`);
        showSuccess("Success", `${user.name} has been activated`);
        loadUsers(pagination.value.current_page);
    } catch (error) {
        showError(
            "Error",
            error.response?.data?.message || "Failed to activate user",
        );
    }
};

const deleteUser = async (user) => {
    const confirmed = await confirmAction(
        "Delete User",
        `Are you sure you want to delete ${user.name}? This action cannot be undone.`,
        "Delete",
    );

    if (!confirmed) return;

    try {
        await api.delete(`/users/${user.id}`);
        showSuccess("Success", `${user.name} has been deleted`);
        loadUsers(pagination.value.current_page);
    } catch (error) {
        showError(
            "Error",
            error.response?.data?.message || "Failed to delete user",
        );
    }
};

// Lifecycle
onMounted(() => {
    loadUsers();
    loadRoles();
    loadOfficeLocations();
});
</script>
