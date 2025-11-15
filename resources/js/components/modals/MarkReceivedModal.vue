<template>
    <!-- Mark Received Modal -->
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
                        <div class="rounded-lg bg-purple-100 p-2">
                            <i
                                class="fas fa-box-check text-xl text-purple-600"
                            ></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Mark Items as Received
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
                    <!-- Instructions -->
                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                    >
                        <div class="flex items-start space-x-2">
                            <i
                                class="fas fa-info-circle mt-0.5 text-blue-600"
                            ></i>
                            <div>
                                <p class="text-sm font-medium text-blue-700">
                                    Receipt Instructions
                                </p>
                                <p class="mt-1 text-xs text-blue-600">
                                    Select the items you've received and enter
                                    the quantities. You can process partial
                                    receipts.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900">
                                Items to Receive
                            </h4>
                            <button
                                type="button"
                                @click="selectAll"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                {{
                                    allSelected ? "Deselect All" : "Select All"
                                }}
                            </button>
                        </div>

                        <div
                            class="overflow-hidden rounded-lg border border-gray-200"
                        >
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Receive
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Description
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Ordered
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Qty Received
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="(item, index) in receiptItems"
                                        :key="item.id"
                                        :class="
                                            item.selected ? 'bg-purple-50' : ''
                                        "
                                    >
                                        <td class="px-4 py-3 text-center">
                                            <input
                                                v-model="item.selected"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                            />
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{ item.description }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{ item.quantity }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                v-model.number="
                                                    item.quantity_received
                                                "
                                                type="number"
                                                :min="0"
                                                :max="item.quantity"
                                                :disabled="!item.selected"
                                                class="block w-24 rounded-lg border px-3 py-2 text-sm transition-colors focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 disabled:bg-gray-100"
                                                :class="
                                                    item.selected
                                                        ? 'border-gray-300'
                                                        : 'border-gray-200'
                                                "
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Selected Summary -->
                        <div
                            v-if="selectedCount > 0"
                            class="mt-4 rounded-lg bg-purple-50 p-3 text-sm text-purple-700"
                        >
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ selectedCount }} item{{
                                selectedCount > 1 ? "s" : ""
                            }}
                            selected for receipt
                        </div>
                    </div>

                    <!-- Receipt Date -->
                    <div>
                        <label
                            for="receipt_date"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Receipt Date
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="receiptDate"
                            type="date"
                            id="receipt_date"
                            :max="today"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                            required
                        />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            for="receipt_notes"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Receipt Notes
                        </label>
                        <textarea
                            v-model="receiptNotes"
                            id="receipt_notes"
                            rows="3"
                            placeholder="Enter any notes about the receipt (optional)"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
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
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100"
                        :disabled="submitting"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex items-center space-x-2 rounded-lg bg-purple-600 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="submitting || selectedCount === 0"
                    >
                        <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-check"></i>
                        <span>{{
                            submitting ? "Processing..." : "Confirm Receipt"
                        }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import Swal from "sweetalert2";

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

const emit = defineEmits(["close", "items-received"]);

const receiptItems = ref([]);
const receiptDate = ref("");
const receiptNotes = ref("");
const submitting = ref(false);

const today = computed(() => {
    return new Date().toISOString().split("T")[0];
});

const selectedCount = computed(() => {
    return receiptItems.value.filter((item) => item.selected).length;
});

const allSelected = computed(() => {
    return (
        receiptItems.value.length > 0 &&
        receiptItems.value.every((item) => item.selected)
    );
});

const selectAll = () => {
    const newValue = !allSelected.value;
    receiptItems.value.forEach((item) => {
        item.selected = newValue;
        if (newValue && item.quantity_received === 0) {
            item.quantity_received = item.quantity;
        }
    });
};

const resetForm = () => {
    receiptItems.value = [];
    receiptDate.value = today.value;
    receiptNotes.value = "";
    submitting.value = false;
};

const closeModal = () => {
    resetForm();
    emit("close");
};

const handleSubmit = async () => {
    const selectedItems = receiptItems.value.filter((item) => item.selected);

    // Validate quantities
    const invalidItems = selectedItems.filter(
        (item) =>
            !item.quantity_received ||
            item.quantity_received <= 0 ||
            item.quantity_received > item.quantity,
    );

    if (invalidItems.length > 0) {
        Swal.fire({
            icon: "error",
            title: "Invalid Quantities",
            text: "Please enter valid quantities for all selected items (between 1 and ordered quantity).",
        });
        return;
    }

    submitting.value = true;

    try {
        const receiptData = {
            receipt_date: receiptDate.value,
            receipt_notes: receiptNotes.value,
            items: selectedItems.map((item) => ({
                item_id: item.id,
                quantity_received: item.quantity_received,
            })),
        };

        emit("items-received", {
            po_id: props.purchaseOrder.id,
            data: receiptData,
        });

        Swal.fire({
            icon: "success",
            title: "Items Received",
            text: `Successfully recorded receipt of ${selectedCount.value} item${selectedCount.value > 1 ? "s" : ""}.`,
            timer: 2000,
            showConfirmButton: false,
        });

        closeModal();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to record receipt. Please try again.",
        });
    } finally {
        submitting.value = false;
    }
};

watch(
    () => props.isVisible,
    (newVal) => {
        if (newVal && props.purchaseOrder?.items) {
            receiptItems.value = props.purchaseOrder.items.map((item) => ({
                ...item,
                selected: false,
                quantity_received: item.quantity,
            }));
            receiptDate.value = today.value;
        } else {
            resetForm();
        }
    },
);
</script>
