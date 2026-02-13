<template>
    <Teleport to="body">
        <div
            v-if="isLocked"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/95"
            @click.stop.prevent
            @keydown.esc.stop.prevent
            @contextmenu.prevent
        >
            <div
                class="w-full max-w-md rounded-lg bg-white p-8 shadow-2xl"
                @click.stop
            >
                <!-- Lock Icon -->
                <div class="mb-6 flex justify-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100"
                    >
                        <i class="fas fa-lock text-3xl text-yellow-600"></i>
                    </div>
                </div>

                <!-- Title -->
                <h2 class="mb-2 text-center text-2xl font-bold text-gray-900">
                    Session Locked
                </h2>
                <p class="mb-6 text-center text-sm text-gray-600">
                    Your session has been locked due to inactivity
                </p>

                <!-- User Info -->
                <div
                    class="mb-6 flex items-center space-x-3 rounded-lg bg-gray-50 p-4"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-lg font-semibold text-white"
                    >
                        {{ userInitials }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ userName }}</p>
                        <p class="text-sm text-gray-500">{{ userEmail }}</p>
                    </div>
                </div>

                <!-- Error Message -->
                <div
                    v-if="error"
                    class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-600"
                >
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ error }}
                </div>

                <!-- Password Form -->
                <form @submit.prevent="handleUnlock">
                    <div class="mb-4">
                        <label
                            for="password"
                            class="mb-2 block text-sm font-medium text-gray-700"
                        >
                            Enter your password to continue
                        </label>
                        <div class="relative">
                            <input
                                ref="passwordInput"
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                placeholder="••••••••"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 pr-12 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :disabled="isLoading"
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <i
                                    :class="
                                        showPassword
                                            ? 'fas fa-eye-slash'
                                            : 'fas fa-eye'
                                    "
                                ></i>
                            </button>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="space-y-3">
                        <button
                            type="submit"
                            :disabled="isLoading || !password"
                            class="flex w-full items-center justify-center space-x-2 rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <i
                                v-if="isLoading"
                                class="fas fa-spinner fa-spin"
                            ></i>
                            <i v-else class="fas fa-unlock"></i>
                            <span>{{
                                isLoading ? "Unlocking..." : "Unlock Session"
                            }}</span>
                        </button>

                        <button
                            type="button"
                            @click="handleFullLogout"
                            :disabled="isLoading"
                            class="flex w-full items-center justify-center space-x-2 rounded-lg border border-red-600 px-4 py-3 font-semibold text-red-600 transition-colors hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout Completely</span>
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <p class="mt-6 text-center text-xs text-gray-400">
                    For security reasons, your session was locked after
                    inactivity.
                </p>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { useAuthStore } from "../../stores/authStore";
import api from "../../api";
import axios from "axios";

const authStore = useAuthStore();

// State
const password = ref("");
const showPassword = ref(false);
const isLoading = ref(false);
const error = ref("");
const passwordInput = ref(null);

/**
 * Clear stale cookies that might cause CSRF issues
 */
function clearStaleCookies() {
    const domain = window.location.hostname;
    const expiry = "expires=Thu, 01 Jan 1970 00:00:00 UTC";

    // Clear XSRF-TOKEN cookie with all possible path/domain combinations
    document.cookie = `XSRF-TOKEN=; ${expiry}; path=/;`;
    document.cookie = `XSRF-TOKEN=; ${expiry}; path=/; domain=${domain};`;
    document.cookie = `XSRF-TOKEN=; ${expiry}; path=/; SameSite=Lax;`;
    document.cookie = `XSRF-TOKEN=; ${expiry}; path=/; domain=${domain}; SameSite=Lax;`;

    // Clear laravel_session cookie
    document.cookie = `laravel_session=; ${expiry}; path=/;`;
    document.cookie = `laravel_session=; ${expiry}; path=/; domain=${domain};`;
    document.cookie = `laravel_session=; ${expiry}; path=/; SameSite=Lax;`;
    document.cookie = `laravel_session=; ${expiry}; path=/; domain=${domain}; SameSite=Lax;`;

    // Also clear the meta tag to ensure no stale token is used
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        metaTag.setAttribute("content", "");
    }
}

// Computed
const isLocked = computed(() => authStore.isSessionLocked);

const userName = computed(() => {
    const user = authStore.lockedUser;
    return user?.name || "User";
});

const userEmail = computed(() => {
    const user = authStore.lockedUser;
    return user?.email || "";
});

const userInitials = computed(() => {
    const name = userName.value;
    if (!name) {
        return "U";
    }
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
});

// Watch for lock state changes
watch(isLocked, (locked) => {
    if (locked) {
        password.value = "";
        error.value = "";
        // Focus password input when lock screen appears
        nextTick(() => {
            passwordInput.value?.focus();
        });

        // Prevent page unload when locked
        window.addEventListener("beforeunload", preventUnload);

        // Disable browser back button
        window.history.pushState(null, "", window.location.href);
        window.addEventListener("popstate", preventNavigation);
    } else {
        // Remove event listeners when unlocked
        window.removeEventListener("beforeunload", preventUnload);
        window.removeEventListener("popstate", preventNavigation);
    }
});

// Prevent page unload
function preventUnload(e) {
    e.preventDefault();
    e.returnValue = "";
    return "";
}

// Prevent navigation
function preventNavigation() {
    window.history.pushState(null, "", window.location.href);
}

// Handle unlock
async function handleUnlock() {
    if (!password.value || isLoading.value) {
        return;
    }

    isLoading.value = true;
    error.value = "";

    try {
        // Clear any stale cookies before requesting fresh CSRF token
        clearStaleCookies();

        // Small delay to ensure cookies are cleared
        await new Promise((resolve) => setTimeout(resolve, 50));

        // Get fresh CSRF cookie to ensure we have a valid token
        await axios.get("/sanctum/csrf-cookie", {
            withCredentials: true,
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        // Wait longer for cookie to be properly set by the browser
        await new Promise((resolve) => setTimeout(resolve, 200));

        // Get CSRF token from cookie
        const csrfCookie = document.cookie
            .split("; ")
            .find((row) => row.startsWith("XSRF-TOKEN="));
        const csrfToken = csrfCookie
            ? decodeURIComponent(csrfCookie.split("=")[1])
            : null;

        // Verify credentials using the verify-password endpoint
        // Use axios directly WITHOUT the expired auth token (this is a public endpoint)
        const response = await axios.post(
            "/api/v1/auth/verify-password",
            {
                email: userEmail.value,
                password: password.value,
            },
            {
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    ...(csrfToken && {
                        "X-CSRF-TOKEN": csrfToken,
                        "X-XSRF-TOKEN": csrfToken,
                    }),
                },
                withCredentials: true,
            },
        );

        if (response.data.status === "success") {
            // Password verified successfully - unlock session with new token
            const newToken = response.data.data?.token;

            if (newToken) {
                // Update axios default auth header immediately
                api.defaults.headers.common["Authorization"] =
                    `Bearer ${newToken}`;

                // Unlock session with new token (this will also refresh CSRF)
                await authStore.unlockSession(newToken);
            } else {
                // Fallback if no token provided
                await authStore.unlockSession();
            }

            password.value = "";
            error.value = "";
        } else {
            error.value = response.data.message || "Invalid password";
        }
    } catch (err) {
        console.error("Unlock error:", err);
        error.value =
            err.response?.data?.message ||
            "Invalid password. Please try again.";
    } finally {
        isLoading.value = false;
    }
}

// Handle full logout
async function handleFullLogout() {
    isLoading.value = true;

    // Set global flag to prevent 401 interceptor from interfering
    window.isLoggingOut = true;

    try {
        await api.post("/auth/logout");
    } catch (err) {
        console.error("Logout error:", err);
        // Continue with logout even if API call fails
    }

    // Clear all auth data
    authStore.fullLogoutFromLock();

    // Force redirect to login page
    window.location.href = "/";
}

// On mount, focus password input if locked
onMounted(() => {
    if (isLocked.value) {
        nextTick(() => {
            passwordInput.value?.focus();
        });
    }
});
</script>
