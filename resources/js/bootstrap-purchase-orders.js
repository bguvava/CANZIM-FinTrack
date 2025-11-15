import { createApp } from "vue";
import { createPinia } from "pinia";
import PurchaseOrders from "./pages/PurchaseOrders.vue";
import { useAuthStore } from "./stores/authStore";

const pinia = createPinia();
const app = createApp(PurchaseOrders);

app.use(pinia);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#purchase-orders-app");
