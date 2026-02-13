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
import axios from "axios";
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
    const warningShown = ref(false); // Prevent multiple warnings
    const isSessionLocked = ref(false); // Session lock state
    const lockedUser = ref(null); // Store user info when locked
    const sessionWarningActive = ref(false); // Flag to prevent 401 logout during warning countdown

    // Session timeout duration (5 minutes in milliseconds)
    const SESSION_TIMEOUT = 5 * 60 * 1000;
    const WARNING_BEFORE_TIMEOUT = 30 * 1000; // 30 seconds warning countdown

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
        const storedLocked = localStorage.getItem("session_locked");
        const storedLockedUser = localStorage.getItem("locked_user");

        if (storedToken && storedUser) {
            try {
                const parsedUser = JSON.parse(storedUser);

                // Validate user has a role and it's one of the 3 authorized roles
                const validRoles = [
                    "programs-manager",
                    "finance-officer",
                    "project-officer",
                ];
                const userRole = parsedUser?.role?.slug;

                if (!userRole || !validRoles.includes(userRole)) {
                    console.error(
                        "Invalid or missing user role detected - clearing auth state",
                    );
                    clearAuthData();
                    return;
                }

                token.value = storedToken;
                user.value = parsedUser;
                isAuthenticated.value = true;
                api.defaults.headers.common.Authorization = `Bearer ${storedToken}`;

                // Check if session was locked before refresh
                if (storedLocked === "true" && storedLockedUser) {
                    isSessionLocked.value = true;
                    lockedUser.value = JSON.parse(storedLockedUser);
                } else {
                    startSessionTimeout();
                }
            } catch (error) {
                console.error("Failed to parse stored user data:", error);
                clearAuthData();
            }
        }
    }

    /**
     * Login user
     */
    async function login(credentials, retryCount = 0) {
        const MAX_RETRIES = 2;

        try {
            // CRITICAL: Always clear stale cookies BEFORE requesting fresh CSRF token
            // This prevents the "CSRF token mismatch" error after session timeout
            clearStaleCookies();

            // Small delay to ensure cookies are cleared
            await new Promise((resolve) => setTimeout(resolve, 50));

            // Get fresh CSRF cookie from Sanctum using a clean axios instance
            // (not the api instance which might have stale headers)
            await axios.get("/sanctum/csrf-cookie", {
                withCredentials: true,
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            // Wait for cookie to be properly set by the browser
            await new Promise((resolve) => setTimeout(resolve, 200));

            // Extract and update CSRF token from XSRF-TOKEN cookie
            const csrfToken = extractAndUpdateCsrfToken();

            // Make login request with credentials using a fresh request config
            // Explicitly include the CSRF token to ensure it's not stale
            const response = await api.post("/auth/login", credentials, {
                headers: csrfToken
                    ? { "X-CSRF-TOKEN": csrfToken, "X-XSRF-TOKEN": csrfToken }
                    : {},
            });

            if (response.data.status === "success") {
                // Validate user has one of the 3 authorized roles
                const validRoles = [
                    "programs-manager",
                    "finance-officer",
                    "project-officer",
                ];
                const userRole = response.data.data.user?.role?.slug;

                if (!userRole || !validRoles.includes(userRole)) {
                    console.error(
                        "User does not have an authorized role:",
                        userRole,
                    );
                    return {
                        success: false,
                        message:
                            "Your account does not have the required permissions to access this system.",
                    };
                }

                // Store auth data IMMEDIATELY
                token.value = response.data.data.token;
                user.value = response.data.data.user;
                isAuthenticated.value = true;
                api.defaults.headers.common.Authorization = `Bearer ${token.value}`;

                // Persist to localStorage BEFORE any other API calls
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
            console.error("Login error:", error);
            const message =
                error.response?.data?.message ||
                "Login failed. Please check your credentials.";

            // Handle 419 CSRF token mismatch specifically for login
            if (error.response?.status === 419 && retryCount < MAX_RETRIES) {
                console.warn(
                    `CSRF token mismatch on login attempt ${retryCount + 1}, retrying with fresh token...`,
                );
                // Clear any stale cookies more aggressively
                clearStaleCookies();
                // Wait longer before retry to ensure clean state
                await new Promise((resolve) => setTimeout(resolve, 300));
                // Recursive retry with incremented count
                return login(credentials, retryCount + 1);
            }

            // If we've exhausted retries for CSRF, provide a helpful message
            if (error.response?.status === 419) {
                return {
                    success: false,
                    message:
                        "Session expired. Please refresh the page and try again.",
                    errors: {},
                };
            }

            return {
                success: false,
                message,
                errors: error.response?.data?.errors || {},
            };
        }
    }

    /**
     * Extract CSRF token from cookie and update meta tag
     * Returns the token if found
     */
    function extractAndUpdateCsrfToken() {
        const csrfCookie = document.cookie
            .split("; ")
            .find((row) => row.startsWith("XSRF-TOKEN="));

        if (csrfCookie) {
            const csrfToken = decodeURIComponent(csrfCookie.split("=")[1]);
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute("content", csrfToken);
            }
            return csrfToken;
        }
        return null;
    }

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

        // Set flag to prevent API calls during logout
        window.isLoggingOut = true;

        try {
            // Call logout API to revoke token
            await api.post("/auth/logout");
        } catch (error) {
            console.error("Logout API error:", error);
            // Continue with local logout even if API call fails
        }

        // Clear auth data and all session state
        clearAuthData();

        // Clear stale cookies to prevent CSRF issues on next login
        clearStaleCookies();

        // Clear any other potential session remnants
        sessionStorage.clear();

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
        sessionWarningActive.value = false;
        isSessionLocked.value = false;
        lockedUser.value = null;

        localStorage.removeItem("auth_token");
        localStorage.removeItem("auth_user");
        localStorage.removeItem("session_warning_active");
        localStorage.removeItem("session_locked");
        localStorage.removeItem("locked_user");

        stopSessionTimeout();
    }

    /**
     * Refresh CSRF token
     * Call this before login attempts to ensure fresh token
     */
    async function refreshCsrfToken() {
        try {
            // Clear any stale cookies first
            clearStaleCookies();

            // Get fresh CSRF cookie from Sanctum
            await axios.get("/sanctum/csrf-cookie", {
                withCredentials: true,
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            // Wait for cookie to be properly set
            await new Promise((resolve) => setTimeout(resolve, 150));

            // Extract and update CSRF token
            const token = extractAndUpdateCsrfToken();

            return !!token;
        } catch (error) {
            console.error("Failed to refresh CSRF token:", error);
            return false;
        }
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
        warningShown.value = false;

        // Check every second for inactivity
        sessionTimeoutId.value = setInterval(() => {
            const now = Date.now();
            const timeSinceLastActivity = now - lastActivity.value;

            // Show warning 30 seconds before timeout (only once)
            if (
                timeSinceLastActivity >=
                    SESSION_TIMEOUT - WARNING_BEFORE_TIMEOUT &&
                timeSinceLastActivity < SESSION_TIMEOUT &&
                !warningShown.value
            ) {
                warningShown.value = true;
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
        // Stop timeout checking while warning is shown to avoid race conditions
        stopSessionTimeout();

        // Set flag to prevent 401 interceptor from logging out during countdown
        // This is critical because background API calls (like dashboard refresh)
        // might trigger 401 errors while the warning is showing
        sessionWarningActive.value = true;
        localStorage.setItem("session_warning_active", "true");

        const userChoice = await sessionTimeoutWarning(30);

        // Clear the warning flag now that user has made a choice
        sessionWarningActive.value = false;
        localStorage.removeItem("session_warning_active");

        if (userChoice === "continue") {
            // User wants to continue - extend session via API
            try {
                await api.post("/auth/extend-session");
                // Reset activity and warning flag
                updateActivity();
                warningShown.value = false;
                // Restart timeout monitoring
                startSessionTimeout();
            } catch (error) {
                console.error("Failed to extend session:", error);
                // If extend-session fails with 401, the session has truly expired
                // Lock the session instead of retrying
                if (error.response?.status === 401) {
                    lockSession();
                    return;
                }
                // For other errors, still restart monitoring locally
                updateActivity();
                warningShown.value = false;
                startSessionTimeout();
            }
        } else if (userChoice === "logout") {
            // User explicitly chose to logout
            await logout(false);
        } else {
            // Timer expired - lock the session (don't logout, show lock screen)
            lockSession();
        }
    }

    /**
     * Handle session timeout
     */
    async function handleSessionTimeout() {
        // Lock the session instead of logging out
        lockSession();
    }

    /**
     * Lock the session (shows lock screen)
     */
    function lockSession() {
        // Store user info for lock screen
        lockedUser.value = { ...user.value };
        isSessionLocked.value = true;

        // Persist lock state to survive browser refresh
        localStorage.setItem("session_locked", "true");
        localStorage.setItem("locked_user", JSON.stringify(user.value));

        // Stop timeout monitoring while locked
        stopSessionTimeout();
    }

    /**
     * Unlock the session (user re-authenticated)
     */
    async function unlockSession(newToken = null) {
        isSessionLocked.value = false;
        lockedUser.value = null;

        // Update token if provided
        if (newToken) {
            token.value = newToken;
            localStorage.setItem("auth_token", newToken);
            api.defaults.headers.common.Authorization = `Bearer ${newToken}`;

            // Refresh CSRF token after getting new auth token
            try {
                await refreshCsrfToken();
            } catch (error) {
                console.error(
                    "Failed to refresh CSRF token after unlock:",
                    error,
                );
            }
        }

        // Clear lock persistence
        localStorage.removeItem("session_locked");
        localStorage.removeItem("locked_user");

        // Restart activity tracking
        updateActivity();
        warningShown.value = false;
        startSessionTimeout();
    }

    /**
     * Full logout from lock screen
     */
    function fullLogoutFromLock() {
        // Clear session lock state first
        isSessionLocked.value = false;
        lockedUser.value = null;
        localStorage.removeItem("session_locked");
        localStorage.removeItem("locked_user");

        // Clear all auth data
        clearAuthData();

        // Ensure redirect happens
        // Note: The component calling this should handle redirect
        // but we set a flag to prevent any further API calls
        window.isLoggingOut = true;
    }

    /**
     * Check if user has specific role
     */
    function hasRole(role) {
        return userRole.value === role;
    }

    /**
     * Check if user has specific permission
     * For now, this is role-based. Can be extended for granular permissions.
     */
    function hasPermission(permission) {
        // Programs Manager has all permissions
        if (isProgramsManager.value) {
            return true;
        }

        // Permission mappings by role
        const rolePermissions = {
            "finance-officer": [
                "view-projects",
                "view-budgets",
                "manage-budgets",
                "view-expenses",
                "review-expenses",
                "approve-expenses",
                "view-cash-flow",
                "manage-cash-flow",
                "view-purchase-orders",
                "manage-purchase-orders",
                "view-vendors",
                "manage-vendors",
                "view-donors",
                "view-reports",
            ],
            "project-officer": [
                "view-projects",
                "view-expenses",
                "submit-expenses",
                "view-documents",
            ],
        };

        const userPermissions = rolePermissions[userRole.value] || [];
        return userPermissions.includes(permission);
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
        isSessionLocked,
        lockedUser,
        sessionWarningActive,

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
        refreshCsrfToken,
        forgotPassword,
        resetPassword,
        fetchProfile,
        updateActivity,
        hasRole,
        hasPermission,
        lockSession,
        unlockSession,
        fullLogoutFromLock,
    };
});
