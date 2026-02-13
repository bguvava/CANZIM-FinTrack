import { createApp } from "vue";
import { createPinia } from "pinia";
import Documents from "./pages/Documents.vue";
import { useAuthStore } from "./stores/authStore";
import SessionLockPlugin from "./plugins/sessionLock";

// Import FontAwesome
import "@fortawesome/fontawesome-free/css/all.css";

const pinia = createPinia();
const app = createApp(Documents);

app.use(pinia);
app.use(SessionLockPlugin);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#documents-app");
