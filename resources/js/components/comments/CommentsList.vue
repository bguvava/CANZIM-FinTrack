<template>
    <div class="comments-list space-y-4">
        <!-- Loading skeleton -->
        <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="animate-pulse">
                <div class="flex space-x-3">
                    <div class="h-10 w-10 bg-gray-300 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        <div class="h-3 bg-gray-300 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-300 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-else-if="!loading && comments.length === 0"
            class="text-center py-12"
        >
            <div
                class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4"
            >
                <i class="fas fa-comments text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">
                No comments yet
            </h3>
            <p class="text-sm text-gray-500">
                Be the first to comment on this item
            </p>
        </div>

        <!-- Comments list -->
        <div v-else class="space-y-4">
            <CommentItem
                v-for="comment in validComments"
                :key="comment.id"
                :comment="comment"
                :commentableType="commentableType"
                :commentableId="commentableId"
                @reply="handleReply"
                @edit="handleEdit"
                @delete="handleDelete"
                @update="handleUpdate"
            />
        </div>

        <!-- Pagination -->
        <div
            v-if="pagination && pagination.last_page > 1"
            class="flex items-center justify-center pt-6 border-t border-gray-200"
        >
            <nav class="flex items-center space-x-1">
                <!-- Previous button -->
                <button
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>

                <!-- Page numbers -->
                <button
                    v-for="page in displayedPages"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                        page === pagination.current_page
                            ? 'bg-blue-800 text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-50',
                        'px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg transition-colors duration-150',
                    ]"
                >
                    {{ page }}
                </button>

                <!-- Next button -->
                <button
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                >
                    <i class="fas fa-chevron-right"></i>
                </button>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import CommentItem from "./CommentItem.vue";
import api from "@/api";
import canzimSwal, {
    showError,
    showSuccess,
    confirmAction,
} from "@/plugins/sweetalert";

const props = defineProps({
    commentableType: {
        type: String,
        required: true,
    },
    commentableId: {
        type: [String, Number],
        required: true,
    },
    refreshTrigger: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(["reply", "comment-count"]);

const comments = ref([]);
const loading = ref(true);
const pagination = ref(null);
const currentPage = ref(1);

// Filter out invalid comments to prevent vnode errors
const validComments = computed(() => {
    return comments.value.filter((comment) => comment && comment.id);
});

const displayedPages = computed(() => {
    if (!pagination.value) return [];

    const total = pagination.value.last_page;
    const current = pagination.value.current_page;
    const pages = [];

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) pages.push(i);
            pages.push("...");
            pages.push(total);
        } else if (current >= total - 3) {
            pages.push(1);
            pages.push("...");
            for (let i = total - 4; i <= total; i++) pages.push(i);
        } else {
            pages.push(1);
            pages.push("...");
            for (let i = current - 1; i <= current + 1; i++) pages.push(i);
            pages.push("...");
            pages.push(total);
        }
    }

    return pages.filter(
        (p) => p !== "..." || pages.indexOf(p) === pages.lastIndexOf(p),
    );
});

// Fetch comments
const fetchComments = async (page = 1) => {
    loading.value = true;

    try {
        const response = await api.get("/comments", {
            params: {
                commentable_type: props.commentableType,
                commentable_id: props.commentableId,
                page: page,
            },
        });

        comments.value = response.data.data || [];
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
        };

        emit("comment-count", pagination.value.total);
    } catch (error) {
        console.error("Failed to fetch comments:", error);

        if (error.response?.status === 401) {
            showError(
                "Authentication Error",
                "Your session may have expired. Please refresh the page.",
            );
        } else {
            const errorMessage =
                error.response?.data?.message ||
                error.message ||
                "Failed to load comments. Please refresh the page.";
            showError("Error", errorMessage);
        }

        // Set empty data on error
        comments.value = [];
        pagination.value = {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        };
    } finally {
        loading.value = false;
    }
};

// Change page
const changePage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    currentPage.value = page;
    fetchComments(page);

    // Scroll to top of comments
    window.scrollTo({ top: 0, behavior: "smooth" });
};

// Handle reply
const handleReply = (comment) => {
    emit("reply", comment);
};

// Handle edit
const handleEdit = (comment) => {
    // Edit functionality handled in CommentItem
};

// Handle delete
const handleDelete = async (commentId) => {
    const confirmed = await confirmAction(
        "Delete Comment?",
        "This action cannot be undone.",
        "Yes, delete it",
        "Cancel",
    );

    if (!confirmed) return;

    try {
        await api.delete(`/comments/${commentId}`);

        await showSuccess("Deleted", "Comment deleted successfully");

        // Refresh comments
        await fetchComments(currentPage.value);
    } catch (error) {
        console.error("Failed to delete comment:", error);

        const errorMessage =
            error.response?.data?.message ||
            error.message ||
            "Failed to delete comment. Please try again.";
        await showError("Error", errorMessage);
    }
};

// Handle update
const handleUpdate = () => {
    fetchComments(currentPage.value);
};

// Watch for refresh trigger
watch(
    () => props.refreshTrigger,
    () => {
        fetchComments(currentPage.value);
    },
);

onMounted(() => {
    fetchComments();
});

defineExpose({
    refresh: () => fetchComments(currentPage.value),
});
</script>

<style scoped>
.comments-list {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
