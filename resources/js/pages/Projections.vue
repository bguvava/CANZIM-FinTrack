<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Cash Flow Projections
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Forecast and analyze future cash flow trends
                </p>
            </div>

            <!-- Configuration Card -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Bank Account Selection -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Select Bank Account
                            <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="selectedBankAccountId"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadProjections"
                        >
                            <option value="">Choose account to project</option>
                            <option
                                v-for="account in cashFlowStore.activeBankAccounts"
                                :key="account.id"
                                :value="account.id"
                            >
                                {{ account.account_name }} -
                                {{ account.bank_name }} (Balance: ${{
                                    formatCurrency(account.current_balance)
                                }})
                            </option>
                        </select>
                    </div>

                    <!-- Projection Period -->
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Projection Period
                        </label>
                        <div class="flex gap-3">
                            <button
                                v-for="period in projectionPeriods"
                                :key="period.value"
                                @click="selectPeriod(period.value)"
                                class="flex-1 rounded-lg border px-4 py-2 text-sm font-medium transition"
                                :class="
                                    selectedPeriod === period.value
                                        ? 'border-blue-800 bg-blue-800 text-white'
                                        : 'border-gray-300 text-gray-700 hover:bg-gray-50'
                                "
                            >
                                {{ period.label }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="loading"
                class="rounded-lg bg-white p-12 shadow text-center"
            >
                <i class="fas fa-spinner fa-spin text-3xl text-blue-800"></i>
                <p class="mt-2 text-sm text-gray-600">
                    Generating projections...
                </p>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="!selectedBankAccountId"
                class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-12 text-center"
            >
                <i class="fas fa-chart-line text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">
                    Select a Bank Account
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Choose a bank account above to view cash flow projections
                </p>
            </div>

            <!-- Projections Content -->
            <template v-else-if="projectionData">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Current Balance
                            </p>
                            <p
                                class="mt-2 text-2xl font-bold"
                                :class="
                                    currentBalance >= 0
                                        ? 'text-gray-900'
                                        : 'text-red-600'
                                "
                            >
                                ${{ formatCurrency(currentBalance) }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Best Case
                            </p>
                            <p class="mt-2 text-2xl font-bold text-green-600">
                                ${{
                                    formatCurrency(
                                        projectionData.best_case_final,
                                    )
                                }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ selectedPeriod }} months
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Likely Case
                            </p>
                            <p class="mt-2 text-2xl font-bold text-blue-600">
                                ${{
                                    formatCurrency(
                                        projectionData.likely_case_final,
                                    )
                                }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ selectedPeriod }} months
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Worst Case
                            </p>
                            <p
                                class="mt-2 text-2xl font-bold"
                                :class="
                                    projectionData.worst_case_final >= 0
                                        ? 'text-yellow-600'
                                        : 'text-red-600'
                                "
                            >
                                ${{
                                    formatCurrency(
                                        projectionData.worst_case_final,
                                    )
                                }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ selectedPeriod }} months
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Projected Cash Flow
                        </h3>
                        <div class="flex gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-3 w-3 rounded-full bg-green-500"
                                ></div>
                                <span class="text-gray-600">Best Case</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-3 w-3 rounded-full bg-blue-500"
                                ></div>
                                <span class="text-gray-600">Likely Case</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-3 w-3 rounded-full bg-yellow-500"
                                ></div>
                                <span class="text-gray-600">Worst Case</span>
                            </div>
                        </div>
                    </div>
                    <div class="relative" style="height: 400px">
                        <canvas ref="chartCanvas"></canvas>
                    </div>
                </div>

                <!-- Assumptions -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Projection Assumptions
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                Average Monthly Inflows
                            </h4>
                            <p class="text-2xl font-bold text-green-600">
                                ${{
                                    formatCurrency(
                                        projectionData.avg_monthly_inflow,
                                    )
                                }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Based on last
                                {{
                                    projectionData.historical_months || 6
                                }}
                                months
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                Average Monthly Outflows
                            </h4>
                            <p class="text-2xl font-bold text-red-600">
                                ${{
                                    formatCurrency(
                                        projectionData.avg_monthly_outflow,
                                    )
                                }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Based on last
                                {{
                                    projectionData.historical_months || 6
                                }}
                                months
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">
                            <i
                                class="fas fa-info-circle text-blue-600 mr-1"
                            ></i>
                            How Projections Work
                        </h4>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>
                                <strong>Best Case:</strong> Historical avg
                                inflows +20%, outflows -10%
                            </li>
                            <li>
                                <strong>Likely Case:</strong> Based on
                                historical averages
                            </li>
                            <li>
                                <strong>Worst Case:</strong> Historical avg
                                inflows -20%, outflows +10%
                            </li>
                        </ul>
                    </div>
                </div>
            </template>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import { useCashFlowStore } from "../stores/cashFlowStore";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

// Register Chart.js components
Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Title,
    Tooltip,
    Legend,
    Filler,
);

const cashFlowStore = useCashFlowStore();

const selectedBankAccountId = ref("");
const selectedPeriod = ref(3);
const loading = ref(false);
const projectionData = ref(null);
const chartCanvas = ref(null);
let chartInstance = null;

const projectionPeriods = [
    { value: 3, label: "3 Months" },
    { value: 6, label: "6 Months" },
    { value: 12, label: "12 Months" },
];

const currentBalance = computed(() => {
    const account = cashFlowStore.activeBankAccounts.find(
        (acc) => acc.id === parseInt(selectedBankAccountId.value),
    );
    return account ? parseFloat(account.current_balance) : 0;
});

const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const selectPeriod = (period) => {
    selectedPeriod.value = period;
    if (selectedBankAccountId.value) {
        loadProjections();
    }
};

const loadProjections = async () => {
    if (!selectedBankAccountId.value) return;

    loading.value = true;
    try {
        const data = await cashFlowStore.fetchProjections(
            selectedBankAccountId.value,
            selectedPeriod.value,
        );
        projectionData.value = data;

        // Wait for next tick to ensure canvas is rendered
        await nextTick();
        renderChart();
    } catch (error) {
        console.error("Failed to load projections:", error);
    } finally {
        loading.value = false;
    }
};

const renderChart = () => {
    if (!chartCanvas.value || !projectionData.value) return;

    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy();
    }

    const ctx = chartCanvas.value.getContext("2d");
    const labels = projectionData.value.months || [];
    const bestCase = projectionData.value.best_case || [];
    const likelyCase = projectionData.value.likely_case || [];
    const worstCase = projectionData.value.worst_case || [];

    chartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Best Case",
                    data: bestCase,
                    borderColor: "rgb(34, 197, 94)",
                    backgroundColor: "rgba(34, 197, 94, 0.1)",
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: "Likely Case",
                    data: likelyCase,
                    borderColor: "rgb(59, 130, 246)",
                    backgroundColor: "rgba(59, 130, 246, 0.1)",
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: "Worst Case",
                    data: worstCase,
                    borderColor: "rgb(234, 179, 8)",
                    backgroundColor: "rgba(234, 179, 8, 0.1)",
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: "index",
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            label += "$" + formatCurrency(context.parsed.y);
                            return label;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function (value) {
                            return "$" + formatCurrency(value);
                        },
                    },
                },
            },
        },
    });
};

// Watch for changes to re-render chart
watch([selectedBankAccountId, selectedPeriod], () => {
    if (selectedBankAccountId.value) {
        loadProjections();
    }
});

onMounted(async () => {
    await cashFlowStore.fetchBankAccounts();
});
</script>
