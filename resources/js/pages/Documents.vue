<template>
    <DashboardLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Documents</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage project, budget, expense, and donor documents
                    </p>
                </div>
                <button
                    @click="openUploadModal"
                    class="flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-900"
                >
                    <i class="fas fa-upload"></i>
                    Upload Document
                </button>
            </div>

            <!-- Filters Card -->
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Search
                        </label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Title, description, filename..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            Category
                        </label>
                        <select
                            v-model="filters.category"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadDocuments"
                        >
                            <option value="">All Categories</option>
                            <option
                                v-for="category in categories"
                                :key="category.slug"
                                :value="category.slug"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- File Type Filter -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-gray-700"
                        >
                            File Type
                        </label>
                        <select
                            v-model="filters.file_type"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            @change="loadDocuments"
                        >
                            <option value="">All Types</option>
                            <option value="application/pdf">PDF</option>
                            <option
                                value="application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            >
                                Word
                            </option>
                            <option
                                value="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            >
                                Excel
                            </option>
                            <option value="image/jpeg">Images</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Documents List -->
            <div class="rounded-lg bg-white shadow">
                <!-- Loading State -->
                <div
                    v-if="loading"
                    class="flex items-center justify-center p-12"
                >
                    <i
                        class="fas fa-spinner fa-spin mr-3 text-2xl text-blue-600"
                    ></i>
                    <span class="text-gray-600">Loading documents...</span>
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="documents.length === 0"
                    class="p-12 text-center"
                >
                    <i
                        class="fas fa-folder-open mb-4 text-6xl text-gray-300"
                    ></i>
                    <h3 class="mb-2 text-lg font-semibold text-gray-700">
                        No documents found
                    </h3>
                    <p class="mb-4 text-sm text-gray-600">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters"
                                : "Upload your first document to get started"
                        }}
                    </p>
                    <button
                        v-if="!hasActiveFilters"
                        @click="openUploadModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                    >
                        <i class="fas fa-upload"></i>
                        Upload Document
                    </button>
                </div>

                <!-- Documents Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700"
                                >
                                    Document
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700"
                                >
                                    Category
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700"
                                >
                                    Size
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700"
                                >
                                    Uploaded
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="document in documents"
                                :key="document.id"
                                class="transition-colors hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <i
                                            :class="
                                                getFileIcon(document.file_type)
                                            "
                                            class="mt-1 text-2xl"
                                        ></i>
                                        <div>
                                            <div
                                                class="font-medium text-gray-900"
                                            >
                                                {{ document.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ document.file_name }}
                                            </div>
                                            <div
                                                v-if="document.description"
                                                class="mt-1 text-sm text-gray-600"
                                            >
                                                {{ document.description }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="
                                            getCategoryBadgeClass(
                                                document.category,
                                            )
                                        "
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ getCategoryName(document.category) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ getFileTypeLabel(document.file_type) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ formatFileSize(document.file_size) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ formatDate(document.created_at) }}
                                    </div>
                                    <div
                                        v-if="document.uploader"
                                        class="text-sm text-gray-500"
                                    >
                                        by {{ document.uploader.name }}
                                    </div>
                                    <div
                                        v-if="document.version_number > 1"
                                        class="mt-1 text-xs text-blue-600"
                                    >
                                        <i class="fas fa-code-branch mr-1"></i>
                                        v{{ document.version_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <!-- Download -->
                                        <button
                                            @click="downloadDocument(document)"
                                            class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50"
                                            title="Download"
                                        >
                                            <i class="fas fa-download"></i>
                                        </button>

                                        <!-- Versions -->
                                        <button
                                            v-if="document.version_number > 1"
                                            @click="viewVersions(document)"
                                            class="rounded-lg p-2 text-purple-600 transition-colors hover:bg-purple-50"
                                            title="Version History"
                                        >
                                            <i class="fas fa-code-branch"></i>
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            @click="deleteDocument(document)"
                                            class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Results Count -->
                <div
                    v-if="documents.length > 0"
                    class="border-t border-gray-200 bg-gray-50 px-6 py-3"
                >
                    <p class="text-sm text-gray-600">
                        Showing {{ documents.length }} document<span
                            v-if="documents.length !== 1"
                            >s</span
                        >
                    </p>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <UploadDocumentModal
            v-if="showUploadModal"
            :categories="categories"
            @close="showUploadModal = false"
            @uploaded="onDocumentUploaded"
        />

        <!-- Versions Modal -->
        <DocumentVersionsModal
            v-if="showVersionsModal"
            :document="selectedDocument"
            @close="showVersionsModal = false"
        />
    </DashboardLayout>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import UploadDocumentModal from "../components/modals/UploadDocumentModal.vue";
import DocumentVersionsModal from "../components/modals/DocumentVersionsModal.vue";
import api from "@/api";
import { showSuccess, showError, confirmAction } from "../plugins/sweetalert";

export default {
    name: "Documents",
    components: {
        DashboardLayout,
        UploadDocumentModal,
        DocumentVersionsModal,
    },
    setup() {
        const documents = ref([]);
        const categories = ref([]);
        const loading = ref(false);
        const showUploadModal = ref(false);
        const showVersionsModal = ref(false);
        const selectedDocument = ref(null);

        const filters = ref({
            search: "",
            category: "",
            file_type: "",
        });

        // Load documents with filters
        const loadDocuments = async () => {
            loading.value = true;
            try {
                const params = {};
                if (filters.value.search) params.search = filters.value.search;
                if (filters.value.category)
                    params.category = filters.value.category;
                if (filters.value.file_type)
                    params.file_type = filters.value.file_type;

                const response = await api.get("/documents", {
                    params,
                });
                documents.value = response.data.data || [];
            } catch (error) {
                console.error("Error loading documents:", error);
                showError(
                    "Error",
                    "Failed to load documents. Please try again.",
                );
            } finally {
                loading.value = false;
            }
        };

        // Load categories
        const loadCategories = async () => {
            try {
                const response = await api.get("/documents/categories");
                categories.value = response.data.data || [];
            } catch (error) {
                console.error("Error loading categories:", error);
            }
        };

        // Debounced search
        let searchTimeout;
        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadDocuments();
            }, 500);
        };

        // Clear filters
        const clearFilters = () => {
            filters.value = {
                search: "",
                category: "",
                file_type: "",
            };
            loadDocuments();
        };

        // Check if filters are active
        const hasActiveFilters = computed(() => {
            return (
                filters.value.search ||
                filters.value.category ||
                filters.value.file_type
            );
        });

        // Modal actions
        const openUploadModal = () => {
            showUploadModal.value = true;
        };

        const viewVersions = (document) => {
            selectedDocument.value = document;
            showVersionsModal.value = true;
        };

        // Download document
        const downloadDocument = async (doc) => {
            try {
                const response = await api.get(
                    `/documents/${doc.id}/download`,
                    { responseType: "blob" },
                );

                const url = window.URL.createObjectURL(
                    new Blob([response.data]),
                );
                const link = window.document.createElement("a");
                link.href = url;
                link.setAttribute("download", doc.file_name);
                window.document.body.appendChild(link);
                link.click();
                link.remove();

                showSuccess("Success", "Document downloaded successfully");
            } catch (error) {
                console.error("Error downloading document:", error);
                showError(
                    "Error",
                    "Failed to download document. Please try again.",
                );
            }
        };

        // Delete document
        const deleteDocument = async (document) => {
            const confirmed = await confirmAction(
                "Are you sure?",
                `This will permanently delete "${document.title}" and all its versions. This action cannot be undone!`,
            );

            if (confirmed) {
                try {
                    await api.delete(`/documents/${document.id}`);
                    showSuccess("Success", "Document deleted successfully");
                    loadDocuments();
                } catch (error) {
                    console.error("Error deleting document:", error);
                    showError(
                        "Error",
                        error.response?.data?.message ||
                            "Failed to delete document",
                    );
                }
            }
        };

        // Event handlers
        const onDocumentUploaded = () => {
            showUploadModal.value = false;
            loadDocuments();
        };

        // Helper functions
        const getFileIcon = (fileType) => {
            if (fileType.includes("pdf")) return "fas fa-file-pdf text-red-600";
            if (fileType.includes("word"))
                return "fas fa-file-word text-blue-600";
            if (fileType.includes("sheet"))
                return "fas fa-file-excel text-green-600";
            if (fileType.includes("image"))
                return "fas fa-file-image text-purple-600";
            return "fas fa-file text-gray-600";
        };

        const getFileTypeLabel = (fileType) => {
            if (fileType.includes("pdf")) return "PDF";
            if (fileType.includes("word")) return "Word";
            if (fileType.includes("sheet")) return "Excel";
            if (fileType.includes("image")) return "Image";
            return "File";
        };

        const getCategoryName = (slug) => {
            const category = categories.value.find((c) => c.slug === slug);
            return category ? category.name : slug;
        };

        const getCategoryBadgeClass = (category) => {
            const classes = {
                "budget-documents": "bg-blue-100 text-blue-800",
                "expense-receipts": "bg-green-100 text-green-800",
                "project-reports": "bg-purple-100 text-purple-800",
                "donor-agreements": "bg-yellow-100 text-yellow-800",
                other: "bg-gray-100 text-gray-800",
            };
            return classes[category] || classes.other;
        };

        const formatFileSize = (bytes) => {
            if (bytes === 0) return "0 Bytes";
            const k = 1024;
            const sizes = ["Bytes", "KB", "MB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (
                Math.round((bytes / Math.pow(k, i)) * 100) / 100 +
                " " +
                sizes[i]
            );
        };

        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        };

        // Initialize
        onMounted(() => {
            loadCategories();
            loadDocuments();
        });

        return {
            documents,
            categories,
            loading,
            filters,
            showUploadModal,
            showVersionsModal,
            selectedDocument,
            hasActiveFilters,
            loadDocuments,
            debouncedSearch,
            clearFilters,
            openUploadModal,
            viewVersions,
            downloadDocument,
            deleteDocument,
            onDocumentUploaded,
            getFileIcon,
            getFileTypeLabel,
            getCategoryName,
            getCategoryBadgeClass,
            formatFileSize,
            formatDate,
        };
    },
};
</script>
