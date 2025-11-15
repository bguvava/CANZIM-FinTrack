import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "../api";

export const useCashFlowStore = defineStore("cashFlow", () => {
    // State
    const bankAccounts = ref([]);
    const cashFlows = ref([]);
    const projections = ref(null);
    const statistics = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    });

    // Filters
    const filters = ref({
        type: "", // 'inflow' or 'outflow'
        bank_account_id: "",
        project_id: "",
        date_from: "",
        date_to: "",
        is_reconciled: "",
        search: "",
    });

    // Getters
    const activeBankAccounts = computed(() =>
        bankAccounts.value.filter((account) => account.is_active),
    );

    const totalBalance = computed(() =>
        bankAccounts.value.reduce(
            (sum, account) => sum + parseFloat(account.current_balance || 0),
            0,
        ),
    );

    const unreconciledTransactions = computed(() =>
        cashFlows.value.filter((cf) => !cf.is_reconciled),
    );

    // Actions - Bank Accounts
    async function fetchBankAccounts() {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get("/bank-accounts");
            bankAccounts.value = response.data.data || response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error fetching bank accounts";
            console.error("Error fetching bank accounts:", err);
        } finally {
            loading.value = false;
        }
    }

    async function createBankAccount(data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post("/bank-accounts", data);
            bankAccounts.value.unshift(
                response.data.bank_account || response.data.data,
            );
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error creating bank account";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function updateBankAccount(id, data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.put(`/bank-accounts/${id}`, data);
            const index = bankAccounts.value.findIndex(
                (account) => account.id === id,
            );
            if (index !== -1) {
                bankAccounts.value[index] =
                    response.data.bank_account || response.data.data;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error updating bank account";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function deactivateBankAccount(id) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/bank-accounts/${id}/deactivate`);
            const index = bankAccounts.value.findIndex(
                (account) => account.id === id,
            );
            if (index !== -1) {
                bankAccounts.value[index].is_active = false;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message ||
                "Error deactivating bank account";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function activateBankAccount(id) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/bank-accounts/${id}/activate`);
            const index = bankAccounts.value.findIndex(
                (account) => account.id === id,
            );
            if (index !== -1) {
                bankAccounts.value[index].is_active = true;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error activating bank account";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function getBankAccountSummary(id) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get(`/bank-accounts/${id}/summary`);
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error fetching account summary";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    // Actions - Cash Flow Transactions
    async function fetchCashFlows(page = 1) {
        loading.value = true;
        error.value = null;
        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
                ...filters.value,
            };

            const response = await api.get("/cash-flows", { params });

            cashFlows.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                per_page: response.data.per_page,
                total: response.data.total,
            };
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error fetching cash flows";
            console.error("Error fetching cash flows:", err);
        } finally {
            loading.value = false;
        }
    }

    async function recordInflow(data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post("/cash-flows/inflow", data);
            await fetchCashFlows(pagination.value.current_page);
            await fetchBankAccounts(); // Refresh balances
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error recording inflow";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function recordOutflow(data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post("/cash-flows/outflow", data);
            await fetchCashFlows(pagination.value.current_page);
            await fetchBankAccounts(); // Refresh balances
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error recording outflow";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function reconcileCashFlow(id) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/cash-flows/${id}/reconcile`);
            const index = cashFlows.value.findIndex((cf) => cf.id === id);
            if (index !== -1) {
                cashFlows.value[index] = response.data.cash_flow;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error reconciling transaction";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function deleteCashFlow(id) {
        loading.value = true;
        error.value = null;
        try {
            await api.delete(`/cash-flows/${id}`);
            cashFlows.value = cashFlows.value.filter((cf) => cf.id !== id);
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error deleting transaction";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    // Actions - Projections
    async function fetchProjections(bankAccountId, months = 3) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get("/cash-flows/projections", {
                params: { bank_account_id: bankAccountId, months },
            });
            projections.value = response.data;
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error fetching projections";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    // Actions - Statistics
    async function fetchStatistics() {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get("/cash-flows/statistics");
            statistics.value = response.data;
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error fetching statistics";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    // Filter Actions
    function setFilter(key, value) {
        filters.value[key] = value;
    }

    function clearFilters() {
        filters.value = {
            type: "",
            bank_account_id: "",
            project_id: "",
            date_from: "",
            date_to: "",
            is_reconciled: "",
            search: "",
        };
    }

    // Reset store
    function $reset() {
        bankAccounts.value = [];
        cashFlows.value = [];
        projections.value = null;
        statistics.value = null;
        loading.value = false;
        error.value = null;
        pagination.value = {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
        };
        clearFilters();
    }

    return {
        // State
        bankAccounts,
        cashFlows,
        projections,
        statistics,
        loading,
        error,
        pagination,
        filters,

        // Getters
        activeBankAccounts,
        totalBalance,
        unreconciledTransactions,

        // Bank Account Actions
        fetchBankAccounts,
        createBankAccount,
        updateBankAccount,
        deactivateBankAccount,
        activateBankAccount,
        getBankAccountSummary,

        // Cash Flow Actions
        fetchCashFlows,
        recordInflow,
        recordOutflow,
        reconcileCashFlow,
        deleteCashFlow,

        // Projection Actions
        fetchProjections,

        // Statistics Actions
        fetchStatistics,

        // Filter Actions
        setFilter,
        clearFilters,

        // Reset
        $reset,
    };
});
