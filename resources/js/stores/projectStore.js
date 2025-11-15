import { defineStore } from "pinia";
import axios from "axios";

export const useProjectStore = defineStore("project", {
    state: () => ({
        projects: [],
        currentProject: null,
        donors: [],
        teamMembers: [],
        budgets: [],
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
            donor_id: "",
        },
    }),

    getters: {
        /**
         * Get active projects
         */
        activeProjects: (state) => {
            return state.projects.filter(
                (project) => project.status === "active",
            );
        },

        /**
         * Get projects by status
         */
        projectsByStatus: (state) => (status) => {
            return state.projects.filter(
                (project) => project.status === status,
            );
        },

        /**
         * Get total project count
         */
        totalProjects: (state) => state.pagination.total,

        /**
         * Check if there are more pages
         */
        hasMorePages: (state) => {
            return state.pagination.current_page < state.pagination.last_page;
        },

        /**
         * Get current project budget summary
         */
        currentProjectBudgetSummary: (state) => {
            if (!state.currentProject || !state.currentProject.budget_summary) {
                return null;
            }
            return state.currentProject.budget_summary;
        },
    },

    actions: {
        /**
         * Fetch projects with filters and pagination
         */
        async fetchProjects(page = 1) {
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

                const response = await axios.get("/api/v1/projects", {
                    params,
                });

                if (response.data.success) {
                    this.projects = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total,
                    };
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch projects";
                console.error("Error fetching projects:", error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Fetch single project by ID
         */
        async fetchProject(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/v1/projects/${id}`);

                if (response.data.success) {
                    this.currentProject = response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to fetch project";
                console.error("Error fetching project:", error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Create new project
         */
        async createProject(projectData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    "/api/v1/projects",
                    projectData,
                );

                if (response.data.success) {
                    this.projects.unshift(response.data.data);
                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to create project";

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
         * Update existing project
         */
        async updateProject(id, projectData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.put(
                    `/api/v1/projects/${id}`,
                    projectData,
                );

                if (response.data.success) {
                    // Update in projects list
                    const index = this.projects.findIndex((p) => p.id === id);
                    if (index !== -1) {
                        this.projects[index] = response.data.data;
                    }

                    // Update current project if it's the same
                    if (this.currentProject?.id === id) {
                        this.currentProject = response.data.data;
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to update project";

                if (error.response?.data?.errors) {
                    throw error.response.data.errors;
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Archive project
         */
        async archiveProject(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/v1/projects/${id}/archive`,
                );

                if (response.data.success) {
                    // Update in projects list
                    const index = this.projects.findIndex((p) => p.id === id);
                    if (index !== -1) {
                        this.projects[index] = response.data.data;
                    }

                    // Update current project if it's the same
                    if (this.currentProject?.id === id) {
                        this.currentProject = response.data.data;
                    }

                    return response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to archive project";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Delete project
         */
        async deleteProject(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.delete(`/api/v1/projects/${id}`);

                if (response.data.success) {
                    // Remove from projects list
                    this.projects = this.projects.filter((p) => p.id !== id);

                    // Clear current project if it's the same
                    if (this.currentProject?.id === id) {
                        this.currentProject = null;
                    }

                    return true;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to delete project";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Generate project financial report
         */
        async generateReport(id, format = "pdf") {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(
                    `/api/v1/projects/${id}/report`,
                    {
                        params: { format },
                        responseType: "blob", // Important for file download
                    },
                );

                // Create download link
                const url = window.URL.createObjectURL(
                    new Blob([response.data]),
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", `project-${id}-report.${format}`);
                document.body.appendChild(link);
                link.click();
                link.remove();

                return true;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to generate report";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Fetch all donors (for project assignment)
         */
        async fetchDonors() {
            try {
                const response = await axios.get("/api/v1/donors");
                if (response.data.success) {
                    this.donors = response.data.data.data || response.data.data;
                }
            } catch (error) {
                console.error("Error fetching donors:", error);
            }
        },

        /**
         * Fetch all team members (for project assignment)
         */
        async fetchTeamMembers() {
            try {
                const response = await axios.get("/api/v1/users", {
                    params: { role: "project-team" },
                });
                if (response.data.success) {
                    this.teamMembers =
                        response.data.data.data || response.data.data;
                }
            } catch (error) {
                console.error("Error fetching team members:", error);
            }
        },

        /**
         * Set search filter
         */
        setSearchFilter(search) {
            this.filters.search = search;
        },

        /**
         * Set status filter
         */
        setStatusFilter(status) {
            this.filters.status = status;
        },

        /**
         * Set donor filter
         */
        setDonorFilter(donorId) {
            this.filters.donor_id = donorId;
        },

        /**
         * Clear all filters
         */
        clearFilters() {
            this.filters = {
                search: "",
                status: "",
                donor_id: "",
            };
        },

        /**
         * Clear current project
         */
        clearCurrentProject() {
            this.currentProject = null;
        },

        /**
         * Clear error
         */
        clearError() {
            this.error = null;
        },
    },
});
