<template>
    <!-- Create Purchase Order Modal -->
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
                            <i
                                class="fas fa-file-invoice-dollar text-xl text-blue-600"
                            ></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Create Purchase Order
                            </h3>
                            <p class="text-sm text-gray-600">
                                Generate a new purchase order for vendor
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
                    <!-- PO Number (Auto-generated) -->
                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                    >
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-hashtag text-blue-600"></i>
                            <div>
                                <p class="text-xs font-medium text-blue-700">
                                    Purchase Order Number
                                </p>
                                <p class="text-sm font-semibold text-blue-900">
                                    {{
                                        form.po_number ||
                                        "Auto-generated on save"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

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
                                class="mt-1 block w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :class="
                                    errors.vendor_id
                                        ? 'border-red-300 bg-red-50'
                                        : 'border-gray-300'
                                "
                                required
                            >
                                <option value="">Select Vendor</option>
                                <option
                                    v-for="vendor in validVendors"
                                    :key="vendor.id"
                                    :value="vendor.id"
                                >
                                    {{ vendor.name }}
                                </option>
                            </select>
                            <p
                                v-if="errors.vendor_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.vendor_id }}
                            </p>
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
                                class="mt-1 block w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :class="
                                    errors.project_id
                                        ? 'border-red-300 bg-red-50'
                                        : 'border-gray-300'
                                "
                                required
                            >
                                <option value="">Select Project</option>
                                <option
                                    v-for="project in validProjects"
                                    :key="project.id"
                                    :value="project.id"
                                >
                                    {{ project.name }}
                                </option>
                            </select>
                            <p
                                v-if="errors.project_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.project_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Order Date and Expected Delivery Row -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Order Date -->
                        <div>
                            <label
                                for="order_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Order Date
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                v-model="form.order_date"
                                id="order_date"
                                :max="todayDate"
                                class="mt-1 block w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :class="
                                    errors.order_date
                                        ? 'border-red-300 bg-red-50'
                                        : 'border-gray-300'
                                "
                                required
                            />
                            <p
                                v-if="errors.order_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.order_date }}
                            </p>
                        </div>

                        <!-- Expected Delivery Date -->
                        <div>
                            <label
                                for="expected_delivery_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Expected Delivery Date
                            </label>
                            <input
                                type="date"
                                v-model="form.expected_delivery_date"
                                id="expected_delivery_date"
                                :min="form.order_date || todayDate"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :class="
                                    errors.expected_delivery_date
                                        ? 'border-red-300 bg-red-50'
                                        : 'border-gray-300'
                                "
                            />
                            <p
                                v-if="errors.expected_delivery_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.expected_delivery_date }}
                            </p>
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
                                        v-for="(item, index) in form.items"
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
                                                placeholder="0"
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
                                                <option value="units">
                                                    Units
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
                                                <option value="kg">
                                                    Kilograms
                                                </option>
                                                <option value="liters">
                                                    Liters
                                                </option>
                                                <option value="meters">
                                                    Meters
                                                </option>
                                                <option value="hours">
                                                    Hours
                                                </option>
                                                <option value="days">
                                                    Days
                                                </option>
                                                <option value="lots">
                                                    Lots
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model.number="item.unit_price"
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                placeholder="0.00"
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
                                                v-if="form.items.length > 1"
                                                type="button"
                                                @click="removeLineItem(index)"
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
                        type="button"
                        @click="saveDraft"
                        class="flex items-center space-x-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100"
                        :disabled="submitting"
                    >
                        <i class="fas fa-save"></i>
                        <span>Save as Draft</span>
                    </button>
                    <button
                        type="submit"
                        class="flex items-center space-x-2 rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="submitting"
                    >
                        <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-paper-plane"></i>
                        <span>{{
                            submitting ? "Creating..." : "Submit for Approval"
                        }}</span>
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
});

const emit = defineEmits(["close", "po-created"]);

const purchaseOrderStore = usePurchaseOrderStore();

const todayDate = new Date().toISOString().split("T")[0];

const form = ref({
    po_number: "",
    vendor_id: "",
    project_id: "",
    order_date: todayDate,
    expected_delivery_date: "",
    notes: "",
    items: [
        {
            description: "",
            quantity: 1,
            unit: "units",
            unit_price: 0,
        },
    ],
});

const errors = ref({});
const submitting = ref(false);
const vendors = ref([]);
const projects = ref([]);

// Computed properties to filter valid items for dropdowns
const validVendors = computed(() => {
    if (!Array.isArray(vendors.value)) return [];
    return vendors.value.filter(
        (v) => v != null && typeof v === "object" && "id" in v && "name" in v,
    );
});

const validProjects = computed(() => {
    if (!Array.isArray(projects.value)) return [];
    return projects.value.filter(
        (p) => p != null && typeof p === "object" && "id" in p && "name" in p,
    );
});

const grandTotal = computed(() => {
    if (!Array.isArray(form.value.items)) return 0;
    return form.value.items.reduce((total, item) => {
        return total + calculateLineTotal(item);
    }, 0);
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
        unit: "units",
        unit_price: 0,
    });
};

const removeLineItem = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
};

const resetForm = () => {
    form.value = {
        po_number: "",
        vendor_id: "",
        project_id: "",
        order_date: new Date().toISOString().split("T")[0],
        expected_delivery_date: "",
        notes: "",
        items: [
            {
                description: "",
                quantity: 1,
                unit: "units",
                unit_price: 0,
            },
        ],
    };
    errors.value = {};
    submitting.value = false;
};

const closeModal = () => {
    resetForm();
    emit("close");
};

const loadProjects = async () => {
    try {
        const response = await api.get("/projects", {
            params: { status: "active", per_page: 100 },
        });
        // Handle various response formats:
        // 1. Direct array: response.data = [...]
        // 2. Wrapped paginated: response.data = { success, data: { data: [...] } }
        // 3. Direct paginated: response.data = { data: [...] }
        const responseData = response.data;
        if (Array.isArray(responseData)) {
            projects.value = responseData;
        } else if (responseData?.data) {
            // Could be wrapped (success/data pattern) or direct paginator
            const innerData = responseData.data;
            if (Array.isArray(innerData)) {
                projects.value = innerData;
            } else if (innerData?.data && Array.isArray(innerData.data)) {
                // Wrapped paginated response: { success, data: { data: [...] } }
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

const loadVendors = async () => {
    try {
        const response = await api.get("/vendors", {
            params: { per_page: 100 },
        });
        // Handle various response formats:
        // 1. Direct array: response.data = [...]
        // 2. Direct paginated: response.data = { data: [...] }
        const responseData = response.data;
        if (Array.isArray(responseData)) {
            vendors.value = responseData;
        } else if (responseData?.data && Array.isArray(responseData.data)) {
            vendors.value = responseData.data;
        } else {
            vendors.value = [];
        }
    } catch (error) {
        console.error("Failed to load vendors:", error);
        vendors.value = [];
    }
};

const saveDraft = async () => {
    errors.value = {};
    submitting.value = true;

    try {
        const poData = {
            ...form.value,
            total_amount: grandTotal.value,
            status: "draft",
        };

        await purchaseOrderStore.createPurchaseOrder(poData);

        Swal.fire({
            icon: "success",
            title: "Draft Saved",
            text: "The purchase order has been saved as a draft.",
            timer: 2000,
            showConfirmButton: false,
        });

        emit("po-created");
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            Swal.fire({
                icon: "error",
                title: "Validation Error",
                text: "Please check the form for errors.",
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error.response?.data?.message ||
                    "Failed to save purchase order. Please try again.",
            });
        }
    } finally {
        submitting.value = false;
    }
};

const handleSubmit = async () => {
    errors.value = {};
    submitting.value = true;

    try {
        const poData = {
            ...form.value,
            total_amount: grandTotal.value,
            status: "pending",
        };

        await purchaseOrderStore.createPurchaseOrder(poData);

        Swal.fire({
            icon: "success",
            title: "PO Submitted",
            text: "The purchase order has been submitted for approval.",
            timer: 2000,
            showConfirmButton: false,
        });

        emit("po-created");
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            Swal.fire({
                icon: "error",
                title: "Validation Error",
                text: "Please check the form for errors.",
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error.response?.data?.message ||
                    "Failed to create purchase order. Please try again.",
            });
        }
    } finally {
        submitting.value = false;
    }
};

watch(
    () => props.isVisible,
    async (newVal) => {
        if (newVal) {
            // Load vendors and projects directly when modal opens
            await Promise.all([loadVendors(), loadProjects()]);
        } else {
            resetForm();
        }
    },
);

onMounted(async () => {
    // Pre-load vendors if component is mounted while visible
    if (props.isVisible) {
        await Promise.all([loadVendors(), loadProjects()]);
    }
});
</script>
