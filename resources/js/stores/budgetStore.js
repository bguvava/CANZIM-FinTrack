import { defineStore } from "pinia";
import axios from "axios";

export const useBudgetStore = defineStore("budget", {
    state: () => ({
        budgets: [],
        currentBudget: null,
        budgetItems: [],
        budgetCategories: [],
        reallocations: [],
        loading: false,
        error: null,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 25,
            total: 0,
        },
        filters: {
            project_id: "",
            status: "",
        },
    }),

    getters: {
        /**
         * Get approved budgets
         */
        approvedBudgets: (state) => {
            return state.budgets.filter(
                (budget) => budget.status === "approved",
            );
        },

        /**
         * Get pending budgets
         */
        pendingBudgets: (state) => {
            return state.budgets.filter(
                (budget) => budget.status === "pending",
            );
        },

        /**
         * Get rejected budgets
         */
        rejectedBudgets: (state) => {
            return state.budgets.filter(
                (budget) => budget.status === "rejected",
            );
        },

        /**
         * Get budgets by project
         */
        budgetsByProject: (state) => (projectId) => {
            return state.budgets.filter(
                (budget) => budget.project_id === projectId,
            );
        },

        /**
         * Get current budget total allocated
         */
        currentBudgetTotal: (state) => {
            if (!state.currentBudget) return 0;
            return state.currentBudget.total_allocated || 0;
        },

        /**
         * Get current budget total spent
         */
        currentBudgetSpent: (state) => {
            if (!state.currentBudget) return 0;
            return state.currentBudget.total_spent || 0;
        },

        /**
         * Get current budget remaining
         */
        currentBudgetRemaining: (state) => {
            if (!state.currentBudget) return 0;
            const allocated = state.currentBudget.total_allocated || 0;
            const spent = state.currentBudget.total_spent || 0;
            return allocated - spent;
        },

        /**
         * Get current budget utilization percentage
         */
        currentBudgetUtilization: (state) => {
            if (!state.currentBudget || !state.currentBudget.total_allocated)
                return 0;
            return state.currentBudget.utilization_percentage || 0;
        },

        /**
         * Get current budget alert level
         */
        currentBudgetAlertLevel: (state) => {
            if (!state.currentBudget) return null;
            return state.currentBudget.alert_level || null;
        },

        /**
         * Get budget items by category
         */
        budgetItemsByCategory: (state) => (category) => {
            return state.budgetItems.filter(
                (item) => item.category === category,
            );
        },

        /**
         * Calculate total by category
         */
        totalByCategory: (state) => (category) => {
            return state.budgetItems
                .filter((item) => item.category === category)
                .reduce(
                    (sum, item) => sum + parseFloat(item.total_amount || 0),
                    0,
                );
        },
    },

    actions: {
        /**
         * Fetch budgets with filters and pagination
         */
        async fetchBudgets(page = 1) {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    page,
                    per_page: this.pagination.per_page,
                    ...this.filters,
                };

                // Remove empty filters
                Object.keys(params).forEach((key) => {
                    if (params[key] === "" || params[key] === null) {
                        delete params[key];
                    }
                });

                const response = await axios.get("/api/v1/budgets", { params });

                if (response.data.success) {
                    this.budgets = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                    };
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch budgets";
                console.error("Error fetching budgets:", error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Fetch single budget by ID
         */
        async fetchBudget(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/v1/budgets/${id}`);

                if (response.data.success) {
                    this.currentBudget = response.data.data;
                    this.budgetItems = response.data.data.items || [];
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch budget";
                console.error("Error fetching budget:", error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Fetch budget categories
         */
        async fetchBudgetCategories() {
            try {
                const response = await axios.get("/api/v1/budgets/categories");

                if (response.data.success) {
                    this.budgetCategories = response.data.data;
                }
            } catch (error) {
                console.error("Error fetching budget categories:", error);
            }
        },

        /**
         * Create new budget
         */
        async createBudget(budgetData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    "/api/v1/budgets",
                    budgetData,
                );

                if (response.data.success) {
                    this.budgets.unshift(response.data.data);
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to create budget";

                // Return validation errors if present
                if (error.response?.data?.errors) {
                    throw error.response.data.errors;
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Update existing budget
         */
        async updateBudget(id, budgetData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.put(
                    `/api/v1/budgets/${id}`,
                    budgetData,
                );

                if (response.data.success) {
                    // Update in budgets list
                    const index = this.budgets.findIndex((b) => b.id === id);
                    if (index !== -1) {
                        this.budgets[index] = response.data.data;
                    }

                    // Update current budget if it's the same
                    if (this.currentBudget?.id === id) {
                        this.currentBudget = response.data.data;
                        this.budgetItems = response.data.data.items || [];
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to update budget";

                if (error.response?.data?.errors) {
                    throw error.response.data.errors;
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Approve budget
         */
        async approveBudget(id, notes = "") {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/budgets/${id}/approve`,
                    { notes },
                );

                if (response.data.success) {
                    // Update in budgets list
                    const index = this.budgets.findIndex((b) => b.id === id);
                    if (index !== -1) {
                        this.budgets[index] = response.data.data;
                    }

                    // Update current budget if it's the same
                    if (this.currentBudget?.id === id) {
                        this.currentBudget = response.data.data;
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to approve budget";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Request budget reallocation
         */
        async requestReallocation(budgetId, reallocationData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/budgets/${budgetId}/reallocations`,
                    reallocationData,
                );

                if (response.data.success) {
                    this.reallocations.unshift(response.data.data);
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to request reallocation";

                if (error.response?.data?.errors) {
                    throw error.response.data.errors;
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Approve budget reallocation
         */
        async approveReallocation(budgetId, reallocationId, notes = "") {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/budgets/${budgetId}/reallocations/${reallocationId}/approve`,
                    { notes },
                );

                if (response.data.success) {
                    // Update in reallocations list
                    const index = this.reallocations.findIndex(
                        (r) => r.id === reallocationId,
                    );
                    if (index !== -1) {
                        this.reallocations[index] = response.data.data;
                    }

                    // Refresh current budget to reflect changes
                    if (this.currentBudget?.id === budgetId) {
                        await this.fetchBudget(budgetId);
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to approve reallocation";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Reject budget reallocation
         */
        async rejectReallocation(budgetId, reallocationId, notes = "") {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/budgets/${budgetId}/reallocations/${reallocationId}/reject`,
                    { notes },
                );

                if (response.data.success) {
                    // Update in reallocations list
                    const index = this.reallocations.findIndex(
                        (r) => r.id === reallocationId,
                    );
                    if (index !== -1) {
                        this.reallocations[index] = response.data.data;
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to reject reallocation";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Delete budget
         */
        async deleteBudget(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.delete(`/api/v1/budgets/${id}`);

                if (response.data.success) {
                    // Remove from budgets list
                    this.budgets = this.budgets.filter((b) => b.id !== id);

                    // Clear current budget if it's the same
                    if (this.currentBudget?.id === id) {
                        this.currentBudget = null;
                        this.budgetItems = [];
                    }

                    return true;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to delete budget";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Set project filter
         */
        setProjectFilter(projectId) {
            this.filters.project_id = projectId;
        },

        /**
         * Set status filter
         */
        setStatusFilter(status) {
            this.filters.status = status;
        },

        /**
         * Clear all filters
         */
        clearFilters() {
            this.filters = {
                project_id: "",
                status: "",
            };
        },

        /**
         * Clear current budget
         */
        clearCurrentBudget() {
            this.currentBudget = null;
            this.budgetItems = [];
        },

        /**
         * Clear error
         */
        clearError() {
            this.error = null;
        },

        /**
         * Add budget item to current items (for form building)
         */
        addBudgetItem(item) {
            this.budgetItems.push(item);
        },

        /**
         * Remove budget item from current items
         */
        removeBudgetItem(index) {
            this.budgetItems.splice(index, 1);
        },

        /**
         * Update budget item in current items
         */
        updateBudgetItem(index, item) {
            this.budgetItems[index] = item;
        },

        /**
         * Clear all budget items (for form reset)
         */
        clearBudgetItems() {
            this.budgetItems = [];
        },
    },
});
