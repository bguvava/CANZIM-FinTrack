<template>
    <form @submit.prevent="handleLogin" class="space-y-6">
        <!-- Email Field -->
        <div>
            <label
                for="email"
                class="block text-sm font-medium text-gray-700 mb-2"
            >
                Email Address
            </label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                :class="[
                    'w-full rounded-lg border px-4 py-2.5 transition-all duration-150',
                    'focus:outline-none focus:ring-2',
                    errors.email
                        ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
                        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200',
                ]"
                placeholder="your.email@example.com"
                @input="clearError('email')"
            />
            <p v-if="errors.email" class="mt-1.5 text-sm text-red-600">
                {{ errors.email[0] }}
            </p>
        </div>

        <!-- Password Field -->
        <div>
            <label
                for="password"
                class="block text-sm font-medium text-gray-700 mb-2"
            >
                Password
            </label>
            <div class="relative">
                <input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    autocomplete="current-password"
                    :class="[
                        'w-full rounded-lg border px-4 py-2.5 pr-12 transition-all duration-150',
                        'focus:outline-none focus:ring-2',
                        errors.password
                            ? 'border-red-300 focus:border-red-500 focus:ring-red-200'
                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200',
                    ]"
                    placeholder="Enter your password"
                    @input="clearError('password')"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                    tabindex="-1"
                >
                    <i
                        :class="
                            showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'
                        "
                    ></i>
                </button>
            </div>
            <p v-if="errors.password" class="mt-1.5 text-sm text-red-600">
                {{ errors.password[0] }}
            </p>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center">
            <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-800 focus:ring-2 focus:ring-blue-200 transition-all duration-150"
            />
            <label for="remember" class="ml-2 text-sm text-gray-700">
                Remember me for 30 days
            </label>
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            :disabled="isLoading"
            :class="[
                'w-full rounded-lg px-6 py-3 text-base font-semibold text-white transition-all duration-150',
                'focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2',
                isLoading
                    ? 'cursor-not-allowed bg-gray-400'
                    : 'bg-blue-800 hover:bg-blue-900 active:scale-95',
            ]"
        >
            <span v-if="isLoading" class="flex items-center justify-center">
                <svg
                    class="mr-2 h-5 w-5 animate-spin text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                Signing in...
            </span>
            <span v-else>Sign In</span>
        </button>
    </form>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useAuthStore } from "../stores/authStore";
import { showError } from "../plugins/sweetalert";

// Auth store
const authStore = useAuthStore();

// Form state
const form = reactive({
    email: "",
    password: "",
    remember: false,
});

// Loading state
const isLoading = ref(false);

// Show/hide password toggle
const showPassword = ref(false);

// Validation errors
const errors = reactive({
    email: null,
    password: null,
});

/**
 * Clear specific field error
 */
function clearError(field) {
    errors[field] = null;
}

/**
 * Clear all errors
 */
function clearErrors() {
    errors.email = null;
    errors.password = null;
}

/**
 * Validate form before submission
 */
function validateForm() {
    clearErrors();
    let isValid = true;

    // Validate email
    if (!form.email) {
        errors.email = ["Email address is required."];
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = ["Please enter a valid email address."];
        isValid = false;
    }

    // Validate password
    if (!form.password) {
        errors.password = ["Password is required."];
        isValid = false;
    } else if (form.password.length < 8) {
        errors.password = ["Password must be at least 8 characters."];
        isValid = false;
    }

    return isValid;
}

/**
 * Handle login form submission
 */
async function handleLogin() {
    // Validate form
    if (!validateForm()) {
        return;
    }

    isLoading.value = true;

    try {
        const result = await authStore.login({
            email: form.email,
            password: form.password,
            remember: form.remember,
        });

        if (result.success) {
            // Redirect to dashboard
            window.location.href = "/dashboard";
        } else {
            // Show error message
            await showError("Login Failed", result.message);

            // Set validation errors if present
            if (result.errors) {
                Object.keys(result.errors).forEach((key) => {
                    if (errors.hasOwnProperty(key)) {
                        errors[key] = result.errors[key];
                    }
                });
            }
        }
    } catch (error) {
        console.error("Login error:", error);
        await showError(
            "Login Failed",
            "An unexpected error occurred. Please try again.",
        );
    } finally {
        isLoading.value = false;
    }
}
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
