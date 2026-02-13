<template>
    <div class="edit-project-container max-w-4xl mx-auto">
        <!-- Loading State -->
        <div
            v-if="loading && !project"
            class="flex justify-center items-center py-12"
        >
            <div
                class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
            ></div>
        </div>

        <!-- Content -->
        <div v-else-if="project">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <a href="/projects" class="hover:text-blue-600">Projects</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a
                        :href="`/projects/${project.id}`"
                        class="hover:text-blue-600"
                        >{{ project.name }}</a
                    >
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span>Edit</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Project</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ project.code }}
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form @submit.prevent="handleSubmit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Project Name -->
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Project Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                            <p
                                v-if="errors.name"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ errors.name[0] }}
                            </p>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.start_date"
                                type="date"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                            <p
                                v-if="errors.start_date"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ errors.start_date[0] }}
                            </p>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                End Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.end_date"
                                type="date"
                                required
                                :min="formData.start_date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                            <p
                                v-if="errors.end_date"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ errors.end_date[0] }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="formData.status"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <p
                                v-if="errors.status"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ errors.status[0] }}
                            </p>
                        </div>

                        <!-- Location -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Location
                            </label>
                            <input
                                v-model="formData.location"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Description
                            </label>
                            <textarea
                                v-model="formData.description"
                                rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex justify-between mt-8 pt-6 border-t border-gray-200"
                    >
                        <a
                            :href="`/projects/${project.id}`"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <i
                                v-if="loading"
                                class="fas fa-spinner fa-spin"
                            ></i>
                            <i v-else class="fas fa-save"></i>
                            {{ loading ? "Saving..." : "Save Changes" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useProjectStore } from "../../stores/projectStore";
import { showSuccess, showError } from "../../plugins/sweetalert";

const projectStore = useProjectStore();

// Get project ID from URL
const projectId = window.location.pathname.split("/")[2];

// State
const loading = ref(false);
const errors = ref({});

const formData = reactive({
    name: "",
    description: "",
    start_date: "",
    end_date: "",
    status: "planning",
    location: "",
});

// Computed
const project = computed(() => projectStore.currentProject);

// Methods
const loadProject = async () => {
    loading.value = true;
    try {
        await projectStore.fetchProject(projectId);

        // Populate form with project data
        if (project.value) {
            formData.name = project.value.name;
            formData.description = project.value.description || "";
            // Convert ISO datetime to yyyy-MM-dd format for date inputs
            formData.start_date = project.value.start_date
                ? project.value.start_date.split("T")[0]
                : "";
            formData.end_date = project.value.end_date
                ? project.value.end_date.split("T")[0]
                : "";
            formData.status = project.value.status;
            formData.location = project.value.location || "";
        }
    } catch (error) {
        await showError(
            "Failed to Load Project",
            error.message || "Could not load project details",
        );
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {};

    try {
        await projectStore.updateProject(projectId, formData);

        await showSuccess("Success!", "Project updated successfully");

        // Redirect to project view
        setTimeout(() => {
            window.location.href = `/projects/${projectId}`;
        }, 1000);
    } catch (err) {
        console.error("Error updating project:", err);

        if (typeof err === "object" && err !== null) {
            errors.value = err;
        }

        await showError(
            "Failed to Update Project",
            projectStore.error || "Please check the form and try again",
        );
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(() => {
    loadProject();
});
</script>
