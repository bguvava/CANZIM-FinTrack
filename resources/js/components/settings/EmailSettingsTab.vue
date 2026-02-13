<template>
    <div class="space-y-6">
        <form @submit.prevent="saveSettings">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- SMTP Host -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        SMTP Host
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.mail_host"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.mail_host }"
                        placeholder="smtp.gmail.com"
                    />
                    <p
                        v-if="errors.mail_host"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.mail_host[0] }}
                    </p>
                </div>

                <!-- SMTP Port -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        SMTP Port
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model.number="form.mail_port"
                        type="number"
                        required
                        min="1"
                        max="65535"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.mail_port }"
                        placeholder="587"
                    />
                    <p
                        v-if="errors.mail_port"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.mail_port[0] }}
                    </p>
                </div>

                <!-- SMTP Username -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        SMTP Username
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.mail_username"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.mail_username }"
                        placeholder="your-email@example.com"
                    />
                    <p
                        v-if="errors.mail_username"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.mail_username[0] }}
                    </p>
                </div>

                <!-- SMTP Password -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        SMTP Password
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.mail_password"
                        type="password"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.mail_password }"
                        placeholder="••••••••"
                    />
                    <p
                        v-if="errors.mail_password"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.mail_password[0] }}
                    </p>
                </div>

                <!-- SMTP Encryption -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Encryption
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.mail_encryption"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.mail_encryption }"
                    >
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">None</option>
                    </select>
                    <p
                        v-if="errors.mail_encryption"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.mail_encryption[0] }}
                    </p>
                </div>

                <!-- From Address -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        From Email Address
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.mail_from_address"
                        type="email"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.mail_from_address }"
                        placeholder="noreply@canzimbabwe.org.zw"
                    />
                    <p
                        v-if="errors.mail_from_address"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.mail_from_address[0] }}
                    </p>
                </div>

                <!-- From Name -->
                <div class="md:col-span-2">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        From Name
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.mail_from_name"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.mail_from_name }"
                        placeholder="Climate Action Network Zimbabwe"
                    />
                    <p
                        v-if="errors.mail_from_name"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.mail_from_name[0] }}
                    </p>
                </div>

                <!-- Info Box -->
                <div class="md:col-span-2">
                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                    >
                        <div class="flex items-start gap-3">
                            <i
                                class="fas fa-info-circle text-blue-600 mt-0.5"
                            ></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium">
                                    Email Configuration Tips
                                </p>
                                <ul class="mt-2 space-y-1 text-xs">
                                    <li>
                                        • For Gmail: Enable "Less secure app
                                        access" or use App Password
                                    </li>
                                    <li>
                                        • Common ports: 587 (TLS), 465 (SSL), 25
                                        (no encryption)
                                    </li>
                                    <li>
                                        • Test email delivery after saving
                                        changes
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div
                    class="md:col-span-2 flex justify-end gap-3 border-t border-gray-200 pt-4"
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
    mail_host: "",
    mail_port: 587,
    mail_username: "",
    mail_password: "",
    mail_encryption: "tls",
    mail_from_address: "",
    mail_from_name: "",
});

const errors = ref({});
const saving = ref(false);

watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings.email) {
            form.value = { ...newSettings.email };
        }
    },
    { immediate: true, deep: true },
);

const saveSettings = async () => {
    saving.value = true;
    errors.value = {};

    try {
        await api.put("/settings/email", form.value);

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Email settings saved successfully!",
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
    if (props.settings.email) {
        form.value = { ...props.settings.email };
    }
    errors.value = {};
};
</script>
