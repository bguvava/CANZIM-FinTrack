<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="closeModal"
    >
        <div
            class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto m-4"
        >
            <!-- Modal Header -->
            <div
                class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between"
            >
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Edit Donor</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Update donor information
                    </p>
                </div>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 transition"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="handleSubmit" class="px-6 py-4">
                <div class="space-y-4">
                    <!-- Organization Name -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Organization Name
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formData.name"
                            type="text"
                            required
                            placeholder="Enter organization name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.name }"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                            {{ errors.name[0] }}
                        </p>
                    </div>

                    <!-- Contact Person -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Contact Person
                        </label>
                        <input
                            v-model="formData.contact_person"
                            type="text"
                            placeholder="Enter contact person name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Email and Phone (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Email Address
                            </label>
                            <input
                                v-model="formData.email"
                                type="email"
                                placeholder="donor@example.com"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': errors.email }"
                            />
                            <p
                                v-if="errors.email"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.email[0] }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Phone Number
                            </label>
                            <input
                                v-model="formData.phone"
                                type="tel"
                                placeholder="+263 XX XXX XXXX"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Physical Address
                        </label>
                        <textarea
                            v-model="formData.address"
                            rows="2"
                            placeholder="Enter physical address"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <!-- Tax ID and Website (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Tax ID / Registration Number
                            </label>
                            <input
                                v-model="formData.tax_id"
                                type="text"
                                placeholder="Enter tax ID"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Website
                            </label>
                            <input
                                v-model="formData.website"
                                type="url"
                                placeholder="https://example.com"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Status
                        </label>
                        <select
                            v-model="formData.status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Notes
                        </label>
                        <textarea
                            v-model="formData.notes"
                            rows="3"
                            placeholder="Additional notes about the donor"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3 -mx-6 -mb-4 mt-6"
                >
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i
                            v-if="submitting"
                            class="fas fa-spinner fa-spin mr-2"
                        ></i>
                        {{ submitting ? "Updating..." : "Update Donor" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useDonorStore } from "../../../stores/donorStore";

const props = defineProps({
    donor: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["close", "donor-updated"]);
const donorStore = useDonorStore();

const submitting = ref(false);
const errors = ref({});

const formData = reactive({
    name: "",
    contact_person: "",
    email: "",
    phone: "",
    address: "",
    tax_id: "",
    website: "",
    status: "active",
    notes: "",
});

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {};

    try {
        const donor = await donorStore.updateDonor(props.donor.id, formData);
        emit("donor-updated", donor);
    } catch (err) {
        if (typeof err === "object" && err !== null) {
            errors.value = err;
        } else {
            console.error("Error updating donor:", err);
        }
    } finally {
        submitting.value = false;
    }
};

const closeModal = () => {
    emit("close");
};

// Load donor data into form
onMounted(() => {
    formData.name = props.donor.name || "";
    formData.contact_person = props.donor.contact_person || "";
    formData.email = props.donor.email || "";
    formData.phone = props.donor.phone || "";
    formData.address = props.donor.address || "";
    formData.tax_id = props.donor.tax_id || "";
    formData.website = props.donor.website || "";
    formData.status = props.donor.status || "active";
    formData.notes = props.donor.notes || "";
});
</script>
