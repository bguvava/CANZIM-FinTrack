<template>
    <DashboardLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    System Settings
                </h1>
                <p class="text-gray-600 mt-1">
                    System configuration and settings
                </p>
            </div>

            <div
                class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-12 text-center"
            >
                <i class="fas fa-cog mb-4 text-6xl text-gray-400"></i>
                <h3 class="mb-2 text-lg font-semibold text-gray-700">
                    Settings Module
                </h3>
                <p class="text-sm text-gray-600">
                    System configuration interface coming soon...
                </p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script>
import DashboardLayout from "../layouts/DashboardLayout.vue";

export default {
    name: "Settings",
    components: {
        DashboardLayout,
    },
};
</script>
