<template>
    <!-- Add Vendor Modal -->
    <div
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300"
        @click.self="closeModal"
    >
        <div
            class="w-full max-w-2xl transform rounded-lg bg-white shadow-2xl transition-all duration-300"
        >
            <!-- Modal Header -->
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="rounded-lg bg-blue-100 p-2">
                            <i
                                class="fas fa-building text-xl text-blue-600"
                            ></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Add New Vendor
                            </h3>
                            <p class="text-sm text-gray-600">
                                Register a new vendor for procurement
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-gray-400 transition-colors hover:text-gray-600"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="handleSubmit">
                <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-6">
                    <!-- Vendor Name -->
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Vendor Name
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            id="name"
                            placeholder="e.g., ABC Supplies Ltd"
                            class="mt-1 block w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            :class="
                                errors.name
                                    ? 'border-red-300 bg-red-50'
                                    : 'border-gray-300'
                            "
                            required
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- Contact Person -->
                    <div>
                        <label
                            for="contact_person"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Contact Person
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.contact_person"
                            type="text"
                            id="contact_person"
                            placeholder="e.g., John Doe"
                            class="mt-1 block w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            :class="
                                errors.contact_person
                                    ? 'border-red-300 bg-red-50'
                                    : 'border-gray-300'
                            "
                            required
                        />
                        <p
                            v-if="errors.contact_person"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.contact_person }}
                        </p>
                    </div>

                    <!-- Email and Phone Row -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Email -->
                        <div>
                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Email
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                id="email"
                                placeholder="e.g., contact@abcsupplies.com"
                                class="mt-1 block w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :class="
                                    errors.email
                                        ? 'border-red-300 bg-red-50'
                                        : 'border-gray-300'
                                "
                                required
                            />
                            <p
                                v-if="errors.email"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.email }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label
                                for="phone"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Phone
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                id="phone"
                                placeholder="e.g., +260 955 123456"
                                class="mt-1 block w-full rounded-lg border px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                :class="
                                    errors.phone
                                        ? 'border-red-300 bg-red-50'
                                        : 'border-gray-300'
                                "
                                required
                            />
                            <p
                                v-if="errors.phone"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label
                            for="address"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Address
                        </label>
                        <textarea
                            v-model="form.address"
                            id="address"
                            rows="3"
                            placeholder="Enter vendor physical address (optional)"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        ></textarea>
                        <p
                            v-if="errors.address"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.address }}
                        </p>
                    </div>

                    <!-- Tax ID -->
                    <div>
                        <label
                            for="tax_id"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Tax ID / TPIN
                        </label>
                        <input
                            v-model="form.tax_id"
                            type="text"
                            id="tax_id"
                            placeholder="e.g., 1234567890"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-colors focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        />
                        <p
                            v-if="errors.tax_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.tax_id }}
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex items-center justify-end space-x-3 border-t border-gray-200 bg-gray-50 px-6 py-4"
                >
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-50"
                        :disabled="submitting"
                    >
                        <i class="fas fa-times mr-1.5"></i>Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex items-center space-x-2 rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="submitting"
                    >
                        <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        <span>{{
                            submitting ? "Saving..." : "Save Vendor"
                        }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { usePurchaseOrderStore } from "../../stores/purchaseOrderStore";
import Swal from "sweetalert2";

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "vendor-added"]);

const purchaseOrderStore = usePurchaseOrderStore();

const form = ref({
    name: "",
    contact_person: "",
    email: "",
    phone: "",
    address: "",
    tax_id: "",
});

const errors = ref({});
const submitting = ref(false);

const resetForm = () => {
    form.value = {
        name: "",
        contact_person: "",
        email: "",
        phone: "",
        address: "",
        tax_id: "",
    };
    errors.value = {};
    submitting.value = false;
};

const closeModal = () => {
    resetForm();
    emit("close");
};

const handleSubmit = async () => {
    errors.value = {};
    submitting.value = true;

    try {
        await purchaseOrderStore.createVendor(form.value);

        Swal.fire({
            icon: "success",
            title: "Vendor Added",
            text: "The vendor has been successfully registered.",
            timer: 2000,
            showConfirmButton: false,
        });

        emit("vendor-added");
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            Swal.fire({
                icon: "error",
                title: "Validation Error",
                text: "Please check the form for errors.",
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    error.response?.data?.message ||
                    "Failed to add vendor. Please try again.",
            });
        }
    } finally {
        submitting.value = false;
    }
};

watch(
    () => props.isVisible,
    (newVal) => {
        if (!newVal) {
            resetForm();
        }
    },
);
</script>
