<template>
    <div class="donor-charts-container">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-center">
                <i
                    class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-3"
                ></i>
                <p class="text-gray-600">Loading chart data...</p>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"
        >
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ error }}
        </div>

        <!-- Charts Grid -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Funding Distribution Pie Chart -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                        Funding Distribution
                    </h3>
                    <span
                        class="text-sm text-gray-500"
                        title="Restricted vs Unrestricted Funding"
                    >
                        <i class="fas fa-info-circle"></i>
                    </span>
                </div>
                <PieChart
                    v-if="hasChartData('funding_distribution')"
                    chartId="funding-distribution-chart"
                    :data="chartData.funding_distribution"
                />
                <EmptyState
                    v-else
                    icon="chart-pie"
                    message="No funding distribution data available"
                />
            </div>

            <!-- Top Donors Bar Chart -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                        Top 10 Donors
                    </h3>
                    <span
                        class="text-sm text-gray-500"
                        title="Top donors by total funding amount"
                    >
                        <i class="fas fa-info-circle"></i>
                    </span>
                </div>
                <BarChart
                    v-if="hasChartData('top_donors')"
                    chartId="top-donors-chart"
                    :data="chartData.top_donors"
                    :options="barChartOptions"
                />
                <EmptyState
                    v-else
                    icon="chart-bar"
                    message="No donor ranking data available"
                />
            </div>

            <!-- Funding Timeline Line Chart (Full Width) -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:col-span-2"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                        Funding Timeline (Last 12 Months)
                    </h3>
                    <span
                        class="text-sm text-gray-500"
                        title="Monthly funding trends"
                    >
                        <i class="fas fa-info-circle"></i>
                    </span>
                </div>
                <LineChart
                    v-if="hasChartData('funding_timeline')"
                    chartId="funding-timeline-chart"
                    :data="chartData.funding_timeline"
                    :options="lineChartOptions"
                />
                <EmptyState
                    v-else
                    icon="chart-line"
                    message="No funding timeline data available"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useDonorStore } from "../../stores/donorStore";
import PieChart from "../charts/PieChart.vue";
import BarChart from "../charts/BarChart.vue";
import LineChart from "../charts/LineChart.vue";
import EmptyState from "../EmptyState.vue";

const donorStore = useDonorStore();

const loading = ref(false);
const error = ref(null);

// Chart data
const chartData = ref({
    funding_distribution: { labels: [], datasets: [] },
    top_donors: { labels: [], datasets: [] },
    funding_timeline: { labels: [], datasets: [] },
});

// Custom chart options
const barChartOptions = {
    indexAxis: "y", // Horizontal bars
    scales: {
        x: {
            beginAtZero: true,
            ticks: {
                callback: function (value) {
                    return "$" + value.toLocaleString();
                },
            },
        },
    },
};

const lineChartOptions = {
    plugins: {
        legend: {
            display: true,
            position: "bottom",
        },
    },
};

// Computed property to check if chart has data
const hasChartData = computed(() => (chartName) => {
    const chart = chartData.value[chartName];
    if (!chart || !chart.datasets || chart.datasets.length === 0) return false;

    return chart.datasets.some(
        (dataset) => dataset.data && dataset.data.some((value) => value > 0),
    );
});

// Fetch chart data on mount
onMounted(async () => {
    await fetchChartData();
});

const fetchChartData = async () => {
    loading.value = true;
    error.value = null;

    try {
        const data = await donorStore.fetchChartData();
        chartData.value = data;
    } catch (err) {
        error.value = err.message || "Failed to load chart data";
        console.error("Chart data error:", err);
    } finally {
        loading.value = false;
    }
};

// Expose refresh method for parent components
defineExpose({
    refresh: fetchChartData,
});
</script>

<style scoped>
.donor-charts-container {
    width: 100%;
}
</style>
