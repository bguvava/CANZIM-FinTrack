<template>
    <div class="space-y-6">
        <form @submit.prevent="saveSettings">
            <div class="grid grid-cols-1 gap-6">
                <!-- Organization Name -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Organization Name
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.org_name"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.org_name }"
                        placeholder="Climate Action Network Zimbabwe"
                    />
                    <p v-if="errors.org_name" class="mt-1 text-sm text-red-600">
                        {{ errors.org_name[0] }}
                    </p>
                </div>

                <!-- Short Name -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Short Name
                    </label>
                    <input
                        v-model="form.org_short_name"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.org_short_name }"
                        placeholder="CANZIM"
                    />
                    <p
                        v-if="errors.org_short_name"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.org_short_name[0] }}
                    </p>
                </div>

                <!-- Contact Email -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Contact Email
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.org_email"
                        type="email"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.org_email }"
                        placeholder="info@canzimbabwe.org.zw"
                    />
                    <p
                        v-if="errors.org_email"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.org_email[0] }}
                    </p>
                </div>

                <!-- Phone -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Phone Number
                    </label>
                    <input
                        v-model="form.org_phone"
                        type="tel"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.org_phone }"
                        placeholder="+263 4 123456"
                    />
                    <p
                        v-if="errors.org_phone"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.org_phone[0] }}
                    </p>
                </div>

                <!-- Address -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Physical Address
                    </label>
                    <textarea
                        v-model="form.org_address"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.org_address }"
                        placeholder="123 Main Street, Harare, Zimbabwe"
                    ></textarea>
                    <p
                        v-if="errors.org_address"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.org_address[0] }}
                    </p>
                </div>

                <!-- Website -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Website URL
                    </label>
                    <input
                        v-model="form.org_website"
                        type="url"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.org_website }"
                        placeholder="https://www.canzimbabwe.org.zw"
                    />
                    <p
                        v-if="errors.org_website"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.org_website[0] }}
                    </p>
                </div>

                <!-- Logo Upload -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Organization Logo
                    </label>
                    <div class="flex items-start gap-4">
                        <!-- Current Logo Preview -->
                        <div v-if="currentLogoUrl" class="flex-shrink-0">
                            <img
                                :src="currentLogoUrl"
                                alt="Current Logo"
                                class="h-24 w-24 rounded-lg border border-gray-300 object-contain"
                            />
                        </div>

                        <!-- Upload Section -->
                        <div class="flex-1">
                            <input
                                ref="logoInput"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/gif"
                                @change="handleLogoSelect"
                                class="hidden"
                            />
                            <button
                                type="button"
                                @click="$refs.logoInput.click()"
                                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                <i class="fas fa-upload"></i>
                                Choose Logo
                            </button>
                            <p class="mt-2 text-xs text-gray-600">
                                Supported formats: JPEG, PNG, JPG, GIF. Max
                                size: 2MB. Image will be resized to 300x300px.
                            </p>
                            <p
                                v-if="errors.logo"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.logo[0] }}
                            </p>
                        </div>

                        <!-- New Logo Preview -->
                        <div v-if="logoPreview" class="flex-shrink-0">
                            <p class="mb-1 text-xs font-medium text-gray-700">
                                New Logo
                            </p>
                            <img
                                :src="logoPreview"
                                alt="New Logo Preview"
                                class="h-24 w-24 rounded-lg border border-blue-300 object-contain"
                            />
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
import { ref, computed, watch } from "vue";
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
    org_name: "",
    org_short_name: "",
    org_email: "",
    org_phone: "",
    org_address: "",
    org_website: "",
});

const logoFile = ref(null);
const logoPreview = ref(null);
const errors = ref({});
const saving = ref(false);

const currentLogoUrl = computed(() => {
    return props.settings.organization?.org_logo
        ? `/storage/${props.settings.organization.org_logo}`
        : null;
});

watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings.organization) {
            form.value = { ...newSettings.organization };
        }
    },
    { immediate: true, deep: true },
);

const handleLogoSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({
            icon: "error",
            title: "File Too Large",
            text: "Logo file must be less than 2MB",
        });
        return;
    }

    logoFile.value = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const saveSettings = async () => {
    saving.value = true;
    errors.value = {};

    try {
        // Save organization settings
        await api.put("/settings/organization", form.value);

        // Upload logo if selected
        if (logoFile.value) {
            const formData = new FormData();
            formData.append("logo", logoFile.value);
            await api.post("/settings/logo", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });
            logoFile.value = null;
            logoPreview.value = null;
        }

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Organization settings saved successfully!",
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
    if (props.settings.organization) {
        form.value = { ...props.settings.organization };
    }
    logoFile.value = null;
    logoPreview.value = null;
    errors.value = {};
};
</script>
