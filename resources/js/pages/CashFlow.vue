<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Cash Flow Management
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Monitor and manage organization cash flow and bank accounts
                </p>
            </div>

            <!-- Navigation Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Bank Accounts Card -->
                <a
                    href="/cash-flow/bank-accounts"
                    class="block rounded-lg bg-white p-6 shadow transition-shadow hover:shadow-lg"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100"
                    >
                        <i class="fas fa-university text-2xl text-blue-800"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">
                        Bank Accounts
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage bank accounts and balances
                    </p>
                </a>

                <!-- Transactions Card -->
                <a
                    href="/cash-flow/transactions"
                    class="block rounded-lg bg-white p-6 shadow transition-shadow hover:shadow-lg"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100"
                    >
                        <i
                            class="fas fa-exchange-alt text-2xl text-green-800"
                        ></i>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">
                        Transactions
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        View and manage cash transactions
                    </p>
                </a>

                <!-- Projections Card -->
                <a
                    href="/cash-flow/projections"
                    class="block rounded-lg bg-white p-6 shadow transition-shadow hover:shadow-lg"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100"
                    >
                        <i
                            class="fas fa-chart-line text-2xl text-purple-800"
                        ></i>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">
                        Projections
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Forecast future cash flow trends
                    </p>
                </a>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from "../layouts/DashboardLayout.vue";
</script>
