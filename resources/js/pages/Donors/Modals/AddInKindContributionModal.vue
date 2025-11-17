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
                        Record In-Kind Contribution
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Log non-cash donation from {{ donor.name }}
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
                            Project <span class="text-red-500">*</span>
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

                    <!-- Category -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="formData.category"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.category }"
                        >
                            <option value="">-- Select category --</option>
                            <option value="equipment">
                                <i class="fas fa-tools"></i> Equipment
                            </option>
                            <option value="supplies">
                                <i class="fas fa-boxes"></i> Supplies
                            </option>
                            <option value="services">
                                <i class="fas fa-hands-helping"></i> Services
                            </option>
                            <option value="training">
                                <i class="fas fa-graduation-cap"></i> Training
                            </option>
                            <option value="other">
                                <i class="fas fa-gift"></i> Other
                            </option>
                        </select>
                        <p
                            v-if="errors.category"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.category[0] }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="formData.description"
                            rows="3"
                            required
                            placeholder="Describe the in-kind contribution (e.g., 20 solar panels for irrigation system)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.description }"
                        ></textarea>
                        <p
                            v-if="errors.description"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.description[0] }}
                        </p>
                    </div>

                    <!-- Estimated Value and Date (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Estimated Value (USD)
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="formData.estimated_value"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="e.g., 25000.00"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'border-red-500': errors.estimated_value,
                                }"
                            />
                            <p
                                v-if="errors.estimated_value"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.estimated_value[0] }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Contribution Date
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="formData.contribution_date"
                                type="date"
                                required
                                :max="today"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'border-red-500': errors.contribution_date,
                                }"
                            />
                            <p
                                v-if="errors.contribution_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.contribution_date[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div
                        class="bg-purple-50 rounded-lg p-4 border border-purple-200"
                    >
                        <h4 class="text-sm font-semibold text-purple-900 mb-2">
                            <i class="fas fa-gift mr-2"></i>
                            Contribution Summary
                        </h4>
                        <div class="space-y-1 text-sm">
                            <div>
                                <span class="text-gray-600">Category:</span>
                                <span
                                    class="font-medium text-gray-900 ml-1 capitalize"
                                >
                                    {{ formData.category || "—" }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600"
                                    >Estimated Value:</span
                                >
                                <span class="font-medium text-purple-600 ml-1">
                                    ${{
                                        formatNumber(
                                            formData.estimated_value || 0,
                                        )
                                    }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium text-gray-900 ml-1">
                                    {{ formData.contribution_date || "—" }}
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
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i
                            v-if="submitting"
                            class="fas fa-spinner fa-spin mr-2"
                        ></i>
                        {{
                            submitting ? "Recording..." : "Record Contribution"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useDonorStore } from "../../../stores/donorStore";
import { useProjectStore } from "../../../stores/projectStore";

const props = defineProps({
    donor: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["close", "recorded"]);
const donorStore = useDonorStore();
const projectStore = useProjectStore();

const submitting = ref(false);
const errors = ref({});
const availableProjects = ref([]);

const today = computed(() => {
    return new Date().toISOString().split("T")[0];
});

const formData = reactive({
    donor_id: props.donor.id,
    project_id: "",
    description: "",
    category: "",
    estimated_value: null,
    contribution_date: today.value,
});

const handleSubmit = async () => {
    submitting.value = true;
    errors.value = {};

    try {
        await donorStore.addInKindContribution(formData);
        emit("recorded");
    } catch (err) {
        if (typeof err === "object" && err !== null) {
            errors.value = err;
        } else {
            console.error("Error recording contribution:", err);
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
