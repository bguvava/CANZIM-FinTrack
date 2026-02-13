import { createApp } from "vue";
import { createPinia } from "pinia";
import ExpensesEdit from "./pages/ExpensesEdit.vue";
import { useAuthStore } from "./stores/authStore";
import SessionLockPlugin from "./plugins/sessionLock";

const pinia = createPinia();
const app = createApp(ExpensesEdit);

app.use(pinia);
app.use(SessionLockPlugin);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#expenses-app");
