<template>
    <div
        v-if="isOpen && bankAccount"
        class="fixed inset-0 z-50 overflow-y-auto"
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
                @click.stop
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
                >
                    <h3 class="text-lg font-semibold text-gray-900">
                        Edit Bank Account
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="px-6 py-6">
                    <div class="max-h-[70vh] overflow-y-auto space-y-6">
                        <!-- Account Name -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Account Name
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="form.account_name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                :class="{
                                    'border-red-500': errors.account_name,
                                }"
                                required
                            />
                            <p
                                v-if="errors.account_name"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.account_name[0] }}
                            </p>
                        </div>

                        <!-- Bank Name and Branch -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Bank Name
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.bank_name"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                    :class="{
                                        'border-red-500': errors.bank_name,
                                    }"
                                    required
                                />
                                <p
                                    v-if="errors.bank_name"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.bank_name[0] }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Branch
                                </label>
                                <input
                                    type="text"
                                    v-model="form.branch"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                />
                            </div>
                        </div>

                        <!-- Account Number (readonly) -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Account Number
                            </label>
                            <input
                                type="text"
                                v-model="form.account_number"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                readonly
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Account number cannot be changed
                            </p>
                        </div>

                        <!-- Current Balance (readonly) -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Current Balance
                            </label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"
                                >
                                    $
                                </span>
                                <input
                                    type="text"
                                    :value="
                                        formatCurrency(
                                            bankAccount.current_balance,
                                        )
                                    "
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                    readonly
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Balance is updated automatically via
                                transactions
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-end gap-3 border-t border-gray-200 pt-4 mt-6"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition"
                            :disabled="submitting"
                        >
                            <i class="fas fa-times mr-1.5"></i>Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-900 transition disabled:opacity-50"
                            :disabled="submitting"
                        >
                            <i
                                v-if="submitting"
                                class="fas fa-spinner fa-spin mr-2"
                            ></i>
                            <i
                                v-if="!submitting"
                                class="fas fa-save mr-1.5"
                            ></i>
                            {{ submitting ? "Updating..." : "Update Account" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useCashFlowStore } from "../../stores/cashFlowStore";
import { showSuccess, showError } from "../../plugins/sweetalert";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    bankAccount: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "account-updated"]);

const cashFlowStore = useCashFlowStore();

const form = ref({
    account_name: "",
    bank_name: "",
    branch: "",
    account_number: "",
    description: "",
});

const errors = ref({});
const submitting = ref(false);

// Watch for bankAccount changes and populate form
watch(
    () => props.bankAccount,
    (newAccount) => {
        if (newAccount) {
            form.value = {
                account_name: newAccount.account_name || "",
                bank_name: newAccount.bank_name || "",
                branch: newAccount.branch || "",
                account_number: newAccount.account_number || "",
                description: newAccount.description || "",
            };
            errors.value = {};
        }
    },
    { immediate: true },
);

const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const closeModal = () => {
    if (!submitting.value) {
        emit("close");
    }
};

const handleSubmit = async () => {
    if (submitting.value || !props.bankAccount) return;

    submitting.value = true;
    errors.value = {};

    try {
        await cashFlowStore.updateBankAccount(props.bankAccount.id, form.value);
        showSuccess("Bank account updated successfully!");
        emit("account-updated");
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            showError("Please check the form for errors.");
        } else {
            showError("Failed to update bank account. Please try again.");
        }
    } finally {
        submitting.value = false;
    }
};
</script>
