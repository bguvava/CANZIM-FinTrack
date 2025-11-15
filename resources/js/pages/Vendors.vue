<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Vendors</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage vendor information and procurement relationships
                    </p>
                </div>
                <button
                    @click="modals.add = true"
                    class="flex items-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                >
                    <i class="fas fa-plus"></i>
                    <span>Add Vendor</span>
                </button>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Search -->
                    <div>
                        <label
                            for="search"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Search
                        </label>
                        <div class="relative mt-1">
                            <input
                                v-model="filters.search"
                                type="text"
                                id="search"
                                placeholder="Search by name or email..."
                                class="block w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                            <i
                                class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <div class="flex items-end">
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="flex items-center space-x-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100"
                        >
                            <i class="fas fa-times"></i>
                            <span>Clear Filters</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vendors Table Card -->
            <div class="rounded-lg border border-gray-200 bg-white">
                <!-- Loading State -->
                <div
                    v-if="purchaseOrderStore.loading"
                    class="flex items-center justify-center py-12"
                >
                    <div class="text-center">
                        <i
                            class="fas fa-spinner fa-spin mb-4 text-4xl text-blue-600"
                        ></i>
                        <p class="text-sm text-gray-600">Loading vendors...</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="!filteredVendors || filteredVendors.length === 0"
                    class="py-12 text-center"
                >
                    <i class="fas fa-building mb-4 text-6xl text-gray-400"></i>
                    <h3 class="text-lg font-semibold text-gray-700">
                        No Vendors Found
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{
                            hasActiveFilters
                                ? "No vendors match your search criteria."
                                : "Get started by adding your first vendor."
                        }}
                    </p>
                    <button
                        v-if="!hasActiveFilters"
                        @click="modals.add = true"
                        class="mt-4 inline-flex items-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Add Vendor</span>
                    </button>
                </div>

                <!-- Vendors Table -->
                <div v-else class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Vendor Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Contact Person
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Email
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Phone
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="vendor in paginatedVendors"
                                :key="vendor.id"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                >
                                    {{ vendor.vendor_name }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{ vendor.contact_person }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{ vendor.email }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{ vendor.phone }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                >
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="viewVendor(vendor)"
                                            class="text-blue-600 transition-colors hover:text-blue-800"
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button
                                            @click="editVendor(vendor)"
                                            class="text-blue-600 transition-colors hover:text-blue-800"
                                            title="Edit Vendor"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            @click="deleteVendor(vendor)"
                                            class="text-red-600 transition-colors hover:text-red-800"
                                            title="Delete Vendor"
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
                    v-if="filteredVendors && filteredVendors.length > 0"
                    class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-3"
                >
                    <div class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ startIndex + 1 }}</span>
                        to
                        <span class="font-medium">{{
                            Math.min(endIndex, filteredVendors.length)
                        }}</span>
                        of
                        <span class="font-medium">{{
                            filteredVendors.length
                        }}</span>
                        results
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="previousPage"
                            :disabled="currentPage === 1"
                            class="rounded-lg border border-gray-300 px-3 py-1 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="currentPage >= totalPages"
                            class="rounded-lg border border-gray-300 px-3 py-1 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <AddVendorModal
            :isVisible="modals.add"
            @close="modals.add = false"
            @vendor-added="handleVendorAdded"
        />
        <EditVendorModal
            :isVisible="modals.edit"
            :vendor="selectedVendor"
            @close="modals.edit = false"
            @vendor-updated="handleVendorUpdated"
        />
        <ViewVendorModal
            :isVisible="modals.view"
            :vendor="selectedVendor"
            @close="modals.view = false"
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import AddVendorModal from "../components/modals/AddVendorModal.vue";
import EditVendorModal from "../components/modals/EditVendorModal.vue";
import ViewVendorModal from "../components/modals/ViewVendorModal.vue";
import { usePurchaseOrderStore } from "../stores/purchaseOrderStore";
import Swal from "sweetalert2";

const purchaseOrderStore = usePurchaseOrderStore();

const modals = ref({
    add: false,
    edit: false,
    view: false,
});

const selectedVendor = ref(null);

const filters = ref({
    search: "",
});

const currentPage = ref(1);
const perPage = 10;

// Debounced search
let searchTimeout = null;
watch(
    () => filters.value.search,
    () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage.value = 1;
        }, 300);
    },
);

const hasActiveFilters = computed(() => {
    return filters.value.search !== "";
});

const clearFilters = () => {
    filters.value = {
        search: "",
    };
    currentPage.value = 1;
};

const filteredVendors = computed(() => {
    let vendors = purchaseOrderStore.vendors || [];

    // Search filter
    if (filters.value.search) {
        const searchLower = filters.value.search.toLowerCase();
        vendors = vendors.filter(
            (vendor) =>
                vendor.vendor_name?.toLowerCase().includes(searchLower) ||
                vendor.email?.toLowerCase().includes(searchLower) ||
                vendor.contact_person?.toLowerCase().includes(searchLower),
        );
    }

    return vendors;
});

const totalPages = computed(() => {
    return Math.ceil(filteredVendors.value.length / perPage);
});

const startIndex = computed(() => {
    return (currentPage.value - 1) * perPage;
});

const endIndex = computed(() => {
    return startIndex.value + perPage;
});

const paginatedVendors = computed(() => {
    return filteredVendors.value.slice(startIndex.value, endIndex.value);
});

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const viewVendor = async (vendor) => {
    try {
        await purchaseOrderStore.fetchVendor(vendor.id);
        selectedVendor.value = purchaseOrderStore.currentVendor;
        modals.value.view = true;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load vendor details.",
        });
    }
};

const editVendor = (vendor) => {
    selectedVendor.value = vendor;
    modals.value.edit = true;
};

const deleteVendor = async (vendor) => {
    const result = await Swal.fire({
        title: "Delete Vendor?",
        text: `Are you sure you want to delete "${vendor.vendor_name}"? This action cannot be undone.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, Delete",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        try {
            await purchaseOrderStore.deleteVendor(vendor.id);
            Swal.fire({
                icon: "success",
                title: "Vendor Deleted",
                text: "The vendor has been successfully deleted.",
                timer: 2000,
                showConfirmButton: false,
            });
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error.response?.data?.message ||
                    "Failed to delete vendor. Please try again.",
            });
        }
    }
};

const handleVendorAdded = () => {
    purchaseOrderStore.fetchVendors();
};

const handleVendorUpdated = () => {
    purchaseOrderStore.fetchVendors();
};

onMounted(() => {
    purchaseOrderStore.fetchVendors();
});
</script>
