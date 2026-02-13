import { createApp } from "vue";
import { createPinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";
import ActivityLogs from "./pages/ActivityLogs.vue";
import { useAuthStore } from "./stores/authStore";
import SessionLockPlugin from "./plugins/sessionLock";

const pinia = createPinia();

// Create a minimal router
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/dashboard/activity-logs",
            name: "activity-logs",
            component: ActivityLogs,
        },
    ],
});

// Unmount any existing app instance
const container = document.querySelector("#activity-logs-app");
if (container && container.__vue_app__) {
    container.__vue_app__.unmount();
}

const app = createApp(ActivityLogs);

app.use(pinia);
app.use(router);
app.use(SessionLockPlugin);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

// Initialize auth state from localStorage and wait for it to complete
const authStore = useAuthStore();
authStore.initializeAuth();

// Mount the app after auth is initialized
app.mount("#activity-logs-app");
