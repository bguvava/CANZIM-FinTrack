<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Manage your profile information and security settings
                </p>
            </div>

            <!-- Profile Information Card -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Profile Information
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Update your personal information and avatar
                        </p>
                    </div>
                    <button
                        v-if="!editingProfile"
                        @click="editingProfile = true"
                        class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        Edit Profile
                    </button>
                </div>

                <!-- Avatar Upload -->
                <div class="mt-6 flex items-center gap-6">
                    <div class="relative">
                        <div
                            v-if="profile?.avatar_url"
                            class="h-24 w-24 rounded-full bg-cover bg-center"
                            :style="{
                                backgroundImage: `url(${profile.avatar_url})`,
                            }"
                        ></div>
                        <div
                            v-else
                            class="flex h-24 w-24 items-center justify-center rounded-full bg-blue-800 text-2xl font-bold text-white"
                        >
                            {{ initials }}
                        </div>
                        <input
                            ref="avatarInput"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg"
                            class="hidden"
                            @change="handleAvatarUpload"
                        />
                    </div>
                    <div>
                        <button
                            @click="$refs.avatarInput.click()"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <i class="fas fa-upload mr-2"></i>
                            Change Avatar
                        </button>
                        <p class="mt-2 text-xs text-gray-500">
                            JPG, PNG. Max 2MB. 200x200 recommended.
                        </p>
                    </div>
                </div>

                <!-- Profile Form -->
                <form @submit.prevent="saveProfile" class="mt-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Full Name
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                :disabled="!editingProfile"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-600"
                                required
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Email Address
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                :disabled="!editingProfile"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-600"
                                required
                            />
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Phone Number
                            </label>
                            <input
                                v-model="profileForm.phone_number"
                                type="tel"
                                :disabled="!editingProfile"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-600"
                            />
                        </div>

                        <!-- Office Location -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Office Location
                            </label>
                            <input
                                v-model="profileForm.office_location"
                                type="text"
                                :disabled="!editingProfile"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-600"
                            />
                        </div>
                    </div>

                    <!-- Role (Read-only) -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Role
                        </label>
                        <input
                            :value="profile?.role?.name"
                            type="text"
                            disabled
                            class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-600"
                        />
                    </div>

                    <!-- Form Actions -->
                    <div v-if="editingProfile" class="flex justify-end gap-3">
                        <button
                            @click="cancelEdit"
                            type="button"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 disabled:opacity-50"
                        >
                            <i
                                v-if="saving"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            {{ saving ? "Saving..." : "Save Changes" }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Card -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Change Password
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Update your password to keep your account secure
                        </p>
                    </div>
                    <button
                        v-if="!changingPassword"
                        @click="changingPassword = true"
                        class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                    >
                        <i class="fas fa-key mr-2"></i>
                        Change Password
                    </button>
                </div>

                <!-- Password Form -->
                <form
                    v-if="changingPassword"
                    @submit.prevent="savePassword"
                    class="mt-6 space-y-4"
                >
                    <!-- Current Password -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Current Password
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required
                        />
                    </div>

                    <!-- New Password -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            New Password
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required
                            minlength="8"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Minimum 8 characters
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Confirm New Password
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required
                        />
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3">
                        <button
                            @click="cancelPasswordChange"
                            type="button"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="savingPassword"
                            class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 disabled:opacity-50"
                        >
                            <i
                                v-if="savingPassword"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            {{
                                savingPassword
                                    ? "Changing..."
                                    : "Change Password"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "../api";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { showSuccess, showError } from "../plugins/sweetalert";

// State
const profile = ref(null);
const editingProfile = ref(false);
const changingPassword = ref(false);
const saving = ref(false);
const savingPassword = ref(false);
const avatarInput = ref(null);

const profileForm = ref({
    name: "",
    email: "",
    phone_number: "",
    office_location: "",
});

const passwordForm = ref({
    current_password: "",
    password: "",
    password_confirmation: "",
});

// Computed
const initials = computed(() => {
    if (!profile.value?.name) return "U";
    const parts = profile.value.name.split(" ");
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return profile.value.name.substring(0, 2).toUpperCase();
});

// Methods
const loadProfile = async () => {
    try {
        const response = await api.get("/profile");
        profile.value = response.data.data;

        // Populate form
        profileForm.value = {
            name: profile.value.name,
            email: profile.value.email,
            phone_number: profile.value.phone_number || "",
            office_location: profile.value.office_location || "",
        };
    } catch (error) {
        console.error("Error loading profile:", error);
        showError(
            "Error",
            error.response?.data?.message || "Failed to load profile",
        );
    }
};

const saveProfile = async () => {
    saving.value = true;

    try {
        const response = await api.put("/profile", profileForm.value);
        profile.value = response.data.data;
        editingProfile.value = false;
        showSuccess("Success", "Profile updated successfully");
    } catch (error) {
        showError(
            "Error",
            error.response?.data?.message || "Failed to update profile",
        );
    } finally {
        saving.value = false;
    }
};

const cancelEdit = () => {
    editingProfile.value = false;
    // Reset form
    profileForm.value = {
        name: profile.value.name,
        email: profile.value.email,
        phone_number: profile.value.phone_number || "",
        office_location: profile.value.office_location || "",
    };
};

const savePassword = async () => {
    if (
        passwordForm.value.password !== passwordForm.value.password_confirmation
    ) {
        showError("Error", "Passwords do not match");
        return;
    }

    savingPassword.value = true;

    try {
        await api.post("/profile/change-password", passwordForm.value);
        changingPassword.value = false;
        passwordForm.value = {
            current_password: "",
            password: "",
            password_confirmation: "",
        };
        showSuccess(
            "Success",
            "Password changed successfully. All other sessions have been logged out.",
        );
    } catch (error) {
        showError(
            "Error",
            error.response?.data?.message || "Failed to change password",
        );
    } finally {
        savingPassword.value = false;
    }
};

const cancelPasswordChange = () => {
    changingPassword.value = false;
    passwordForm.value = {
        current_password: "",
        password: "",
        password_confirmation: "",
    };
};

const handleAvatarUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        showError("Error", "Image size must be less than 2MB");
        return;
    }

    // Validate file type
    if (!["image/jpeg", "image/png", "image/jpg"].includes(file.type)) {
        showError("Error", "Only JPG and PNG images are allowed");
        return;
    }

    const formData = new FormData();
    formData.append("avatar", file);

    try {
        const response = await api.post("/profile/avatar", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
        profile.value.avatar_url = response.data.data.avatar_url;
        showSuccess("Success", "Avatar uploaded successfully");
    } catch (error) {
        showError(
            "Error",
            error.response?.data?.message || "Failed to upload avatar",
        );
    }
};

// Lifecycle
onMounted(() => {
    loadProfile();
});
</script>
