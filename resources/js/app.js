import "./bootstrap";

/**
 * CANZIM FinTrack - Main Application Entry Point
 *
 * This file initializes the Vue.js application with all necessary plugins,
 * components, and configurations for the CANZIM FinTrack system.
 */

import { createApp } from "vue";
import { createPinia } from "pinia";
import Alpine from "alpinejs";

// Import SweetAlert2 plugin
import { canzimSwal, Toast } from "./plugins/sweetalert";

// Import FontAwesome
import "@fortawesome/fontawesome-free/css/all.css";

// Import pages
import LandingPage from "./pages/LandingPage.vue";
import Dashboard from "./pages/Dashboard.vue";
import Users from "./pages/Users.vue";
import ActivityLogs from "./pages/ActivityLogs.vue";
import Profile from "./pages/Profile.vue";

// Import stores
import { useAuthStore } from "./stores/authStore";

// Initialize Alpine.js for micro-interactions
window.Alpine = Alpine;
Alpine.start();

// Create Pinia store
const pinia = createPinia();

// Make SweetAlert2 available on window for non-Vue usage
window.$swal = canzimSwal;
window.$toast = Toast;

// Check if landing page element exists
const landingPageElement = document.getElementById("landing-page");

if (landingPageElement) {
    // Create Vue app instance for Landing Page
    const landingApp = createApp(LandingPage);

    // Register Pinia
    landingApp.use(pinia);

    // Make SweetAlert2 globally available
    landingApp.config.globalProperties.$swal = canzimSwal;
    landingApp.config.globalProperties.$toast = Toast;

    // Mount Landing Page
    landingApp.mount("#landing-page");

    // Initialize auth store
    const authStore = useAuthStore();
    authStore.initializeAuth();
}

// Check if dashboard element exists
const dashboardElement = document.getElementById("dashboard-app");

if (dashboardElement) {
    // Create Vue app instance for Dashboard
    const dashboardApp = createApp(Dashboard);

    // Register Pinia
    dashboardApp.use(pinia);

    // Make SweetAlert2 globally available
    dashboardApp.config.globalProperties.$swal = canzimSwal;
    dashboardApp.config.globalProperties.$toast = Toast;

    // Mount Dashboard
    dashboardApp.mount("#dashboard-app");

    // Initialize auth store
    const authStore = useAuthStore();
    authStore.initializeAuth();
}

// Check if users element exists
const usersElement = document.getElementById("users-app");

if (usersElement) {
    // Create Vue app instance for Users
    const usersApp = createApp(Users);

    // Register Pinia
    usersApp.use(pinia);

    // Make SweetAlert2 globally available
    usersApp.config.globalProperties.$swal = canzimSwal;
    usersApp.config.globalProperties.$toast = Toast;

    // Mount Users
    usersApp.mount("#users-app");

    // Initialize auth store
    const authStore = useAuthStore();
    authStore.initializeAuth();
}

// Check if activity logs element exists
const activityLogsElement = document.getElementById("activity-logs-app");

if (activityLogsElement) {
    // Create Vue app instance for Activity Logs
    const activityLogsApp = createApp(ActivityLogs);

    // Register Pinia
    activityLogsApp.use(pinia);

    // Make SweetAlert2 globally available
    activityLogsApp.config.globalProperties.$swal = canzimSwal;
    activityLogsApp.config.globalProperties.$toast = Toast;

    // Mount Activity Logs
    activityLogsApp.mount("#activity-logs-app");

    // Initialize auth store
    const authStore = useAuthStore();
    authStore.initializeAuth();
}

// Check if profile element exists
const profileElement = document.getElementById("profile-app");

if (profileElement) {
    // Create Vue app instance for Profile
    const profileApp = createApp(Profile);

    // Register Pinia
    profileApp.use(pinia);

    // Make SweetAlert2 globally available
    profileApp.config.globalProperties.$swal = canzimSwal;
    profileApp.config.globalProperties.$toast = Toast;

    // Mount Profile
    profileApp.mount("#profile-app");

    // Initialize auth store
    const authStore = useAuthStore();
    authStore.initializeAuth();
}

// Export for use in other modules
export { pinia };
