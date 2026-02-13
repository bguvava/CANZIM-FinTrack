<template>
    <div class="space-y-6">
        <form @submit.prevent="saveSettings">
            <div class="grid grid-cols-1 gap-6">
                <!-- Session Timeout -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Session Timeout (minutes)
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model.number="form.session_timeout"
                        type="number"
                        required
                        min="1"
                        max="1440"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.session_timeout }"
                    />
                    <p class="mt-1 text-xs text-gray-600">
                        Current value: {{ form.session_timeout }} minutes
                        (auto-logout after inactivity)
                    </p>
                    <p
                        v-if="errors.session_timeout"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.session_timeout[0] }}
                    </p>
                </div>

                <!-- Password Requirements -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Password Requirements
                    </label>
                    <div
                        class="space-y-3 rounded-lg border border-gray-300 p-4"
                    >
                        <!-- Minimum Length -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Minimum Length
                                </p>
                                <p class="text-xs text-gray-600">
                                    Require at least
                                    {{ form.password_min_length }} characters
                                </p>
                            </div>
                            <input
                                v-model.number="form.password_min_length"
                                type="number"
                                min="6"
                                max="20"
                                class="w-20 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            />
                        </div>

                        <!-- Require Uppercase -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Require Uppercase Letter
                                </p>
                                <p class="text-xs text-gray-600">
                                    Password must contain at least one uppercase
                                    letter
                                </p>
                            </div>
                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >
                                <input
                                    v-model="form.password_require_uppercase"
                                    type="checkbox"
                                    class="peer sr-only"
                                />
                                <div
                                    class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                                ></div>
                            </label>
                        </div>

                        <!-- Require Lowercase -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Require Lowercase Letter
                                </p>
                                <p class="text-xs text-gray-600">
                                    Password must contain at least one lowercase
                                    letter
                                </p>
                            </div>
                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >
                                <input
                                    v-model="form.password_require_lowercase"
                                    type="checkbox"
                                    class="peer sr-only"
                                />
                                <div
                                    class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                                ></div>
                            </label>
                        </div>

                        <!-- Require Numbers -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Require Numbers
                                </p>
                                <p class="text-xs text-gray-600">
                                    Password must contain at least one number
                                </p>
                            </div>
                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >
                                <input
                                    v-model="form.password_require_numbers"
                                    type="checkbox"
                                    class="peer sr-only"
                                />
                                <div
                                    class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                                ></div>
                            </label>
                        </div>

                        <!-- Require Special Characters -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Require Special Characters
                                </p>
                                <p class="text-xs text-gray-600">
                                    Password must contain at least one special
                                    character (@, #, $, etc.)
                                </p>
                            </div>
                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >
                                <input
                                    v-model="form.password_require_special"
                                    type="checkbox"
                                    class="peer sr-only"
                                />
                                <div
                                    class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"
                                ></div>
                            </label>
                        </div>
                    </div>
                    <p
                        v-if="errors.password_min_length"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.password_min_length[0] }}
                    </p>
                </div>

                <!-- Account Lockout -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Account Lockout Settings
                    </label>
                    <div
                        class="grid grid-cols-1 gap-4 rounded-lg border border-gray-300 p-4 md:grid-cols-2"
                    >
                        <!-- Max Login Attempts -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Max Login Attempts
                            </label>
                            <input
                                v-model.number="form.max_login_attempts"
                                type="number"
                                min="3"
                                max="10"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                :class="{
                                    'border-red-500': errors.max_login_attempts,
                                }"
                            />
                            <p class="mt-1 text-xs text-gray-600">
                                Lock account after failed attempts
                            </p>
                            <p
                                v-if="errors.max_login_attempts"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.max_login_attempts[0] }}
                            </p>
                        </div>

                        <!-- Lockout Duration -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Lockout Duration (minutes)
                            </label>
                            <input
                                v-model.number="form.lockout_duration"
                                type="number"
                                min="5"
                                max="1440"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                :class="{
                                    'border-red-500': errors.lockout_duration,
                                }"
                            />
                            <p class="mt-1 text-xs text-gray-600">
                                How long to lock the account
                            </p>
                            <p
                                v-if="errors.lockout_duration"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.lockout_duration[0] }}
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
    session_timeout: 5,
    password_min_length: 8,
    password_require_uppercase: false,
    password_require_lowercase: false,
    password_require_numbers: false,
    password_require_special: false,
    max_login_attempts: 5,
    lockout_duration: 30,
});

const errors = ref({});
const saving = ref(false);

watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings.security) {
            // Convert string booleans to actual booleans
            const security = { ...newSettings.security };
            security.password_require_uppercase =
                security.password_require_uppercase === true ||
                security.password_require_uppercase === "true" ||
                security.password_require_uppercase === 1;
            security.password_require_lowercase =
                security.password_require_lowercase === true ||
                security.password_require_lowercase === "true" ||
                security.password_require_lowercase === 1;
            security.password_require_numbers =
                security.password_require_numbers === true ||
                security.password_require_numbers === "true" ||
                security.password_require_numbers === 1;
            security.password_require_special =
                security.password_require_special === true ||
                security.password_require_special === "true" ||
                security.password_require_special === 1;
            form.value = security;
        }
    },
    { immediate: true, deep: true },
);

const saveSettings = async () => {
    saving.value = true;
    errors.value = {};

    try {
        await api.put("/settings/security", form.value);

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Security settings saved successfully!",
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
    if (props.settings.security) {
        const security = { ...props.settings.security };
        security.password_require_uppercase =
            security.password_require_uppercase === true ||
            security.password_require_uppercase === "true" ||
            security.password_require_uppercase === 1;
        security.password_require_lowercase =
            security.password_require_lowercase === true ||
            security.password_require_lowercase === "true" ||
            security.password_require_lowercase === 1;
        security.password_require_numbers =
            security.password_require_numbers === true ||
            security.password_require_numbers === "true" ||
            security.password_require_numbers === 1;
        security.password_require_special =
            security.password_require_special === true ||
            security.password_require_special === "true" ||
            security.password_require_special === 1;
        form.value = security;
    }
    errors.value = {};
};
</script>
