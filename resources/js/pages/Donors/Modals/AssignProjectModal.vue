<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="closeModal"
    >
        <div
            class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto m-4"
        >
            <!-- Modal Header -->
            <div
                class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between"
            >
                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        Assign to Project
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Link {{ donor.name }} to a project with funding details
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
            <form @submit.prevent="handleSubmit" class="px-6 py-4">
                <div class="space-y-4">
                    <!-- Project Selection -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Select Project <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="formData.project_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.project_id }"
                        >
                            <option value="">-- Select a project --</option>
                            <option
                                v-for="project in availableProjects"
                                :key="project.id"
                                :value="project.id"
                            >
                                {{ project.name }} ({{ project.code }})
                            </option>
                        </select>
                        <p
                            v-if="errors.project_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.project_id[0] }}
                        </p>
                    </div>

                    <!-- Funding Amount -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Funding Amount (USD)
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="formData.funding_amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            required
                            placeholder="e.g., 500000.00"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.funding_amount }"
                        />
                        <p
                            v-if="errors.funding_amount"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.funding_amount[0] }}
                        </p>
                    </div>

                    <!-- Funding Period (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Funding Start Date
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.funding_start_date"
                                type="date"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'border-red-500': errors.funding_start_date,
                                }"
                            />
                            <p
                                v-if="errors.funding_start_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.funding_start_date[0] }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Funding End Date
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.funding_end_date"
                                type="date"
                                required
                                :min="formData.funding_start_date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'border-red-500': errors.funding_end_date,
                                }"
                            />
                            <p
                                v-if="errors.funding_end_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.funding_end_date[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Funding Type -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Funding Type <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-6">
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    v-model="formData.is_restricted"
                                    type="radio"
                                    :value="false"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">
                                    <i
                                        class="fas fa-unlock text-green-600 mr-1"
                                    ></i>
                                    Unrestricted
                                </span>
                            </label>
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    v-model="formData.is_restricted"
                                    type="radio"
                                    :value="true"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">
                                    <i
                                        class="fas fa-lock text-purple-600 mr-1"
                                    ></i>
                                    Restricted
                                </span>
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Restricted funding can only be used for specific
                            purposes defined by the donor
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Notes / Restrictions
                        </label>
                        <textarea
                            v-model="formData.notes"
                            rows="3"
                            placeholder="e.g., For equipment purchase only, Training programs in rural areas, etc."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Add any specific requirements or restrictions for
                            this funding
                        </p>
                    </div>

                    <!-- Summary Card -->
                    <div
                        class="bg-blue-50 rounded-lg p-4 border border-blue-200"
                    >
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">
                            Funding Summary
                        </h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Amount:</span>
                                <span class="font-medium text-gray-900 ml-1">
                                    ${{
                                        formatNumber(
                                            formData.funding_amount || 0,
                                        )
                                    }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Type:</span>
                                <span
                                    :class="
                                        formData.is_restricted
                                            ? 'text-purple-600'
                                            : 'text-green-600'
                                    "
                                    class="font-medium ml-1"
                                >
                                    {{
                                        formData.is_restricted
                                            ? "Restricted"
                                            : "Unrestricted"
                                    }}
                                </span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-600">Period:</span>
                                <span class="font-medium text-gray-900 ml-1">
                                    {{ formData.funding_start_date || "—" }} to
                                    {{ formData.funding_end_date || "—" }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3 -mx-6 -mb-4 mt-6"
                >
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50 transition"
                    >
                        <i class="fas fa-times mr-1.5"></i>
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting || !formData.project_id"
                        class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i
                            v-if="submitting"
                            class="fas fa-spinner fa-spin mr-2"
                        ></i>
                        <i v-else class="fas fa-link mr-1.5"></i>
                        {{ submitting ? "Assigning..." : "Assign to Project" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useDonorStore } from "../../../stores/donorStore";
import { useProjectStore } from "../../../stores/projectStore";

const props = defineProps({
    donor: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["close", "assigned"]);
const donorStore = useDonorStore();
const projectStore = useProjectStore();

const submitting = ref(false);
const errors = ref({});
const availableProjects = ref([]);

const formData = reactive({
    project_id: "",
    funding_amount: null,
    funding_start_date: "",
    funding_end_date: "",
    is_restricted: false,
    notes: "",
});

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {};

    try {
        await donorStore.assignToProject(props.donor.id, formData);
        emit("assigned");
    } catch (err) {
        if (typeof err === "object" && err !== null) {
            errors.value = err;
        } else {
            console.error("Error assigning project:", err);
        }
    } finally {
        submitting.value = false;
    }
};

const formatNumber = (value) => {
    if (!value) return "0.00";
    return parseFloat(value).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const closeModal = () => {
    emit("close");
};

// Load available projects
onMounted(async () => {
    await projectStore.fetchProjects(1);
    availableProjects.value = projectStore.projects.filter(
        (project) => project.status !== "cancelled",
    );
});
</script>
