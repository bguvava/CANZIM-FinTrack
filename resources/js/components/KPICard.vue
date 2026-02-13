<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center gap-3">
            <!-- Icon -->
            <div
                :class="`w-10 h-10 bg-${color}-100 rounded-lg flex items-center justify-center flex-shrink-0`"
            >
                <i :class="`fas fa-${icon} text-${color}-600 text-lg`"></i>
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
                <h3 class="text-gray-600 text-xs font-medium truncate">{{ title }}</h3>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <p class="text-xl font-bold text-gray-800">{{ value }}</p>
                    <span
                        v-if="trend"
                        :class="`text-${trendColor}-600 text-xs font-medium flex items-center gap-0.5`"
                    >
                        <i :class="`fas fa-arrow-${trendDirection} text-[10px]`"></i>
                        {{ trend }}
                    </span>
                </div>
                <p v-if="description" class="text-[10px] text-gray-500 mt-0.5 truncate">
                    {{ description }}
                </p>
            </div>
        </div>
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
