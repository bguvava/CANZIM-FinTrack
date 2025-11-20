<template>
    <div class="view-project-container">
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
                    <span>{{ project.name }}</span>
                </div>
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ project.name }}
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ project.code }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a
                            v-if="canUpdate"
                            :href="`/projects/${project.id}/edit`"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
                        >
                            <i class="fas fa-edit"></i>
                            Edit
                        </a>
                        <button
                            @click="handleGenerateReport"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2"
                        >
                            <i class="fas fa-file-pdf"></i>
                            Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="mb-6">
                <span
                    :class="getStatusClass(project.status)"
                    class="px-4 py-2 rounded-full text-sm font-medium"
                >
                    {{ getStatusLabel(project.status) }}
                </span>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex gap-4 px-6" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'py-4 px-1 border-b-2 font-medium text-sm transition',
                                activeTab === tab.id
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            ]"
                        >
                            <i :class="tab.icon" class="mr-2"></i>
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Overview Tab -->
                    <div v-show="activeTab === 'overview'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Info -->
                            <div class="space-y-4">
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3"
                                >
                                    Project Details
                                </h3>

                                <div>
                                    <label class="text-sm text-gray-600"
                                        >Description</label
                                    >
                                    <p class="text-gray-900 mt-1">
                                        {{
                                            project.description ||
                                            "No description provided"
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600"
                                        >Location</label
                                    >
                                    <p class="text-gray-900 mt-1">
                                        {{
                                            project.location || "Not specified"
                                        }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-600"
                                            >Start Date</label
                                        >
                                        <p class="text-gray-900 mt-1">
                                            {{ formatDate(project.start_date) }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-600"
                                            >End Date</label
                                        >
                                        <p class="text-gray-900 mt-1">
                                            {{ formatDate(project.end_date) }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600"
                                        >Created By</label
                                    >
                                    <p class="text-gray-900 mt-1">
                                        {{ project.creator?.name || "Unknown" }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ formatDateTime(project.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Donors -->
                            <div>
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3"
                                >
                                    Donors & Funding
                                </h3>

                                <div
                                    v-if="
                                        project.donors &&
                                        project.donors.length > 0
                                    "
                                    class="space-y-3"
                                >
                                    <div
                                        v-for="donor in project.donors"
                                        :key="donor.id"
                                        class="p-4 bg-gray-50 rounded-lg"
                                    >
                                        <div
                                            class="flex justify-between items-start"
                                        >
                                            <div>
                                                <div
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{ donor.name }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-600"
                                                >
                                                    {{ donor.contact_person }}
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div
                                                    class="font-bold text-green-600"
                                                >
                                                    ${{
                                                        formatNumber(
                                                            donor.pivot
                                                                ?.funding_amount ||
                                                                0,
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{
                                                        donor.pivot
                                                            ?.is_restricted
                                                            ? "Restricted"
                                                            : "Unrestricted"
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-3 border-t border-gray-200">
                                        <div
                                            class="flex justify-between text-sm font-semibold"
                                        >
                                            <span class="text-gray-700"
                                                >Total Funding</span
                                            >
                                            <span class="text-green-600">
                                                ${{
                                                    formatNumber(totalFunding)
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-gray-500 text-sm">
                                    No donors assigned
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Tab -->
                    <div v-show="activeTab === 'budget'">
                        <div v-if="project.budget_summary">
                            <!-- Budget Summary Cards -->
                            <div
                                class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6"
                            >
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-sm text-blue-600">
                                        Total Allocated
                                    </div>
                                    <div
                                        class="text-2xl font-bold text-blue-900"
                                    >
                                        ${{
                                            formatNumber(
                                                project.budget_summary
                                                    .total_allocated,
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-sm text-green-600">
                                        Total Spent
                                    </div>
                                    <div
                                        class="text-2xl font-bold text-green-900"
                                    >
                                        ${{
                                            formatNumber(
                                                project.budget_summary
                                                    .total_spent,
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <div class="text-sm text-purple-600">
                                        Remaining
                                    </div>
                                    <div
                                        class="text-2xl font-bold text-purple-900"
                                    >
                                        ${{
                                            formatNumber(
                                                project.budget_summary
                                                    .total_allocated -
                                                    project.budget_summary
                                                        .total_spent,
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <div class="text-sm text-orange-600">
                                        Utilization
                                    </div>
                                    <div
                                        class="text-2xl font-bold text-orange-900"
                                    >
                                        {{
                                            project.budget_summary
                                                .utilization_percentage
                                        }}%
                                    </div>
                                </div>
                            </div>

                            <!-- Budget Alert -->
                            <div
                                v-if="project.budget_summary.alert_level"
                                :class="
                                    getAlertClass(
                                        project.budget_summary.alert_level,
                                    )
                                "
                                class="p-4 rounded-lg mb-6"
                            >
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span class="font-semibold">{{
                                        getAlertMessage(
                                            project.budget_summary.alert_level,
                                        )
                                    }}</span>
                                </div>
                            </div>

                            <!-- Budget List -->
                            <div
                                v-if="
                                    project.budgets &&
                                    project.budgets.length > 0
                                "
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-4"
                                >
                                    Budget Details
                                </h3>
                                <div class="space-y-3">
                                    <div
                                        v-for="budget in project.budgets"
                                        :key="budget.id"
                                        class="p-4 border border-gray-200 rounded-lg hover:border-blue-500 transition"
                                    >
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <div>
                                                <div
                                                    class="font-semibold text-gray-900"
                                                >
                                                    Budget #{{ budget.id }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-600"
                                                >
                                                    {{
                                                        budget.items?.length ||
                                                        0
                                                    }}
                                                    line items
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div
                                                    class="font-bold text-gray-900"
                                                >
                                                    ${{
                                                        formatNumber(
                                                            budget.total_allocated,
                                                        )
                                                    }}
                                                </div>
                                                <span
                                                    :class="
                                                        getStatusClass(
                                                            budget.status,
                                                        )
                                                    "
                                                    class="text-xs px-2 py-1 rounded-full"
                                                >
                                                    {{
                                                        getStatusLabel(
                                                            budget.status,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <i
                                class="fas fa-calculator text-4xl text-gray-300 mb-3"
                            ></i>
                            <p class="text-gray-600">No budget created yet</p>
                            <a
                                href="/budgets/create"
                                class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Create Budget
                            </a>
                        </div>
                    </div>

                    <!-- Team Tab -->
                    <div v-show="activeTab === 'team'">
                        <div
                            v-if="
                                project.team_members &&
                                project.team_members.length > 0
                            "
                        >
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-4"
                            >
                                Team Members
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="member in project.team_members"
                                    :key="member.id"
                                    class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg"
                                >
                                    <div
                                        class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-lg font-bold text-gray-700"
                                    >
                                        {{ getInitials(member.name) }}
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="font-semibold text-gray-900"
                                        >
                                            {{ member.name }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ member.email }}
                                        </div>
                                        <div
                                            v-if="member.role"
                                            class="text-xs text-gray-500 mt-1"
                                        >
                                            {{ member.role }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <i
                                class="fas fa-users text-4xl text-gray-300 mb-3"
                            ></i>
                            <p class="text-gray-600">
                                No team members assigned
                            </p>
                        </div>
                    </div>

                    <!-- Activities Tab -->
                    <div v-show="activeTab === 'activities'">
                        <div class="text-center py-12">
                            <i
                                class="fas fa-history text-4xl text-gray-300 mb-3"
                            ></i>
                            <p class="text-gray-600">
                                Activity tracking will be available in Module 7
                            </p>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div v-show="activeTab === 'documents'">
                        <div class="text-center py-12">
                            <i
                                class="fas fa-file-alt text-4xl text-gray-300 mb-3"
                            ></i>
                            <p class="text-gray-600">
                                Document management will be available in Module
                                7
                            </p>
                        </div>
                    </div>

                    <!-- Comments Tab -->
                    <div v-show="activeTab === 'comments'">
                        <CommentsSection
                            commentableType="Project"
                            :commentableId="parseInt(projectId)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useProjectStore } from "../../stores/projectStore";
import { useAuthStore } from "../../stores/authStore";
import CommentsSection from "../../components/comments/CommentsSection.vue";

const projectStore = useProjectStore();
const authStore = useAuthStore();

// Get project ID from URL
const projectId = window.location.pathname.split("/")[2];

// State
const loading = ref(false);
const activeTab = ref("overview");

const tabs = [
    { id: "overview", label: "Overview", icon: "fas fa-info-circle" },
    { id: "budget", label: "Budget", icon: "fas fa-calculator" },
    { id: "team", label: "Team", icon: "fas fa-users" },
    { id: "activities", label: "Activities", icon: "fas fa-history" },
    { id: "documents", label: "Documents", icon: "fas fa-file-alt" },
    { id: "comments", label: "Comments", icon: "fas fa-comments" },
];

// Computed
const project = computed(() => projectStore.currentProject);

const canUpdate = computed(() => {
    return authStore.hasPermission("update-project");
});

const totalFunding = computed(() => {
    if (!project.value?.donors) return 0;
    return project.value.donors.reduce((sum, donor) => {
        return sum + parseFloat(donor.pivot?.funding_amount || 0);
    }, 0);
});

// Methods
const loadProject = async () => {
    loading.value = true;
    try {
        await projectStore.fetchProject(projectId);
    } catch (error) {
        window.$swal.fire({
            icon: "error",
            title: "Failed to Load Project",
            text: error.message || "Could not load project details",
        });
    } finally {
        loading.value = false;
    }
};

const handleGenerateReport = async () => {
    try {
        await projectStore.generateReport(projectId, "pdf");
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
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
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
        pending: "Pending",
        approved: "Approved",
        rejected: "Rejected",
    };
    return labels[status] || status;
};

const getAlertClass = (level) => {
    const classes = {
        warning: "bg-yellow-50 text-yellow-800",
        critical: "bg-red-50 text-red-800",
    };
    return classes[level] || "";
};

const getAlertMessage = (level) => {
    const messages = {
        warning: "Budget utilization is approaching 90%",
        critical: "Budget utilization has exceeded 100%",
    };
    return messages[level] || "";
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
        month: "long",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
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
onMounted(() => {
    loadProject();
});
</script>
