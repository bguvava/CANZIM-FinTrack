<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
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
                        Add Bank Account
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
                                placeholder="e.g., CANZIM Main Account"
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
                                    placeholder="e.g., Standard Chartered"
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
                                    :class="{
                                        'border-red-500': errors.branch,
                                    }"
                                    placeholder="e.g., Harare Main"
                                />
                                <p
                                    v-if="errors.branch"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.branch[0] }}
                                </p>
                            </div>
                        </div>

                        <!-- Account Number and Currency -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Account Number
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.account_number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                    :class="{
                                        'border-red-500': errors.account_number,
                                    }"
                                    placeholder="e.g., 1234567890"
                                    required
                                />
                                <p
                                    v-if="errors.account_number"
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{ errors.account_number[0] }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Currency
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.currency"
                                    value="USD"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                    readonly
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    All accounts use USD currency
                                </p>
                            </div>
                        </div>

                        <!-- Opening Balance -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Opening Balance
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"
                                >
                                    $
                                </span>
                                <input
                                    type="number"
                                    v-model.number="form.opening_balance"
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                                    :class="{
                                        'border-red-500':
                                            errors.opening_balance,
                                    }"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0"
                                    required
                                />
                            </div>
                            <p
                                v-if="errors.opening_balance"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.opening_balance[0] }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                The initial balance for this account
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
                                :class="{
                                    'border-red-500': errors.description,
                                }"
                                placeholder="Additional notes about this account..."
                            ></textarea>
                            <p
                                v-if="errors.description"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ errors.description[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex justify-end gap-3 border-t border-gray-200 pt-4 mt-6"
                    >
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            :disabled="submitting"
                        >
                            Cancel
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
                            {{ submitting ? "Creating..." : "Create Account" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useCashFlowStore } from "../../stores/cashFlowStore";
import { showSuccess, showError } from "../../plugins/sweetalert";

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["close", "account-created"]);

const cashFlowStore = useCashFlowStore();

const form = ref({
    account_name: "",
    bank_name: "",
    branch: "",
    account_number: "",
    currency: "USD",
    opening_balance: 0,
    description: "",
});

const errors = ref({});
const submitting = ref(false);

const closeModal = () => {
    if (!submitting.value) {
        resetForm();
        emit("close");
    }
};

const resetForm = () => {
    form.value = {
        account_name: "",
        bank_name: "",
        branch: "",
        account_number: "",
        currency: "USD",
        opening_balance: 0,
        description: "",
    };
    errors.value = {};
};

const handleSubmit = async () => {
    if (submitting.value) return;

    submitting.value = true;
    errors.value = {};

    try {
        await cashFlowStore.createBankAccount(form.value);
        showSuccess("Bank account created successfully!");
        emit("account-created");
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            showError("Please check the form for errors.");
        } else {
            showError("Failed to create bank account. Please try again.");
        }
    } finally {
        submitting.value = false;
    }
};
</script>
