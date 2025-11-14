<template>
    <div
        class="h-screen overflow-hidden bg-linear-to-br from-blue-900 via-blue-800 to-blue-900"
    >
        <!-- Landing Page Container -->
        <div class="relative flex h-screen flex-col">
            <!-- Header -->
            <header class="w-full px-4 py-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl flex items-center gap-3">
                    <img
                        :src="logoPath"
                        alt="CANZIM Logo"
                        class="h-12 w-auto animate-fade-in sm:h-14"
                    />
                    <div>
                        <h1 class="text-xl font-bold text-white sm:text-2xl">
                            CANZIM FinTrack
                        </h1>
                        <p class="text-xs text-blue-100 sm:text-sm">
                            Financial Management Simplified
                        </p>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main
                class="flex flex-1 items-center justify-center px-4 sm:px-6 lg:px-8 overflow-hidden"
            >
                <div class="w-full max-w-6xl">
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-10">
                        <!-- Hero Section -->
                        <div
                            class="flex flex-col justify-center space-y-4 animate-fade-in-up"
                        >
                            <h1
                                class="text-3xl font-bold text-white sm:text-4xl lg:text-5xl"
                            >
                                Welcome to
                                <span class="text-blue-300"
                                    >CANZIM FinTrack</span
                                >
                            </h1>

                            <p class="text-base text-blue-100 sm:text-lg">
                                Comprehensive financial management and
                                accounting system for Climate Action Network
                                Zimbabwe. Track budgets, manage expenses,
                                monitor cash flow, and generate reports with
                                ease.
                            </p>

                            <!-- Features Grid -->
                            <div class="grid grid-cols-2 gap-3 pt-3">
                                <div
                                    v-for="(feature, index) in features"
                                    :key="index"
                                    class="flex items-start space-x-2 animate-fade-in-up"
                                    :style="{
                                        animationDelay: `${(index + 1) * 100}ms`,
                                    }"
                                >
                                    <i
                                        :class="feature.icon"
                                        class="text-blue-300 mt-0.5 text-sm"
                                    ></i>
                                    <span
                                        class="text-xs text-blue-100 font-medium sm:text-sm"
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
                                    class="rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-blue-300/20 backdrop-blur-sm sm:p-8"
                                >
                                    <h2
                                        class="mb-4 text-center text-xl font-bold text-gray-900 sm:text-2xl"
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
            <footer
                class="w-full px-4 py-3 sm:px-6 lg:px-8 border-t border-blue-700/30"
            >
                <div class="mx-auto max-w-7xl">
                    <p class="text-center text-xs text-blue-200 sm:text-sm">
                        © {{ currentYear }} Climate Action Network Zimbabwe.
                        All rights reserved. Developed with ❤️ by bguvava.
                    </p>
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import LoginForm from "../components/LoginForm.vue";
import { canzimSwal } from "../plugins/sweetalert";
import { useAuthStore } from "../stores/authStore";

// Initialize auth store
const authStore = useAuthStore();

// Logo path
const logoPath = "/images/logo/canzim_white.png";

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
