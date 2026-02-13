<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Purchase Orders
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage purchase orders and procurement processes
                    </p>
                </div>
                <button
                    @click="modals.create = true"
                    class="flex items-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                >
                    <i class="fas fa-plus"></i>
                    <span>Create PO</span>
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Draft
                            </p>
                            <p class="mt-2 text-3xl font-bold text-gray-700">
                                {{ purchaseOrderStore.draftPOs?.length || 0 }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-gray-100 p-3">
                            <i
                                class="fas fa-file-alt text-2xl text-gray-600"
                            ></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Pending
                            </p>
                            <p class="mt-2 text-3xl font-bold text-yellow-600">
                                {{ purchaseOrderStore.pendingPOs?.length || 0 }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-yellow-100 p-3">
                            <i
                                class="fas fa-clock text-2xl text-yellow-600"
                            ></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Approved
                            </p>
                            <p class="mt-2 text-3xl font-bold text-blue-600">
                                {{
                                    purchaseOrderStore.approvedPOs?.length || 0
                                }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-blue-100 p-3">
                            <i
                                class="fas fa-check-circle text-2xl text-blue-600"
                            ></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Received
                            </p>
                            <p class="mt-2 text-3xl font-bold text-purple-600">
                                {{
                                    purchaseOrderStore.purchaseOrders?.filter(
                                        (po) =>
                                            po.status?.toLowerCase() ===
                                            "received",
                                    ).length || 0
                                }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-purple-100 p-3">
                            <i
                                class="fas fa-box-check text-2xl text-purple-600"
                            ></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Completed
                            </p>
                            <p class="mt-2 text-3xl font-bold text-green-600">
                                {{
                                    purchaseOrderStore.completedPOs?.length || 0
                                }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-green-100 p-3">
                            <i
                                class="fas fa-check-double text-2xl text-green-600"
                            ></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
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
                                placeholder="Search PO# or vendor..."
                                class="block w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            />
                            <i
                                class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                            ></i>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>
                        <select
                            v-model="filters.status"
                            id="status"
                            class="mt-1 block w-full rounded-lg border border-gray-300 py-2 pl-3 pr-10 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="received">Received</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Project Filter -->
                    <div>
                        <label
                            for="project"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Project
                        </label>
                        <select
                            v-model="filters.project"
                            id="project"
                            class="mt-1 block w-full rounded-lg border border-gray-300 py-2 pl-3 pr-10 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="">All Projects</option>
                            <option
                                v-for="project in uniqueProjects"
                                :key="project.id"
                                :value="project.id"
                            >
                                {{ project.name }}
                            </option>
                        </select>
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

            <!-- Purchase Orders Table -->
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
                        <p class="text-sm text-gray-600">
                            Loading purchase orders...
                        </p>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="
                        !filteredPurchaseOrders ||
                        filteredPurchaseOrders.length === 0
                    "
                    class="py-12 text-center"
                >
                    <i
                        class="fas fa-file-invoice-dollar mb-4 text-6xl text-gray-400"
                    ></i>
                    <h3 class="text-lg font-semibold text-gray-700">
                        No Purchase Orders Found
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{
                            hasActiveFilters
                                ? "No purchase orders match your search criteria."
                                : "Get started by creating your first purchase order."
                        }}
                    </p>
                    <button
                        v-if="!hasActiveFilters"
                        @click="modals.create = true"
                        class="mt-4 inline-flex items-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Create Purchase Order</span>
                    </button>
                </div>

                <!-- Table -->
                <div v-else class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    PO Number
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Vendor
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Project
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Total Amount
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Date
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
                                v-for="po in paginatedPurchaseOrders"
                                :key="po.id"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                >
                                    {{ po.po_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ po.vendor?.name || "N/A" }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ po.project?.name || "N/A" }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                >
                                    ${{ formatCurrency(po.total_amount) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="getStatusClass(po.status)"
                                    >
                                        {{ po.status }}
                                    </span>
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{ formatDate(po.created_at) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                >
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="viewPO(po)"
                                            class="text-blue-600 transition-colors hover:text-blue-800"
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button
                                            v-if="
                                                po.status?.toLowerCase() ===
                                                'draft'
                                            "
                                            @click="editPO(po)"
                                            class="text-blue-600 transition-colors hover:text-blue-800"
                                            title="Edit Draft"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            v-if="
                                                po.status?.toLowerCase() ===
                                                'draft'
                                            "
                                            @click="deletePO(po)"
                                            class="text-red-600 transition-colors hover:text-red-800"
                                            title="Delete Draft"
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
                    v-if="
                        filteredPurchaseOrders &&
                        filteredPurchaseOrders.length > 0
                    "
                    class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-3"
                >
                    <div class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ startIndex + 1 }}</span>
                        to
                        <span class="font-medium">{{
                            Math.min(endIndex, filteredPurchaseOrders.length)
                        }}</span>
                        of
                        <span class="font-medium">{{
                            filteredPurchaseOrders.length
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
        <CreatePurchaseOrderModal
            :isVisible="modals.create"
            @close="modals.create = false"
            @po-created="handlePOCreated"
        />
        <EditPurchaseOrderModal
            :isVisible="modals.edit"
            :purchase-order="selectedPO"
            @close="modals.edit = false"
            @po-updated="handlePOUpdated"
            @submit-for-approval="handleSubmitForApproval"
        />
        <ViewPurchaseOrderModal
            :isVisible="modals.view"
            :purchase-order="selectedPO"
            @close="modals.view = false"
            @edit="handleEditFromView"
            @submit-for-approval="handleSubmitForApproval"
            @approve="handleApprovePO"
            @reject="handleRejectPO"
            @mark-partially-received="handleMarkPartiallyReceived"
            @mark-received="handleMarkReceived"
            @complete="handleCompletePO"
        />
        <MarkReceivedModal
            :isVisible="modals.markReceived"
            :purchase-order="selectedPO"
            :mode="receivingMode"
            @close="modals.markReceived = false"
            @items-received="handleItemsReceived"
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import CreatePurchaseOrderModal from "../components/modals/CreatePurchaseOrderModal.vue";
import EditPurchaseOrderModal from "../components/modals/EditPurchaseOrderModal.vue";
import ViewPurchaseOrderModal from "../components/modals/ViewPurchaseOrderModal.vue";
import MarkReceivedModal from "../components/modals/MarkReceivedModal.vue";
import { usePurchaseOrderStore } from "../stores/purchaseOrderStore";
import Swal from "sweetalert2";

const purchaseOrderStore = usePurchaseOrderStore();

const modals = ref({
    create: false,
    edit: false,
    view: false,
    markReceived: false,
});

const selectedPO = ref(null);
const receivingMode = ref("full"); // 'partial' or 'full'

const filters = ref({
    search: "",
    status: "",
    project: "",
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
    return (
        filters.value.search !== "" ||
        filters.value.status !== "" ||
        filters.value.project !== ""
    );
});

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        project: "",
    };
    currentPage.value = 1;
};

const uniqueProjects = computed(() => {
    const projects = [];
    const projectIds = new Set();

    purchaseOrderStore.purchaseOrders?.forEach((po) => {
        if (po.project && !projectIds.has(po.project.id)) {
            projectIds.add(po.project.id);
            projects.push(po.project);
        }
    });

    return projects;
});

const filteredPurchaseOrders = computed(() => {
    let orders = purchaseOrderStore.purchaseOrders || [];

    // Search filter
    if (filters.value.search) {
        const searchLower = filters.value.search.toLowerCase();
        orders = orders.filter(
            (po) =>
                po.po_number?.toLowerCase().includes(searchLower) ||
                po.vendor?.name?.toLowerCase().includes(searchLower),
        );
    }

    // Status filter
    if (filters.value.status) {
        orders = orders.filter(
            (po) =>
                po.status?.toLowerCase() === filters.value.status.toLowerCase(),
        );
    }

    // Project filter
    if (filters.value.project) {
        orders = orders.filter(
            (po) => po.project_id === parseInt(filters.value.project),
        );
    }

    return orders;
});

const totalPages = computed(() => {
    return Math.ceil(filteredPurchaseOrders.value.length / perPage);
});

const startIndex = computed(() => {
    return (currentPage.value - 1) * perPage;
});

const endIndex = computed(() => {
    return startIndex.value + perPage;
});

const paginatedPurchaseOrders = computed(() => {
    return filteredPurchaseOrders.value.slice(startIndex.value, endIndex.value);
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

const formatCurrency = (value) => {
    if (!value && value !== 0) return "0.00";
    return parseFloat(value).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getStatusClass = (status) => {
    const statusClasses = {
        Draft: "bg-gray-100 text-gray-800",
        Pending: "bg-yellow-100 text-yellow-800",
        Approved: "bg-blue-100 text-blue-800",
        "Partially Received": "bg-orange-100 text-orange-800",
        Received: "bg-purple-100 text-purple-800",
        Completed: "bg-green-100 text-green-800",
        Rejected: "bg-red-100 text-red-800",
        Cancelled: "bg-gray-100 text-gray-800",
    };
    return statusClasses[status] || "bg-gray-100 text-gray-800";
};

const viewPO = async (po) => {
    try {
        await purchaseOrderStore.fetchPurchaseOrder(po.id);
        selectedPO.value = purchaseOrderStore.currentPurchaseOrder;
        modals.value.view = true;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load purchase order details.",
        });
    }
};

const editPO = (po) => {
    selectedPO.value = po;
    modals.value.edit = true;
};

const deletePO = async (po) => {
    // Only allow deletion of draft POs
    if (po.status?.toLowerCase() !== "draft") {
        Swal.fire({
            icon: "error",
            title: "Cannot Delete",
            text: "Only draft purchase orders can be deleted.",
        });
        return;
    }

    const result = await Swal.fire({
        icon: "warning",
        title: "Delete Purchase Order",
        text: `Are you sure you want to delete PO ${po.po_number}? This action cannot be undone.`,
        showCancelButton: true,
        confirmButtonColor: "#DC2626",
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        try {
            await purchaseOrderStore.deletePurchaseOrder(po.id);
            Swal.fire({
                icon: "success",
                title: "Deleted",
                text: "Purchase order has been deleted.",
                timer: 2000,
                showConfirmButton: false,
            });
            purchaseOrderStore.fetchPurchaseOrders();
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error.response?.data?.message ||
                    "Failed to delete purchase order.",
            });
        }
    }
};

const handleEditFromView = (po) => {
    modals.value.view = false;
    setTimeout(() => {
        selectedPO.value = po;
        modals.value.edit = true;
    }, 300);
};

const handlePOCreated = () => {
    purchaseOrderStore.fetchPurchaseOrders();
};

const handlePOUpdated = () => {
    purchaseOrderStore.fetchPurchaseOrders();
};

const handleSubmitForApproval = async (poId) => {
    try {
        await purchaseOrderStore.submitForApproval(poId);
        Swal.fire({
            icon: "success",
            title: "Submitted",
            text: "Purchase order has been submitted for approval.",
            timer: 2000,
            showConfirmButton: false,
        });
        modals.value.view = false;
        purchaseOrderStore.fetchPurchaseOrders();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to submit for approval.",
        });
    }
};

const handleApprovePO = async (poId) => {
    try {
        await purchaseOrderStore.approvePurchaseOrder(poId);
        Swal.fire({
            icon: "success",
            title: "Approved",
            text: "Purchase order has been approved.",
            timer: 2000,
            showConfirmButton: false,
        });
        modals.value.view = false;
        purchaseOrderStore.fetchPurchaseOrders();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to approve purchase order.",
        });
    }
};

const handleRejectPO = async ({ id, reason }) => {
    try {
        await purchaseOrderStore.rejectPurchaseOrder(id, reason);
        Swal.fire({
            icon: "success",
            title: "Rejected",
            text: "Purchase order has been rejected.",
            timer: 2000,
            showConfirmButton: false,
        });
        modals.value.view = false;
        purchaseOrderStore.fetchPurchaseOrders();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to reject purchase order.",
        });
    }
};

const handleMarkPartiallyReceived = (po) => {
    modals.value.view = false;
    setTimeout(() => {
        selectedPO.value = po;
        receivingMode.value = "partial";
        modals.value.markReceived = true;
    }, 300);
};

const handleMarkReceived = (po) => {
    modals.value.view = false;
    setTimeout(() => {
        selectedPO.value = po;
        receivingMode.value = "full";
        modals.value.markReceived = true;
    }, 300);
};

const handleItemsReceived = async ({ po_id, data }) => {
    try {
        await purchaseOrderStore.markAsReceived(po_id, data);
        purchaseOrderStore.fetchPurchaseOrders();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.response?.data?.message || "Failed to record receipt.",
        });
    }
};

const handleCompletePO = async (poId) => {
    try {
        await purchaseOrderStore.completePurchaseOrder(poId);
        Swal.fire({
            icon: "success",
            title: "Completed",
            text: "Purchase order has been marked as completed.",
            timer: 2000,
            showConfirmButton: false,
        });
        modals.value.view = false;
        purchaseOrderStore.fetchPurchaseOrders();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to complete purchase order.",
        });
    }
};

onMounted(() => {
    purchaseOrderStore.fetchPurchaseOrders();
    purchaseOrderStore.fetchVendors();
});
</script>
