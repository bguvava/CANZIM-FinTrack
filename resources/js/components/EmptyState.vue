<template>
    <div class="h-64 flex items-center justify-center text-gray-400">
        <div class="text-center">
            <i :class="`fas fa-${icon} text-5xl mb-3`"></i>
            <p>{{ message }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    icon: {
        type: String,
        default: "inbox",
    },
    message: {
        type: String,
        default: "No data available",
    },
});
</script>
