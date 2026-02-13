<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-blue-800"></i>
            {{ isEdit ? "Edit Communication" : "Log New Communication" }}
        </h3>

        <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Communication Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Type <span class="text-red-600">*</span>
                    </label>
                    <select
                        v-model="form.type"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    >
                        <option value="">Select Type</option>
                        <option value="Email">Email</option>
                        <option value="Phone Call">Phone Call</option>
                        <option value="Meeting">Meeting</option>
                        <option value="Letter">Letter</option>
                        <option value="Other">Other</option>
                    </select>
                    <span
                        v-if="errors.type"
                        class="text-xs text-red-600 mt-1"
                        >{{ errors.type }}</span
                    >
                </div>

                <!-- Communication Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Date <span class="text-red-600">*</span>
                    </label>
                    <input
                        v-model="form.communication_date"
                        type="date"
                        required
                        :max="today"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                    <span
                        v-if="errors.communication_date"
                        class="text-xs text-red-600 mt-1"
                        >{{ errors.communication_date }}</span
                    >
                </div>
            </div>

            <!-- Subject -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Subject <span class="text-red-600">*</span>
                </label>
                <input
                    v-model="form.subject"
                    type="text"
                    required
                    maxlength="255"
                    placeholder="Brief subject of the communication"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                />
                <span v-if="errors.subject" class="text-xs text-red-600 mt-1">{{
                    errors.subject
                }}</span>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notes
                </label>
                <textarea
                    v-model="form.notes"
                    rows="4"
                    placeholder="Detailed notes about this communication..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                ></textarea>
                <span v-if="errors.notes" class="text-xs text-red-600 mt-1">{{
                    errors.notes
                }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Next Action Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Next Action Date
                    </label>
                    <input
                        v-model="form.next_action_date"
                        type="date"
                        :min="today"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                    />
                    <span
                        v-if="errors.next_action_date"
                        class="text-xs text-red-600 mt-1"
                        >{{ errors.next_action_date }}</span
                    >
                </div>

                <!-- Attachment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Attachment
                    </label>
                    <input
                        @change="handleFileChange"
                        type="file"
                        accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-800 hover:file:bg-blue-100"
                    />
                    <span class="text-xs text-gray-500"
                        >Max 5MB (PDF, DOC, TXT, JPG, PNG)</span
                    >
                    <span
                        v-if="errors.attachment"
                        class="text-xs text-red-600 mt-1 block"
                        >{{ errors.attachment }}</span
                    >
                </div>
            </div>

            <!-- Next Action Notes -->
            <div v-if="form.next_action_date" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Next Action Notes
                </label>
                <textarea
                    v-model="form.next_action_notes"
                    rows="2"
                    placeholder="Details about the next action..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-transparent"
                ></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    @click="$emit('cancel')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-800 rounded-lg hover:bg-blue-900 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-spinner fa-spin mr-2" v-if="loading"></i>
                    {{
                        loading
                            ? "Saving..."
                            : isEdit
                              ? "Update"
                              : "Log Communication"
                    }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import api from "@/api";

const props = defineProps({
    communicableType: {
        type: String,
        required: true,
    },
    communicableId: {
        type: [String, Number],
        required: true,
    },
    communication: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "cancel"]);

const isEdit = ref(!!props.communication);
const loading = ref(false);
const errors = ref({});
const today = ref(new Date().toISOString().split("T")[0]);

const form = reactive({
    communicable_type: props.communicableType,
    communicable_id: props.communicableId,
    type: "",
    communication_date: "",
    subject: "",
    notes: "",
    next_action_date: "",
    next_action_notes: "",
    attachment: null,
});

// Populate form if editing
if (props.communication) {
    form.type = props.communication.type;
    form.communication_date =
        props.communication.communication_date.split(" ")[0];
    form.subject = props.communication.subject;
    form.notes = props.communication.notes || "";
    form.next_action_date = props.communication.next_action_date || "";
    form.next_action_notes = props.communication.next_action_notes || "";
}

const handleFileChange = (event) => {
    form.attachment = event.target.files[0];
};

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const formData = new FormData();

        if (!isEdit.value) {
            formData.append("communicable_type", form.communicable_type);
            formData.append("communicable_id", form.communicable_id);
        }

        formData.append("type", form.type);
        formData.append("communication_date", form.communication_date);
        formData.append("subject", form.subject);

        if (form.notes) formData.append("notes", form.notes);
        if (form.next_action_date)
            formData.append("next_action_date", form.next_action_date);
        if (form.next_action_notes)
            formData.append("next_action_notes", form.next_action_notes);
        if (form.attachment) formData.append("attachment", form.attachment);

        let response;
        if (isEdit.value) {
            formData.append("_method", "PUT");
            response = await api.post(
                `/communications/${props.communication.id}`,
                formData,
                {
                    headers: { "Content-Type": "multipart/form-data" },
                },
            );
        } else {
            response = await api.post("/communications", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });
        }

        if (response.data.success) {
            emit("saved", response.data.data);
            resetForm();
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error("Error saving communication:", error);
            alert(
                error.response?.data?.message || "Failed to save communication",
            );
        }
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    if (!isEdit.value) {
        form.type = "";
        form.communication_date = "";
        form.subject = "";
        form.notes = "";
        form.next_action_date = "";
        form.next_action_notes = "";
        form.attachment = null;
    }
};
</script>
