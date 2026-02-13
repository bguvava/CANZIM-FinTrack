<template>
    <!-- View Vendor Modal -->
    <div
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300"
        @click.self="closeModal"
    >
        <div
            class="w-full max-w-3xl transform rounded-lg bg-white shadow-2xl transition-all duration-300"
        >
            <!-- Modal Header -->
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="rounded-lg bg-blue-100 p-2">
                            <i
                                class="fas fa-building text-xl text-blue-600"
                            ></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Vendor Details
                            </h3>
                            <p class="text-sm text-gray-600">
                                View comprehensive vendor information
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-gray-400 transition-colors hover:text-gray-600"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-6">
                <!-- Vendor Information Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h4
                        class="mb-4 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Vendor Information
                    </h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Vendor Name
                            </p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ vendor?.name || "N/A" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Contact Person
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ vendor?.contact_person || "N/A" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Email
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ vendor?.email || "N/A" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Phone
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ vendor?.phone || "N/A" }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-medium text-gray-500">
                                Address
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ vendor?.address || "N/A" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Tax ID / TPIN
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ vendor?.tax_id || "N/A" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Purchase Orders Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h4
                        class="mb-4 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i
                            class="fas fa-file-invoice-dollar mr-2 text-blue-600"
                        ></i>
                        Purchase Orders
                    </h4>

                    <div
                        v-if="
                            vendor?.purchase_orders &&
                            vendor.purchase_orders.length > 0
                        "
                        class="overflow-hidden rounded-lg border border-gray-200"
                    >
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        PO Number
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Project
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Total Amount
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="po in vendor.purchase_orders"
                                    :key="po.id"
                                    class="transition-colors hover:bg-gray-50"
                                >
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900"
                                    >
                                        {{ po.po_number }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-sm text-gray-900"
                                    >
                                        {{ po.project?.project_name || "N/A" }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900"
                                    >
                                        ${{ formatCurrency(po.total_amount) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="getStatusClass(po.status)"
                                        >
                                            {{ po.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-sm text-gray-900"
                                    >
                                        {{ formatDate(po.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="rounded-lg bg-gray-50 p-6 text-center text-sm text-gray-600"
                    >
                        <i class="fas fa-inbox mb-2 text-3xl text-gray-400"></i>
                        <p>No purchase orders found for this vendor.</p>
                    </div>
                </div>

                <!-- Audit Trail Card -->
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h4
                        class="mb-4 flex items-center text-base font-semibold text-gray-900"
                    >
                        <i class="fas fa-history mr-2 text-blue-600"></i>
                        Audit Trail
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Created By
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ vendor?.created_by?.name || "System" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Created At
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDate(vendor?.created_at) }}
                            </p>
                        </div>
                        <div
                            v-if="
                                vendor?.updated_at &&
                                vendor.updated_at !== vendor.created_at
                            "
                        >
                            <p class="text-xs font-medium text-gray-500">
                                Last Updated
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDate(vendor.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="flex items-center justify-end border-t border-gray-200 bg-gray-50 px-6 py-4"
            >
                <button
                    @click="closeModal"
                    class="rounded-lg border border-red-300 px-6 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-50"
                >
                    <i class="fas fa-times mr-1.5"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false,
    },
    vendor: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close"]);

const closeModal = () => {
    emit("close");
};

const formatCurrency = (value) => {
    if (!value) return "0.00";
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
        draft: "bg-gray-100 text-gray-800",
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-blue-100 text-blue-800",
        received: "bg-purple-100 text-purple-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return statusClasses[status?.toLowerCase()] || "bg-gray-100 text-gray-800";
};
</script>
