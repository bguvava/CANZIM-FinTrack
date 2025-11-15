<template>
    <DashboardLayout>
        <div class="po-pending-approval-container space-y-6">
            <!-- Page Header -->
            <div
                class="rounded-lg bg-linear-to-r from-yellow-500 to-orange-500 p-6 text-white shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">
                            Purchase Orders - Pending Approval
                        </h1>
                        <p class="mt-1 text-yellow-100">
                            Review and approve purchase orders submitted for
                            your approval
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-bold">
                            {{ pendingPurchaseOrders.length }}
                        </div>
                        <div class="text-sm text-yellow-100">
                            Pending Approval
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i
                        class="fas fa-spinner fa-spin text-4xl text-blue-600"
                    ></i>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">
                        Loading purchase orders...
                    </p>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="!loading && pendingPurchaseOrders.length === 0"
                class="rounded-lg bg-white p-12 text-center shadow-sm dark:bg-gray-800"
            >
                <i class="fas fa-check-circle mb-4 text-6xl text-green-500"></i>
                <h3
                    class="mb-2 text-xl font-semibold text-gray-900 dark:text-white"
                >
                    All Caught Up!
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    There are no purchase orders pending your approval at this
                    time.
                </p>
            </div>

            <!-- Purchase Orders Table -->
            <div
                v-else
                class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800"
            >
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                >
                                    PO Number
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                >
                                    Vendor
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                >
                                    Project
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                >
                                    Total Amount
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                >
                                    Submitted Date
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"
                        >
                            <tr
                                v-for="po in pendingPurchaseOrders"
                                :key="po.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{ po.po_number }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 dark:text-white"
                                >
                                    <div>
                                        <div class="font-medium">
                                            {{
                                                po.vendor?.vendor_name || "N/A"
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                po.vendor?.contact_person || ""
                                            }}
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 dark:text-white"
                                >
                                    {{ po.project?.project_name || "N/A" }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white"
                                >
                                    ${{ formatCurrency(po.total_amount) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white"
                                >
                                    {{ formatDate(po.submitted_at) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-center text-sm"
                                >
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <button
                                            @click="viewPODetails(po)"
                                            class="rounded bg-blue-600 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-blue-700"
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button
                                            @click="handleQuickApprove(po)"
                                            class="rounded bg-green-600 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-green-700"
                                            title="Approve"
                                        >
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button
                                            @click="handleQuickReject(po)"
                                            class="rounded bg-red-600 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-red-700"
                                            title="Reject"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View Purchase Order Modal -->
            <ViewPurchaseOrderModal
                :is-visible="modals.view"
                :purchase-order="selectedPO"
                @close="modals.view = false"
                @approve="handleApprovePO"
                @reject="handleRejectPO"
            />
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { usePurchaseOrderStore } from "@/stores/purchaseOrderStore";
import ViewPurchaseOrderModal from "@/components/modals/ViewPurchaseOrderModal.vue";
import Swal from "sweetalert2";

const purchaseOrderStore = usePurchaseOrderStore();

const loading = ref(false);
const selectedPO = ref(null);
const modals = ref({
    view: false,
});

// Computed properties
const pendingPurchaseOrders = computed(() => {
    return purchaseOrderStore.purchaseOrders.filter(
        (po) => po.status === "Pending",
    );
});

// Methods
const loadPendingPurchaseOrders = async () => {
    try {
        loading.value = true;
        await purchaseOrderStore.fetchPurchaseOrders();
    } catch (error) {
        console.error("Error loading pending purchase orders:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load pending purchase orders.",
        });
    } finally {
        loading.value = false;
    }
};

const viewPODetails = (po) => {
    selectedPO.value = po;
    modals.value.view = true;
};

const handleQuickApprove = async (po) => {
    const result = await Swal.fire({
        title: "Approve Purchase Order?",
        html: `
            <div class="text-left">
                <p class="mb-2"><strong>PO Number:</strong> ${po.po_number}</p>
                <p class="mb-2"><strong>Vendor:</strong> ${po.vendor?.vendor_name || "N/A"}</p>
                <p class="mb-2"><strong>Project:</strong> ${po.project?.project_name || "N/A"}</p>
                <p class="mb-2"><strong>Amount:</strong> $${formatCurrency(po.total_amount)}</p>
            </div>
        `,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#059669",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, Approve",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        await approvePurchaseOrder(po.id);
    }
};

const handleQuickReject = async (po) => {
    const result = await Swal.fire({
        title: "Reject Purchase Order?",
        html: `
            <div class="text-left mb-4">
                <p class="mb-2"><strong>PO Number:</strong> ${po.po_number}</p>
                <p class="mb-2"><strong>Vendor:</strong> ${po.vendor?.vendor_name || "N/A"}</p>
                <p class="mb-2"><strong>Amount:</strong> $${formatCurrency(po.total_amount)}</p>
            </div>
        `,
        input: "textarea",
        inputLabel: "Rejection Reason",
        inputPlaceholder: "Enter reason for rejection...",
        inputAttributes: {
            "aria-label": "Enter reason for rejection",
        },
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Reject",
        cancelButtonText: "Cancel",
        inputValidator: (value) => {
            if (!value) {
                return "Please provide a reason for rejection";
            }
        },
    });

    if (result.isConfirmed) {
        await rejectPurchaseOrder(po.id, result.value);
    }
};

const handleApprovePO = async (poId) => {
    await approvePurchaseOrder(poId);
    modals.value.view = false;
};

const handleRejectPO = async ({ id, reason }) => {
    await rejectPurchaseOrder(id, reason);
    modals.value.view = false;
};

const approvePurchaseOrder = async (poId) => {
    try {
        await purchaseOrderStore.approvePurchaseOrder(poId);
        Swal.fire({
            icon: "success",
            title: "Approved",
            text: "Purchase order has been approved successfully.",
            timer: 2000,
            showConfirmButton: false,
        });
        await loadPendingPurchaseOrders();
    } catch (error) {
        console.error("Error approving purchase order:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to approve purchase order.",
        });
    }
};

const rejectPurchaseOrder = async (poId, reason) => {
    try {
        await purchaseOrderStore.rejectPurchaseOrder(poId, reason);
        Swal.fire({
            icon: "success",
            title: "Rejected",
            text: "Purchase order has been rejected.",
            timer: 2000,
            showConfirmButton: false,
        });
        await loadPendingPurchaseOrders();
    } catch (error) {
        console.error("Error rejecting purchase order:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to reject purchase order.",
        });
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
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Lifecycle
onMounted(async () => {
    await loadPendingPurchaseOrders();
});
</script>
