import { createApp } from "vue";
import { createPinia } from "pinia";
import Transactions from "./pages/Transactions.vue";
import { useAuthStore } from "./stores/authStore";

const pinia = createPinia();
const app = createApp(Transactions);

app.use(pinia);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#transactions-app");
