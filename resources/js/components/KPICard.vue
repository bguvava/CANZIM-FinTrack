<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div
                :class="`w-12 h-12 bg-${color}-100 rounded-lg flex items-center justify-center`"
            >
                <i :class="`fas fa-${icon} text-${color}-600 text-xl`"></i>
            </div>
            <span
                v-if="trend"
                :class="`text-${trendColor}-600 text-sm font-medium flex items-center gap-1`"
            >
                <i :class="`fas fa-arrow-${trendDirection} text-xs`"></i>
                {{ trend }}
            </span>
        </div>
        <h3 class="text-gray-600 text-sm font-medium">{{ title }}</h3>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ value }}</p>
        <p v-if="description" class="text-xs text-gray-500 mt-2">
            {{ description }}
        </p>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: "blue",
    },
    description: {
        type: String,
        default: "",
    },
    trend: {
        type: String,
        default: "",
    },
    trendDirection: {
        type: String,
        default: "up",
        validator: (value) => ["up", "down"].includes(value),
    },
});

const trendColor = computed(() => {
    return props.trendDirection === "up" ? "green" : "red";
});
</script>
