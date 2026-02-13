<template>
    <div class="space-y-6">
        <form @submit.prevent="saveSettings">
            <div class="grid grid-cols-1 gap-6">
                <!-- Email Notifications -->
                <div>
                    <div
                        class="flex items-center justify-between rounded-lg border border-gray-300 p-4"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <i
                                    class="fas fa-envelope mr-2 text-blue-600"
                                ></i>
                                Email Notifications
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                Receive notifications via email for important
                                system events
                            </p>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                v-model="form.enable_email_notifications"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                            ></div>
                        </label>
                    </div>
                </div>

                <!-- Slack Notifications -->
                <div>
                    <div
                        class="flex items-center justify-between rounded-lg border border-gray-300 p-4"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <i
                                    class="fab fa-slack mr-2 text-purple-600"
                                ></i>
                                Slack Notifications
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                Send notifications to Slack channels for team
                                collaboration
                            </p>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                v-model="form.enable_slack_notifications"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                            ></div>
                        </label>
                    </div>
                </div>

                <!-- Database Notifications -->
                <div>
                    <div
                        class="flex items-center justify-between rounded-lg border border-gray-300 p-4"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <i
                                    class="fas fa-database mr-2 text-green-600"
                                ></i>
                                In-App Notifications
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                Store notifications in database for in-app
                                display
                            </p>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                v-model="form.enable_database_notifications"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                            ></div>
                        </label>
                    </div>
                </div>

                <!-- SMS Notifications -->
                <div>
                    <div
                        class="flex items-center justify-between rounded-lg border border-gray-300 p-4"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <i class="fas fa-sms mr-2 text-orange-600"></i>
                                SMS Notifications
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                Send SMS alerts for critical system events
                                (requires SMS gateway)
                            </p>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                v-model="form.enable_sms_notifications"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                            ></div>
                        </label>
                    </div>
                </div>

                <!-- Push Notifications -->
                <div>
                    <div
                        class="flex items-center justify-between rounded-lg border border-gray-300 p-4"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <i class="fas fa-bell mr-2 text-red-600"></i>
                                Push Notifications
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                Browser push notifications for real-time alerts
                            </p>
                        </div>
                        <label
                            class="relative inline-flex cursor-pointer items-center"
                        >
                            <input
                                v-model="form.enable_push_notifications"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                            ></div>
                        </label>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium">Notification Channels</p>
                            <p class="mt-1 text-xs">
                                Enable the notification channels you want to
                                use. Some channels may require additional
                                configuration (e.g., Slack webhook URL, SMS
                                gateway credentials). These settings control
                                system-wide notification delivery, but users can
                                customize their individual notification
                                preferences in their profile settings.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div
                    class="flex justify-end gap-3 border-t border-gray-200 pt-4"
                >
                    <button
                        type="button"
                        @click="resetForm"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <i
                            :class="
                                saving
                                    ? 'fas fa-spinner fa-spin'
                                    : 'fas fa-save'
                            "
                        ></i>
                        {{ saving ? "Saving..." : "Save Changes" }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import api from "../../api.js";
import Swal from "sweetalert2";

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update"]);

const form = ref({
    enable_email_notifications: false,
    enable_slack_notifications: false,
    enable_database_notifications: false,
    enable_sms_notifications: false,
    enable_push_notifications: false,
});

const errors = ref({});
const saving = ref(false);

watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings.notifications) {
            // Convert string booleans to actual booleans
            const notifications = { ...newSettings.notifications };
            notifications.enable_email_notifications =
                notifications.enable_email_notifications === true ||
                notifications.enable_email_notifications === "true" ||
                notifications.enable_email_notifications === 1;
            notifications.enable_slack_notifications =
                notifications.enable_slack_notifications === true ||
                notifications.enable_slack_notifications === "true" ||
                notifications.enable_slack_notifications === 1;
            notifications.enable_database_notifications =
                notifications.enable_database_notifications === true ||
                notifications.enable_database_notifications === "true" ||
                notifications.enable_database_notifications === 1;
            notifications.enable_sms_notifications =
                notifications.enable_sms_notifications === true ||
                notifications.enable_sms_notifications === "true" ||
                notifications.enable_sms_notifications === 1;
            notifications.enable_push_notifications =
                notifications.enable_push_notifications === true ||
                notifications.enable_push_notifications === "true" ||
                notifications.enable_push_notifications === 1;
            form.value = notifications;
        }
    },
    { immediate: true, deep: true },
);

const saveSettings = async () => {
    saving.value = true;
    errors.value = {};

    try {
        await api.put("/settings/notifications", form.value);

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Notification settings saved successfully!",
            timer: 2000,
            showConfirmButton: false,
        });

        emit("update");
    } catch (error) {
        console.error("Error saving settings:", error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to save settings. Please try again.",
        });
    } finally {
        saving.value = false;
    }
};

const resetForm = () => {
    if (props.settings.notifications) {
        const notifications = { ...props.settings.notifications };
        notifications.enable_email_notifications =
            notifications.enable_email_notifications === true ||
            notifications.enable_email_notifications === "true" ||
            notifications.enable_email_notifications === 1;
        notifications.enable_slack_notifications =
            notifications.enable_slack_notifications === true ||
            notifications.enable_slack_notifications === "true" ||
            notifications.enable_slack_notifications === 1;
        notifications.enable_database_notifications =
            notifications.enable_database_notifications === true ||
            notifications.enable_database_notifications === "true" ||
            notifications.enable_database_notifications === 1;
        notifications.enable_sms_notifications =
            notifications.enable_sms_notifications === true ||
            notifications.enable_sms_notifications === "true" ||
            notifications.enable_sms_notifications === 1;
        notifications.enable_push_notifications =
            notifications.enable_push_notifications === true ||
            notifications.enable_push_notifications === "true" ||
            notifications.enable_push_notifications === 1;
        form.value = notifications;
    }
    errors.value = {};
};
</script>
