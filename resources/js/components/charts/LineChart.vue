<template>
    <div class="chart-container">
        <canvas :id="chartId"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from "vue";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    LineController,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    LineController,
    Title,
    Tooltip,
    Legend,
    Filler,
);

const props = defineProps({
    chartId: {
        type: String,
        required: true,
    },
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const chart = ref(null);

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: "bottom",
            labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                    size: 12,
                },
            },
        },
        tooltip: {
            mode: "index",
            intersect: false,
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
            cornerRadius: 8,
            titleFont: {
                size: 14,
                weight: "bold",
            },
            bodyFont: {
                size: 13,
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: {
                    size: 11,
                },
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: "rgba(0, 0, 0, 0.05)",
                borderDash: [5, 5],
            },
            ticks: {
                font: {
                    size: 11,
                },
                callback: function (value) {
                    return "$" + value.toLocaleString();
                },
            },
        },
    },
    interaction: {
        mode: "nearest",
        axis: "x",
        intersect: false,
    },
};

const initChart = () => {
    const canvas = document.getElementById(props.chartId);
    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    if (chart.value) {
        chart.value.destroy();
    }

    chart.value = new ChartJS(ctx, {
        type: "line",
        data: props.data,
        options: { ...defaultOptions, ...props.options },
    });
};

const updateChart = () => {
    if (chart.value) {
        chart.value.data = props.data;
        chart.value.update();
    }
};

onMounted(() => {
    initChart();
});

watch(
    () => props.data,
    () => {
        updateChart();
    },
    { deep: true },
);

onUnmounted(() => {
    if (chart.value) {
        chart.value.destroy();
    }
});
</script>

<style scoped>
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}
</style>
