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
                class="relative w-full max-w-3xl transform rounded-lg bg-white shadow-xl transition-all"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <div>
                        <h3
                            id="modal-title"
                            class="text-lg font-semibold text-gray-900"
                        >
                            Version History
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ document.title }}
                        </p>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Loading State -->
                <div
                    v-if="loading"
                    class="flex items-center justify-center p-12"
                >
                    <i
                        class="fas fa-spinner fa-spin mr-3 text-2xl text-blue-600"
                    ></i>
                    <span class="text-gray-600">Loading versions...</span>
                </div>

                <!-- Versions List -->
                <div v-else class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <div
                        v-if="versions.length === 0"
                        class="text-center py-8 text-gray-500"
                    >
                        <i class="fas fa-code-branch text-4xl mb-3"></i>
                        <p>No version history available</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="version in versions"
                            :key="version.id"
                            :class="[
                                'rounded-lg border p-4 transition-colors',
                                version.is_current
                                    ? 'border-blue-300 bg-blue-50'
                                    : 'border-gray-200 bg-white hover:bg-gray-50',
                            ]"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3 flex-1">
                                    <i
                                        :class="getFileIcon(version.file_type)"
                                        class="text-2xl mt-1"
                                    ></i>
                                    <div class="flex-1">
                                        <!-- Version Badge -->
                                        <div
                                            class="flex items-center gap-2 mb-2"
                                        >
                                            <span
                                                :class="[
                                                    'inline-flex items-center gap-1.5 px-2 py-1 text-xs font-semibold rounded-full',
                                                    version.is_current
                                                        ? 'bg-blue-600 text-white'
                                                        : 'bg-gray-200 text-gray-700',
                                                ]"
                                            >
                                                <i
                                                    class="fas fa-code-branch"
                                                ></i>
                                                Version
                                                {{ version.version_number }}
                                            </span>
                                            <span
                                                v-if="version.is_current"
                                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700"
                                            >
                                                <i
                                                    class="fas fa-check-circle"
                                                ></i>
                                                Current
                                            </span>
                                        </div>

                                        <!-- File Info -->
                                        <div
                                            class="font-medium text-gray-900 text-sm"
                                        >
                                            {{ version.file_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{
                                                formatFileSize(
                                                    version.file_size,
                                                )
                                            }}
                                            •
                                            {{
                                                getFileTypeLabel(
                                                    version.file_type,
                                                )
                                            }}
                                        </div>

                                        <!-- Replacement Info -->
                                        <div
                                            v-if="
                                                !version.is_current &&
                                                version.replaced_at
                                            "
                                            class="mt-2 text-xs text-gray-600"
                                        >
                                            <i
                                                class="fas fa-sync-alt mr-1 text-orange-500"
                                            ></i>
                                            Replaced on
                                            {{
                                                formatDateTime(
                                                    version.replaced_at,
                                                )
                                            }}
                                            <span v-if="version.replacer">
                                                by {{ version.replacer.name }}
                                            </span>
                                        </div>

                                        <!-- Upload Info (for current version) -->
                                        <div
                                            v-if="version.is_current"
                                            class="mt-2 text-xs text-gray-600"
                                        >
                                            <i
                                                class="fas fa-upload mr-1 text-blue-500"
                                            ></i>
                                            Uploaded on
                                            {{
                                                formatDateTime(
                                                    version.created_at,
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 ml-4">
                                    <button
                                        @click="downloadVersion(version)"
                                        class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-100"
                                        title="Download this version"
                                    >
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-4"
                >
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Total Versions: {{ versions.length }}
                    </p>
                    <button
                        @click="closeModal"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import axios from "axios";

export default {
    name: "DocumentVersionsModal",
    props: {
        document: {
            type: Object,
            required: true,
        },
    },
    emits: ["close"],
    setup(props, { emit }) {
        const versions = ref([]);
        const loading = ref(false);

        const loadVersions = async () => {
            loading.value = true;
            try {
                const response = await axios.get(
                    `/api/v1/documents/${props.document.id}/versions`,
                );
                versions.value = response.data.data.versions || [];
            } catch (error) {
                console.error("Error loading versions:", error);
                window.$toast.error("Failed to load version history");
            } finally {
                loading.value = false;
            }
        };

        const downloadVersion = async (version) => {
            try {
                // For archived versions, we might need a different endpoint
                // For now, we'll use the document ID and construct a download URL
                const response = await axios.get(
                    `/api/v1/documents/${props.document.id}/download`,
                    {
                        params: { version: version.version_number },
                        responseType: "blob",
                    },
                );

                const url = window.URL.createObjectURL(
                    new Blob([response.data]),
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", version.file_name);
                document.body.appendChild(link);
                link.click();
                link.remove();

                window.$toast.success(
                    `Version ${version.version_number} downloaded`,
                );
            } catch (error) {
                console.error("Error downloading version:", error);
                window.$toast.error("Failed to download version");
            }
        };

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

        const formatDateTime = (dateString) => {
            return new Date(dateString).toLocaleString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        };

        const closeModal = () => {
            emit("close");
        };

        onMounted(() => {
            loadVersions();
        });

        return {
            versions,
            loading,
            downloadVersion,
            getFileIcon,
            getFileTypeLabel,
            formatFileSize,
            formatDateTime,
            closeModal,
        };
    },
};
</script>
