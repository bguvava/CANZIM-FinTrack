<template>
    <div class="chart-container">
        <canvas :id="chartId"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from "vue";
import { Chart as ChartJS, ArcElement, Tooltip, Legend, DoughnutController } from "chart.js";

ChartJS.register(ArcElement, Tooltip, Legend, DoughnutController);

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
            callbacks: {
                label: function (context) {
                    let label = context.label || "";
                    if (label) {
                        label += ": ";
                    }
                    if (context.parsed !== null) {
                        label += "$" + context.parsed.toLocaleString();
                    }

                    // Calculate percentage
                    const total = context.dataset.data.reduce(
                        (a, b) => a + b,
                        0,
                    );
                    const percentage = ((context.parsed / total) * 100).toFixed(
                        1,
                    );
                    label += " (" + percentage + "%)";

                    return label;
                },
            },
        },
    },
    cutout: "60%",
};

const initChart = () => {
    const canvas = document.getElementById(props.chartId);
    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    if (chart.value) {
        chart.value.destroy();
    }

    chart.value = new ChartJS(ctx, {
        type: "doughnut",
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
