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
                        Replace Document
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
                            <!-- Current Version Info -->
                            <div
                                class="rounded-lg bg-gray-50 p-4 border border-gray-200"
                            >
                                <div
                                    class="text-xs font-semibold text-gray-500 uppercase mb-2"
                                >
                                    Current Version
                                </div>
                                <div class="flex items-center gap-3">
                                    <i
                                        :class="getFileIcon(document.file_type)"
                                        class="text-3xl"
                                    ></i>
                                    <div class="flex-1">
                                        <div
                                            class="font-medium text-gray-900 text-sm"
                                        >
                                            {{ document.title }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ document.file_name }} •
                                            {{
                                                formatFileSize(
                                                    document.file_size,
                                                )
                                            }}
                                        </div>
                                        <div class="text-xs text-blue-600 mt-1">
                                            <i
                                                class="fas fa-code-branch mr-1"
                                            ></i>
                                            Version
                                            {{ document.version_number }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning Message -->
                            <div
                                class="rounded-lg bg-orange-50 p-3 border border-orange-200"
                            >
                                <div class="flex gap-2">
                                    <i
                                        class="fas fa-exclamation-triangle text-orange-600 mt-0.5"
                                    ></i>
                                    <div class="text-sm text-orange-700">
                                        <p class="font-semibold mb-1">
                                            Important Notes:
                                        </p>
                                        <ul
                                            class="list-disc list-inside space-y-1"
                                        >
                                            <li>
                                                The current file will be moved
                                                to archive
                                            </li>
                                            <li>
                                                The new file will become version
                                                {{
                                                    document.version_number + 1
                                                }}
                                            </li>
                                            <li>
                                                Previous versions remain
                                                accessible via version history
                                            </li>
                                            <li>
                                                Title and metadata will remain
                                                unchanged
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Select New File
                                    <span class="text-red-500">*</span>
                                </label>
                                <div
                                    class="flex items-center justify-center w-full"
                                >
                                    <label
                                        for="replace-file-upload"
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
                                            id="replace-file-upload"
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
                            :disabled="replacing"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-orange-700 disabled:bg-gray-400"
                            :disabled="replacing || !file"
                        >
                            <i
                                :class="
                                    replacing
                                        ? 'fas fa-spinner fa-spin'
                                        : 'fas fa-sync-alt'
                                "
                            ></i>
                            {{
                                replacing ? "Replacing..." : "Replace Document"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from "vue";
import axios from "axios";

export default {
    name: "ReplaceDocumentModal",
    props: {
        document: {
            type: Object,
            required: true,
        },
    },
    emits: ["close", "replaced"],
    setup(props, { emit }) {
        const file = ref(null);
        const selectedFileName = ref("");
        const errors = ref({});
        const replacing = ref(false);

        const handleFileChange = (event) => {
            const selectedFile = event.target.files[0];
            if (selectedFile) {
                file.value = selectedFile;
                selectedFileName.value = selectedFile.name;
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

        const submitForm = async () => {
            if (!file.value) {
                errors.value = { file: ["Please select a file"] };
                return;
            }

            replacing.value = true;
            errors.value = {};

            try {
                const formData = new FormData();
                formData.append("file", file.value);

                await axios.post(
                    `/api/v1/documents/${props.document.id}/replace`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    },
                );

                window.$toast.success(
                    `Document replaced successfully. New version: ${props.document.version_number + 1}`,
                );
                emit("replaced");
            } catch (error) {
                console.error("Replace error:", error);
                if (error.response?.data?.errors) {
                    errors.value = error.response.data.errors;
                } else {
                    window.$toast.error(
                        error.response?.data?.message ||
                            "Failed to replace document",
                    );
                }
            } finally {
                replacing.value = false;
            }
        };

        const closeModal = () => {
            if (!replacing.value) {
                emit("close");
            }
        };

        return {
            file,
            selectedFileName,
            errors,
            replacing,
            handleFileChange,
            getFileIcon,
            formatFileSize,
            submitForm,
            closeModal,
        };
    },
};
</script>
