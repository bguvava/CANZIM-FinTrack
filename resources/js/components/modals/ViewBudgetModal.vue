<template>
    <div v-if="isOpen && budget" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
            @click="closeModal"
        ></div>

        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div
                class="relative w-full max-w-4xl transform rounded-lg bg-white shadow-xl transition-all"
                @click.stop
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3 class="text-lg font-semibold text-gray-900">
                        Budget Details
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="px-6 py-6">
                    <div class="max-h-[70vh] overflow-y-auto space-y-6">
                        <!-- Project and Donor Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 mb-1"
                                >
                                    Project
                                </label>
                                <p class="text-gray-900 font-medium">
                                    {{ budget.project?.name || "N/A" }}
                                </p>
                            </div>

                            <!-- Donor -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 mb-1"
                                >
                                    Donor
                                </label>
                                <p class="text-gray-900 font-medium">
                                    {{ budget.donor?.name || "N/A" }}
                                </p>
                            </div>
                        </div>

                        <!-- Fiscal Year and Quarter -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fiscal Year -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 mb-1"
                                >
                                    Fiscal Year
                                </label>
                                <p class="text-gray-900 font-medium">
                                    {{ budget.fiscal_year }}
                                </p>
                            </div>

                            <!-- Quarter -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 mb-1"
                                >
                                    Quarter
                                </label>
                                <p class="text-gray-900 font-medium">
                                    {{ budget.quarter || "Full Year" }}
                                </p>
                            </div>
                        </div>

                        <!-- Budget Summary -->
                        <div
                            class="p-4 bg-linear-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg"
                        >
                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center"
                            >
                                <!-- Total Allocated -->
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-600 mb-1"
                                    >
                                        Total Allocated
                                    </p>
                                    <p class="text-2xl font-bold text-blue-800">
                                        ${{ totalAllocated.toLocaleString() }}
                                    </p>
                                </div>

                                <!-- Total Spent -->
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-600 mb-1"
                                    >
                                        Total Spent
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-orange-600"
                                    >
                                        ${{ totalSpent.toLocaleString() }}
                                    </p>
                                </div>

                                <!-- Remaining -->
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-600 mb-1"
                                    >
                                        Remaining
                                    </p>
                                    <p
                                        class="text-2xl font-bold"
                                        :class="
                                            remaining >= 0
                                                ? 'text-green-600'
                                                : 'text-red-600'
                                        "
                                    >
                                        ${{ remaining.toLocaleString() }}
                                    </p>
                                </div>
                            </div>

                            <!-- Utilization Bar -->
                            <div class="mt-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600"
                                        >Budget Utilization</span
                                    >
                                    <span class="font-medium text-gray-900"
                                        >{{ utilizationPercentage }}%</span
                                    >
                                </div>
                                <div
                                    class="w-full bg-gray-200 rounded-full h-3"
                                >
                                    <div
                                        class="h-3 rounded-full transition-all"
                                        :class="getUtilizationColor()"
                                        :style="{
                                            width: `${Math.min(utilizationPercentage, 100)}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Budget Line Items -->
                        <div>
                            <h4
                                class="text-md font-semibold text-gray-900 mb-3"
                            >
                                Budget Line Items
                            </h4>
                            <div
                                class="overflow-x-auto border border-gray-200 rounded-lg"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Category
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Allocated
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Spent
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            >
                                                Remaining
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200"
                                    >
                                        <tr
                                            v-for="(
                                                item, index
                                            ) in budget.line_items"
                                            :key="index"
                                        >
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                            >
                                                {{ item.category }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900"
                                            >
                                                ${{
                                                    parseFloat(
                                                        item.allocated_amount ||
                                                            0,
                                                    ).toLocaleString()
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right text-orange-600"
                                            >
                                                ${{
                                                    parseFloat(
                                                        item.spent_amount || 0,
                                                    ).toLocaleString()
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium"
                                                :class="
                                                    getLineItemRemainingClass(
                                                        item,
                                                    )
                                                "
                                            >
                                                ${{
                                                    (
                                                        parseFloat(
                                                            item.allocated_amount ||
                                                                0,
                                                        ) -
                                                        parseFloat(
                                                            item.spent_amount ||
                                                                0,
                                                        )
                                                    ).toLocaleString()
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="budget.notes">
                            <h4
                                class="text-md font-semibold text-gray-900 mb-2"
                            >
                                Notes
                            </h4>
                            <p class="text-gray-600 whitespace-pre-line">
                                {{ budget.notes }}
                            </p>
                        </div>

                        <!-- Metadata -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200"
                        >
                            <div>
                                <p class="text-sm text-gray-500">Created</p>
                                <p class="text-sm text-gray-900">
                                    {{ formatDate(budget.created_at) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">
                                    Last Updated
                                </p>
                                <p class="text-sm text-gray-900">
                                    {{ formatDate(budget.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-end gap-3 border-t border-gray-200 pt-4 mt-6"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Close
                        </button>
                        <button
                            type="button"
                            @click="handleEdit"
                            class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition"
                        >
                            <i class="fas fa-edit mr-2"></i>
                            Edit Budget
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    budget: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "edit"]);

const totalAllocated = computed(() => {
    if (!props.budget?.line_items) return 0;
    return props.budget.line_items.reduce(
        (sum, item) => sum + (parseFloat(item.allocated_amount) || 0),
        0,
    );
});

const totalSpent = computed(() => {
    if (!props.budget?.line_items) return 0;
    return props.budget.line_items.reduce(
        (sum, item) => sum + (parseFloat(item.spent_amount) || 0),
        0,
    );
});

const remaining = computed(() => {
    return totalAllocated.value - totalSpent.value;
});

const utilizationPercentage = computed(() => {
    if (totalAllocated.value === 0) return 0;
    return Math.round((totalSpent.value / totalAllocated.value) * 100);
});

const getUtilizationColor = () => {
    const percentage = utilizationPercentage.value;
    if (percentage < 50) return "bg-green-500";
    if (percentage < 80) return "bg-yellow-500";
    if (percentage < 100) return "bg-orange-500";
    return "bg-red-500";
};

const getLineItemRemainingClass = (item) => {
    const remaining =
        parseFloat(item.allocated_amount || 0) -
        parseFloat(item.spent_amount || 0);
    return remaining >= 0 ? "text-green-600" : "text-red-600";
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const closeModal = () => {
    emit("close");
};

const handleEdit = () => {
    emit("edit", props.budget);
};
</script>
