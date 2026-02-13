import { createApp } from "vue";
import { createPinia } from "pinia";
import ProjectsShow from "./pages/ProjectsShow.vue";
import { useAuthStore } from "./stores/authStore";
import SessionLockPlugin from "./plugins/sessionLock";

const pinia = createPinia();
const app = createApp(ProjectsShow);

app.use(pinia);
app.use(SessionLockPlugin);
app.config.globalProperties.$swal = window.$swal;
app.config.globalProperties.$toast = window.$toast;

const authStore = useAuthStore();
authStore.initializeAuth();

app.mount("#projects-show-app");
