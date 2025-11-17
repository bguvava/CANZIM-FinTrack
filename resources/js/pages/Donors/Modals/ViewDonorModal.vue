<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="closeModal"
    >
        <div
            class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto m-4"
        >
            <!-- Modal Header -->
            <div
                class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10"
            >
                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ donor.name }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <span
                            :class="
                                donor.status === 'active'
                                    ? 'text-green-600'
                                    : 'text-gray-600'
                            "
                        >
                            <i
                                :class="
                                    donor.status === 'active'
                                        ? 'fas fa-check-circle'
                                        : 'fas fa-times-circle'
                                "
                            ></i>
                            {{
                                donor.status === "active"
                                    ? "Active"
                                    : "Inactive"
                            }}
                        </span>
                    </p>
                </div>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 transition"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-blue-800 text-blue-800'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            ]"
                        >
                            <i :class="tab.icon + ' mr-2'"></i>
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="space-y-6">
                    <!-- Overview Tab -->
                    <div v-show="activeTab === 'overview'">
                        <!-- Donor Information Card -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-4"
                            >
                                Donor Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Organization Name
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ donor.name }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Contact Person
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ donor.contact_person || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Email
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ donor.email || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Phone
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ donor.phone || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Tax ID
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ donor.tax_id || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Website
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <a
                                            v-if="donor.website"
                                            :href="donor.website"
                                            target="_blank"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            {{ donor.website }}
                                            <i
                                                class="fas fa-external-link-alt ml-1 text-xs"
                                            ></i>
                                        </a>
                                        <span v-else>—</span>
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Address
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ donor.address || "—" }}
                                    </p>
                                </div>
                                <div class="md:col-span-2" v-if="donor.notes">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Notes
                                    </label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ donor.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Funding Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-blue-600 mb-1">
                                    Total Funding
                                </p>
                                <h4 class="text-2xl font-bold text-blue-900">
                                    ${{ formatNumber(donor.total_funding) }}
                                </h4>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <p class="text-sm text-purple-600 mb-1">
                                    Restricted
                                </p>
                                <h4 class="text-2xl font-bold text-purple-900">
                                    ${{
                                        formatNumber(donor.restricted_funding)
                                    }}
                                </h4>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <p class="text-sm text-green-600 mb-1">
                                    Unrestricted
                                </p>
                                <h4 class="text-2xl font-bold text-green-900">
                                    ${{
                                        formatNumber(donor.unrestricted_funding)
                                    }}
                                </h4>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-4">
                                <p class="text-sm text-orange-600 mb-1">
                                    In-Kind Value
                                </p>
                                <h4 class="text-2xl font-bold text-orange-900">
                                    ${{ formatNumber(donor.in_kind_total) }}
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Tab -->
                    <div v-show="activeTab === 'projects'">
                        <div
                            v-if="donor.projects && donor.projects.length > 0"
                            class="overflow-x-auto"
                        >
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Project
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Funding Amount
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Period
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr
                                        v-for="project in donor.projects"
                                        :key="project.id"
                                    >
                                        <td class="px-4 py-3">
                                            <div
                                                class="font-medium text-gray-900"
                                            >
                                                {{ project.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ project.code }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            ${{
                                                formatNumber(
                                                    project.pivot
                                                        .funding_amount,
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="
                                                    project.pivot.is_restricted
                                                        ? 'bg-purple-100 text-purple-800'
                                                        : 'bg-green-100 text-green-800'
                                                "
                                                class="px-2 py-1 rounded-full text-xs font-medium"
                                            >
                                                {{
                                                    project.pivot.is_restricted
                                                        ? "Restricted"
                                                        : "Unrestricted"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-600"
                                        >
                                            {{
                                                formatDate(
                                                    project.pivot
                                                        .funding_start_date,
                                                )
                                            }}
                                            -
                                            {{
                                                formatDate(
                                                    project.pivot
                                                        .funding_end_date,
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8">
                            <i
                                class="fas fa-folder-open text-4xl text-gray-300 mb-2"
                            ></i>
                            <p class="text-gray-500">No projects assigned</p>
                        </div>
                    </div>

                    <!-- In-Kind Contributions Tab -->
                    <div v-show="activeTab === 'inkind'">
                        <div
                            v-if="
                                donor.in_kind_contributions &&
                                donor.in_kind_contributions.length > 0
                            "
                            class="overflow-x-auto"
                        >
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Date
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Description
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Category
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Estimated Value
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr
                                        v-for="contribution in donor.in_kind_contributions"
                                        :key="contribution.id"
                                    >
                                        <td
                                            class="px-4 py-3 text-sm text-gray-600"
                                        >
                                            {{
                                                formatDate(
                                                    contribution.contribution_date,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{ contribution.description }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium"
                                            >
                                                {{ contribution.category }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm font-medium text-gray-900"
                                        >
                                            ${{
                                                formatNumber(
                                                    contribution.estimated_value,
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8">
                            <i
                                class="fas fa-gift text-4xl text-gray-300 mb-2"
                            ></i>
                            <p class="text-gray-500">
                                No in-kind contributions recorded
                            </p>
                        </div>
                    </div>

                    <!-- Communications Tab -->
                    <div v-show="activeTab === 'communications'">
                        <div
                            v-if="
                                donor.communications &&
                                donor.communications.length > 0
                            "
                            class="space-y-4"
                        >
                            <div
                                v-for="communication in donor.communications"
                                :key="communication.id"
                                class="bg-gray-50 rounded-lg p-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div
                                            class="flex items-center gap-2 mb-2"
                                        >
                                            <span
                                                class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium"
                                            >
                                                {{ communication.type }}
                                            </span>
                                            <span class="text-sm text-gray-600">
                                                {{
                                                    formatDate(
                                                        communication.communication_date,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <h4 class="font-medium text-gray-900">
                                            {{ communication.subject }}
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ communication.notes }}
                                        </p>
                                        <div
                                            v-if="
                                                communication.next_action_date
                                            "
                                            class="mt-2 text-sm text-orange-600"
                                        >
                                            <i
                                                class="fas fa-calendar-alt mr-1"
                                            ></i>
                                            Next action:
                                            {{
                                                formatDate(
                                                    communication.next_action_date,
                                                )
                                            }}
                                            <span
                                                v-if="
                                                    communication.next_action_notes
                                                "
                                            >
                                                -
                                                {{
                                                    communication.next_action_notes
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                    <a
                                        v-if="communication.attachment_path"
                                        :href="communication.attachment_path"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 ml-4"
                                    >
                                        <i class="fas fa-paperclip"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <i
                                class="fas fa-comments text-4xl text-gray-300 mb-2"
                            ></i>
                            <p class="text-gray-500">
                                No communications logged
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between"
            >
                <div class="flex gap-2">
                    <button
                        v-if="canUpdate"
                        @click="handleEdit"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        Edit
                    </button>
                    <button
                        @click="handleGenerateReport"
                        :disabled="isGeneratingReport"
                        class="px-4 py-2 border border-blue-300 rounded-lg text-blue-700 hover:bg-blue-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i
                            :class="
                                isGeneratingReport
                                    ? 'fas fa-spinner fa-spin mr-2'
                                    : 'fas fa-file-pdf mr-2'
                            "
                        ></i>
                        {{
                            isGeneratingReport
                                ? "Generating..."
                                : "Generate Report"
                        }}
                    </button>
                    <button
                        v-if="canUpdate"
                        @click="handleAssignProject"
                        class="px-4 py-2 border border-green-300 rounded-lg text-green-700 hover:bg-green-50 transition"
                    >
                        <i class="fas fa-link mr-2"></i>
                        Assign Project
                    </button>
                    <button
                        v-if="canUpdate"
                        @click="handleAddInKind"
                        class="px-4 py-2 border border-purple-300 rounded-lg text-purple-700 hover:bg-purple-50 transition"
                    >
                        <i class="fas fa-gift mr-2"></i>
                        Add In-Kind
                    </button>
                    <button
                        v-if="canUpdate"
                        @click="handleLogCommunication"
                        class="px-4 py-2 border border-orange-300 rounded-lg text-orange-700 hover:bg-orange-50 transition"
                    >
                        <i class="fas fa-comment mr-2"></i>
                        Log Communication
                    </button>
                </div>
                <button
                    @click="closeModal"
                    class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "../../../stores/authStore";
import { useDonorStore } from "../../../stores/donorStore";
import Swal from "sweetalert2";

const props = defineProps({
    donor: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    "close",
    "edit",
    "assign-project",
    "add-inkind",
    "log-communication",
]);
const authStore = useAuthStore();
const donorStore = useDonorStore();

const activeTab = ref("overview");
const isGeneratingReport = ref(false);

const tabs = [
    { id: "overview", label: "Overview", icon: "fas fa-info-circle" },
    { id: "projects", label: "Projects", icon: "fas fa-project-diagram" },
    { id: "inkind", label: "In-Kind", icon: "fas fa-gift" },
    { id: "communications", label: "Communications", icon: "fas fa-comments" },
];

const canUpdate = computed(() => authStore.hasPermission("update-donors"));

const formatNumber = (value) => {
    if (!value) return "0.00";
    return parseFloat(value).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const handleEdit = () => {
    emit("edit", props.donor);
};

const handleGenerateReport = async () => {
    isGeneratingReport.value = true;

    try {
        await donorStore.generateReport(props.donor.id);

        Swal.fire({
            icon: "success",
            title: "Report Generated",
            text: "PDF report downloaded successfully",
            timer: 2000,
            showConfirmButton: false,
        });
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Generation Failed",
            text: error.message || "Failed to generate report",
        });
    } finally {
        isGeneratingReport.value = false;
    }
};

const handleAssignProject = () => {
    emit("assign-project", props.donor);
};

const handleAddInKind = () => {
    emit("add-inkind", props.donor);
};

const handleLogCommunication = () => {
    emit("log-communication", props.donor);
};

const closeModal = () => {
    emit("close");
};
</script>
