<template>
    <div class="comment-item">
        <!-- Main comment -->
        <div class="flex space-x-3" :class="{ 'ml-12': comment.parent_id }">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                <div
                    class="h-10 w-10 rounded-full bg-blue-800 text-white flex items-center justify-center font-medium"
                >
                    {{ comment.user.name.charAt(0).toUpperCase() }}
                </div>
            </div>

            <!-- Content -->
            <div
                class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between"
                >
                    <div class="flex items-center space-x-2">
                        <span class="font-medium text-sm text-gray-900">{{
                            comment.user.name
                        }}</span>
                        <span class="text-xs text-gray-500">•</span>
                        <span
                            class="text-xs text-gray-500"
                            :title="formatDateFull(comment.created_at)"
                        >
                            {{ formatDateRelative(comment.created_at) }}
                        </span>
                        <span
                            v-if="comment.updated_at !== comment.created_at"
                            class="text-xs text-gray-500"
                        >
                            (edited)
                        </span>
                    </div>

                    <!-- Actions dropdown (only for own comments) -->
                    <div v-if="isOwnComment" class="relative">
                        <button
                            @click="showActionsMenu = !showActionsMenu"
                            class="p-1 rounded hover:bg-gray-200 transition-colors duration-150"
                        >
                            <i class="fas fa-ellipsis-v text-gray-500"></i>
                        </button>

                        <!-- Dropdown menu -->
                        <div
                            v-if="showActionsMenu"
                            v-click-outside="() => (showActionsMenu = false)"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                        >
                            <button
                                @click="startEdit"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-edit mr-2 text-blue-800"></i>
                                Edit
                            </button>
                            <button
                                @click="deleteComment"
                                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition-colors duration-150 flex items-center"
                            >
                                <i class="fas fa-trash mr-2"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-4 py-3">
                    <!-- Viewing mode -->
                    <div v-if="!isEditing" class="space-y-3">
                        <div
                            class="text-sm text-gray-800 whitespace-pre-wrap break-words"
                            v-html="renderContent(comment.content)"
                        ></div>

                        <!-- Attachments -->
                        <div
                            v-if="
                                comment.attachments &&
                                comment.attachments.length > 0
                            "
                            class="space-y-2"
                        >
                            <div
                                v-for="attachment in comment.attachments"
                                :key="attachment.id"
                                class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-200"
                            >
                                <div class="flex items-center flex-1 min-w-0">
                                    <i
                                        :class="
                                            getFileIcon(attachment.mime_type)
                                        "
                                        class="text-gray-400 mr-2"
                                    ></i>
                                    <span
                                        class="text-sm text-gray-700 truncate"
                                        >{{ attachment.file_name }}</span
                                    >
                                    <span class="text-xs text-gray-500 ml-2"
                                        >({{
                                            formatFileSize(
                                                attachment.file_size,
                                            )
                                        }})</span
                                    >
                                </div>
                                <a
                                    :href="`/api/v1/comment-attachments/${attachment.id}/download`"
                                    download
                                    class="ml-2 text-blue-800 hover:text-blue-900 transition-colors duration-150"
                                    title="Download"
                                >
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Edit mode -->
                    <div v-else class="space-y-3">
                        <textarea
                            v-model="editContent"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-800 focus:border-blue-800 resize-none"
                            rows="3"
                        ></textarea>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="saveEdit"
                                :disabled="saving"
                                class="px-3 py-1 text-sm font-medium text-white bg-blue-800 rounded hover:bg-blue-900 disabled:opacity-50 transition-colors duration-150"
                            >
                                <span v-if="saving">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Saving...
                                </span>
                                <span v-else>Save</span>
                            </button>
                            <button
                                @click="cancelEdit"
                                class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors duration-150"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer actions -->
                <div
                    class="px-4 py-2 bg-gray-50 border-t border-gray-200 flex items-center space-x-4"
                >
                    <button
                        @click="reply"
                        class="text-xs font-medium text-blue-800 hover:text-blue-900 transition-colors duration-150"
                    >
                        <i class="fas fa-reply mr-1"></i>
                        Reply
                    </button>
                    <span
                        v-if="comment.replies && comment.replies.length > 0"
                        class="text-xs text-gray-500"
                    >
                        {{ comment.replies.length }}
                        {{ comment.replies.length === 1 ? "reply" : "replies" }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Nested replies -->
        <div
            v-if="comment.replies && comment.replies.length > 0"
            class="mt-3 ml-12 space-y-3"
        >
            <CommentItem
                v-for="reply in comment.replies"
                :key="reply.id"
                :comment="reply"
                :commentableType="commentableType"
                :commentableId="commentableId"
                @reply="$emit('reply', $event)"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
                @update="$emit('update')"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import axios from "axios";
import { formatDistanceToNow, format } from "date-fns";

const props = defineProps({
    comment: {
        type: Object,
        required: true,
    },
    commentableType: {
        type: String,
        required: true,
    },
    commentableId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(["reply", "edit", "delete", "update"]);

const authStore = useAuthStore();
const showActionsMenu = ref(false);
const isEditing = ref(false);
const editContent = ref("");
const saving = ref(false);

const isOwnComment = computed(() => {
    return authStore.user && authStore.user.id === props.comment.user_id;
});

// Format date relative (e.g., "2 hours ago")
const formatDateRelative = (date) => {
    return formatDistanceToNow(new Date(date), { addSuffix: true });
};

// Format date full (e.g., "Nov 19, 2025 at 2:30 PM")
const formatDateFull = (date) => {
    return format(new Date(date), "MMM dd, yyyy 'at' h:mm a");
};

// Render content with @mentions highlighted
const renderContent = (content) => {
    return content.replace(
        /@(\w+)/g,
        '<span class="text-blue-800 font-medium">@$1</span>',
    );
};

// Get file icon based on mime type
const getFileIcon = (mimeType) => {
    if (mimeType.startsWith("image/")) return "fas fa-file-image";
    if (mimeType === "application/pdf") return "fas fa-file-pdf";
    if (mimeType.includes("word")) return "fas fa-file-word";
    return "fas fa-file-alt";
};

// Format file size
const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
};

// Start editing
const startEdit = () => {
    editContent.value = props.comment.content;
    isEditing.value = true;
    showActionsMenu.value = false;
};

// Cancel edit
const cancelEdit = () => {
    isEditing.value = false;
    editContent.value = "";
};

// Save edit
const saveEdit = async () => {
    if (!editContent.value.trim()) return;

    saving.value = true;

    try {
        await axios.put(`/api/v1/comments/${props.comment.id}`, {
            content: editContent.value,
        });

        Swal.fire({
            icon: "success",
            title: "Updated",
            text: "Comment updated successfully",
            timer: 2000,
            showConfirmButton: false,
        });

        isEditing.value = false;
        emit("update");
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to update comment. Please try again.",
        });
    } finally {
        saving.value = false;
    }
};

// Delete comment
const deleteComment = () => {
    showActionsMenu.value = false;
    emit("delete", props.comment.id);
};

// Reply to comment
const reply = () => {
    emit("reply", props.comment);
};

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener("click", el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener("click", el.clickOutsideEvent);
    },
};
</script>

<style scoped>
.comment-item {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
