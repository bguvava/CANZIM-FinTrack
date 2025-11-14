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
});

// Request interceptor - Add auth token to requests
api.interceptors.request.use(
    (config) => {
        // Get token from localStorage
        const token = localStorage.getItem("auth_token");

        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    },
);

// Response interceptor - Handle errors globally
api.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        // Handle 401 Unauthorized - Session expired
        if (error.response && error.response.status === 401) {
            // Clear auth data
            localStorage.removeItem("auth_token");
            localStorage.removeItem("auth_user");

            // Show session expired message
            await showError(
                "Session Expired",
                "Your session has expired. Please login again.",
            );

            // Redirect to login
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
            return Promise.reject(error);
        }

        // Handle 500 Internal Server Error
        if (error.response && error.response.status === 500) {
            await showError(
                "Server Error",
                "An unexpected error occurred. Please try again later.",
            );
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
