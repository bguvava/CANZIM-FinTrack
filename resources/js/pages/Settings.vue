<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        System Settings
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage system configuration and preferences
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="clearAllCaches"
                        :disabled="clearingCache"
                        class="flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-gray-700 disabled:opacity-50"
                    >
                        <i
                            :class="
                                clearingCache
                                    ? 'fas fa-spinner fa-spin'
                                    : 'fas fa-broom'
                            "
                        ></i>
                        {{ clearingCache ? "Clearing..." : "Clear Cache" }}
                    </button>
                    <button
                        @click="loadSystemHealth"
                        :disabled="loadingHealth"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
                    >
                        <i
                            :class="
                                loadingHealth
                                    ? 'fas fa-spinner fa-spin'
                                    : 'fas fa-heartbeat'
                            "
                        ></i>
                        {{ loadingHealth ? "Checking..." : "System Health" }}
                    </button>
                </div>
            </div>

            <!-- System Health Alert (if loaded) -->
            <div
                v-if="systemHealth"
                class="rounded-lg border border-blue-200 bg-blue-50 p-4"
            >
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <p class="text-xs text-blue-700">Disk Usage</p>
                        <p class="text-lg font-semibold text-blue-900">
                            {{ systemHealth.disk_usage }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-700">Database Size</p>
                        <p class="text-lg font-semibold text-blue-900">
                            {{ systemHealth.database_size }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-700">Cache Status</p>
                        <p class="text-lg font-semibold text-blue-900">
                            {{
                                systemHealth.cache_status
                                    ? "Active"
                                    : "Inactive"
                            }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-700">Last Backup</p>
                        <p class="text-lg font-semibold text-blue-900">
                            {{ systemHealth.last_backup || "Never" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="rounded-lg bg-white shadow">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                activeTab === tab.key
                                    ? 'border-blue-600 text-blue-800'
                                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-800',
                                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors',
                            ]"
                        >
                            <i :class="tab.icon + ' mr-2'"></i>
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <component
                        :is="activeTabComponent"
                        :settings="settings"
                        @update="loadSettings"
                    />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import OrganizationSettingsTab from "../components/settings/OrganizationSettingsTab.vue";
import FinancialSettingsTab from "../components/settings/FinancialSettingsTab.vue";
import EmailSettingsTab from "../components/settings/EmailSettingsTab.vue";
import SecuritySettingsTab from "../components/settings/SecuritySettingsTab.vue";
import NotificationSettingsTab from "../components/settings/NotificationSettingsTab.vue";
import api from "../api.js";
import Swal from "sweetalert2";

const activeTab = ref("organization");
const settings = ref({});
const systemHealth = ref(null);
const clearingCache = ref(false);
const loadingHealth = ref(false);

const tabs = [
    {
        key: "organization",
        label: "Organization",
        icon: "fas fa-building",
    },
    {
        key: "financial",
        label: "Financial",
        icon: "fas fa-dollar-sign",
    },
    {
        key: "email",
        label: "Email",
        icon: "fas fa-envelope",
    },
    {
        key: "security",
        label: "Security",
        icon: "fas fa-shield-alt",
    },
    {
        key: "notifications",
        label: "Notifications",
        icon: "fas fa-bell",
    },
];

const activeTabComponent = computed(() => {
    const components = {
        organization: OrganizationSettingsTab,
        financial: FinancialSettingsTab,
        email: EmailSettingsTab,
        security: SecuritySettingsTab,
        notifications: NotificationSettingsTab,
    };
    return components[activeTab.value];
});

const loadSettings = async () => {
    try {
        const response = await api.get("/settings");
        settings.value = response.data.data;
    } catch (error) {
        console.error("Error loading settings:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to load settings. Please try again.",
        });
    }
};

const loadSystemHealth = async () => {
    loadingHealth.value = true;
    try {
        const response = await api.get("/settings/system-health");
        systemHealth.value = response.data.data;
        Swal.fire({
            icon: "success",
            title: "System Health",
            html: `
                <div class="text-left space-y-2">
                    <p><strong>Disk Usage:</strong> ${systemHealth.value.disk_usage}</p>
                    <p><strong>Database Size:</strong> ${systemHealth.value.database_size}</p>
                    <p><strong>Cache:</strong> ${systemHealth.value.cache_status ? "Active" : "Inactive"}</p>
                    <p><strong>Last Backup:</strong> ${systemHealth.value.last_backup || "Never"}</p>
                </div>
            `,
            timer: 5000,
        });
    } catch (error) {
        console.error("Error loading system health:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to load system health. Please try again.",
        });
    } finally {
        loadingHealth.value = false;
    }
};

const clearAllCaches = async () => {
    const result = await Swal.fire({
        title: "Clear All Caches?",
        text: "This will clear all application caches. Continue?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#1E40AF",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, clear caches",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        clearingCache.value = true;
        try {
            await api.post("/settings/clear-cache");
            Swal.fire({
                icon: "success",
                title: "Success",
                text: "All caches cleared successfully!",
                timer: 2000,
                showConfirmButton: false,
            });
        } catch (error) {
            console.error("Error clearing cache:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error.response?.data?.message ||
                    "Failed to clear caches. Please try again.",
            });
        } finally {
            clearingCache.value = false;
        }
    }
};

onMounted(() => {
    loadSettings();
});
</script>
