<template>
    <div
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
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3
                        id="modal-title"
                        class="text-lg font-semibold text-gray-900"
                    >
                        Upload Document
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm">
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 gap-4">
                            <!-- File Upload -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Select File
                                    <span class="text-red-500">*</span>
                                </label>
                                <div
                                    class="flex items-center justify-center w-full"
                                >
                                    <label
                                        for="file-upload"
                                        :class="[
                                            'flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors',
                                            errors.file
                                                ? 'border-red-500 bg-red-50 hover:bg-red-100'
                                                : 'border-gray-300 bg-gray-50 hover:bg-gray-100',
                                        ]"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center pt-5 pb-6"
                                        >
                                            <i
                                                v-if="!selectedFileName"
                                                class="fas fa-cloud-upload-alt mb-3 text-4xl text-gray-400"
                                            ></i>
                                            <i
                                                v-else
                                                class="fas fa-file-check mb-3 text-4xl text-green-500"
                                            ></i>
                                            <p
                                                v-if="!selectedFileName"
                                                class="mb-2 text-sm text-gray-500"
                                            >
                                                <span class="font-semibold"
                                                    >Click to upload</span
                                                >
                                                or drag and drop
                                            </p>
                                            <p
                                                v-else
                                                class="mb-2 text-sm text-gray-700 font-semibold"
                                            >
                                                {{ selectedFileName }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                PDF, Word, Excel, or Images (Max
                                                5MB)
                                            </p>
                                        </div>
                                        <input
                                            id="file-upload"
                                            type="file"
                                            class="hidden"
                                            @change="handleFileChange"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                        />
                                    </label>
                                </div>
                                <p
                                    v-if="errors.file"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.file[0] }}
                                </p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Document Title
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.title,
                                    }"
                                    placeholder="Q1 2025 Budget Proposal"
                                />
                                <p
                                    v-if="errors.title"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.title[0] }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Description (Optional)
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.description,
                                    }"
                                    placeholder="Brief description of the document..."
                                ></textarea>
                                <p
                                    v-if="errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.description[0] }}
                                </p>
                            </div>

                            <!-- Category -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Category
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.category"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500': errors.category,
                                    }"
                                >
                                    <option value="">Select a category</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.slug"
                                        :value="category.slug"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.category"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.category[0] }}
                                </p>
                            </div>

                            <!-- Attach To Type -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Attach To
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.documentable_type"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500':
                                            errors.documentable_type,
                                    }"
                                    @change="loadEntities"
                                >
                                    <option value="">Select entity type</option>
                                    <option value="App\Models\Project">
                                        Project
                                    </option>
                                    <option value="App\Models\Budget">
                                        Budget
                                    </option>
                                    <option value="App\Models\Expense">
                                        Expense
                                    </option>
                                    <option value="App\Models\Donor">
                                        Donor
                                    </option>
                                </select>
                                <p
                                    v-if="errors.documentable_type"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.documentable_type[0] }}
                                </p>
                            </div>

                            <!-- Attach To Entity -->
                            <div v-if="form.documentable_type">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Select {{ getEntityLabel() }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.documentable_id"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :class="{
                                        'border-red-500':
                                            errors.documentable_id,
                                    }"
                                    :disabled="loadingEntities"
                                >
                                    <option value="">
                                        {{
                                            loadingEntities
                                                ? "Loading..."
                                                : `Select ${getEntityLabel()}`
                                        }}
                                    </option>
                                    <option
                                        v-for="entity in entities"
                                        :key="entity.id"
                                        :value="entity.id"
                                    >
                                        {{ getEntityDisplayName(entity) }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.documentable_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.documentable_id[0] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                            :disabled="uploading"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-900 disabled:bg-gray-400"
                            :disabled="uploading"
                        >
                            <i
                                :class="
                                    uploading
                                        ? 'fas fa-spinner fa-spin'
                                        : 'fas fa-upload'
                                "
                            ></i>
                            {{ uploading ? "Uploading..." : "Upload Document" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from "vue";
import axios from "axios";

export default {
    name: "UploadDocumentModal",
    props: {
        categories: {
            type: Array,
            required: true,
        },
    },
    emits: ["close", "uploaded"],
    setup(props, { emit }) {
        const form = ref({
            file: null,
            title: "",
            description: "",
            category: "",
            documentable_type: "",
            documentable_id: "",
        });

        const selectedFileName = ref("");
        const errors = ref({});
        const uploading = ref(false);
        const entities = ref([]);
        const loadingEntities = ref(false);

        const handleFileChange = (event) => {
            const file = event.target.files[0];
            if (file) {
                form.value.file = file;
                selectedFileName.value = file.name;

                // Auto-populate title if empty
                if (!form.value.title) {
                    form.value.title = file.name.replace(/\.[^/.]+$/, "");
                }
            }
        };

        const getEntityLabel = () => {
            const labels = {
                "App\\Models\\Project": "Project",
                "App\\Models\\Budget": "Budget",
                "App\\Models\\Expense": "Expense",
                "App\\Models\\Donor": "Donor",
            };
            return labels[form.value.documentable_type] || "Entity";
        };

        const getEntityDisplayName = (entity) => {
            // For different entity types, display appropriate field
            return (
                entity.name ||
                entity.title ||
                entity.description ||
                `ID: ${entity.id}`
            );
        };

        const loadEntities = async () => {
            if (!form.value.documentable_type) {
                entities.value = [];
                return;
            }

            loadingEntities.value = true;
            try {
                const endpoints = {
                    "App\\Models\\Project": "/api/v1/projects",
                    "App\\Models\\Budget": "/api/v1/budgets",
                    "App\\Models\\Expense": "/api/v1/expenses",
                    "App\\Models\\Donor": "/api/v1/donors",
                };

                const endpoint = endpoints[form.value.documentable_type];
                if (endpoint) {
                    const response = await axios.get(endpoint);
                    entities.value = response.data.data || [];
                }
            } catch (error) {
                console.error("Error loading entities:", error);
                window.$toast.error("Failed to load entities");
            } finally {
                loadingEntities.value = false;
            }
        };

        const submitForm = async () => {
            if (!form.value.file) {
                errors.value = { file: ["Please select a file to upload"] };
                return;
            }

            uploading.value = true;
            errors.value = {};

            try {
                const formData = new FormData();
                formData.append("file", form.value.file);
                formData.append("title", form.value.title);
                if (form.value.description) {
                    formData.append("description", form.value.description);
                }
                formData.append("category", form.value.category);
                formData.append(
                    "documentable_type",
                    form.value.documentable_type,
                );
                formData.append("documentable_id", form.value.documentable_id);

                await axios.post("/api/v1/documents", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });

                window.$toast.success("Document uploaded successfully");
                emit("uploaded");
            } catch (error) {
                console.error("Upload error:", error);
                if (error.response?.data?.errors) {
                    errors.value = error.response.data.errors;
                } else {
                    window.$toast.error(
                        error.response?.data?.message ||
                            "Failed to upload document",
                    );
                }
            } finally {
                uploading.value = false;
            }
        };

        const closeModal = () => {
            if (!uploading.value) {
                emit("close");
            }
        };

        return {
            form,
            selectedFileName,
            errors,
            uploading,
            entities,
            loadingEntities,
            handleFileChange,
            getEntityLabel,
            getEntityDisplayName,
            loadEntities,
            submitForm,
            closeModal,
        };
    },
};
</script>
