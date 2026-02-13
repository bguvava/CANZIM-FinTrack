<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-history mr-2 text-blue-800"></i>
                Communication History
                <span
                    v-if="total > 0"
                    class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
                >
                    {{ total }}
                </span>
            </h3>
            <button
                @click="showAddForm = !showAddForm"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-800 rounded-lg hover:bg-blue-900 transition-colors"
            >
                <i
                    :class="showAddForm ? 'fas fa-times' : 'fas fa-plus'"
                    class="mr-2"
                ></i>
                {{ showAddForm ? "Cancel" : "Add Communication" }}
            </button>
        </div>

        <!-- Add Communication Form -->
        <div v-if="showAddForm" class="mb-6">
            <AddCommunicationForm
                :communicableType="communicableType"
                :communicableId="communicableId"
                @saved="handleSaved"
                @cancel="showAddForm = false"
            />
        </div>

        <!-- Edit Communication Form -->
        <div v-if="editingCommunication" class="mb-6">
            <AddCommunicationForm
                :communicableType="communicableType"
                :communicableId="communicableId"
                :communication="editingCommunication"
                @saved="handleUpdated"
                @cancel="editingCommunication = null"
            />
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl text-blue-800"></i>
        </div>

        <!-- Empty State -->
        <div v-else-if="communications.length === 0" class="text-center py-12">
            <i class="fas fa-comments text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg mb-2">
                No communications logged yet
            </p>
            <p class="text-gray-400 text-sm">
                Click "Add Communication" to log your first interaction
            </p>
        </div>

        <!-- Timeline View -->
        <div v-else class="space-y-6">
            <div
                v-for="communication in communications"
                :key="communication.id"
                class="relative border-l-4 border-blue-200 pl-6 pb-6 hover:border-blue-400 transition-colors"
            >
                <!-- Timeline Dot -->
                <div
                    class="absolute -left-2.5 top-0 w-5 h-5 rounded-full bg-blue-800 border-4 border-white"
                ></div>

                <!-- Communication Card -->
                <div
                    class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <i
                                    :class="getTypeIcon(communication.type)"
                                    class="text-blue-800"
                                ></i>
                                <span class="font-semibold text-gray-900">{{
                                    communication.type
                                }}</span>
                                <span class="text-xs text-gray-500">
                                    {{
                                        formatDate(
                                            communication.communication_date,
                                        )
                                    }}
                                </span>
                            </div>
                            <h4 class="text-base font-medium text-gray-900">
                                {{ communication.subject }}
                            </h4>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <button
                                @click="handleEdit(communication)"
                                class="p-2 text-gray-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                                title="Edit"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                @click="handleDelete(communication)"
                                class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Delete"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="communication.notes" class="mb-3">
                        <p class="text-sm text-gray-700 line-clamp-3">
                            {{ communication.notes }}
                        </p>
                        <button
                            v-if="communication.notes.length > 200"
                            @click="toggleExpanded(communication.id)"
                            class="text-xs text-blue-800 hover:underline mt-1"
                        >
                            {{
                                expandedIds.has(communication.id)
                                    ? "Show less"
                                    : "Read more"
                            }}
                        </button>
                    </div>

                    <!-- Metadata -->
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-user"></i>
                            <span>{{
                                communication.creator?.name || "Unknown"
                            }}</span>
                        </div>
                        <div
                            v-if="communication.attachment_path"
                            class="flex items-center gap-1 text-purple-600"
                        >
                            <i class="fas fa-paperclip"></i>
                            <span>Attachment</span>
                        </div>
                        <div
                            v-if="communication.next_action_date"
                            class="flex items-center gap-1 text-orange-600"
                        >
                            <i class="fas fa-calendar-check"></i>
                            <span
                                >Follow-up:
                                {{
                                    formatDate(communication.next_action_date)
                                }}</span
                            >
                        </div>
                    </div>

                    <!-- Next Action Notes -->
                    <div
                        v-if="communication.next_action_notes"
                        class="mt-3 p-3 bg-orange-50 border-l-4 border-orange-400 rounded"
                    >
                        <div class="flex items-start gap-2">
                            <i
                                class="fas fa-sticky-note text-orange-600 mt-0.5"
                            ></i>
                            <div class="flex-1">
                                <p
                                    class="text-xs font-medium text-orange-900 mb-1"
                                >
                                    Next Action:
                                </p>
                                <p class="text-xs text-orange-800">
                                    {{ communication.next_action_notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="total > perPage"
                class="flex justify-center items-center gap-2 pt-4"
            >
                <button
                    @click="loadPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Previous
                </button>
                <span class="text-sm text-gray-600">
                    Page {{ currentPage }} of {{ lastPage }}
                </span>
                <button
                    @click="loadPage(currentPage + 1)"
                    :disabled="currentPage === lastPage"
                    class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/api";
import AddCommunicationForm from "./AddCommunicationForm.vue";

const props = defineProps({
    communicableType: {
        type: String,
        required: true,
    },
    communicableId: {
        type: [String, Number],
        required: true,
    },
});

const loading = ref(false);
const communications = ref([]);
const total = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(25);
const showAddForm = ref(false);
const editingCommunication = ref(null);
const expandedIds = ref(new Set());

onMounted(() => {
    loadCommunications();
});

const loadCommunications = async () => {
    loading.value = true;
    try {
        const response = await api.get("/communications", {
            params: {
                communicable_type: props.communicableType,
                communicable_id: props.communicableId,
                page: currentPage.value,
            },
        });

        if (response.data.success) {
            communications.value = response.data.data.data;
            total.value = response.data.data.total;
            currentPage.value = response.data.data.current_page;
            lastPage.value = response.data.data.last_page;
            perPage.value = response.data.data.per_page;
        }
    } catch (error) {
        console.error("Error loading communications:", error);
    } finally {
        loading.value = false;
    }
};

const loadPage = (page) => {
    currentPage.value = page;
    loadCommunications();
};

const handleSaved = (newCommunication) => {
    showAddForm.value = false;
    loadCommunications();
};

const handleEdit = (communication) => {
    editingCommunication.value = communication;
    showAddForm.value = false;
};

const handleUpdated = () => {
    editingCommunication.value = null;
    loadCommunications();
};

const handleDelete = async (communication) => {
    if (
        !confirm(
            `Are you sure you want to delete this communication?\n\n"${communication.subject}"`,
        )
    ) {
        return;
    }

    try {
        const response = await api.delete(
            `/communications/${communication.id}`,
        );
        if (response.data.success) {
            loadCommunications();
        }
    } catch (error) {
        console.error("Error deleting communication:", error);
        alert(
            error.response?.data?.message || "Failed to delete communication",
        );
    }
};

const toggleExpanded = (id) => {
    if (expandedIds.value.has(id)) {
        expandedIds.value.delete(id);
    } else {
        expandedIds.value.add(id);
    }
};

const getTypeIcon = (type) => {
    const icons = {
        Email: "fas fa-envelope",
        "Phone Call": "fas fa-phone",
        Meeting: "fas fa-users",
        Letter: "fas fa-file-alt",
        Other: "fas fa-comment",
    };
    return icons[type] || "fas fa-comment";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
