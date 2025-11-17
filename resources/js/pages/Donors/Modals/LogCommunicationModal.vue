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
                    <h2 class="text-xl font-bold text-gray-900">
                        Log Communication
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Record communication with {{ donor.name }}
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
                    <!-- Communication Type and Date (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Communication Type
                                <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="formData.type"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': errors.type }"
                            >
                                <option value="">-- Select type --</option>
                                <option value="email">
                                    <i class="fas fa-envelope"></i> Email
                                </option>
                                <option value="phone_call">
                                    <i class="fas fa-phone"></i> Phone Call
                                </option>
                                <option value="meeting">
                                    <i class="fas fa-handshake"></i> Meeting
                                </option>
                                <option value="letter">
                                    <i class="fas fa-file-alt"></i> Letter
                                </option>
                                <option value="other">
                                    <i class="fas fa-comment"></i> Other
                                </option>
                            </select>
                            <p
                                v-if="errors.type"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.type[0] }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Communication Date
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.communication_date"
                                type="datetime-local"
                                required
                                :max="maxDateTime"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'border-red-500': errors.communication_date,
                                }"
                            />
                            <p
                                v-if="errors.communication_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.communication_date[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formData.subject"
                            type="text"
                            required
                            placeholder="e.g., Q3 Project Update Meeting"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.subject }"
                        />
                        <p
                            v-if="errors.subject"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.subject[0] }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Notes / Summary
                        </label>
                        <textarea
                            v-model="formData.notes"
                            rows="4"
                            placeholder="Summarize the key points discussed or actions taken..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <!-- File Attachment -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Attachment (Optional)
                        </label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            @change="handleFileChange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Allowed: PDF, DOC, DOCX, JPG, PNG (Max 5MB)
                        </p>
                        <p
                            v-if="errors.attachment"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.attachment[0] }}
                        </p>
                        <div
                            v-if="selectedFile"
                            class="mt-2 flex items-center gap-2 text-sm text-gray-700"
                        >
                            <i class="fas fa-paperclip text-blue-600"></i>
                            <span
                                >{{ selectedFile.name }} ({{
                                    formatFileSize(selectedFile.size)
                                }})</span
                            >
                            <button
                                type="button"
                                @click="clearFile"
                                class="text-red-600 hover:text-red-700"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Next Action (Optional) -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">
                            <i
                                class="fas fa-calendar-alt text-orange-600 mr-2"
                            ></i>
                            Next Action (Optional)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Next Action Date
                                </label>
                                <input
                                    v-model="formData.next_action_date"
                                    type="date"
                                    :min="today"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Next Action Notes
                                </label>
                                <input
                                    v-model="formData.next_action_notes"
                                    type="text"
                                    placeholder="e.g., Schedule site visit"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
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
                        {{ submitting ? "Logging..." : "Log Communication" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useDonorStore } from "../../../stores/donorStore";

const props = defineProps({
    donor: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["close", "logged"]);
const donorStore = useDonorStore();

const submitting = ref(false);
const errors = ref({});
const fileInput = ref(null);
const selectedFile = ref(null);

const today = computed(() => {
    return new Date().toISOString().split("T")[0];
});

const maxDateTime = computed(() => {
    return new Date().toISOString().slice(0, 16);
});

const formData = reactive({
    communicable_type: "App\\Models\\Donor",
    communicable_id: props.donor.id,
    type: "",
    communication_date: new Date().toISOString().slice(0, 16),
    subject: "",
    notes: "",
    attachment: null,
    next_action_date: "",
    next_action_notes: "",
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            errors.value.attachment = ["File size must not exceed 5MB"];
            fileInput.value.value = "";
            return;
        }
        selectedFile.value = file;
        formData.attachment = file;
        errors.value.attachment = null;
    }
};

const clearFile = () => {
    selectedFile.value = null;
    formData.attachment = null;
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + " KB";
    return (bytes / (1024 * 1024)).toFixed(2) + " MB";
};

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {};

    try {
        await donorStore.logCommunication(formData);
        emit("logged");
    } catch (err) {
        if (typeof err === "object" && err !== null) {
            errors.value = err;
        } else {
            console.error("Error logging communication:", err);
        }
    } finally {
        submitting.value = false;
    }
};

const closeModal = () => {
    emit("close");
};
</script>
