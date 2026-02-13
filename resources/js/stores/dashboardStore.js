/**
 * Dashboard Store
 *
 * Manages dashboard state including KPIs, charts data, notifications
 * Handles auto-refresh polling every 30 seconds
 */

import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "../api";

export const useDashboardStore = defineStore("dashboard", () => {
    // State
    const loading = ref(false);
    const dashboardData = ref(null);
    const notifications = ref([]);
    const unreadCount = ref(0);
    const refreshInterval = ref(null);
    const lastRefreshTime = ref(null);
    const error = ref(null);

    // Computed
    const kpis = computed(() => dashboardData.value?.kpis || {});
    const charts = computed(() => dashboardData.value?.charts || {});
    const recentActivity = computed(
        () => dashboardData.value?.recent_activity || [],
    );
    const pendingItems = computed(
        () => dashboardData.value?.pending_items || [],
    );

    // Programs Manager specific
    const pmDashboard = computed(() => ({
        kpis: kpis.value,
        charts: charts.value,
        recentActivity: recentActivity.value,
        pendingItems: pendingItems.value,
    }));

    // Finance Officer specific
    const foDashboard = computed(() => ({
        kpis: kpis.value,
        charts: charts.value,
        recentTransactions: dashboardData.value?.recent_transactions || [],
        poStatus: dashboardData.value?.po_status || {},
        paymentSchedule: dashboardData.value?.payment_schedule || [],
    }));

    // Project Officer specific
    const poDashboard = computed(() => ({
        kpis: kpis.value,
        assignedProjects: dashboardData.value?.assigned_projects || [],
        projectActivities: dashboardData.value?.project_activities || [],
        personalTasks: dashboardData.value?.personal_tasks || [],
        projectTimeline: dashboardData.value?.project_timeline || [],
        teamCollaboration: dashboardData.value?.team_collaboration || [],
    }));

    // Actions
    const fetchDashboardData = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await api.get("/dashboard");
            dashboardData.value = response.data.data;
            lastRefreshTime.value = new Date();
        } catch (err) {
            console.error("Failed to fetch dashboard data:", err);
            error.value =
                err.response?.data?.message ||
                "Failed to load dashboard data. Please try again.";
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const fetchNotifications = async () => {
        try {
            const response = await api.get("/dashboard/notifications");
            const data = response.data.data;
            notifications.value = data.notifications || [];
            unreadCount.value = data.unread_count || 0;
        } catch (error) {
            console.error("Failed to fetch notifications:", error);
        }
    };

    const markNotificationRead = async (notificationId) => {
        try {
            await api.post(`/dashboard/notifications/${notificationId}/read`);

            // Update local state
            const notification = notifications.value.find(
                (n) => n.id === notificationId,
            );
            if (notification) {
                notification.read = true;
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (error) {
            console.error("Failed to mark notification as read:", error);
        }
    };

    const startAutoRefresh = () => {
        // Clear existing interval if any
        if (refreshInterval.value) {
            clearInterval(refreshInterval.value);
        }

        // Refresh every 30 seconds
        refreshInterval.value = setInterval(() => {
            fetchDashboardData();
            fetchNotifications();
        }, 30000);
    };

    const stopAutoRefresh = () => {
        if (refreshInterval.value) {
            clearInterval(refreshInterval.value);
            refreshInterval.value = null;
        }
    };

    const refreshDashboard = async () => {
        await Promise.all([fetchDashboardData(), fetchNotifications()]);
    };

    const clearDashboard = () => {
        dashboardData.value = null;
        notifications.value = [];
        unreadCount.value = 0;
        lastRefreshTime.value = null;
        error.value = null;
        stopAutoRefresh();
    };

    return {
        // State
        loading,
        dashboardData,
        notifications,
        unreadCount,
        lastRefreshTime,
        error,

        // Computed
        kpis,
        charts,
        recentActivity,
        pendingItems,
        pmDashboard,
        foDashboard,
        poDashboard,

        // Actions
        fetchDashboardData,
        fetchNotifications,
        markNotificationRead,
        startAutoRefresh,
        stopAutoRefresh,
        refreshDashboard,
        clearDashboard,
    };
});
