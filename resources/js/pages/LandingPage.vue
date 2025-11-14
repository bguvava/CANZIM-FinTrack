<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50"
    >
        <!-- Landing Page Container -->
        <div class="relative flex min-h-screen flex-col">
            <!-- Header -->
            <header class="w-full px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <img
                        :src="logoPath"
                        alt="CANZIM Logo"
                        class="h-16 w-auto animate-fade-in sm:h-20"
                    />
                </div>
            </header>

            <!-- Main Content -->
            <main
                class="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8"
            >
                <div class="w-full max-w-6xl">
                    <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
                        <!-- Hero Section -->
                        <div
                            class="flex flex-col justify-center space-y-6 animate-fade-in-up"
                        >
                            <h1
                                class="text-4xl font-bold text-gray-900 sm:text-5xl lg:text-6xl"
                            >
                                CANZIM
                                <span class="text-blue-800">FinTrack</span>
                            </h1>

                            <p class="text-xl text-gray-700 sm:text-2xl">
                                Financial Management Simplified
                            </p>

                            <p class="text-lg text-gray-600">
                                Comprehensive financial management and
                                accounting system for Climate Action Network
                                Zimbabwe. Track budgets, manage expenses,
                                monitor cash flow, and generate reports with
                                ease.
                            </p>

                            <!-- Features Grid -->
                            <div class="grid grid-cols-2 gap-4 pt-4">
                                <div
                                    v-for="(feature, index) in features"
                                    :key="index"
                                    class="flex items-start space-x-3 animate-fade-in-up"
                                    :style="{
                                        animationDelay: `${(index + 1) * 100}ms`,
                                    }"
                                >
                                    <i
                                        :class="feature.icon"
                                        class="text-blue-800 mt-1"
                                    ></i>
                                    <span
                                        class="text-sm text-gray-700 font-medium"
                                        >{{ feature.name }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Login Card -->
                        <div
                            class="flex items-center justify-center animate-fade-in-up"
                            style="animation-delay: 200ms"
                        >
                            <div class="w-full max-w-md">
                                <div
                                    class="rounded-2xl bg-white p-8 shadow-xl ring-1 ring-gray-200 transition-transform duration-300 hover:scale-105"
                                >
                                    <h2
                                        class="mb-6 text-center text-2xl font-bold text-gray-900"
                                    >
                                        Sign In to Your Account
                                    </h2>

                                    <LoginForm />

                                    <div class="mt-6 text-center">
                                        <button
                                            @click="showForgotPassword"
                                            class="text-sm text-blue-800 hover:text-blue-900 transition-colors duration-150"
                                        >
                                            Forgot your password?
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="w-full px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div
                        class="flex flex-col items-center space-y-4 text-center"
                    >
                        <p class="text-sm text-gray-600">
                            <strong>Climate Action Network Zimbabwe</strong>
                        </p>
                        <p class="text-sm text-gray-500">
                            Financial Management & Accounting System
                        </p>
                        <p class="text-xs text-gray-500">
                            Developed with ❤️ by
                            <a
                                href="https://bguvava.com"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-blue-800 hover:text-blue-900 transition-colors duration-150"
                            >
                                bguvava (bguvava.com)
                            </a>
                        </p>
                        <p class="text-xs text-gray-400">
                            © {{ currentYear }} Climate Action Network
                            Zimbabwe. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import LoginForm from "../components/LoginForm.vue";
import { canzimSwal } from "../plugins/sweetalert";

// Logo path
const logoPath = "/images/logo/canzim_logo.png";

// Current year for copyright
const currentYear = computed(() => new Date().getFullYear());

// Features list
const features = ref([
    { icon: "fas fa-chart-line", name: "Budget Tracking" },
    { icon: "fas fa-receipt", name: "Expense Management" },
    { icon: "fas fa-hand-holding-usd", name: "Donor Reporting" },
    { icon: "fas fa-money-bill-wave", name: "Cash Flow" },
    { icon: "fas fa-file-invoice-dollar", name: "Purchase Orders" },
    { icon: "fas fa-chart-bar", name: "Real-time Analytics" },
]);

// Show forgot password modal
function showForgotPassword() {
    canzimSwal
        .fire({
            title: "Reset Password",
            html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address
                </label>
                <input
                    id="reset-email"
                    type="email"
                    placeholder="Enter your email address"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                />
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: "Send Reset Link",
            cancelButtonText: "Cancel",
            preConfirm: () => {
                const email = document.getElementById("reset-email").value;
                if (!email) {
                    canzimSwal.showValidationMessage(
                        "Please enter your email address",
                    );
                    return false;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    canzimSwal.showValidationMessage(
                        "Please enter a valid email address",
                    );
                    return false;
                }
                return email;
            },
        })
        .then(async (result) => {
            if (result.isConfirmed) {
                // Handle password reset request
                const { useAuthStore } = await import("../stores/authStore");
                const authStore = useAuthStore();
                await authStore.forgotPassword(result.value);
            }
        });
}
</script>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out;
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out;
    animation-fill-mode: both;
}
</style>
