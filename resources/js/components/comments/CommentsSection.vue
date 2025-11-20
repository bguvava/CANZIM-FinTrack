<template>
    <div
        class="comments-section bg-white rounded-lg shadow-sm border border-gray-200 p-6"
    >
        <!-- Section Header -->
        <div
            class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200"
        >
            <div>
                <h3
                    class="text-lg font-semibold text-gray-900 flex items-center"
                >
                    <i class="fas fa-comments mr-2 text-blue-800"></i>
                    Comments
                    <span
                        v-if="commentCount > 0"
                        class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
                    >
                        {{ commentCount }}
                    </span>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Discuss and collaborate with your team
                </p>
            </div>
            <button
                v-if="!showCommentBox"
                @click="showCommentBox = true"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-800 rounded-lg hover:bg-blue-900 transition-colors duration-150"
            >
                <i class="fas fa-plus mr-2"></i>
                Add Comment
            </button>
        </div>

        <!-- Comment Box (New Root Comment) -->
        <div v-if="showCommentBox" class="mb-6">
            <CommentBox
                :commentableType="commentableType"
                :commentableId="commentableId"
                :replyTo="null"
                @comment-added="handleCommentAdded"
                @cancel-reply="showCommentBox = false"
            />
        </div>

        <!-- Reply Box (Replying to a comment) -->
        <div v-if="replyingTo" class="mb-6">
            <CommentBox
                :commentableType="commentableType"
                :commentableId="commentableId"
                :replyTo="replyingTo"
                @comment-added="handleReplyAdded"
                @cancel-reply="cancelReply"
            />
        </div>

        <!-- Comments List -->
        <CommentsList
            ref="commentsListRef"
            :commentableType="commentableType"
            :commentableId="commentableId"
            :refreshTrigger="refreshTrigger"
            @reply="handleReply"
            @comment-count="handleCommentCount"
        />
    </div>
</template>

<script setup>
import { ref } from "vue";
import CommentBox from "./CommentBox.vue";
import CommentsList from "./CommentsList.vue";

const props = defineProps({
    commentableType: {
        type: String,
        required: true,
    },
    commentableId: {
        type: [String, Number],
        required: true,
    },
});

const showCommentBox = ref(false);
const replyingTo = ref(null);
const refreshTrigger = ref(0);
const commentCount = ref(0);
const commentsListRef = ref(null);

// Handle new comment added
const handleCommentAdded = () => {
    showCommentBox.value = false;
    refreshTrigger.value++;
};

// Handle reply added
const handleReplyAdded = () => {
    replyingTo.value = null;
    refreshTrigger.value++;
};

// Handle reply action
const handleReply = (comment) => {
    replyingTo.value = comment;
    showCommentBox.value = false;

    // Scroll to reply box
    setTimeout(() => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    }, 100);
};

// Cancel reply
const cancelReply = () => {
    replyingTo.value = null;
};

// Handle comment count update
const handleCommentCount = (count) => {
    commentCount.value = count;
};

// Refresh comments
const refresh = () => {
    if (commentsListRef.value) {
        commentsListRef.value.refresh();
    }
};

defineExpose({ refresh });
</script>

<style scoped>
.comments-section {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
