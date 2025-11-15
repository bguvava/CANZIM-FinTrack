<template>
    <div class="add-project-container max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                <a href="/projects" class="hover:text-blue-600">Projects</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span>New Project</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Create New Project</h1>
        </div>

        <!-- Multi-step Progress -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div
                    v-for="(step, index) in steps"
                    :key="index"
                    class="flex items-center flex-1"
                >
                    <div class="flex items-center gap-3">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-semibold transition',
                                currentStep > index
                                    ? 'bg-green-500 text-white'
                                    : currentStep === index
                                      ? 'bg-blue-600 text-white'
                                      : 'bg-gray-200 text-gray-500',
                            ]"
                        >
                            <i
                                v-if="currentStep > index"
                                class="fas fa-check"
                            ></i>
                            <span v-else>{{ index + 1 }}</span>
                        </div>
                        <span
                            :class="[
                                'text-sm font-medium hidden md:block',
                                currentStep >= index
                                    ? 'text-gray-900'
                                    : 'text-gray-500',
                            ]"
                        >
                            {{ step }}
                        </span>
                    </div>
                    <div
                        v-if="index < steps.length - 1"
                        :class="[
                            'flex-1 h-1 mx-4 transition',
                            currentStep > index
                                ? 'bg-green-500'
                                : 'bg-gray-200',
                        ]"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form @submit.prevent="handleSubmit">
                <!-- Step 1: Basic Information -->
                <div v-show="currentStep === 0">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Basic Information
                    </h2>

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
                                placeholder="Enter project name"
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
                                placeholder="e.g., Lusaka, Zambia"
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
                                placeholder="Describe the project objectives and activities..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Donor Assignment -->
                <div v-show="currentStep === 1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Assign Donors
                    </h2>

                    <div
                        v-if="availableDonors.length === 0"
                        class="text-center py-8"
                    >
                        <i
                            class="fas fa-hand-holding-usd text-4xl text-gray-300 mb-3"
                        ></i>
                        <p class="text-gray-600">No donors available</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="donor in availableDonors"
                            :key="donor.id"
                            class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition"
                        >
                            <div class="flex items-start gap-4">
                                <input
                                    type="checkbox"
                                    :id="`donor-${donor.id}`"
                                    :value="donor.id"
                                    v-model="selectedDonorIds"
                                    class="mt-1"
                                />
                                <div class="flex-1">
                                    <label
                                        :for="`donor-${donor.id}`"
                                        class="cursor-pointer"
                                    >
                                        <div
                                            class="font-semibold text-gray-900"
                                        >
                                            {{ donor.name }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ donor.contact_person }}
                                        </div>
                                    </label>

                                    <div
                                        v-if="
                                            selectedDonorIds.includes(donor.id)
                                        "
                                        class="mt-3 grid grid-cols-2 gap-3"
                                    >
                                        <div>
                                            <label
                                                class="block text-sm text-gray-700 mb-1"
                                            >
                                                Funding Amount ($)
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>
                                            <input
                                                v-model.number="
                                                    donorFunding[donor.id]
                                                        .funding_amount
                                                "
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm text-gray-700 mb-1"
                                            >
                                                Funding Type
                                            </label>
                                            <select
                                                v-model="
                                                    donorFunding[donor.id]
                                                        .is_restricted
                                                "
                                                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                            >
                                                <option :value="false">
                                                    Unrestricted
                                                </option>
                                                <option :value="true">
                                                    Restricted
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Team Assignment -->
                <div v-show="currentStep === 2">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Assign Team Members
                    </h2>

                    <div
                        v-if="availableTeamMembers.length === 0"
                        class="text-center py-8"
                    >
                        <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-600">No team members available</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="member in availableTeamMembers"
                            :key="member.id"
                            class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition"
                        >
                            <label
                                :for="`member-${member.id}`"
                                class="flex items-center gap-3 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    :id="`member-${member.id}`"
                                    :value="member.id"
                                    v-model="formData.team_member_ids"
                                />
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">
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
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div
                    class="flex justify-between mt-8 pt-6 border-t border-gray-200"
                >
                    <button
                        v-if="currentStep > 0"
                        type="button"
                        @click="currentStep--"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                    >
                        <i class="fas fa-chevron-left mr-2"></i>
                        Previous
                    </button>
                    <div v-else></div>

                    <div class="flex gap-3">
                        <a
                            href="/projects"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancel
                        </a>
                        <button
                            v-if="currentStep < steps.length - 1"
                            type="button"
                            @click="nextStep"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            Next
                            <i class="fas fa-chevron-right ml-2"></i>
                        </button>
                        <button
                            v-else
                            type="submit"
                            :disabled="loading"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <i
                                v-if="loading"
                                class="fas fa-spinner fa-spin"
                            ></i>
                            <i v-else class="fas fa-check"></i>
                            {{ loading ? "Creating..." : "Create Project" }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useProjectStore } from "../../stores/projectStore";

const projectStore = useProjectStore();

// State
const currentStep = ref(0);
const loading = ref(false);
const errors = ref({});
const steps = ["Basic Info", "Donors", "Team"];

const formData = reactive({
    name: "",
    description: "",
    start_date: "",
    end_date: "",
    status: "planning",
    location: "",
    team_member_ids: [],
});

const selectedDonorIds = ref([]);
const donorFunding = reactive({});

// Computed
const availableDonors = computed(() => projectStore.donors);
const availableTeamMembers = computed(() => projectStore.teamMembers);

// Watch selected donors to initialize funding data
const initializeDonorFunding = (donorId) => {
    if (!donorFunding[donorId]) {
        donorFunding[donorId] = {
            funding_amount: 0,
            is_restricted: false,
        };
    }
};

// Methods
const nextStep = () => {
    errors.value = {};

    if (currentStep.value === 0) {
        // Validate basic info
        if (!formData.name) {
            errors.value.name = ["Project name is required"];
            return;
        }
        if (!formData.start_date) {
            errors.value.start_date = ["Start date is required"];
            return;
        }
        if (!formData.end_date) {
            errors.value.end_date = ["End date is required"];
            return;
        }
        if (new Date(formData.end_date) <= new Date(formData.start_date)) {
            errors.value.end_date = ["End date must be after start date"];
            return;
        }
    }

    currentStep.value++;
};

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {};

    try {
        // Prepare donors data
        const donors = selectedDonorIds.value.map((donorId) => ({
            donor_id: donorId,
            funding_amount: donorFunding[donorId].funding_amount,
            is_restricted: donorFunding[donorId].is_restricted,
        }));

        const payload = {
            ...formData,
            donors,
        };

        const project = await projectStore.createProject(payload);

        window.$toast.fire({
            icon: "success",
            title: "Project created successfully",
        });

        // Redirect to project view
        setTimeout(() => {
            window.location.href = `/projects/${project.id}`;
        }, 1000);
    } catch (err) {
        if (typeof err === "object" && err !== null) {
            errors.value = err;
            currentStep.value = 0; // Go back to first step to show errors
        }

        window.$swal.fire({
            icon: "error",
            title: "Failed to Create Project",
            text: projectStore.error || "Please check the form and try again",
        });
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(async () => {
    await projectStore.fetchDonors();
    await projectStore.fetchTeamMembers();

    // Initialize donor funding for all available donors
    availableDonors.value.forEach((donor) => {
        initializeDonorFunding(donor.id);
    });
});

// Watch for donor selection changes
const watchDonorSelection = () => {
    selectedDonorIds.value.forEach((donorId) => {
        initializeDonorFunding(donorId);
    });
};

// Call watch function whenever selectedDonorIds changes
const unwatchDonors = () => {
    watchDonorSelection();
};
</script>
