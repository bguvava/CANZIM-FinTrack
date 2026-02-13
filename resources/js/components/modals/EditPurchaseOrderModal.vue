<template>
    <!-- Edit Purchase Order Modal (Draft Only) -->
    <div
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300"
        @click.self="closeModal"
    >
        <div
            class="w-full max-w-4xl transform rounded-lg bg-white shadow-2xl transition-all duration-300"
        >
            <!-- Modal Header -->
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="rounded-lg bg-blue-100 p-2">
                            <i class="fas fa-edit text-xl text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Edit Purchase Order (Draft)
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ purchaseOrder?.po_number || "N/A" }}
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
            <form @submit.prevent="handleSubmit">
                <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-6">
                    <!-- Vendor and Project Row -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Vendor -->
                        <div>
                            <label
                                for="vendor_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Vendor
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.vendor_id"
                                id="vendor_id"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                required
                            >
                                <option value="">Select Vendor</option>
                                <option
                                    v-for="vendor in vendors"
                                    :key="vendor.id"
                                    :value="vendor.id"
                                >
                                    {{ vendor.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Project -->
                        <div>
                            <label
                                for="project_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Project
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.project_id"
                                id="project_id"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                required
                            >
                                <option value="">Select Project</option>
                                <option
                                    v-for="project in projects"
                                    :key="project.id"
                                    :value="project.id"
                                >
                                    {{ project.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Line Items Section -->
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Line Items
                                <span class="text-red-500">*</span>
                            </label>
                            <button
                                type="button"
                                @click="addLineItem"
                                class="flex items-center space-x-2 rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-green-700"
                            >
                                <i class="fas fa-plus"></i>
                                <span>Add Item</span>
                            </button>
                        </div>

                        <div
                            class="overflow-hidden rounded-lg border border-gray-200"
                        >
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Description
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Quantity
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Unit
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Unit Price ($)
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Total ($)
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="(item, index) in validItems"
                                        :key="index"
                                    >
                                        <td class="px-4 py-3">
                                            <input
                                                v-model="item.description"
                                                type="text"
                                                placeholder="Item description"
                                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                required
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model.number="item.quantity"
                                                type="number"
                                                min="1"
                                                step="1"
                                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                required
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <select
                                                v-model="item.unit"
                                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                required
                                            >
                                                <option value="" disabled>
                                                    Select
                                                </option>
                                                <option value="pieces">
                                                    Pieces
                                                </option>
                                                <option value="sets">
                                                    Sets
                                                </option>
                                                <option value="boxes">
                                                    Boxes
                                                </option>
                                                <option value="packs">
                                                    Packs
                                                </option>
                                                <option value="kg">Kg</option>
                                                <option value="liters">
                                                    Liters
                                                </option>
                                                <option value="meters">
                                                    Meters
                                                </option>
                                                <option value="units">
                                                    Units
                                                </option>
                                                <option value="reams">
                                                    Reams
                                                </option>
                                                <option value="rolls">
                                                    Rolls
                                                </option>
                                                <option value="bottles">
                                                    Bottles
                                                </option>
                                                <option value="cartons">
                                                    Cartons
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model.number="item.unit_price"
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                required
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                ${{
                                                    formatCurrency(
                                                        calculateLineTotal(
                                                            item,
                                                        ),
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                type="button"
                                                @click="removeLineItem(item)"
                                                class="text-red-600 transition-colors hover:text-red-800"
                                                title="Remove Item"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Grand Total -->
                        <div
                            class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-blue-700">
                                    Grand Total:
                                </span>
                                <span class="text-xl font-bold text-blue-900">
                                    ${{ formatCurrency(grandTotal) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            for="notes"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Notes / Additional Information
                        </label>
                        <textarea
                            v-model="form.notes"
                            id="notes"
                            rows="3"
                            placeholder="Enter any additional notes or special instructions (optional)"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        ></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex items-center justify-end space-x-3 border-t border-gray-200 bg-gray-50 px-6 py-4"
                >
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-50"
                        :disabled="submitting"
                    >
                        <i class="fas fa-times mr-1.5"></i>Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex items-center space-x-2 rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="submitting"
                    >
                        <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        <span>{{
                            submitting ? "Updating..." : "Update Draft"
                        }}</span>
                    </button>
                    <button
                        type="button"
                        @click="handleSubmitForApproval"
                        class="flex items-center space-x-2 rounded-lg bg-green-600 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="submitting"
                    >
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit for Approval</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { usePurchaseOrderStore } from "../../stores/purchaseOrderStore";
import Swal from "sweetalert2";
import api from "../../api";

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false,
    },
    purchaseOrder: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "po-updated", "submit-for-approval"]);

const purchaseOrderStore = usePurchaseOrderStore();

const form = ref({
    vendor_id: "",
    project_id: "",
    notes: "",
    items: [],
});

const submitting = ref(false);
const vendors = ref([]);
const projects = ref([]);

const grandTotal = computed(() => {
    if (!form.value.items || !Array.isArray(form.value.items)) {
        return 0;
    }
    return form.value.items.reduce((total, item) => {
        if (!item) return total;
        return total + calculateLineTotal(item);
    }, 0);
});

const validItems = computed(() => {
    if (!form.value.items || !Array.isArray(form.value.items)) {
        return [];
    }
    return form.value.items.filter(
        (item) => item !== null && item !== undefined,
    );
});

const calculateLineTotal = (item) => {
    return (item.quantity || 0) * (item.unit_price || 0);
};

const formatCurrency = (value) => {
    if (!value && value !== 0) return "0.00";
    return parseFloat(value).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const addLineItem = () => {
    form.value.items.push({
        description: "",
        quantity: 1,
        unit: "pieces",
        unit_price: 0,
    });
};

const removeLineItem = (itemToRemove) => {
    const index = form.value.items.indexOf(itemToRemove);
    if (index > -1) {
        form.value.items.splice(index, 1);
        // Ensure at least one empty row remains
        if (validItems.value.length === 0) {
            form.value.items.push({
                description: "",
                quantity: 1,
                unit: "pieces",
                unit_price: 0,
            });
        }
    }
};

const loadPOData = () => {
    if (props.purchaseOrder) {
        form.value = {
            vendor_id: props.purchaseOrder.vendor_id || "",
            project_id: props.purchaseOrder.project_id || "",
            notes: props.purchaseOrder.notes || "",
            items:
                props.purchaseOrder.items?.map((item) => ({
                    id: item.id,
                    description: item.description || "",
                    quantity: item.quantity || 1,
                    unit: item.unit || "pieces",
                    unit_price: item.unit_price || 0,
                })) || [],
        };

        if (form.value.items.length === 0) {
            form.value.items = [
                { description: "", quantity: 1, unit: "pieces", unit_price: 0 },
            ];
        }
    }
};

const resetForm = () => {
    form.value = {
        vendor_id: "",
        project_id: "",
        notes: "",
        items: [
            { description: "", quantity: 1, unit: "pieces", unit_price: 0 },
        ],
    };
    submitting.value = false;
};

const closeModal = () => {
    resetForm();
    emit("close");
};

const loadVendors = async () => {
    try {
        await purchaseOrderStore.fetchVendors();
        vendors.value = purchaseOrderStore.vendors || [];
    } catch (error) {
        console.error("Failed to load vendors:", error);
        vendors.value = [];
    }
};

const loadProjects = async () => {
    try {
        const response = await api.get("/projects", {
            params: { status: "active", per_page: 100 },
        });
        // Handle different response formats
        const responseData = response.data;
        if (Array.isArray(responseData)) {
            projects.value = responseData;
        } else if (responseData?.data) {
            // Check if it's { data: [...] } or { data: { data: [...] } }
            const innerData = responseData.data;
            if (Array.isArray(innerData)) {
                projects.value = innerData;
            } else if (innerData?.data && Array.isArray(innerData.data)) {
                projects.value = innerData.data;
            } else {
                projects.value = [];
            }
        } else {
            projects.value = [];
        }
    } catch (error) {
        console.error("Failed to load projects:", error);
        projects.value = [];
    }
};

const handleSubmit = async () => {
    submitting.value = true;

    try {
        const poData = {
            ...form.value,
            total_amount: grandTotal.value,
        };

        await purchaseOrderStore.updatePurchaseOrder(
            props.purchaseOrder.id,
            poData,
        );

        Swal.fire({
            icon: "success",
            title: "PO Updated",
            text: "The purchase order draft has been updated successfully.",
            timer: 2000,
            showConfirmButton: false,
        });

        emit("po-updated");
        closeModal();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to update purchase order. Please try again.",
        });
    } finally {
        submitting.value = false;
    }
};

const handleSubmitForApproval = async () => {
    // First save the draft, then emit submit-for-approval
    submitting.value = true;
    try {
        const poData = {
            ...form.value,
            total_amount: grandTotal.value,
        };

        await purchaseOrderStore.updatePurchaseOrder(
            props.purchaseOrder.id,
            poData,
        );

        emit("submit-for-approval", props.purchaseOrder.id);
        closeModal();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to save and submit purchase order. Please try again.",
        });
    } finally {
        submitting.value = false;
    }
};

watch(
    () => props.isVisible,
    async (newVal) => {
        if (newVal) {
            // Load data in parallel
            await Promise.all([loadProjects(), loadVendors()]);
            // Load PO data after dropdowns are ready
            loadPOData();
        } else {
            resetForm();
        }
    },
);

watch(
    () => props.purchaseOrder,
    () => {
        if (props.isVisible) {
            loadPOData();
        }
    },
    { deep: true },
);

onMounted(() => {
    // Pre-fetch vendors and projects when component mounts
    loadVendors();
    loadProjects();
});
</script>
