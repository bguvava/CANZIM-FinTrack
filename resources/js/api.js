/**
 * Axios HTTP Client Configuration
 * CANZIM FinTrack API Client
 *
 * Configured with:
 * - Base URL for API endpoints
 * - Authentication token injection
 * - Request/Response interceptors
 * - Error handling
 *
 * @see https://axios-http.com/docs/intro
 */

import axios from "axios";
import { confirmLogout, showError } from "./plugins/sweetalert";

// Create axios instance with default config
const api = axios.create({
    baseURL: "/api/v1",
    timeout: 30000, // 30 seconds
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
    withCredentials: true, // Required for Sanctum CSRF protection
    xsrfCookieName: "XSRF-TOKEN",
    xsrfHeaderName: "X-XSRF-TOKEN",
});

// Get CSRF token from meta tag
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.content : null;
}

// Request interceptor - Add auth token and CSRF token to requests
api.interceptors.request.use(
    (config) => {
        // Skip token attachment during logout
        if (window.isLoggingOut && !config.url.includes("/auth/logout")) {
            return config;
        }

        // Get token from localStorage
        const token = localStorage.getItem("auth_token");

        // Always add Authorization header if token exists
        if (token && token.trim() !== "") {
            config.headers.Authorization = `Bearer ${token}`;
        }

        // Add CSRF token for state-changing requests
        const csrfToken = getCsrfToken();
        if (
            csrfToken &&
            !["get", "head", "options"].includes(config.method?.toLowerCase())
        ) {
            config.headers["X-CSRF-TOKEN"] = csrfToken;
        }

        return config;
    },
    (error) => {
        console.error("Request interceptor error:", error);
        return Promise.reject(error);
    },
);

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
 * Extract CSRF token from cookie and update meta tag
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

// Response interceptor - Handle errors globally
api.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        const originalRequest = error.config;

        // Handle 419 CSRF Token Mismatch - Retry once with refreshed token
        if (
            error.response &&
            error.response.status === 419 &&
            !originalRequest._retry
        ) {
            console.warn(
                "CSRF token mismatch detected, refreshing token and retrying...",
            );
            originalRequest._retry = true;

            try {
                // Clear any stale cookies first
                clearStaleCookies();

                // Wait for cookie clearing to take effect
                await new Promise((resolve) => setTimeout(resolve, 100));

                // Refresh CSRF cookie with explicit headers
                await axios.get("/sanctum/csrf-cookie", {
                    withCredentials: true,
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                // Wait longer for cookie to be properly set by the browser
                await new Promise((resolve) => setTimeout(resolve, 200));

                // Extract and update CSRF token
                const csrfToken = extractAndUpdateCsrfToken();

                if (csrfToken) {
                    // Update the CSRF token in the original request headers
                    originalRequest.headers["X-CSRF-TOKEN"] = csrfToken;
                    originalRequest.headers["X-XSRF-TOKEN"] = csrfToken;
                }

                // Retry the original request
                return api(originalRequest);
            } catch (retryError) {
                console.error("Failed to refresh CSRF token:", retryError);
                return Promise.reject(retryError);
            }
        }

        // Handle 401 Unauthorized - Session expired or invalid token
        if (error.response && error.response.status === 401) {
            console.warn("401 Unauthorized error:", error.config?.url);

            // Skip if already logging out or session is locked
            if (window.isLoggingOut) {
                return Promise.reject(error);
            }

            // Check if session warning is active (countdown is showing)
            // During the warning countdown, background API calls might fail with 401
            // but we don't want to logout - the warning dialog will handle it
            const isWarningActive =
                localStorage.getItem("session_warning_active") === "true";
            if (isWarningActive) {
                console.log(
                    "Session warning is active, ignoring 401 during countdown",
                );
                return Promise.reject(error);
            }

            // Check if session is locked (user should see lock screen instead)
            const isLocked = localStorage.getItem("session_locked") === "true";
            if (isLocked) {
                // Don't show error or redirect - lock screen is handling it
                console.log("Session is locked, user should unlock first");
                return Promise.reject(error);
            }

            // Only handle 401 if user was actually logged in (has a token)
            const hasToken = localStorage.getItem("auth_token");

            // Set flag to prevent multiple redirects
            window.isLoggingOut = true;

            // Clear all auth data completely
            localStorage.removeItem("auth_token");
            localStorage.removeItem("auth_user");
            localStorage.removeItem("session_locked");
            localStorage.removeItem("locked_user");
            localStorage.removeItem("session_warning_active");

            if (hasToken) {
                console.error(
                    "Token exists but received 401 - token may be invalid or expired",
                );

                // Show session expired message
                await showError(
                    "Session Expired",
                    "Your session has expired. Please login again.",
                );
            } else {
                console.error("Received 401 - Unauthorized access detected");

                // Show unauthorized access message
                await showError(
                    "Unauthorized",
                    "You must be logged in to access this resource.",
                );
            }

            // Always redirect to login on 401
            window.location.href = "/";

            return Promise.reject(error);
        }

        // Handle 403 Forbidden - Insufficient permissions
        if (error.response && error.response.status === 403) {
            await showError(
                "Access Denied",
                "You do not have permission to perform this action.",
            );
            return Promise.reject(error);
        }

        // Handle 404 Not Found
        if (error.response && error.response.status === 404) {
            await showError(
                "Not Found",
                "The requested resource was not found.",
            );
            return Promise.reject(error);
        }

        // Handle 422 Validation Error
        if (error.response && error.response.status === 422) {
            // Validation errors are handled by the form components
            // Don't show global error - let the component handle it
            return Promise.reject(error);
        }

        // Handle 500 Internal Server Error
        if (error.response && error.response.status === 500) {
            // If the API returned a specific error message, use it
            // Otherwise, show a generic error message
            const errorMessage =
                error.response.data?.message ||
                "An unexpected error occurred. Please try again later.";

            // Only show the generic error if there's no specific message from the API
            // The component will handle showing the specific message
            if (!error.response.data?.message) {
                await showError("Server Error", errorMessage);
            }
            return Promise.reject(error);
        }

        // Handle network errors
        if (error.message === "Network Error") {
            await showError(
                "Network Error",
                "Unable to connect to the server. Please check your internet connection.",
            );
            return Promise.reject(error);
        }

        // Handle timeout errors
        if (error.code === "ECONNABORTED") {
            await showError(
                "Request Timeout",
                "The request took too long to complete. Please try again.",
            );
            return Promise.reject(error);
        }

        return Promise.reject(error);
    },
);

export default api;
