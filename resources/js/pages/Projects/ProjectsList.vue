<template>
    <div class="projects-list-container">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Projects</h1>
                <p class="text-gray-600 mt-1">
                    Manage all project activities and budgets
                </p>
            </div>
            <button
                v-if="canCreate"
                @click="openAddProjectModal"
                class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-900"
            >
                <i class="fas fa-plus"></i>
                Add Project
            </button>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search Projects
                    </label>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search by name, code, or description..."
                            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                        <i
                            class="fas fa-search absolute left-3 top-3 text-gray-400"
                        ></i>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select
                        v-model="statusFilter"
                        @change="handleFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="planning">Planning</option>
                        <option value="active">Active</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Donor Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Donor
                    </label>
                    <select
                        v-model="donorFilter"
                        @change="handleFilter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Donors</option>
                        <option
                            v-for="donor in donors"
                            :key="donor.id"
                            :value="donor.id"
                        >
                            {{ donor.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="mt-4 flex items-center gap-2">
                <span class="text-sm text-gray-600">Active Filters:</span>
                <span
                    v-if="searchQuery"
                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm flex items-center gap-2"
                >
                    Search: "{{ searchQuery }}"
                    <button @click="clearSearch" class="hover:text-blue-600">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
                <span
                    v-if="statusFilter"
                    class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm flex items-center gap-2"
                >
                    Status: {{ getStatusLabel(statusFilter) }}
                    <button
                        @click="clearStatusFilter"
                        class="hover:text-green-600"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </span>
                <span
                    v-if="donorFilter"
                    class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm flex items-center gap-2"
                >
                    Donor: {{ getDonorName(donorFilter) }}
                    <button
                        @click="clearDonorFilter"
                        class="hover:text-purple-600"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </span>
                <button
                    @click="clearAllFilters"
                    class="text-sm text-red-600 hover:text-red-700"
                >
                    Clear All
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div
                class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
            ></div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
        >
            <div class="flex items-center gap-2 text-red-800">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ error }}</span>
            </div>
        </div>

        <!-- Projects Table -->
        <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Empty State -->
            <div v-if="projects.length === 0" class="text-center py-12">
                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">
                    No Projects Found
                </h3>
                <p class="text-gray-500 mb-4">
                    {{
                        hasActiveFilters
                            ? "Try adjusting your filters"
                            : "Get started by creating your first project"
                    }}
                </p>
                <button
                    v-if="canCreate && !hasActiveFilters"
                    @click="goToAddProject"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Create First Project
                </button>
            </div>

            <!-- Projects Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Project
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Budget
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Dates
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Team
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr
                            v-for="project in projects"
                            :key="project.id"
                            class="hover:bg-gray-50 transition cursor-pointer"
                            @click="viewProject(project)"
                        >
                            <!-- Project Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="shrink-0">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                        >
                                            <i
                                                class="fas fa-folder text-blue-600"
                                            ></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div
                                            class="font-semibold text-gray-900"
                                        >
                                            {{ project.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ project.code }}
                                        </div>
                                        <div
                                            v-if="project.description"
                                            class="text-xs text-gray-400 mt-1 line-clamp-1"
                                        >
                                            {{ project.description }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span
                                    :class="getStatusClass(project.status)"
                                    class="px-3 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ getStatusLabel(project.status) }}
                                </span>
                            </td>

                            <!-- Budget -->
                            <td class="px-6 py-4">
                                <div
                                    v-if="project.budget_summary"
                                    class="text-sm"
                                >
                                    <div class="font-semibold text-gray-900">
                                        ${{
                                            formatNumber(
                                                project.budget_summary
                                                    .total_allocated,
                                            )
                                        }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <span
                                            :class="
                                                getUtilizationClass(
                                                    project.budget_summary
                                                        .utilization_percentage,
                                                )
                                            "
                                        >
                                            {{
                                                project.budget_summary
                                                    .utilization_percentage
                                            }}% used
                                        </span>
                                    </div>
                                </div>
                                <span v-else class="text-sm text-gray-400"
                                    >No budget</span
                                >
                            </td>

                            <!-- Dates -->
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ formatDate(project.start_date) }}</div>
                                <div class="text-xs">
                                    to {{ formatDate(project.end_date) }}
                                </div>
                            </td>

                            <!-- Team -->
                            <td class="px-6 py-4">
                                <div class="flex -space-x-2">
                                    <div
                                        v-for="(
                                            member, index
                                        ) in project.team_members?.slice(0, 3)"
                                        :key="member.id"
                                        :title="member.name"
                                        class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-700"
                                    >
                                        {{ getInitials(member.name) }}
                                    </div>
                                    <div
                                        v-if="project.team_members?.length > 3"
                                        class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600"
                                    >
                                        +{{ project.team_members.length - 3 }}
                                    </div>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right" @click.stop>
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click="viewProject(project)"
                                        class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded transition"
                                        title="View Project"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button
                                        v-if="canUpdate"
                                        @click="editProject(project)"
                                        class="px-3 py-1 text-green-600 hover:bg-green-50 rounded transition"
                                        title="Edit Project"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        v-if="
                                            canArchive &&
                                            project.status !== 'cancelled'
                                        "
                                        @click="handleArchive(project)"
                                        class="px-3 py-1 text-orange-600 hover:bg-orange-50 rounded transition"
                                        title="Archive Project"
                                    >
                                        <i class="fas fa-archive"></i>
                                    </button>
                                    <button
                                        @click="
                                            handleGenerateReport(project.id)
                                        "
                                        class="px-3 py-1 text-purple-600 hover:bg-purple-50 rounded transition"
                                        title="Generate Report"
                                    >
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.total > pagination.per_page"
                class="px-6 py-4 border-t border-gray-200"
            >
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing
                        {{
                            (pagination.current_page - 1) *
                                pagination.per_page +
                            1
                        }}
                        to
                        {{
                            Math.min(
                                pagination.current_page * pagination.per_page,
                                pagination.total,
                            )
                        }}
                        of {{ pagination.total }} projects
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="px-3 py-1 text-sm text-gray-600">
                            Page {{ pagination.current_page }} of
                            {{ pagination.last_page }}
                        </span>
                        <button
                            @click="goToPage(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page === pagination.last_page
                            "
                            class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Project Modal -->
        <AddProjectModal
            :is-open="showAddProjectModal"
            :donors="donors"
            @close="showAddProjectModal = false"
            @project-created="handleProjectCreated"
        />

        <!-- Edit Project Modal -->
        <EditProjectModal
            :is-open="showEditProjectModal"
            :project="selectedProject"
            :donors="donors"
            @close="showEditProjectModal = false"
            @project-updated="handleProjectUpdated"
        />

        <!-- View Project Modal -->
        <ViewProjectModal
            :is-open="showViewProjectModal"
            :project="selectedProject"
            @close="showViewProjectModal = false"
            @edit="handleViewEdit"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useProjectStore } from "../../stores/projectStore";
import { useAuthStore } from "../../stores/authStore";
import AddProjectModal from "../../components/modals/AddProjectModal.vue";
import EditProjectModal from "../../components/modals/EditProjectModal.vue";
import ViewProjectModal from "../../components/modals/ViewProjectModal.vue";

// Stores
const projectStore = useProjectStore();
const authStore = useAuthStore();

// Local state
const searchQuery = ref("");
const statusFilter = ref("");
const donorFilter = ref("");
let searchTimeout = null;

// Modal state
const showAddProjectModal = ref(false);
const showEditProjectModal = ref(false);
const showViewProjectModal = ref(false);
const selectedProject = ref(null);

// Computed properties
const projects = computed(() => projectStore.projects);
const donors = computed(() => projectStore.donors);
const loading = computed(() => projectStore.loading);
const error = computed(() => projectStore.error);
const pagination = computed(() => projectStore.pagination);

const hasActiveFilters = computed(() => {
    return searchQuery.value || statusFilter.value || donorFilter.value;
});

const canCreate = computed(() => {
    return authStore.hasPermission("create-project");
});

const canUpdate = computed(() => {
    return authStore.hasPermission("update-project");
});

const canArchive = computed(() => {
    return authStore.hasPermission("archive-project");
});

// Methods
const handleSearch = () => {
    // Debounce search
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        projectStore.setSearchFilter(searchQuery.value);
        loadProjects(1);
    }, 500);
};

const handleFilter = () => {
    projectStore.setStatusFilter(statusFilter.value);
    projectStore.setDonorFilter(donorFilter.value);
    loadProjects(1);
};

const clearSearch = () => {
    searchQuery.value = "";
    handleSearch();
};

const clearStatusFilter = () => {
    statusFilter.value = "";
    handleFilter();
};

const clearDonorFilter = () => {
    donorFilter.value = "";
    handleFilter();
};

const clearAllFilters = () => {
    searchQuery.value = "";
    statusFilter.value = "";
    donorFilter.value = "";
    projectStore.clearFilters();
    loadProjects(1);
};

const loadProjects = async (page = 1) => {
    await projectStore.fetchProjects(page);
};

const goToPage = (page) => {
    loadProjects(page);
};

// Modal methods
const openAddProjectModal = () => {
    showAddProjectModal.value = true;
};

const viewProject = (project) => {
    selectedProject.value = project;
    showViewProjectModal.value = true;
};

const editProject = (project) => {
    selectedProject.value = project;
    showEditProjectModal.value = true;
};

const handleViewEdit = (project) => {
    showViewProjectModal.value = false;
    selectedProject.value = project;
    showEditProjectModal.value = true;
};

const handleProjectCreated = () => {
    showAddProjectModal.value = false;
    loadProjects();
};

const handleProjectUpdated = () => {
    showEditProjectModal.value = false;
    selectedProject.value = null;
    loadProjects();
};

const handleArchive = async (project) => {
    const result = await window.$swal.fire({
        title: "Archive Project?",
        html: `Are you sure you want to archive <strong>${project.name}</strong>?<br><small>This will set the project status to "Cancelled".</small>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d97706",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Yes, archive it",
        cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
        try {
            await projectStore.archiveProject(project.id);
            window.$toast.fire({
                icon: "success",
                title: "Project archived successfully",
            });
        } catch (error) {
            window.$swal.fire({
                icon: "error",
                title: "Archive Failed",
                text: error.message || "Failed to archive project",
            });
        }
    }
};

const handleGenerateReport = async (id) => {
    try {
        await projectStore.generateReport(id, "pdf");
        window.$toast.fire({
            icon: "success",
            title: "Report generated successfully",
        });
    } catch (error) {
        window.$swal.fire({
            icon: "error",
            title: "Report Generation Failed",
            text: error.message || "Failed to generate report",
        });
    }
};

const getStatusClass = (status) => {
    const classes = {
        planning: "bg-gray-100 text-gray-800",
        active: "bg-green-100 text-green-800",
        on_hold: "bg-yellow-100 text-yellow-800",
        completed: "bg-blue-100 text-blue-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || classes.planning;
};

const getStatusLabel = (status) => {
    const labels = {
        planning: "Planning",
        active: "Active",
        on_hold: "On Hold",
        completed: "Completed",
        cancelled: "Cancelled",
    };
    return labels[status] || status;
};

const getUtilizationClass = (percentage) => {
    if (percentage >= 100) return "text-red-600 font-semibold";
    if (percentage >= 90) return "text-orange-600 font-semibold";
    if (percentage >= 50) return "text-yellow-600";
    return "text-green-600";
};

const getDonorName = (donorId) => {
    const donor = donors.value.find((d) => d.id == donorId);
    return donor?.name || "Unknown";
};

const formatNumber = (num) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num || 0);
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getInitials = (name) => {
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase()
        .substring(0, 2);
};

// Lifecycle
onMounted(async () => {
    await loadProjects();
    await projectStore.fetchDonors();
});
</script>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
