<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Reports & Analytics
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Generate and manage financial reports
                    </p>
                </div>
                <button
                    @click="refreshReports"
                    :disabled="refreshing"
                    class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:opacity-50"
                >
                    <i
                        class="fas fa-sync-alt"
                        :class="{ 'fa-spin': refreshing }"
                    ></i>
                    {{ refreshing ? "Refreshing..." : "Refresh" }}
                </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'generate'"
                        :class="[
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'generate'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]"
                    >
                        <i class="fas fa-plus-circle mr-2"></i>
                        Generate Report
                    </button>
                    <button
                        @click="activeTab = 'history'"
                        :class="[
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'history'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        ]"
                    >
                        <i class="fas fa-history mr-2"></i>
                        Report History
                        <span
                            v-if="reports.length > 0"
                            class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs"
                        >
                            {{ reports.length }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Generate Report Tab -->
            <div v-if="activeTab === 'generate'" class="space-y-6">
                <!-- Report Type Cards -->
                <div class="grid grid-cols-6 gap-3">
                    <!-- Budget vs Actual Report -->
                    <button
                        @click="selectReportType('budget-vs-actual')"
                        class="flex flex-col items-center justify-center rounded-lg border-2 bg-white p-4 transition hover:shadow-md"
                        :class="
                            selectedReportType === 'budget-vs-actual'
                                ? 'border-blue-500 shadow-md'
                                : 'border-gray-200 hover:border-blue-300'
                        "
                    >
                        <i
                            class="fas fa-chart-line mb-2 text-3xl"
                            :class="
                                selectedReportType === 'budget-vs-actual'
                                    ? 'text-blue-600'
                                    : 'text-blue-500'
                            "
                        ></i>
                        <span
                            class="text-center text-xs font-medium text-gray-700"
                        >
                            Budget vs Actual
                        </span>
                    </button>

                    <!-- Cash Flow Report -->
                    <button
                        @click="selectReportType('cash-flow')"
                        class="flex flex-col items-center justify-center rounded-lg border-2 bg-white p-4 transition hover:shadow-md"
                        :class="
                            selectedReportType === 'cash-flow'
                                ? 'border-green-500 shadow-md'
                                : 'border-gray-200 hover:border-green-300'
                        "
                    >
                        <i
                            class="fas fa-money-bill-wave mb-2 text-3xl"
                            :class="
                                selectedReportType === 'cash-flow'
                                    ? 'text-green-600'
                                    : 'text-green-500'
                            "
                        ></i>
                        <span
                            class="text-center text-xs font-medium text-gray-700"
                        >
                            Cash Flow
                        </span>
                    </button>

                    <!-- Expense Summary Report -->
                    <button
                        @click="selectReportType('expense-summary')"
                        class="flex flex-col items-center justify-center rounded-lg border-2 bg-white p-4 transition hover:shadow-md"
                        :class="
                            selectedReportType === 'expense-summary'
                                ? 'border-purple-500 shadow-md'
                                : 'border-gray-200 hover:border-purple-300'
                        "
                    >
                        <i
                            class="fas fa-receipt mb-2 text-3xl"
                            :class="
                                selectedReportType === 'expense-summary'
                                    ? 'text-purple-600'
                                    : 'text-purple-500'
                            "
                        ></i>
                        <span
                            class="text-center text-xs font-medium text-gray-700"
                        >
                            Expense Summary
                        </span>
                    </button>

                    <!-- Project Status Report -->
                    <button
                        @click="selectReportType('project-status')"
                        class="flex flex-col items-center justify-center rounded-lg border-2 bg-white p-4 transition hover:shadow-md"
                        :class="
                            selectedReportType === 'project-status'
                                ? 'border-orange-500 shadow-md'
                                : 'border-gray-200 hover:border-orange-300'
                        "
                    >
                        <i
                            class="fas fa-tasks mb-2 text-3xl"
                            :class="
                                selectedReportType === 'project-status'
                                    ? 'text-orange-600'
                                    : 'text-orange-500'
                            "
                        ></i>
                        <span
                            class="text-center text-xs font-medium text-gray-700"
                        >
                            Project Status
                        </span>
                    </button>

                    <!-- Donor Contributions Report -->
                    <button
                        v-if="isProgramsManager"
                        @click="selectReportType('donor-contributions')"
                        class="flex flex-col items-center justify-center rounded-lg border-2 bg-white p-4 transition hover:shadow-md"
                        :class="
                            selectedReportType === 'donor-contributions'
                                ? 'border-pink-500 shadow-md'
                                : 'border-gray-200 hover:border-pink-300'
                        "
                    >
                        <i
                            class="fas fa-hands-helping mb-2 text-3xl"
                            :class="
                                selectedReportType === 'donor-contributions'
                                    ? 'text-pink-600'
                                    : 'text-pink-500'
                            "
                        ></i>
                        <span
                            class="text-center text-xs font-medium text-gray-700"
                        >
                            Donor Contributions
                        </span>
                    </button>
                </div>

                <!-- Report Generation Form -->
                <div
                    v-if="selectedReportType"
                    class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">
                        Generate {{ getReportTitle(selectedReportType) }}
                    </h3>

                    <form @submit.prevent="generateReport">
                        <div class="flex items-end gap-3">
                            <!-- Date Range (All except Project Status) -->
                            <template
                                v-if="selectedReportType !== 'project-status'"
                            >
                                <div class="flex-1">
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700"
                                    >
                                        Start Date
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="reportForm.start_date"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        required
                                    />
                                </div>
                                <div class="flex-1">
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700"
                                    >
                                        End Date
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="reportForm.end_date"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        required
                                    />
                                </div>
                            </template>

                            <!-- Project Selection (Project Status) -->
                            <div
                                v-if="selectedReportType === 'project-status'"
                                class="flex-1"
                            >
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Select Project
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="reportForm.project_id"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    required
                                >
                                    <option value="">
                                        Choose a project...
                                    </option>
                                    <option
                                        v-for="project in projects"
                                        :key="project?.id"
                                        :value="project?.id"
                                    >
                                        {{ project?.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Grouping (Cash Flow) -->
                            <div
                                v-if="selectedReportType === 'cash-flow'"
                                class="flex-1"
                            >
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Group By
                                </label>
                                <select
                                    v-model="reportForm.grouping"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                                    <option value="month">Month</option>
                                    <option value="quarter">Quarter</option>
                                    <option value="year">Year</option>
                                </select>
                            </div>

                            <!-- Group By (Expense Summary) -->
                            <div
                                v-if="selectedReportType === 'expense-summary'"
                                class="flex-1"
                            >
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Group By
                                </label>
                                <select
                                    v-model="reportForm.group_by"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                                    <option value="category">Category</option>
                                    <option value="project">Project</option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="selectedReportType = null"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900 disabled:opacity-50"
                                >
                                    <i
                                        v-if="loading"
                                        class="fas fa-spinner fa-spin"
                                    ></i>
                                    <i v-else class="fas fa-file-pdf"></i>
                                    {{
                                        loading
                                            ? "Generating..."
                                            : "Generate Report"
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report History Tab -->
            <div v-if="activeTab === 'history'" class="space-y-4">
                <!-- Filters Card - Horizontal Layout -->
                <div class="rounded-lg bg-white p-3 shadow">
                    <div class="flex items-center gap-3">
                        <!-- Search -->
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search reports..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            />
                        </div>
                        <!-- Report Type Filter -->
                        <div class="w-64">
                            <select
                                v-model="filterType"
                                @change="filterReports"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                                <option value="">All Types</option>
                                <option value="budget-vs-actual">
                                    Budget vs Actual
                                </option>
                                <option value="cash-flow">Cash Flow</option>
                                <option value="expense-summary">
                                    Expense Summary
                                </option>
                                <option value="project-status">
                                    Project Status
                                </option>
                                <option value="donor-contributions">
                                    Donor Contributions
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Reports Table -->
                <div
                    class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Report
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Generated
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="report in filteredReports"
                                :key="report.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <i
                                            class="fas fa-file-pdf mr-3 text-red-500"
                                        ></i>
                                        <div>
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ report.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Generated by
                                                {{ report.generated_by?.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="
                                            getReportTypeBadge(
                                                report.report_type,
                                            )
                                        "
                                    >
                                        {{
                                            formatReportType(report.report_type)
                                        }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ formatDate(report.created_at) }}
                                    </div>
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                >
                                    <button
                                        @click="downloadReport(report.id)"
                                        class="mr-3 text-blue-600 hover:text-blue-900"
                                        title="Download PDF"
                                    >
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button
                                        @click="deleteReport(report.id)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete Report"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredReports.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <i
                                        class="fas fa-file-invoice mb-3 text-4xl text-gray-300"
                                    ></i>
                                    <p class="text-sm text-gray-500">
                                        No reports found. Generate your first
                                        report!
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { useAuthStore } from "../stores/authStore";
import api from "../api";
import { showSuccess, showError, confirmAction } from "../plugins/sweetalert";

const authStore = useAuthStore();
const isProgramsManager = computed(() => authStore.isProgramsManager);

const activeTab = ref("generate");
const selectedReportType = ref(null);
const loading = ref(false);
const refreshing = ref(false);
const reports = ref([]);
const projects = ref([]);
const searchQuery = ref("");
const filterType = ref("");

const reportForm = ref({
    start_date: "",
    end_date: "",
    project_id: "",
    grouping: "month",
    group_by: "category",
});

const selectReportType = (type) => {
    selectedReportType.value = type;
    // Reset form
    reportForm.value = {
        start_date: "",
        end_date: "",
        project_id: "",
        grouping: "month",
        group_by: "category",
    };
};

const getReportTitle = (type) => {
    const titles = {
        "budget-vs-actual": "Budget vs Actual Report",
        "cash-flow": "Cash Flow Report",
        "expense-summary": "Expense Summary Report",
        "project-status": "Project Status Report",
        "donor-contributions": "Donor Contributions Report",
    };
    return titles[type] || "Report";
};

const generateReport = async () => {
    loading.value = true;
    let reportGenerated = false;
    let reportId = null;

    try {
        const endpoint = `/reports/${selectedReportType.value}`;
        // Build payload, stripping empty optional fields
        const payload = { ...reportForm.value };
        if (!payload.project_id) {
            delete payload.project_id;
        }

        const response = await api.post(endpoint, payload);
        reportGenerated = true;

        // Get the generated report ID and auto-download
        reportId = response.data?.data?.id;

        // Try to download, but don't fail if download is blocked
        if (reportId) {
            try {
                await downloadReportSilent(reportId);
            } catch (downloadError) {
                // Download failed (possibly blocked by ad blocker) - continue anyway
                console.warn("Auto-download failed:", downloadError);
            }
        }

        await showSuccess(
            "Report Generated!",
            "Your report has been generated successfully. You can download it from the history tab.",
        );

        // Refresh reports list
        await fetchReports();

        // Switch to history tab
        activeTab.value = "history";
        selectedReportType.value = null;
    } catch (error) {
        if (error.response?.status === 422) {
            const errors = Object.values(error.response.data.errors).flat();
            await showError("Validation Error", errors.join("\n"));
        } else {
            await showError(
                "Generation Failed",
                error.response?.data?.message ||
                    "Failed to generate report. Please try again.",
            );
        }
    } finally {
        loading.value = false;
    }
};

const fetchReports = async () => {
    try {
        const response = await api.get("/reports");
        reports.value = response.data.data.data || [];
    } catch (error) {
        console.error("Failed to fetch reports:", error);
    }
};

const fetchProjects = async () => {
    try {
        const response = await api.get("/projects");
        // Handle various response formats:
        // 1. Wrapped paginated: { success, data: { data: [...] } }
        // 2. Direct paginated: { data: [...] }
        // 3. Direct array: [...]
        const responseData = response.data;
        if (Array.isArray(responseData)) {
            projects.value = responseData;
        } else if (responseData?.data) {
            const innerData = responseData.data;
            if (Array.isArray(innerData)) {
                projects.value = innerData;
            } else if (innerData?.data && Array.isArray(innerData.data)) {
                projects.value = innerData.data;
            } else {
                projects.value = [];
            }
        } else {
            projects.value = [];
        }
    } catch (error) {
        console.error("Failed to fetch projects:", error);
    }
};

// Silent download - doesn't show error messages (used during auto-download after generation)
const downloadReportSilent = async (reportId) => {
    const response = await api.get(`/reports/${reportId}/pdf`, {
        responseType: "blob",
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `report-${reportId}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
};

const downloadReport = async (reportId) => {
    try {
        await downloadReportSilent(reportId);
    } catch (error) {
        console.error("Download error:", error);
        await showError(
            "Download Failed",
            "Failed to download report. Please try again or check if your browser is blocking downloads.",
        );
    }
};

const refreshReports = async () => {
    refreshing.value = true;
    try {
        await fetchReports();
        await fetchProjects();
    } finally {
        refreshing.value = false;
    }
};

const deleteReport = async (reportId) => {
    const confirmed = await confirmAction(
        "Delete Report",
        "Are you sure you want to delete this report? This action cannot be undone.",
        "Yes, Delete",
        "Cancel",
    );

    if (confirmed) {
        try {
            await api.delete(`/reports/${reportId}`);
            await showSuccess(
                "Report Deleted",
                "Report has been deleted successfully.",
            );
            await fetchReports();
        } catch (error) {
            await showError(
                "Delete Failed",
                "Failed to delete report. Please try again.",
            );
        }
    }
};

const filterReports = () => {
    // Filtering is handled by computed property
};

const filteredReports = computed(() => {
    let filtered = reports.value;

    if (filterType.value) {
        filtered = filtered.filter((r) => r.report_type === filterType.value);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter((r) =>
            r.title.toLowerCase().includes(query),
        );
    }

    return filtered;
});

const formatReportType = (type) => {
    if (!type) return "Unknown";
    return type
        .split("-")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};

const getReportTypeBadge = (type) => {
    if (!type) return "bg-gray-100 text-gray-800";
    const badges = {
        "budget-vs-actual": "bg-blue-100 text-blue-800",
        "cash-flow": "bg-green-100 text-green-800",
        "expense-summary": "bg-purple-100 text-purple-800",
        "project-status": "bg-orange-100 text-orange-800",
        "donor-contributions": "bg-pink-100 text-pink-800",
    };
    return badges[type] || "bg-gray-100 text-gray-800";
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

onMounted(async () => {
    await fetchReports();
    await fetchProjects();
});
</script>
