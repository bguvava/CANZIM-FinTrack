import { defineStore } from "pinia";
import axios from "axios";

export const useDonorStore = defineStore("donor", {
    state: () => ({
        donors: [],
        currentDonor: null,
        loading: false,
        error: null,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 25,
            total: 0,
        },
        filters: {
            search: "",
            status: "",
            funding_min: "",
        },
        statistics: {
            total_donors: 0,
            active_donors: 0,
            total_funding: 0,
            average_funding: 0,
        },
    }),

    getters: {
        /**
         * Get active donors
         */
        activeDonors: (state) => {
            return state.donors.filter((donor) => donor.status === "active");
        },

        /**
         * Get inactive donors
         */
        inactiveDonors: (state) => {
            return state.donors.filter((donor) => donor.status === "inactive");
        },

        /**
         * Get donor by ID
         */
        getDonorById: (state) => (id) => {
            return state.donors.find((donor) => donor.id === id);
        },

        /**
         * Get total donor count
         */
        totalDonors: (state) => state.pagination.total,

        /**
         * Check if there are more pages
         */
        hasMorePages: (state) => {
            return state.pagination.current_page < state.pagination.last_page;
        },

        /**
         * Get total funding across all donors
         */
        totalFunding: (state) => {
            return state.donors.reduce(
                (sum, donor) => sum + parseFloat(donor.total_funding || 0),
                0,
            );
        },
    },

    actions: {
        /**
         * Fetch donors with filters and pagination
         */
        async fetchDonors(page = 1) {
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

                const response = await axios.get("/api/v1/donors", { params });

                if (response.data.success) {
                    this.donors = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                    };
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch donors";
                console.error("Error fetching donors:", error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Fetch donor statistics
         */
        async fetchStatistics() {
            try {
                const response = await axios.get("/api/v1/donors/statistics");

                if (response.data.success) {
                    this.statistics = response.data.data;
                }
            } catch (error) {
                console.error("Error fetching donor statistics:", error);
            }
        },

        /**
         * Fetch single donor by ID
         */
        async fetchDonor(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/v1/donors/${id}`);

                if (response.data.success) {
                    this.currentDonor = response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch donor";
                console.error("Error fetching donor:", error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Create new donor
         */
        async createDonor(donorData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post("/api/v1/donors", donorData);

                if (response.data.success) {
                    this.donors.unshift(response.data.data);
                    await this.fetchStatistics();
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to create donor";

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
         * Update existing donor
         */
        async updateDonor(id, donorData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.put(
                    `/api/v1/donors/${id}`,
                    donorData,
                );

                if (response.data.success) {
                    // Update in donors list
                    const index = this.donors.findIndex(
                        (donor) => donor.id === id,
                    );
                    if (index !== -1) {
                        this.donors[index] = response.data.data;
                    }

                    // Update current donor if it's the one being updated
                    if (this.currentDonor?.id === id) {
                        this.currentDonor = response.data.data;
                    }

                    await this.fetchStatistics();
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to update donor";

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
         * Delete donor
         */
        async deleteDonor(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.delete(`/api/v1/donors/${id}`);

                if (response.data.success) {
                    // Remove from donors list
                    this.donors = this.donors.filter(
                        (donor) => donor.id !== id,
                    );

                    await this.fetchStatistics();
                    return response.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to delete donor";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Toggle donor status
         */
        async toggleStatus(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/donors/${id}/toggle-status`,
                );

                if (response.data.success) {
                    // Update in donors list
                    const index = this.donors.findIndex(
                        (donor) => donor.id === id,
                    );
                    if (index !== -1) {
                        this.donors[index] = response.data.data;
                    }

                    // Update current donor if it's the one being updated
                    if (this.currentDonor?.id === id) {
                        this.currentDonor = response.data.data;
                    }

                    await this.fetchStatistics();
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to toggle donor status";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Assign donor to project
         */
        async assignToProject(donorId, projectData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/donors/${donorId}/assign-project`,
                    projectData,
                );

                if (response.data.success) {
                    // Refresh current donor to get updated relationships
                    if (this.currentDonor?.id === donorId) {
                        await this.fetchDonor(donorId);
                    }

                    await this.fetchStatistics();
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to assign donor to project";

                if (error.response?.data?.errors) {
                    throw error.response.data.errors;
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Remove donor from project
         */
        async removeFromProject(donorId, projectId) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.delete(
                    `/api/v1/donors/${donorId}/projects/${projectId}`,
                );

                if (response.data.success) {
                    // Refresh current donor to get updated relationships
                    if (this.currentDonor?.id === donorId) {
                        await this.fetchDonor(donorId);
                    }

                    await this.fetchStatistics();
                    return response.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to remove donor from project";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Add in-kind contribution
         */
        async addInKindContribution(contributionData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    "/api/v1/in-kind-contributions",
                    contributionData,
                );

                if (response.data.success) {
                    // Refresh current donor if it matches
                    if (this.currentDonor?.id === contributionData.donor_id) {
                        await this.fetchDonor(contributionData.donor_id);
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to add in-kind contribution";

                if (error.response?.data?.errors) {
                    throw error.response.data.errors;
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Log communication
         */
        async logCommunication(communicationData) {
            this.loading = true;
            this.error = null;

            try {
                const formData = new FormData();
                Object.keys(communicationData).forEach((key) => {
                    if (communicationData[key] !== null) {
                        formData.append(key, communicationData[key]);
                    }
                });

                const response = await axios.post(
                    "/api/v1/communications",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    },
                );

                if (response.data.success) {
                    // Refresh current donor if it matches
                    if (
                        this.currentDonor?.id ===
                        communicationData.communicable_id
                    ) {
                        await this.fetchDonor(
                            communicationData.communicable_id,
                        );
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to log communication";

                if (error.response?.data?.errors) {
                    throw error.response.data.errors;
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Get funding summary for donor
         */
        async getFundingSummary(donorId) {
            try {
                const response = await axios.get(
                    `/api/v1/donors/${donorId}/funding-summary`,
                );

                if (response.data.success) {
                    return response.data.data;
                }
            } catch (error) {
                console.error("Error fetching funding summary:", error);
                throw error;
            }
        },

        /**
         * Generate donor financial report (PDF)
         */
        async generateReport(donorId) {
            try {
                const response = await axios.get(
                    `/api/v1/donors/${donorId}/report`,
                    {
                        responseType: "blob",
                    },
                );

                // Create blob URL and trigger download
                const blob = new Blob([response.data], {
                    type: "application/pdf",
                });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.href = url;
                link.download = `donor-financial-report-${donorId}-${Date.now()}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);

                return true;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to generate report";
                throw error;
            }
        },

        /**
         * Restore a soft-deleted donor
         */
        async restoreDonor(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/donors/${id}/restore`,
                );

                if (response.data.success) {
                    await this.fetchDonors(this.pagination.current_page);
                    await this.fetchStatistics();
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to restore donor";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Set filter values
         */
        setFilters(filters) {
            this.filters = { ...this.filters, ...filters };
        },

        /**
         * Clear all filters
         */
        clearFilters() {
            this.filters = {
                search: "",
                status: "",
                funding_min: "",
            };
        },

        /**
         * Fetch chart data for donor analytics
         */
        async fetchChartData() {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/v1/donors/chart-data");

                if (response.data.success) {
                    return response.data.data;
                }

                return {
                    funding_distribution: { labels: [], datasets: [] },
                    top_donors: { labels: [], datasets: [] },
                    funding_timeline: { labels: [], datasets: [] },
                };
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to fetch chart data";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Reset store state
         */
        resetState() {
            this.donors = [];
            this.currentDonor = null;
            this.loading = false;
            this.error = null;
            this.clearFilters();
        },
    },
});
