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

// Initialize Alpine.js for micro-interactions
window.Alpine = Alpine;
Alpine.start();

// Create Pinia store
const pinia = createPinia();

// Create Vue app instance
const app = createApp({});

// Register Pinia
app.use(pinia);

// Make SweetAlert2 globally available
app.config.globalProperties.$swal = canzimSwal;
app.config.globalProperties.$toast = Toast;

// Make SweetAlert2 available on window for non-Vue usage
window.$swal = canzimSwal;
window.$toast = Toast;

// Mount Vue app (will be used for components)
// app.mount('#app');

// Export for use in other modules
export { app, pinia };
