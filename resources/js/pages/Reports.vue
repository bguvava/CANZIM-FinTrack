<template>
    <DashboardLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
                <p class="text-gray-600 mt-1">
                    Generate and view financial reports and analytics
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-6">
                    <div class="mb-2 flex items-center">
                        <i
                            class="fas fa-file-pdf mr-3 text-2xl text-blue-600"
                        ></i>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Financial Reports
                        </h3>
                    </div>
                    <p class="text-sm text-gray-600">Coming soon...</p>
                </div>

                <div class="rounded-lg border border-green-200 bg-green-50 p-6">
                    <div class="mb-2 flex items-center">
                        <i
                            class="fas fa-chart-bar mr-3 text-2xl text-green-600"
                        ></i>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Project Reports
                        </h3>
                    </div>
                    <p class="text-sm text-gray-600">Coming soon...</p>
                </div>

                <div
                    class="rounded-lg border border-purple-200 bg-purple-50 p-6"
                >
                    <div class="mb-2 flex items-center">
                        <i
                            class="fas fa-analytics mr-3 text-2xl text-purple-600"
                        ></i>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Analytics
                        </h3>
                    </div>
                    <p class="text-sm text-gray-600">Coming soon...</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script>
import DashboardLayout from "../layouts/DashboardLayout.vue";

export default {
    name: "Reports",
    components: {
        DashboardLayout,
    },
};
</script>
