import { defineStore } from "pinia";
import api from "../api";

export const useExpenseStore = defineStore("expense", {
    state: () => ({
        expenses: [],
        categories: [],
        currentExpense: null,
        pendingReview: [],
        pendingApproval: [],
        loading: false,
        error: null,
        pagination: {
            current_page: 1,
            per_page: 15,
            total: 0,
            last_page: 1,
        },
        filters: {
            status: null,
            project_id: null,
            expense_category_id: null,
            date_from: null,
            date_to: null,
            search: "",
        },
    }),

    getters: {
        pendingCount: (state) => {
            const user = JSON.parse(localStorage.getItem("user") || "{}");
            if (user.role === "Finance Officer") {
                return state.pendingReview.length;
            } else if (user.role === "Programs Manager") {
                return state.pendingApproval.length;
            }
            return 0;
        },

        myExpenses: (state) => {
            const user = JSON.parse(localStorage.getItem("user") || "{}");
            return state.expenses.filter(
                (expense) => expense.submitted_by === user.id,
            );
        },

        filteredExpenses: (state) => {
            let filtered = [...state.expenses];

            if (state.filters.status) {
                filtered = filtered.filter(
                    (e) => e.status === state.filters.status,
                );
            }

            if (state.filters.project_id) {
                filtered = filtered.filter(
                    (e) => e.project_id === state.filters.project_id,
                );
            }

            if (state.filters.expense_category_id) {
                filtered = filtered.filter(
                    (e) =>
                        e.expense_category_id ===
                        state.filters.expense_category_id,
                );
            }

            if (state.filters.search) {
                const search = state.filters.search.toLowerCase();
                filtered = filtered.filter(
                    (e) =>
                        e.expense_number?.toLowerCase().includes(search) ||
                        e.description?.toLowerCase().includes(search),
                );
            }

            return filtered;
        },
    },

    actions: {
        async fetchExpenses(page = 1) {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    page,
                    per_page: this.pagination.per_page,
                    ...this.filters,
                };

                const response = await api.get("/expenses", {
                    params,
                });

                this.expenses = response.data.data;
                this.pagination = {
                    current_page: response.data.current_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                    last_page: response.data.last_page,
                };
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error fetching expenses";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                const response = await api.get("/expenses/categories");
                this.categories = response.data;
            } catch (error) {
                console.error("Error fetching categories:", error);
            }
        },

        async fetchPendingReview() {
            try {
                const response = await api.get("/expenses/pending-review");
                this.pendingReview = response.data;
            } catch (error) {
                console.error("Error fetching pending review:", error);
            }
        },

        async fetchPendingApproval() {
            try {
                const response = await api.get("/expenses/pending-approval");
                this.pendingApproval = response.data;
            } catch (error) {
                console.error("Error fetching pending approval:", error);
            }
        },

        async fetchExpense(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.get(`/expenses/${id}`);
                this.currentExpense = response.data;
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error fetching expense";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createExpense(data) {
            this.loading = true;
            this.error = null;

            try {
                const formData = new FormData();
                Object.keys(data).forEach((key) => {
                    if (data[key] !== null && data[key] !== undefined) {
                        formData.append(key, data[key]);
                    }
                });

                const response = await api.post("/expenses", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

                return response.data.expense;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error creating expense";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateExpense(id, data) {
            this.loading = true;
            this.error = null;

            try {
                const formData = new FormData();
                formData.append("_method", "PUT");
                Object.keys(data).forEach((key) => {
                    if (data[key] !== null && data[key] !== undefined) {
                        formData.append(key, data[key]);
                    }
                });

                const response = await api.post(`/expenses/${id}`, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

                return response.data.expense;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error updating expense";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async submitExpense(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post(`/expenses/${id}/submit`);
                await this.fetchExpenses(this.pagination.current_page);
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error submitting expense";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async reviewExpense(id, action, comments = null) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post(`/expenses/${id}/review`, {
                    action,
                    comments,
                });

                await this.fetchPendingReview();
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error reviewing expense";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async approveExpense(id, action, comments = null) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post(`/expenses/${id}/approve`, {
                    action,
                    comments,
                });

                await this.fetchPendingApproval();
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error processing expense";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async markAsPaid(id, paymentData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post(
                    `/expenses/${id}/mark-paid`,
                    paymentData,
                );
                await this.fetchExpenses(this.pagination.current_page);
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error marking expense as paid";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteExpense(id) {
            this.loading = true;
            this.error = null;

            try {
                await api.delete(`/expenses/${id}`);
                await this.fetchExpenses(this.pagination.current_page);
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error deleting expense";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setFilter(key, value) {
            this.filters[key] = value;
        },

        clearFilters() {
            this.filters = {
                status: null,
                project_id: null,
                expense_category_id: null,
                date_from: null,
                date_to: null,
                search: "",
            };
        },

        setPerPage(perPage) {
            this.pagination.per_page = perPage;
        },
    },
});
