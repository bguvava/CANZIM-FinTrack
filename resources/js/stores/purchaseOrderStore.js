import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "../api";

export const usePurchaseOrderStore = defineStore("purchaseOrder", () => {
    // State
    const purchaseOrders = ref([]);
    const vendors = ref([]);
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
        status: "", // draft, pending, approved, received, completed, rejected, cancelled
        vendor_id: "",
        project_id: "",
        date_from: "",
        date_to: "",
        search: "",
    });

    // Getters
    const draftPOs = computed(() =>
        purchaseOrders.value.filter((po) => po.status === "draft"),
    );

    const pendingPOs = computed(() =>
        purchaseOrders.value.filter((po) => po.status === "pending"),
    );

    const approvedPOs = computed(() =>
        purchaseOrders.value.filter((po) => po.status === "approved"),
    );

    const completedPOs = computed(() =>
        purchaseOrders.value.filter((po) => po.status === "completed"),
    );

    const activeVendors = computed(() =>
        vendors.value.filter((vendor) => !vendor.deleted_at),
    );

    const pendingApprovalCount = computed(() => pendingPOs.value.length);

    // Actions - Purchase Orders
    async function fetchPurchaseOrders(page = 1) {
        loading.value = true;
        error.value = null;
        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
                ...filters.value,
            };

            const response = await api.get("/purchase-orders", { params });

            purchaseOrders.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                per_page: response.data.per_page,
                total: response.data.total,
            };
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error fetching purchase orders";
            console.error("Error fetching purchase orders:", err);
        } finally {
            loading.value = false;
        }
    }

    async function createPurchaseOrder(data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post("/purchase-orders", data);
            purchaseOrders.value.unshift(
                response.data.purchase_order || response.data.data,
            );
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error creating purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function updatePurchaseOrder(id, data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.put(`/purchase-orders/${id}`, data);
            const index = purchaseOrders.value.findIndex((po) => po.id === id);
            if (index !== -1) {
                purchaseOrders.value[index] =
                    response.data.purchase_order || response.data.data;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error updating purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function submitForApproval(id) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/purchase-orders/${id}/submit`);
            const index = purchaseOrders.value.findIndex((po) => po.id === id);
            if (index !== -1) {
                purchaseOrders.value[index].status = "pending";
                purchaseOrders.value[index].submitted_at =
                    response.data.purchase_order.submitted_at;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message ||
                "Error submitting purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function approvePurchaseOrder(id) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/purchase-orders/${id}/approve`);
            const index = purchaseOrders.value.findIndex((po) => po.id === id);
            if (index !== -1) {
                purchaseOrders.value[index] = response.data.purchase_order;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error approving purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function rejectPurchaseOrder(id, rejectionReason) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/purchase-orders/${id}/reject`, {
                rejection_reason: rejectionReason,
            });
            const index = purchaseOrders.value.findIndex((po) => po.id === id);
            if (index !== -1) {
                purchaseOrders.value[index] = response.data.purchase_order;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error rejecting purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function markAsReceived(id, receivedItems) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/purchase-orders/${id}/receive`, {
                items: receivedItems,
            });
            const index = purchaseOrders.value.findIndex((po) => po.id === id);
            if (index !== -1) {
                purchaseOrders.value[index] = response.data.purchase_order;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message ||
                "Error marking items as received";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function completePurchaseOrder(id) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/purchase-orders/${id}/complete`);
            const index = purchaseOrders.value.findIndex((po) => po.id === id);
            if (index !== -1) {
                purchaseOrders.value[index] = response.data.purchase_order;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message ||
                "Error completing purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function cancelPurchaseOrder(id, reason) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/purchase-orders/${id}/cancel`, {
                reason,
            });
            const index = purchaseOrders.value.findIndex((po) => po.id === id);
            if (index !== -1) {
                purchaseOrders.value[index] = response.data.purchase_order;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message ||
                "Error cancelling purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function deletePurchaseOrder(id) {
        loading.value = true;
        error.value = null;
        try {
            await api.delete(`/purchase-orders/${id}`);
            purchaseOrders.value = purchaseOrders.value.filter(
                (po) => po.id !== id,
            );
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error deleting purchase order";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    // Actions - Vendors
    async function fetchVendors() {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get("/vendors");
            vendors.value = response.data.data || response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error fetching vendors";
            console.error("Error fetching vendors:", err);
        } finally {
            loading.value = false;
        }
    }

    async function createVendor(data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post("/vendors", data);
            vendors.value.unshift(response.data.vendor || response.data.data);
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error creating vendor";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function updateVendor(id, data) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.put(`/vendors/${id}`, data);
            const index = vendors.value.findIndex((vendor) => vendor.id === id);
            if (index !== -1) {
                vendors.value[index] =
                    response.data.vendor || response.data.data;
            }
            return response.data;
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error updating vendor";
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function deleteVendor(id) {
        loading.value = true;
        error.value = null;
        try {
            await api.delete(`/vendors/${id}`);
            vendors.value = vendors.value.filter((vendor) => vendor.id !== id);
        } catch (err) {
            error.value =
                err.response?.data?.message || "Error deleting vendor";
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
            const response = await api.get("/purchase-orders/statistics");
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
            status: "",
            vendor_id: "",
            project_id: "",
            date_from: "",
            date_to: "",
            search: "",
        };
    }

    // Reset store
    function $reset() {
        purchaseOrders.value = [];
        vendors.value = [];
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
        purchaseOrders,
        vendors,
        statistics,
        loading,
        error,
        pagination,
        filters,

        // Getters
        draftPOs,
        pendingPOs,
        approvedPOs,
        completedPOs,
        activeVendors,
        pendingApprovalCount,

        // Purchase Order Actions
        fetchPurchaseOrders,
        createPurchaseOrder,
        updatePurchaseOrder,
        submitForApproval,
        approvePurchaseOrder,
        rejectPurchaseOrder,
        markAsReceived,
        completePurchaseOrder,
        cancelPurchaseOrder,
        deletePurchaseOrder,

        // Vendor Actions
        fetchVendors,
        createVendor,
        updateVendor,
        deleteVendor,

        // Statistics Actions
        fetchStatistics,

        // Filter Actions
        setFilter,
        clearFilters,

        // Reset
        $reset,
    };
});
