<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
            @click="closeModal"
        ></div>

        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div
                class="relative w-full max-w-4xl transform rounded-lg bg-white shadow-xl transition-all"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <div class="flex items-center gap-4">
                        <!-- Avatar -->
                        <div
                            v-if="user?.avatar_url"
                            class="h-12 w-12 overflow-hidden rounded-full"
                        >
                            <img
                                :src="user.avatar_url"
                                :alt="user.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-800 text-white font-semibold"
                        >
                            {{ user?.initials }}
                        </div>
                        <div>
                            <h3
                                id="modal-title"
                                class="text-lg font-semibold text-gray-900"
                            >
                                {{ user?.name }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ user?.email }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="flex px-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'details'"
                            :class="[
                                'border-b-2 px-4 py-3 text-sm font-medium transition-colors',
                                activeTab === 'details'
                                    ? 'border-blue-800 text-blue-800'
                                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-700',
                            ]"
                        >
                            <i class="fas fa-user mr-2"></i>
                            Details
                        </button>
                        <button
                            @click="activeTab = 'activity'"
                            :class="[
                                'border-b-2 px-4 py-3 text-sm font-medium transition-colors',
                                activeTab === 'activity'
                                    ? 'border-blue-800 text-blue-800'
                                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-700',
                            ]"
                        >
                            <i class="fas fa-history mr-2"></i>
                            Activity Logs
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div
                    class="px-6 py-4"
                    style="max-height: 60vh; overflow-y: auto"
                >
                    <!-- Details Tab -->
                    <div v-show="activeTab === 'details'">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Full Name
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ user?.name || "N/A" }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Email Address
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ user?.email || "N/A" }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Phone Number
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ user?.phone_number || "N/A" }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Office Location
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ user?.office_location || "N/A" }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Role
                                </label>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800"
                                    >
                                        {{ user?.role?.name || "N/A" }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Status
                                </label>
                                <p class="mt-1">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            user?.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{ user?.status || "N/A" }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Last Login
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(user?.last_login_at) }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Member Since
                                </label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(user?.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Tab -->
                    <div v-show="activeTab === 'activity'">
                        <div v-if="loadingActivity" class="py-8 text-center">
                            <i
                                class="fas fa-spinner fa-spin text-3xl text-blue-800"
                            ></i>
                            <p class="mt-2 text-sm text-gray-600">
                                Loading activity logs...
                            </p>
                        </div>

                        <div
                            v-else-if="activityLogs.length === 0"
                            class="py-8 text-center"
                        >
                            <i
                                class="fas fa-history text-4xl text-gray-400"
                            ></i>
                            <p class="mt-2 text-sm font-medium text-gray-900">
                                No activity logs
                            </p>
                            <p class="text-sm text-gray-600">
                                No recent activity for this user
                            </p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="log in activityLogs"
                                :key="log.id"
                                class="rounded-lg border border-gray-200 p-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800"
                                            >
                                                {{ log.activity_type }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ log.created_at_human }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ log.description }}
                                        </p>
                                        <p
                                            v-if="log.ip_address"
                                            class="mt-1 text-xs text-gray-500"
                                        >
                                            IP: {{ log.ip_address }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Load More Button -->
                            <button
                                v-if="
                                    activityPagination.current_page <
                                    activityPagination.last_page
                                "
                                @click="loadMoreActivity"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                                :disabled="loadingActivity"
                            >
                                <i
                                    :class="
                                        loadingActivity
                                            ? 'fas fa-spinner fa-spin'
                                            : 'fas fa-chevron-down'
                                    "
                                    class="mr-2"
                                ></i>
                                {{
                                    loadingActivity ? "Loading..." : "Load More"
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t border-gray-200 px-6 py-4"
                >
                    <button
                        @click="emit('edit', user)"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        Edit User
                    </button>
                    <button
                        @click="closeModal"
                        class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-900"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import api from "../../api";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "edit"]);

// State
const activeTab = ref("details");
const loadingActivity = ref(false);
const activityLogs = ref([]);
const activityPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
});

// Watch for user changes and tab switches
watch(
    [() => props.user, () => props.isOpen, () => activeTab.value],
    ([newUser, isOpen, tab]) => {
        if (
            isOpen &&
            newUser &&
            tab === "activity" &&
            activityLogs.value.length === 0
        ) {
            loadActivity();
        }
    },
);

// Reset when modal closes
watch(
    () => props.isOpen,
    (newValue) => {
        if (!newValue) {
            activeTab.value = "details";
            activityLogs.value = [];
            activityPagination.value = {
                current_page: 1,
                last_page: 1,
                per_page: 10,
            };
        }
    },
);

// Methods
const closeModal = () => {
    emit("close");
};

const loadActivity = async () => {
    if (!props.user || loadingActivity.value) return;

    loadingActivity.value = true;

    try {
        const response = await api.get(`/users/${props.user.id}/activity`, {
            params: {
                page: 1,
                per_page: 10,
            },
        });

        activityLogs.value = response.data.data;
        activityPagination.value = response.data.meta;
    } catch (error) {
        console.error("Failed to load activity logs:", error);
    } finally {
        loadingActivity.value = false;
    }
};

const loadMoreActivity = async () => {
    if (!props.user || loadingActivity.value) return;

    loadingActivity.value = true;

    try {
        const response = await api.get(`/users/${props.user.id}/activity`, {
            params: {
                page: activityPagination.value.current_page + 1,
                per_page: 10,
            },
        });

        activityLogs.value.push(...response.data.data);
        activityPagination.value = response.data.meta;
    } catch (error) {
        console.error("Failed to load more activity logs:", error);
    } finally {
        loadingActivity.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";

    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? "s" : ""} ago`;
    if (diffHours < 24)
        return `${diffHours} hour${diffHours > 1 ? "s" : ""} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? "s" : ""} ago`;

    return date.toLocaleDateString();
};
</script>
