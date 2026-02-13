<template>
    <div
        v-if="isOpen && project"
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
                @click.stop
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <div>
                        <h3
                            id="modal-title"
                            class="text-lg font-semibold text-gray-900"
                        >
                            Manage Team - {{ project.name }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Assign Project Officers to this project
                        </p>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="max-h-[70vh] overflow-y-auto px-6 py-4">
                    <!-- Add Team Members Section -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Add Team Members
                        </label>
                        <div class="flex gap-2">
                            <select
                                v-model="selectedUserId"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">
                                    Select a Project Officer
                                </option>
                                <option
                                    v-for="user in availableUsers"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                            <button
                                @click="addTeamMember"
                                :disabled="!selectedUserId || loading"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <i class="fas fa-plus mr-1"></i>
                                Add
                            </button>
                        </div>
                    </div>

                    <!-- Current Team Members -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">
                            Current Team Members ({{ teamMembers.length }})
                        </h4>

                        <div v-if="loading" class="text-center py-8">
                            <i
                                class="fas fa-spinner fa-spin text-3xl text-gray-400"
                            ></i>
                            <p class="text-gray-600 mt-2">
                                Loading team members...
                            </p>
                        </div>

                        <div
                            v-else-if="teamMembers.length === 0"
                            class="text-center py-8 bg-gray-50 rounded-lg"
                        >
                            <i
                                class="fas fa-users text-4xl text-gray-400 mb-2"
                            ></i>
                            <p class="text-gray-600">
                                No team members assigned yet
                            </p>
                        </div>

                        <div v-else class="space-y-2">
                            <div
                                v-for="member in teamMembers"
                                :key="member.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold"
                                    >
                                        {{ getInitials(member.name) }}
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ member.name }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ member.email }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="removeTeamMember(member.id)"
                                    :disabled="loading"
                                    class="text-red-600 hover:text-red-800 disabled:opacity-50 transition"
                                    title="Remove team member"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
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
                        class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 transition-colors"
                    >
                        <i class="fas fa-times mr-1.5"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { showSuccess, showError, confirmAction } from "@/plugins/sweetalert";
import api from "@/api";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    project: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close"]);

const loading = ref(false);
const teamMembers = ref([]);
const allUsers = ref([]);
const selectedUserId = ref("");

const availableUsers = computed(() => {
    const assignedIds = teamMembers.value.map((m) => m.id);
    return allUsers.value.filter((user) => !assignedIds.includes(user.id));
});

const getInitials = (name) => {
    return name
        .split(" ")
        .map((word) => word[0])
        .join("")
        .toUpperCase()
        .substring(0, 2);
};

const fetchTeamMembers = async () => {
    if (!props.project) return;

    loading.value = true;
    try {
        const response = await api.get(
            `/projects/${props.project.id}/team-members`,
        );
        if (response.data.success) {
            teamMembers.value = response.data.data;
        }
    } catch (error) {
        const message =
            error.response?.status === 401
                ? "Your session has expired. Please refresh the page and log in again."
                : error.response?.data?.message ||
                  "Unable to load team members. Please try again.";
        showError(message);
    } finally {
        loading.value = false;
    }
};

const fetchProjectOfficers = async () => {
    try {
        const response = await api.get("/users", {
            params: { role: "project-officer", per_page: 100 },
        });
        if (response.data.status === "success" || response.data.success) {
            allUsers.value = response.data.data || [];
        }
    } catch (error) {
        const message =
            error.response?.status === 401
                ? "Your session has expired. Please refresh the page and log in again."
                : "Unable to load available team members. Please try again.";
        showError(message);
    }
};

const addTeamMember = async () => {
    if (!selectedUserId.value) return;

    loading.value = true;
    try {
        const response = await api.post(
            `/projects/${props.project.id}/team-members`,
            { user_ids: [selectedUserId.value] },
        );

        if (response.data.success) {
            showSuccess("Team member added successfully");
            selectedUserId.value = "";
            await fetchTeamMembers();
        }
    } catch (error) {
        const message =
            error.response?.status === 401
                ? "Your session has expired. Please refresh the page and log in again."
                : error.response?.data?.message ||
                  "Unable to add team member. Please try again.";
        showError(message);
    } finally {
        loading.value = false;
    }
};

const removeTeamMember = async (userId) => {
    const confirmed = await confirmAction(
        "Remove Team Member",
        "Are you sure you want to remove this team member from the project?",
        "Yes, Remove",
    );

    if (!confirmed) return;

    loading.value = true;
    try {
        const response = await api.delete(
            `/projects/${props.project.id}/team-members/${userId}`,
        );

        if (response.data.success) {
            showSuccess("Team member removed successfully");
            await fetchTeamMembers();
        }
    } catch (error) {
        const message =
            error.response?.status === 401
                ? "Your session has expired. Please refresh the page and log in again."
                : error.response?.data?.message ||
                  "Unable to remove team member. Please try again.";
        showError(message);
    } finally {
        loading.value = false;
    }
};

const closeModal = () => {
    emit("close");
};

watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            fetchTeamMembers();
            fetchProjectOfficers();
        }
    },
);

onMounted(() => {
    if (props.isOpen) {
        fetchTeamMembers();
        fetchProjectOfficers();
    }
});
</script>
