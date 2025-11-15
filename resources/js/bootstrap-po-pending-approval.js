import { createApp } from "vue";
import { createPinia } from "pinia";
import POPendingApproval from "./pages/POPendingApproval.vue";
import { useAuthStore } from "./stores/authStore";

const pinia = createPinia();
const app = createApp(POPendingApproval);

app.use(pinia);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#po-pending-approval-app");
