import { createApp } from "vue";
import { createPinia } from "pinia";
import Expenses from "./pages/Expenses.vue";
import { useAuthStore } from "./stores/authStore";

const pinia = createPinia();
const app = createApp(Expenses);

app.use(pinia);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

// Initialize auth state from localStorage and wait for it to complete
const authStore = useAuthStore();
authStore.initializeAuth();

// Mount the app after auth is initialized
app.mount("#expenses-app");
