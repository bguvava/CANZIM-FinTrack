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

// Export for use in other modules
export { pinia };
