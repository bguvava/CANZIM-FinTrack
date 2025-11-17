<template>
    <div class="p-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Donors</h1>
                <p class="text-gray-600 mt-1">
                    Manage donor information and funding relationships
                </p>
            </div>
            <button
                v-if="canCreate"
                @click="openAddDonorModal"
                class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-900"
            >
                <i class="fas fa-plus"></i>
                Add Donor
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Total Donors Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Donors</p>
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ statistics.total_donors }}
                        </h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-hands-helping text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Active Donors Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Active Donors</p>
                        <h3 class="text-2xl font-bold text-green-600">
                            {{ statistics.active_donors }}
                        </h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <!-- Total Funding Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Funding</p>
                        <h3 class="text-2xl font-bold text-blue-600">
                            ${{ formatNumber(statistics.total_funding) }}
                        </h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-dollar-sign text-blue-600"></i>
                    </div>
                </div>
            </div>

            <!-- Average Funding Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">
                            Average Funding
                        </p>
                        <h3 class="text-2xl font-bold text-purple-600">
                            ${{ formatNumber(statistics.average_funding) }}
                        </h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Donor Analytics Charts -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                    Funding Analytics
                </h2>
                <button
                    @click="toggleChartsVisibility"
                    class="text-sm text-blue-600 hover:text-blue-800"
                >
                    <i
                        :class="
                            showCharts
                                ? 'fas fa-chevron-up'
                                : 'fas fa-chevron-down'
                        "
                        class="mr-1"
                    ></i>
                    {{ showCharts ? "Hide" : "Show" }} Charts
                </button>
            </div>

            <!-- Charts Component (Collapsible) -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 transform -translate-y-2"
                enter-to-class="opacity-100 transform translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 transform translate-y-0"
                leave-to-class="opacity-0 transform -translate-y-2"
            >
                <DonorCharts v-if="showCharts" ref="chartsRef" />
            </transition>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search Donors
                    </label>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search by name, email, or contact person..."
                            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                        <i
                            class="fas fa-search absolute left-3 top-3 text-gray-400"
                        ></i>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select
                        v-model="statusFilter"
                        @change="handleFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Minimum Funding Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Minimum Funding
                    </label>
                    <input
                        v-model="fundingMinFilter"
                        @input="handleFilter"
                        type="number"
                        min="0"
                        step="1000"
                        placeholder="e.g., 50000"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="mt-4 flex items-center gap-2">
                <span class="text-sm text-gray-600">Active Filters:</span>
                <span
                    v-if="searchQuery"
                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm flex items-center gap-2"
                >
                    Search: "{{ searchQuery }}"
                    <button @click="clearSearch" class="hover:text-blue-600">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
                <span
                    v-if="statusFilter"
                    class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm flex items-center gap-2"
                >
                    Status:
                    {{ statusFilter === "active" ? "Active" : "Inactive" }}
                    <button
                        @click="clearStatusFilter"
                        class="hover:text-green-600"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </span>
                <span
                    v-if="fundingMinFilter"
                    class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm flex items-center gap-2"
                >
                    Min Funding: ${{ formatNumber(fundingMinFilter) }}
                    <button
                        @click="clearFundingFilter"
                        class="hover:text-purple-600"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </span>
                <button
                    @click="clearAllFilters"
                    class="text-sm text-red-600 hover:text-red-700"
                >
                    Clear All
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div
                class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
            ></div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
        >
            <div class="flex items-center gap-2 text-red-800">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ error }}</span>
            </div>
        </div>

        <!-- Donors Table -->
        <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Empty State -->
            <div v-if="donors.length === 0" class="text-center py-12">
                <i class="fas fa-hands-helping text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">
                    No Donors Found
                </h3>
                <p class="text-gray-500 mb-4">
                    {{
                        hasActiveFilters
                            ? "Try adjusting your filters"
                            : "Get started by creating your first donor"
                    }}
                </p>
                <button
                    v-if="canCreate && !hasActiveFilters"
                    @click="openAddDonorModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Create First Donor
                </button>
            </div>

            <!-- Donors Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Donor
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Contact
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Funding
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Projects
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr
                            v-for="donor in donors"
                            :key="donor.id"
                            class="hover:bg-gray-50 transition cursor-pointer"
                            @click="viewDonor(donor)"
                        >
                            <!-- Donor Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="shrink-0">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                        >
                                            <i
                                                class="fas fa-building text-blue-600"
                                            ></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div
                                            class="font-semibold text-gray-900"
                                        >
                                            {{ donor.name }}
                                        </div>
                                        <div
                                            v-if="donor.contact_person"
                                            class="text-sm text-gray-500"
                                        >
                                            Contact: {{ donor.contact_person }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div v-if="donor.email">
                                    <i
                                        class="fas fa-envelope text-gray-400 mr-1"
                                    ></i>
                                    {{ donor.email }}
                                </div>
                                <div v-if="donor.phone" class="mt-1">
                                    <i
                                        class="fas fa-phone text-gray-400 mr-1"
                                    ></i>
                                    {{ donor.phone }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span
                                    :class="getStatusClass(donor.status)"
                                    class="px-3 py-1 rounded-full text-xs font-medium"
                                >
                                    {{
                                        donor.status === "active"
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </td>

                            <!-- Funding -->
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div class="font-semibold text-gray-900">
                                        ${{ formatNumber(donor.total_funding) }}
                                    </div>
                                    <div
                                        v-if="donor.restricted_funding > 0"
                                        class="text-xs text-gray-500"
                                    >
                                        ${{
                                            formatNumber(
                                                donor.restricted_funding,
                                            )
                                        }}
                                        restricted
                                    </div>
                                    <div
                                        v-if="donor.in_kind_total > 0"
                                        class="text-xs text-purple-600"
                                    >
                                        ${{ formatNumber(donor.in_kind_total) }}
                                        in-kind
                                    </div>
                                </div>
                            </td>

                            <!-- Projects -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ donor.active_projects_count }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        active
                                    </span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right" @click.stop>
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click="viewDonor(donor)"
                                        class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded transition"
                                        title="View Donor"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button
                                        v-if="canUpdate"
                                        @click="editDonor(donor)"
                                        class="px-3 py-1 text-green-600 hover:bg-green-50 rounded transition"
                                        title="Edit Donor"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        v-if="canUpdate"
                                        @click="toggleDonorStatus(donor)"
                                        class="px-3 py-1 hover:bg-gray-50 rounded transition"
                                        :class="
                                            donor.status === 'active'
                                                ? 'text-orange-600'
                                                : 'text-green-600'
                                        "
                                        :title="
                                            donor.status === 'active'
                                                ? 'Deactivate'
                                                : 'Activate'
                                        "
                                    >
                                        <i
                                            :class="
                                                donor.status === 'active'
                                                    ? 'fas fa-ban'
                                                    : 'fas fa-check-circle'
                                            "
                                        ></i>
                                    </button>
                                    <button
                                        v-if="canDelete"
                                        @click="deleteDonor(donor)"
                                        class="px-3 py-1 text-red-600 hover:bg-red-50 rounded transition"
                                        title="Delete Donor"
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
                v-if="donors.length > 0"
                class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between"
            >
                <div class="text-sm text-gray-600">
                    Showing
                    {{
                        (pagination.current_page - 1) * pagination.per_page + 1
                    }}
                    to
                    {{
                        Math.min(
                            pagination.current_page * pagination.per_page,
                            pagination.total,
                        )
                    }}
                    of {{ pagination.total }} donors
                </div>
                <div class="flex gap-2">
                    <button
                        @click="goToPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="px-3 py-1 text-gray-700">
                        Page {{ pagination.current_page }} of
                        {{ pagination.last_page }}
                    </span>
                    <button
                        @click="goToPage(pagination.current_page + 1)"
                        :disabled="
                            pagination.current_page === pagination.last_page
                        "
                        class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modals (to be created separately) -->
        <AddDonorModal
            v-if="showAddModal"
            @close="closeAddModal"
            @donor-created="handleDonorCreated"
        />
        <EditDonorModal
            v-if="showEditModal"
            :donor="selectedDonor"
            @close="closeEditModal"
            @donor-updated="handleDonorUpdated"
        />
        <ViewDonorModal
            v-if="showViewModal"
            :donor="selectedDonor"
            @close="closeViewModal"
            @edit="editDonor"
            @assign-project="openAssignProjectModal"
            @add-inkind="openAddInKindModal"
            @log-communication="openLogCommunicationModal"
        />
        <AssignProjectModal
            v-if="showAssignProjectModal"
            :donor="selectedDonor"
            @close="closeAssignProjectModal"
            @assigned="handleProjectAssigned"
        />
        <AddInKindContributionModal
            v-if="showAddInKindModal"
            :donor="selectedDonor"
            @close="closeAddInKindModal"
            @recorded="handleInKindRecorded"
        />
        <LogCommunicationModal
            v-if="showLogCommunicationModal"
            :donor="selectedDonor"
            @close="closeLogCommunicationModal"
            @logged="handleCommunicationLogged"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useDonorStore } from "../../stores/donorStore";
import { useAuthStore } from "../../stores/authStore";
import Swal from "sweetalert2";
import AddDonorModal from "./Modals/AddDonorModal.vue";
import EditDonorModal from "./Modals/EditDonorModal.vue";
import ViewDonorModal from "./Modals/ViewDonorModal.vue";
import AssignProjectModal from "./Modals/AssignProjectModal.vue";
import AddInKindContributionModal from "./Modals/AddInKindContributionModal.vue";
import LogCommunicationModal from "./Modals/LogCommunicationModal.vue";
import DonorCharts from "../../components/donors/DonorCharts.vue";

const donorStore = useDonorStore();
const authStore = useAuthStore();

// State
const searchQuery = ref("");
const statusFilter = ref("");
const fundingMinFilter = ref("");
const showAddModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showAssignProjectModal = ref(false);
const showAddInKindModal = ref(false);
const showLogCommunicationModal = ref(false);
const selectedDonor = ref(null);
const showCharts = ref(true); // Charts visible by default
const chartsRef = ref(null); // Reference to charts component
let searchTimeout = null;

// Computed
const donors = computed(() => donorStore.donors);
const loading = computed(() => donorStore.loading);
const error = computed(() => donorStore.error);
const pagination = computed(() => donorStore.pagination);
const statistics = computed(() => donorStore.statistics);

const hasActiveFilters = computed(
    () =>
        searchQuery.value !== "" ||
        statusFilter.value !== "" ||
        fundingMinFilter.value !== "",
);

// Permissions
const canCreate = computed(() => authStore.hasPermission("create-donors"));
const canUpdate = computed(() => authStore.hasPermission("update-donors"));
const canDelete = computed(() => authStore.hasPermission("delete-donors"));

// Methods
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        donorStore.setFilters({ search: searchQuery.value });
        donorStore.fetchDonors(1);
    }, 300);
};

const handleFilter = () => {
    donorStore.setFilters({
        status: statusFilter.value,
        funding_min: fundingMinFilter.value,
    });
    donorStore.fetchDonors(1);
};

const clearSearch = () => {
    searchQuery.value = "";
    handleSearch();
};

const clearStatusFilter = () => {
    statusFilter.value = "";
    handleFilter();
};

const clearFundingFilter = () => {
    fundingMinFilter.value = "";
    handleFilter();
};

const clearAllFilters = () => {
    searchQuery.value = "";
    statusFilter.value = "";
    fundingMinFilter.value = "";
    donorStore.clearFilters();
    donorStore.fetchDonors(1);
};

const toggleChartsVisibility = () => {
    showCharts.value = !showCharts.value;
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        donorStore.fetchDonors(page);
    }
};

const getStatusClass = (status) => {
    return status === "active"
        ? "bg-green-100 text-green-800"
        : "bg-gray-100 text-gray-800";
};

const formatNumber = (value) => {
    if (!value) return "0";
    return parseFloat(value).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

// Modal Methods
const openAddDonorModal = () => {
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const handleDonorCreated = (donor) => {
    closeAddModal();
    Swal.fire({
        icon: "success",
        title: "Success!",
        text: "Donor created successfully",
        timer: 2000,
        showConfirmButton: false,
    });
    donorStore.fetchDonors(1);
};

const editDonor = (donor) => {
    selectedDonor.value = donor;
    showViewModal.value = false;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedDonor.value = null;
};

const handleDonorUpdated = (donor) => {
    closeEditModal();
    Swal.fire({
        icon: "success",
        title: "Success!",
        text: "Donor updated successfully",
        timer: 2000,
        showConfirmButton: false,
    });
    donorStore.fetchDonors(pagination.value.current_page);
};

const viewDonor = async (donor) => {
    selectedDonor.value = donor;
    await donorStore.fetchDonor(donor.id);
    selectedDonor.value = donorStore.currentDonor;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedDonor.value = null;
};

const openAssignProjectModal = (donor) => {
    selectedDonor.value = donor;
    showViewModal.value = false;
    showAssignProjectModal.value = true;
};

const closeAssignProjectModal = () => {
    showAssignProjectModal.value = false;
    selectedDonor.value = null;
};

const handleProjectAssigned = async () => {
    closeAssignProjectModal();
    Swal.fire({
        icon: "success",
        title: "Success!",
        text: "Donor assigned to project successfully",
        timer: 2000,
        showConfirmButton: false,
    });
    await donorStore.fetchDonors(pagination.value.current_page);
};

const openAddInKindModal = (donor) => {
    selectedDonor.value = donor;
    showViewModal.value = false;
    showAddInKindModal.value = true;
};

const closeAddInKindModal = () => {
    showAddInKindModal.value = false;
    selectedDonor.value = null;
};

const handleInKindRecorded = async () => {
    closeAddInKindModal();
    Swal.fire({
        icon: "success",
        title: "Success!",
        text: "In-kind contribution recorded successfully",
        timer: 2000,
        showConfirmButton: false,
    });
    await donorStore.fetchDonors(pagination.value.current_page);
};

const openLogCommunicationModal = (donor) => {
    selectedDonor.value = donor;
    showViewModal.value = false;
    showLogCommunicationModal.value = true;
};

const closeLogCommunicationModal = () => {
    showLogCommunicationModal.value = false;
    selectedDonor.value = null;
};

const handleCommunicationLogged = async () => {
    closeLogCommunicationModal();
    Swal.fire({
        icon: "success",
        title: "Success!",
        text: "Communication logged successfully",
        timer: 2000,
        showConfirmButton: false,
    });
    await donorStore.fetchDonors(pagination.value.current_page);
};

const toggleDonorStatus = async (donor) => {
    const action = donor.status === "active" ? "deactivate" : "activate";
    const result = await Swal.fire({
        title: `${action === "activate" ? "Activate" : "Deactivate"} Donor?`,
        text: `Are you sure you want to ${action} ${donor.name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#1E40AF",
        cancelButtonColor: "#6B7280",
        confirmButtonText: `Yes, ${action}!`,
    });

    if (result.isConfirmed) {
        try {
            await donorStore.toggleStatus(donor.id);
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: `Donor ${action}d successfully`,
                timer: 2000,
                showConfirmButton: false,
            });
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.message || `Failed to ${action} donor`,
            });
        }
    }
};

const deleteDonor = async (donor) => {
    const result = await Swal.fire({
        title: "Delete Donor?",
        text: `Are you sure you want to delete ${donor.name}? This action cannot be undone.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DC2626",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, delete it!",
    });

    if (result.isConfirmed) {
        try {
            await donorStore.deleteDonor(donor.id);
            Swal.fire({
                icon: "success",
                title: "Deleted!",
                text: "Donor has been deleted successfully",
                timer: 2000,
                showConfirmButton: false,
            });
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error.message ||
                    "Failed to delete donor. They may have active projects.",
            });
        }
    }
};

// Lifecycle
onMounted(() => {
    donorStore.fetchDonors(1);
    donorStore.fetchStatistics();
});
</script>
