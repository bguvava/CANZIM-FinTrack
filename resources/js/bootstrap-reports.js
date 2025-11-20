import { createApp } from "vue";
import { createPinia } from "pinia";
import Reports from "./pages/Reports.vue";
import { useAuthStore } from "./stores/authStore";

// Import FontAwesome
import "@fortawesome/fontawesome-free/css/all.css";

const pinia = createPinia();
const app = createApp(Reports);

app.use(pinia);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#reports-app");
