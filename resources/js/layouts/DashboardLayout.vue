<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <Sidebar :pending-expenses-count="pendingExpensesCount" />

        <!-- Main Content Area -->
        <div
            :class="[
                'transition-all duration-300',
                sidebarCollapsed ? 'ml-16' : 'ml-64',
            ]"
        >
            <!-- Top Header Bar -->
            <header
                class="bg-white border-b border-gray-200 h-16 fixed top-0 right-0 z-40"
                :style="{ left: sidebarCollapsed ? '4rem' : '16rem' }"
            >
                <div class="h-full px-6 flex items-center justify-between">
                    <!-- Search Bar -->
                    <div class="flex-1 max-w-2xl">
                        <div class="relative">
                            <i
                                class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search... (Ctrl+K)"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @keydown.ctrl.k.prevent="focusSearch"
                            />
                        </div>
                    </div>

                    <!-- Right Side: Notifications & User Menu -->
                    <div class="flex items-center gap-4 ml-6">
                        <!-- Notifications -->
                        <div class="relative">
                            <button
                                @click="toggleNotifications"
                                class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                title="Notifications"
                            >
                                <i class="fas fa-bell text-xl"></i>
                                <span
                                    v-if="notificationCount > 0"
                                    class="absolute top-1 right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
                                >
                                    {{
                                        notificationCount > 9
                                            ? "9+"
                                            : notificationCount
                                    }}
                                </span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div
                                v-if="showNotifications"
                                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                                @click.stop
                            >
                                <div class="px-4 py-2 border-b border-gray-200">
                                    <h3 class="font-semibold text-gray-800">
                                        Notifications
                                    </h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <div
                                        v-if="notifications.length === 0"
                                        class="px-4 py-8 text-center text-gray-500"
                                    >
                                        <i
                                            class="fas fa-bell-slash text-3xl mb-2"
                                        ></i>
                                        <p>No new notifications</p>
                                    </div>
                                    <a
                                        v-for="notification in notifications"
                                        :key="notification.id"
                                        href="#"
                                        class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0"
                                    >
                                        <p
                                            class="text-sm font-medium text-gray-800"
                                        >
                                            {{ notification.title }}
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            {{ notification.message }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ notification.time }}
                                        </p>
                                    </a>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-200">
                                    <a
                                        href="/dashboard/notifications"
                                        class="text-sm text-blue-600 hover:text-blue-700"
                                    >
                                        View all notifications
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="relative">
                            <button
                                @click="toggleUserMenu"
                                class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <div
                                    class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold"
                                >
                                    {{ userInitials }}
                                </div>
                                <div class="text-left hidden md:block">
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        {{ userName }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ userRoleName }}
                                    </p>
                                </div>
                                <i
                                    class="fas fa-chevron-down text-gray-600 text-sm"
                                ></i>
                            </button>

                            <!-- User Dropdown Menu -->
                            <div
                                v-if="showUserMenu"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                                @click.stop
                            >
                                <a
                                    href="/dashboard/profile"
                                    class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 text-gray-700"
                                >
                                    <i class="fas fa-user-circle w-5"></i>
                                    <span>My Profile</span>
                                </a>
                                <a
                                    href="/dashboard/settings"
                                    v-if="authStore.isProgramsManager"
                                    class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 text-gray-700"
                                >
                                    <i class="fas fa-cog w-5"></i>
                                    <span>Settings</span>
                                </a>
                                <a
                                    href="/dashboard/messages"
                                    class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 text-gray-700"
                                >
                                    <i class="fas fa-envelope w-5"></i>
                                    <span>Messages</span>
                                </a>
                                <div
                                    class="border-t border-gray-200 my-2"
                                ></div>
                                <button
                                    @click="handleLogout"
                                    class="w-full flex items-center gap-3 px-4 py-2 hover:bg-red-50 text-red-600"
                                >
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>Logout</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb Navigation -->
            <div class="pt-16 px-6 py-4 bg-white border-b border-gray-200">
                <nav class="flex items-center gap-2 text-sm">
                    <a
                        href="/dashboard"
                        class="text-gray-600 hover:text-blue-600"
                    >
                        <i class="fas fa-home"></i>
                    </a>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    <span v-for="(crumb, index) in breadcrumbs" :key="index">
                        <a
                            v-if="index < breadcrumbs.length - 1"
                            :href="crumb.path"
                            class="text-gray-600 hover:text-blue-600"
                        >
                            {{ crumb.label }}
                        </a>
                        <span v-else class="text-gray-800 font-medium">
                            {{ crumb.label }}
                        </span>
                        <i
                            v-if="index < breadcrumbs.length - 1"
                            class="fas fa-chevron-right text-gray-400 text-xs mx-2"
                        ></i>
                    </span>
                </nav>
            </div>

            <!-- Main Content Area -->
            <main class="p-6 pb-16">
                <slot></slot>
            </main>

            <!-- Footer -->
            <footer
                class="fixed bottom-0 right-0 bg-white border-t border-gray-200 px-6 py-3 transition-all duration-300"
                :class="sidebarCollapsed ? 'left-16' : 'left-64'"
            >
                <p class="text-xs text-gray-500 text-center">
                    All rights reserved. Developed with ❤️ by bguvava.
                </p>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useAuthStore } from "../stores/authStore";
import Sidebar from "../components/Sidebar.vue";

// Auth Store
const authStore = useAuthStore();

// State
const searchQuery = ref("");
const showNotifications = ref(false);
const showUserMenu = ref(false);
const sidebarCollapsed = ref(false);
const pendingExpensesCount = ref(0);
const notificationCount = ref(0);
const notifications = ref([]);
const breadcrumbs = ref([]);

// Computed properties
const userName = computed(() => {
    const user = authStore.currentUser;
    return user?.name || "User";
});

const userInitials = computed(() => {
    const user = authStore.currentUser;
    if (!user?.name) return "U";

    const parts = user.name.split(" ");
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return user.name.substring(0, 2).toUpperCase();
});

const userRoleName = computed(() => {
    const role = authStore.currentUser?.role?.name;
    return role || "User";
});

// Toggle functions
const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    showUserMenu.value = false;
};

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
    showNotifications.value = false;
};

// Focus search input
const focusSearch = () => {
    document.querySelector('input[type="text"]').focus();
};

// Handle logout
const handleLogout = async () => {
    showUserMenu.value = false;
    await authStore.logout();
};

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest(".relative")) {
        showNotifications.value = false;
        showUserMenu.value = false;
    }
};

// Initialize sidebar collapsed state
const initializeSidebarState = () => {
    const savedState = localStorage.getItem("sidebar_collapsed");
    sidebarCollapsed.value = savedState === "true";
};

// Watch for sidebar state changes
const watchSidebarState = () => {
    const interval = setInterval(() => {
        const savedState = localStorage.getItem("sidebar_collapsed");
        sidebarCollapsed.value = savedState === "true";
    }, 100);

    return () => clearInterval(interval);
};

// Generate breadcrumbs from current path
const generateBreadcrumbs = () => {
    const path = window.location.pathname;
    const segments = path.split("/").filter((segment) => segment !== "");

    // Remove 'dashboard' from segments
    const filteredSegments = segments.filter(
        (segment) => segment !== "dashboard",
    );

    breadcrumbs.value = filteredSegments.map((segment, index) => {
        const path =
            "/dashboard/" + filteredSegments.slice(0, index + 1).join("/");
        const label = segment
            .split("-")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(" ");

        return { path, label };
    });
};

// Keyboard shortcuts
const handleKeyboardShortcuts = (event) => {
    // Ctrl+K for search
    if (event.ctrlKey && event.key === "k") {
        event.preventDefault();
        focusSearch();
    }
};

// Lifecycle hooks
onMounted(() => {
    initializeSidebarState();
    const cleanupWatcher = watchSidebarState();
    generateBreadcrumbs();

    document.addEventListener("click", handleClickOutside);
    document.addEventListener("keydown", handleKeyboardShortcuts);

    // Cleanup on unmount
    onUnmounted(() => {
        cleanupWatcher();
        document.removeEventListener("click", handleClickOutside);
        document.removeEventListener("keydown", handleKeyboardShortcuts);
    });
});
</script>
