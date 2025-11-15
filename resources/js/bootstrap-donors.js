import { createApp } from "vue";
import { createPinia } from "pinia";
import Donors from "./pages/Donors.vue";
import { useAuthStore } from "./stores/authStore";

const pinia = createPinia();
const app = createApp(Donors);

app.use(pinia);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#donors-app");
