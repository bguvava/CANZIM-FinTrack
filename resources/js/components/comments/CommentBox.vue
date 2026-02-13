<template>
    <div
        class="comment-box bg-white rounded-lg shadow-sm border border-gray-200 p-4"
    >
        <!-- Header -->
        <div
            v-if="replyTo"
            class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200"
        >
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-reply mr-2"></i>
                <span
                    >Replying to
                    <strong>{{ replyTo?.user?.name || "User" }}</strong></span
                >
            </div>
            <button
                @click="cancelReply"
                type="button"
                class="text-gray-400 hover:text-gray-600 transition-colors duration-150"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Main Form -->
        <form @submit.prevent="submitComment" class="space-y-3">
            <!-- Textarea with @mention support -->
            <div class="relative">
                <textarea
                    v-model="formData.content"
                    ref="contentInput"
                    @input="handleInput"
                    @keydown="handleKeydown"
                    :placeholder="
                        replyTo
                            ? 'Write a reply...'
                            : 'Add a comment... Use @username to mention someone'
                    "
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-blue-800 resize-none transition-all duration-150"
                    :class="{ 'border-red-500': errors.content }"
                    rows="3"
                    :disabled="submitting"
                ></textarea>

                <!-- Character counter -->
                <div class="absolute bottom-2 right-2 text-xs text-gray-400">
                    {{ formData.content.length }}
                </div>

                <!-- @mention autocomplete dropdown -->
                <div
                    v-if="showMentionDropdown && filteredUsers.length > 0"
                    class="absolute z-50 w-64 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                >
                    <button
                        v-for="(user, index) in filteredUsers"
                        :key="user.id"
                        type="button"
                        @click="selectMention(user)"
                        @mouseenter="selectedMentionIndex = index"
                        class="w-full px-3 py-2 text-left hover:bg-blue-50 focus:bg-blue-50 transition-colors duration-150 flex items-center"
                        :class="{
                            'bg-blue-50': selectedMentionIndex === index,
                        }"
                    >
                        <div
                            class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-800 text-white flex items-center justify-center mr-2"
                        >
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-medium text-sm text-gray-900">
                                {{ user.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                @{{
                                    user.name.toLowerCase().replace(/\s+/g, "")
                                }}
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Error message -->
                <p v-if="errors.content" class="mt-1 text-sm text-red-600">
                    {{ errors.content[0] }}
                </p>
            </div>

            <!-- File attachments -->
            <div class="space-y-2">
                <!-- File input -->
                <div class="flex items-center space-x-2">
                    <label for="comment-attachments" class="cursor-pointer">
                        <div
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150"
                        >
                            <i class="fas fa-paperclip mr-2"></i>
                            <span>Attach Files</span>
                        </div>
                        <input
                            id="comment-attachments"
                            type="file"
                            ref="fileInput"
                            @change="handleFileSelect"
                            multiple
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="hidden"
                            :disabled="
                                submitting || formData.attachments.length >= 3
                            "
                        />
                    </label>
                    <span class="text-xs text-gray-500"
                        >Max 3 files, 2MB each</span
                    >
                </div>

                <!-- Selected files list -->
                <div v-if="formData.attachments.length > 0" class="space-y-1">
                    <div
                        v-for="(file, index) in formData.attachments"
                        :key="index"
                        class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-200"
                    >
                        <div class="flex items-center flex-1 min-w-0">
                            <i class="fas fa-file-alt text-gray-400 mr-2"></i>
                            <span class="text-sm text-gray-700 truncate">{{
                                file.name
                            }}</span>
                            <span class="text-xs text-gray-500 ml-2"
                                >({{ formatFileSize(file.size) }})</span
                            >
                        </div>
                        <button
                            @click="removeAttachment(index)"
                            type="button"
                            class="ml-2 text-red-600 hover:text-red-800 transition-colors duration-150"
                            :disabled="submitting"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Error message for attachments -->
                <p v-if="errors.attachments" class="text-sm text-red-600">
                    {{ errors.attachments[0] }}
                </p>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-between pt-2">
                <div class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Use @ to mention team members
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        v-if="replyTo"
                        @click="cancelReply"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-150"
                        :disabled="submitting"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-800 rounded-lg hover:bg-blue-900 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="submitting || !formData.content.trim()"
                    >
                        <span v-if="submitting">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Posting...
                        </span>
                        <span v-else>
                            <i class="fas fa-paper-plane mr-2"></i>
                            {{ replyTo ? "Reply" : "Comment" }}
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { useAuthStore } from "@/stores/authStore";
import api from "@/api";
import { showError, showSuccess } from "@/plugins/sweetalert";

const props = defineProps({
    commentableType: {
        type: String,
        required: true,
    },
    commentableId: {
        type: [String, Number],
        required: true,
    },
    replyTo: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["comment-added", "cancel-reply"]);

const authStore = useAuthStore();
const contentInput = ref(null);
const fileInput = ref(null);
const submitting = ref(false);
const showMentionDropdown = ref(false);
const mentionQuery = ref("");
const selectedMentionIndex = ref(0);
const allUsers = ref([]);

const formData = ref({
    content: "",
    attachments: [],
});

const errors = ref({});

const filteredUsers = computed(() => {
    if (!mentionQuery.value) return [];

    const query = mentionQuery.value.toLowerCase();
    return allUsers.value
        .filter(
            (user) =>
                user.name.toLowerCase().includes(query) ||
                user.email.toLowerCase().includes(query),
        )
        .slice(0, 5);
});

// Fetch users for @mention autocomplete
const fetchUsers = async () => {
    try {
        const response = await api.get("/users/search", {
            params: { per_page: 100 },
        });
        allUsers.value = response.data.data || [];
    } catch (error) {
        console.error("Failed to fetch users:", error);
        // Don't show error to user - @mention is optional feature
        allUsers.value = [];
    }
};

// Handle textarea input for @mention detection
const handleInput = (event) => {
    const cursorPosition = event.target.selectionStart;
    const textBeforeCursor = formData.value.content.substring(
        0,
        cursorPosition,
    );
    const lastAtIndex = textBeforeCursor.lastIndexOf("@");

    if (lastAtIndex !== -1) {
        const textAfterAt = textBeforeCursor.substring(lastAtIndex + 1);
        if (!textAfterAt.includes(" ")) {
            mentionQuery.value = textAfterAt;
            showMentionDropdown.value = true;
            selectedMentionIndex.value = 0;
            return;
        }
    }

    showMentionDropdown.value = false;
    mentionQuery.value = "";
};

// Handle keyboard navigation in mention dropdown
const handleKeydown = (event) => {
    if (!showMentionDropdown.value) return;

    if (event.key === "ArrowDown") {
        event.preventDefault();
        selectedMentionIndex.value = Math.min(
            selectedMentionIndex.value + 1,
            filteredUsers.value.length - 1,
        );
    } else if (event.key === "ArrowUp") {
        event.preventDefault();
        selectedMentionIndex.value = Math.max(
            selectedMentionIndex.value - 1,
            0,
        );
    } else if (event.key === "Enter" && filteredUsers.value.length > 0) {
        event.preventDefault();
        selectMention(filteredUsers.value[selectedMentionIndex.value]);
    } else if (event.key === "Escape") {
        showMentionDropdown.value = false;
    }
};

// Select a user mention
const selectMention = (user) => {
    const cursorPosition = contentInput.value.selectionStart;
    const textBeforeCursor = formData.value.content.substring(
        0,
        cursorPosition,
    );
    const lastAtIndex = textBeforeCursor.lastIndexOf("@");

    const username = user.name.toLowerCase().replace(/\s+/g, "");
    const beforeAt = formData.value.content.substring(0, lastAtIndex);
    const afterCursor = formData.value.content.substring(cursorPosition);

    formData.value.content = `${beforeAt}@${username} ${afterCursor}`;
    showMentionDropdown.value = false;
    mentionQuery.value = "";

    nextTick(() => {
        const newPosition = lastAtIndex + username.length + 2;
        contentInput.value.focus();
        contentInput.value.setSelectionRange(newPosition, newPosition);
    });
};

// Handle file selection
const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);

    if (formData.value.attachments.length + files.length > 3) {
        errors.value.attachments = ["Maximum 3 files allowed"];
        return;
    }

    for (const file of files) {
        if (file.size > 2 * 1024 * 1024) {
            // 2MB
            errors.value.attachments = [`File ${file.name} exceeds 2MB limit`];
            return;
        }
    }

    formData.value.attachments.push(...files);
    errors.value.attachments = null;

    // Reset file input
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

// Remove attachment
const removeAttachment = (index) => {
    formData.value.attachments.splice(index, 1);
    errors.value.attachments = null;
};

// Format file size
const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
};

// Submit comment
const submitComment = async () => {
    if (!formData.value.content.trim()) return;

    submitting.value = true;
    errors.value = {};

    try {
        const data = new FormData();
        data.append("commentable_type", props.commentableType);
        data.append("commentable_id", props.commentableId);
        data.append("content", formData.value.content);

        if (props.replyTo) {
            data.append("parent_id", props.replyTo.id);
        }

        formData.value.attachments.forEach((file, index) => {
            data.append(`attachments[${index}]`, file);
        });

        const response = await api.post("/comments", data, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        // Reset form first
        formData.value.content = "";
        formData.value.attachments = [];
        errors.value = {};

        // Emit comment added (updates list and triggers close)
        emit("comment-added", response.data.data);

        // Show success message without blocking (don't await)
        showSuccess("Success", "Comment posted successfully");
    } catch (error) {
        console.error("Error posting comment:", error);

        if (error.response?.status === 401) {
            showError(
                "Authentication Error",
                "Your session may have expired. Please refresh the page and try again.",
            );
        } else if (
            error.response?.status === 422 &&
            error.response?.data?.errors
        ) {
            errors.value = error.response.data.errors;
            showError(
                "Validation Error",
                "Please check your input and try again.",
            );
        } else {
            const errorMessage =
                error.response?.data?.message ||
                error.message ||
                "Failed to post comment. Please try again.";
            showError("Error", errorMessage);
        }
    } finally {
        submitting.value = false;
    }
};

// Cancel reply
const cancelReply = () => {
    emit("cancel-reply");
    formData.value.content = "";
    formData.value.attachments = [];
};

// Watch for reply changes to focus input
watch(
    () => props.replyTo,
    (newVal) => {
        if (newVal && contentInput.value) {
            nextTick(() => {
                contentInput.value.focus();
            });
        }
    },
);

onMounted(() => {
    fetchUsers();
});
</script>

<style scoped>
.comment-box {
    animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
