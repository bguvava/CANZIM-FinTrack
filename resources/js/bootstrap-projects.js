import { createApp } from "vue";
import { createPinia } from "pinia";
import Projects from "./pages/Projects.vue";
import { useAuthStore } from "./stores/authStore";
import { canzimSwal, Toast } from "./plugins/sweetalert";
import SessionLockPlugin from "./plugins/sessionLock";

// Make SweetAlert2 available globally
window.Swal = canzimSwal;
window.$swal = canzimSwal;
window.$toast = Toast;

const pinia = createPinia();
const app = createApp(Projects);

app.use(pinia);
app.use(SessionLockPlugin);
app.config.globalProperties.$swal = canzimSwal;
app.config.globalProperties.$toast = Toast;

// Initialize auth state from localStorage and wait for it to complete
const authStore = useAuthStore();
authStore.initializeAuth();

// Mount the app after auth is initialized
app.mount("#projects-app");
