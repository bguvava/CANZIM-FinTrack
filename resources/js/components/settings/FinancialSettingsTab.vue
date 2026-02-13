<template>
    <div class="space-y-6">
        <form @submit.prevent="saveSettings">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Currency -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Default Currency
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.currency"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.currency }"
                    >
                        <option value="USD">USD - US Dollar</option>
                        <option value="ZWL">ZWL - Zimbabwe Dollar</option>
                        <option value="ZAR">ZAR - South African Rand</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                    </select>
                    <p v-if="errors.currency" class="mt-1 text-sm text-red-600">
                        {{ errors.currency[0] }}
                    </p>
                </div>

                <!-- Fiscal Year Start -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Fiscal Year Start Month
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.fiscal_year_start"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.fiscal_year_start }"
                    >
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="May">May</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>
                    </select>
                    <p
                        v-if="errors.fiscal_year_start"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.fiscal_year_start[0] }}
                    </p>
                </div>

                <!-- Date Format -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Date Format
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.date_format"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.date_format }"
                    >
                        <option value="Y-m-d">YYYY-MM-DD (2025-01-15)</option>
                        <option value="d/m/Y">DD/MM/YYYY (15/01/2025)</option>
                        <option value="m/d/Y">MM/DD/YYYY (01/15/2025)</option>
                        <option value="d-M-Y">DD-MMM-YYYY (15-Jan-2025)</option>
                    </select>
                    <p
                        v-if="errors.date_format"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.date_format[0] }}
                    </p>
                    <p class="mt-1 text-xs text-gray-600">
                        Example: {{ formatDateExample }}
                    </p>
                </div>

                <!-- Time Format -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Time Format
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.time_format"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.time_format }"
                    >
                        <option value="H:i:s">24-hour (13:45:00)</option>
                        <option value="h:i A">12-hour (01:45 PM)</option>
                    </select>
                    <p
                        v-if="errors.time_format"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ errors.time_format[0] }}
                    </p>
                </div>

                <!-- Timezone -->
                <div class="md:col-span-2">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Timezone
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.timezone"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.timezone }"
                    >
                        <option value="Africa/Harare">
                            Africa/Harare (CAT)
                        </option>
                        <option value="Africa/Johannesburg">
                            Africa/Johannesburg (SAST)
                        </option>
                        <option value="UTC">UTC</option>
                        <option value="Europe/London">
                            Europe/London (GMT/BST)
                        </option>
                        <option value="America/New_York">
                            America/New_York (EST/EDT)
                        </option>
                    </select>
                    <p v-if="errors.timezone" class="mt-1 text-sm text-red-600">
                        {{ errors.timezone[0] }}
                    </p>
                </div>

                <!-- Tax Rate -->
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Default Tax Rate (%)
                    </label>
                    <input
                        v-model.number="form.tax_rate"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        :class="{ 'border-red-500': errors.tax_rate }"
                        placeholder="15.00"
                    />
                    <p v-if="errors.tax_rate" class="mt-1 text-sm text-red-600">
                        {{ errors.tax_rate[0] }}
                    </p>
                </div>

                <!-- Submit Button -->
                <div
                    class="md:col-span-2 flex justify-end gap-3 border-t border-gray-200 pt-4"
                >
                    <button
                        type="button"
                        @click="resetForm"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Reset
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <i
                            :class="
                                saving
                                    ? 'fas fa-spinner fa-spin'
                                    : 'fas fa-save'
                            "
                        ></i>
                        {{ saving ? "Saving..." : "Save Changes" }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import api from "../../api.js";
import Swal from "sweetalert2";

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update"]);

const form = ref({
    currency: "USD",
    fiscal_year_start: "January",
    date_format: "Y-m-d",
    time_format: "H:i:s",
    timezone: "Africa/Harare",
    tax_rate: 0,
});

const errors = ref({});
const saving = ref(false);

const formatDateExample = computed(() => {
    const now = new Date();
    const formats = {
        "Y-m-d": "2025-01-15",
        "d/m/Y": "15/01/2025",
        "m/d/Y": "01/15/2025",
        "d-M-Y": "15-Jan-2025",
    };
    return formats[form.value.date_format] || "N/A";
});

watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings.financial) {
            form.value = { ...newSettings.financial };
        }
    },
    { immediate: true, deep: true },
);

const saveSettings = async () => {
    saving.value = true;
    errors.value = {};

    try {
        await api.put("/settings/financial", form.value);

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Financial settings saved successfully!",
            timer: 2000,
            showConfirmButton: false,
        });

        emit("update");
    } catch (error) {
        console.error("Error saving settings:", error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                error.response?.data?.message ||
                "Failed to save settings. Please try again.",
        });
    } finally {
        saving.value = false;
    }
};

const resetForm = () => {
    if (props.settings.financial) {
        form.value = { ...props.settings.financial };
    }
    errors.value = {};
};
</script>
