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
                        Edit Document
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
                            <!-- Current File Info -->
                            <div
                                class="rounded-lg bg-gray-50 p-4 border border-gray-200"
                            >
                                <div class="flex items-center gap-3">
                                    <i
                                        :class="getFileIcon(document.file_type)"
                                        class="text-3xl"
                                    ></i>
                                    <div class="flex-1">
                                        <div
                                            class="font-medium text-gray-900 text-sm"
                                        >
                                            {{ document.file_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{
                                                formatFileSize(
                                                    document.file_size,
                                                )
                                            }}
                                            • Uploaded
                                            {{
                                                formatDate(document.created_at)
                                            }}
                                        </div>
                                    </div>
                                </div>
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

                            <!-- Note -->
                            <div
                                class="rounded-lg bg-blue-50 p-3 border border-blue-200"
                            >
                                <div class="flex gap-2">
                                    <i
                                        class="fas fa-info-circle text-blue-600 mt-0.5"
                                    ></i>
                                    <p class="text-sm text-blue-700">
                                        This will only update the document
                                        metadata (title, description, category).
                                        To replace the file, use the "Replace"
                                        button instead.
                                    </p>
                                </div>
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
                            class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-50"
                            :disabled="updating"
                        >
                            <i class="fas fa-times mr-1.5"></i>Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700 disabled:bg-gray-400"
                            :disabled="updating"
                        >
                            <i
                                :class="
                                    updating
                                        ? 'fas fa-spinner fa-spin'
                                        : 'fas fa-save'
                                "
                            ></i>
                            {{ updating ? "Updating..." : "Update Document" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import api from "@/api";

export default {
    name: "EditDocumentModal",
    props: {
        document: {
            type: Object,
            required: true,
        },
        categories: {
            type: Array,
            required: true,
        },
    },
    emits: ["close", "updated"],
    setup(props, { emit }) {
        const form = ref({
            title: "",
            description: "",
            category: "",
        });

        const errors = ref({});
        const updating = ref(false);

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

        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        };

        const submitForm = async () => {
            updating.value = true;
            errors.value = {};

            try {
                await api.put(
                    `/api/v1/documents/${props.document.id}`,
                    form.value,
                );

                window.$toast.success("Document updated successfully");
                emit("updated");
            } catch (error) {
                console.error("Update error:", error);
                if (error.response?.data?.errors) {
                    errors.value = error.response.data.errors;
                } else {
                    window.$toast.error(
                        error.response?.data?.message ||
                            "Failed to update document",
                    );
                }
            } finally {
                updating.value = false;
            }
        };

        const closeModal = () => {
            if (!updating.value) {
                emit("close");
            }
        };

        onMounted(() => {
            // Populate form with existing data
            form.value.title = props.document.title;
            form.value.description = props.document.description || "";
            form.value.category = props.document.category;
        });

        return {
            form,
            errors,
            updating,
            getFileIcon,
            formatFileSize,
            formatDate,
            submitForm,
            closeModal,
        };
    },
};
</script>
