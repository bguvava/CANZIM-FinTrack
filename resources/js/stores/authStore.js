/**
 * Authentication Store
 * Pinia Store for managing authentication state
 *
 * Handles:
 * - User authentication (login/logout)
 * - User profile data
 * - Authentication token management
 * - Session timeout tracking
 *
 * @see https://pinia.vuejs.org/
 */

import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "../api";
import {
    showSuccess,
    showError,
    confirmLogout,
    sessionTimeoutWarning,
} from "../plugins/sweetalert";

export const useAuthStore = defineStore("auth", () => {
    // State
    const user = ref(null);
    const token = ref(null);
    const isAuthenticated = ref(false);
    const sessionTimeoutId = ref(null);
    const lastActivity = ref(Date.now());

    // Session timeout duration (5 minutes in milliseconds)
    const SESSION_TIMEOUT = 5 * 60 * 1000;
    const WARNING_BEFORE_TIMEOUT = 60 * 1000; // 1 minute warning

    // Getters
    const currentUser = computed(() => user.value);
    const userRole = computed(() => user.value?.role?.slug || null);
    const isLoggedIn = computed(() => isAuthenticated.value && !!token.value);

    /**
     * Initialize auth state from localStorage
     */
    function initializeAuth() {
        const storedToken = localStorage.getItem("auth_token");
        const storedUser = localStorage.getItem("auth_user");

        if (storedToken && storedUser) {
            token.value = storedToken;
            user.value = JSON.parse(storedUser);
            isAuthenticated.value = true;
            startSessionTimeout();
        }
    }

    /**
     * Login user
     */
    async function login(credentials) {
        try {
            const response = await api.post("/auth/login", credentials);

            if (response.data.status === "success") {
                // Store auth data
                token.value = response.data.data.token;
                user.value = response.data.data.user;
                isAuthenticated.value = true;

                // Persist to localStorage
                localStorage.setItem("auth_token", token.value);
                localStorage.setItem("auth_user", JSON.stringify(user.value));

                // Start session timeout
                startSessionTimeout();

                return {
                    success: true,
                    message: response.data.message,
                    user: user.value,
                };
            }

            return {
                success: false,
                message: response.data.message || "Login failed",
            };
        } catch (error) {
            const message =
                error.response?.data?.message ||
                "Login failed. Please check your credentials.";

            return {
                success: false,
                message,
                errors: error.response?.data?.errors || {},
            };
        }
    }

    /**
     * Logout user
     */
    async function logout(showConfirmation = true) {
        // Show confirmation if requested
        if (showConfirmation) {
            const confirmed = await confirmLogout();
            if (!confirmed) {
                return false;
            }
        }

        try {
            // Call logout API to revoke token
            await api.post("/auth/logout");
        } catch (error) {
            console.error("Logout API error:", error);
            // Continue with local logout even if API call fails
        }

        // Clear auth data
        clearAuthData();

        // Show success message
        await showSuccess(
            "Logged Out",
            "You have been logged out successfully.",
        );

        // Redirect to landing page
        window.location.href = "/";

        return true;
    }

    /**
     * Clear authentication data
     */
    function clearAuthData() {
        token.value = null;
        user.value = null;
        isAuthenticated.value = false;

        localStorage.removeItem("auth_token");
        localStorage.removeItem("auth_user");

        stopSessionTimeout();
    }

    /**
     * Request password reset link
     */
    async function forgotPassword(email) {
        try {
            const response = await api.post("/auth/forgot-password", { email });

            if (response.data.status === "success") {
                await showSuccess(
                    "Reset Link Sent",
                    response.data.message ||
                        "If an account exists with that email, a password reset link has been sent.",
                );
                return { success: true };
            }

            return {
                success: false,
                message: response.data.message || "Failed to send reset link",
            };
        } catch (error) {
            const message =
                error.response?.data?.message ||
                "Failed to send password reset link. Please try again.";

            await showError("Error", message);

            return {
                success: false,
                message,
            };
        }
    }

    /**
     * Reset password with token
     */
    async function resetPassword(data) {
        try {
            const response = await api.post("/auth/reset-password", data);

            if (response.data.status === "success") {
                await showSuccess(
                    "Password Reset",
                    "Your password has been reset successfully. Please login with your new password.",
                );
                return { success: true };
            }

            return {
                success: false,
                message: response.data.message || "Password reset failed",
            };
        } catch (error) {
            const message =
                error.response?.data?.message ||
                "Failed to reset password. Please try again.";

            await showError("Error", message);

            return {
                success: false,
                message,
                errors: error.response?.data?.errors || {},
            };
        }
    }

    /**
     * Fetch user profile
     */
    async function fetchProfile() {
        try {
            const response = await api.get("/auth/profile");

            if (response.data.status === "success") {
                user.value = response.data.data.user;
                localStorage.setItem("auth_user", JSON.stringify(user.value));
                return { success: true };
            }

            return { success: false };
        } catch (error) {
            console.error("Failed to fetch profile:", error);
            return { success: false };
        }
    }

    /**
     * Update last activity timestamp
     */
    function updateActivity() {
        lastActivity.value = Date.now();
    }

    /**
     * Start session timeout monitoring
     */
    function startSessionTimeout() {
        // Clear existing timeout
        stopSessionTimeout();

        // Check every second for inactivity
        sessionTimeoutId.value = setInterval(() => {
            const now = Date.now();
            const timeSinceLastActivity = now - lastActivity.value;

            // Show warning 1 minute before timeout
            if (
                timeSinceLastActivity >=
                    SESSION_TIMEOUT - WARNING_BEFORE_TIMEOUT &&
                timeSinceLastActivity < SESSION_TIMEOUT
            ) {
                showSessionWarning();
            }

            // Auto logout on timeout
            if (timeSinceLastActivity >= SESSION_TIMEOUT) {
                handleSessionTimeout();
            }
        }, 1000);

        // Track user activity
        const activityEvents = ["mousedown", "keydown", "scroll", "touchstart"];
        activityEvents.forEach((event) => {
            document.addEventListener(event, updateActivity);
        });
    }

    /**
     * Stop session timeout monitoring
     */
    function stopSessionTimeout() {
        if (sessionTimeoutId.value) {
            clearInterval(sessionTimeoutId.value);
            sessionTimeoutId.value = null;
        }
    }

    /**
     * Show session timeout warning
     */
    async function showSessionWarning() {
        const continueSession = await sessionTimeoutWarning(60);

        if (continueSession) {
            // User wants to continue - reset activity
            updateActivity();
        } else {
            // User chose to logout
            await logout(false);
        }
    }

    /**
     * Handle session timeout
     */
    async function handleSessionTimeout() {
        clearAuthData();

        await showError(
            "Session Expired",
            "You have been logged out due to inactivity.",
        );

        window.location.href = "/";
    }

    /**
     * Check if user has specific role
     */
    function hasRole(role) {
        return userRole.value === role;
    }

    /**
     * Check if user is Programs Manager
     */
    const isProgramsManager = computed(() => hasRole("programs-manager"));

    /**
     * Check if user is Finance Officer
     */
    const isFinanceOfficer = computed(() => hasRole("finance-officer"));

    /**
     * Check if user is Project Officer
     */
    const isProjectOfficer = computed(() => hasRole("project-officer"));

    return {
        // State
        user,
        token,
        isAuthenticated,
        lastActivity,

        // Getters
        currentUser,
        userRole,
        isLoggedIn,
        isProgramsManager,
        isFinanceOfficer,
        isProjectOfficer,

        // Actions
        initializeAuth,
        login,
        logout,
        clearAuthData,
        forgotPassword,
        resetPassword,
        fetchProfile,
        updateActivity,
        hasRole,
    };
});
