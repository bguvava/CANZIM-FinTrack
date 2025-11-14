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
                class="relative w-full max-w-2xl transform rounded-lg bg-white shadow-xl transition-all"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3
                        id="modal-title"
                        class="text-lg font-semibold text-gray-900"
                    >
                        Edit User
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm">
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Full Name
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.name,
                                    }"
                                    placeholder="John Doe"
                                />
                                <p
                                    v-if="errors.name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.name[0] }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Email Address
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.email,
                                    }"
                                    placeholder="john@example.com"
                                />
                                <p
                                    v-if="errors.email"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.email[0] }}
                                </p>
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Phone Number
                                </label>
                                <input
                                    v-model="form.phone_number"
                                    type="tel"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.phone_number,
                                    }"
                                    placeholder="+263 712 345 678"
                                />
                                <p
                                    v-if="errors.phone_number"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.phone_number[0] }}
                                </p>
                            </div>

                            <!-- Office Location -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Office Location
                                </label>
                                <input
                                    v-model="form.office_location"
                                    type="text"
                                    list="office-locations"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500':
                                            errors.office_location,
                                    }"
                                    placeholder="Harare Office"
                                />
                                <datalist id="office-locations">
                                    <option
                                        v-for="location in officeLocations"
                                        :key="location"
                                        :value="location"
                                    />
                                </datalist>
                                <p
                                    v-if="errors.office_location"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.office_location[0] }}
                                </p>
                            </div>

                            <!-- Role -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Role
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.role_id"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.role_id,
                                    }"
                                >
                                    <option value="">Select Role</option>
                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.id"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.role_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.role_id[0] }}
                                </p>
                            </div>

                            <!-- Status Info -->
                            <div class="md:col-span-2">
                                <div class="rounded-lg bg-blue-50 p-3">
                                    <div class="flex items-center gap-2">
                                        <i
                                            class="fas fa-info-circle text-blue-600"
                                        ></i>
                                        <p class="text-sm text-blue-800">
                                            Password cannot be changed here. Use
                                            the "Change Password" feature or
                                            contact the user to reset their
                                            password.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                            :disabled="loading"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-900 disabled:opacity-50"
                            :disabled="loading"
                        >
                            <i
                                :class="
                                    loading
                                        ? 'fas fa-spinner fa-spin'
                                        : 'fas fa-save'
                                "
                                class="mr-2"
                            ></i>
                            {{ loading ? "Saving..." : "Save Changes" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import api from "../../api";
import { Toast } from "../../plugins/sweetalert";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    officeLocations: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "user-updated"]);

// State
const loading = ref(false);
const form = ref({
    name: "",
    email: "",
    phone_number: "",
    office_location: "",
    role_id: "",
});
const errors = ref({});

// Watch for user changes to populate form
watch(
    () => props.user,
    (newUser) => {
        if (newUser) {
            form.value = {
                name: newUser.name || "",
                email: newUser.email || "",
                phone_number: newUser.phone_number || "",
                office_location: newUser.office_location || "",
                role_id: newUser.role?.id || "",
            };
            errors.value = {};
        }
    },
    { immediate: true },
);

// Reset errors when modal closes
watch(
    () => props.isOpen,
    (newValue) => {
        if (!newValue) {
            errors.value = {};
        }
    },
);

// Methods
const closeModal = () => {
    if (!loading.value) {
        emit("close");
    }
};

const submitForm = async () => {
    if (loading.value || !props.user) return;

    loading.value = true;
    errors.value = {};

    try {
        const response = await api.put(`/users/${props.user.id}`, form.value);

        Toast.fire({
            icon: "success",
            title: "User updated successfully!",
        });

        emit("user-updated", response.data.data);
        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            Toast.fire({
                icon: "error",
                title: "Failed to update user",
                text: error.response?.data?.message || "Something went wrong",
            });
        }
    } finally {
        loading.value = false;
    }
};
</script>
