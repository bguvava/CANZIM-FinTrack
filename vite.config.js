import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/bootstrap-projects.js",
                "resources/js/bootstrap-projects-create.js",
                "resources/js/bootstrap-projects-show.js",
                "resources/js/bootstrap-projects-edit.js",
                "resources/js/bootstrap-budgets.js",
                "resources/js/bootstrap-budgets-create.js",
                "resources/js/bootstrap-expenses.js",
                "resources/js/bootstrap-expenses-create.js",
                "resources/js/bootstrap-expenses-edit.js",
                "resources/js/bootstrap-expenses-show.js",
                "resources/js/bootstrap-activity-logs.js",
                "resources/js/bootstrap-pending-approval.js",
                "resources/js/bootstrap-pending-review.js",
                "resources/js/bootstrap-cash-flow.js",
                "resources/js/bootstrap-cash-flow-transactions.js",
                "resources/js/bootstrap-cash-flow-bank-accounts.js",
                "resources/js/bootstrap-cash-flow-projections.js",
                "resources/js/bootstrap-purchase-orders.js",
                "resources/js/bootstrap-po-vendors.js",
                "resources/js/bootstrap-po-pending-approval.js",
                "resources/js/bootstrap-donors.js",
                "resources/js/bootstrap-reports.js",
                "resources/js/bootstrap-documents.js",
                "resources/js/bootstrap-settings.js",
                "resources/js/bootstrap-audit-trail.js",
            ],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Split vendor modules
                    if (id.includes("node_modules")) {
                        if (id.includes("vue") || id.includes("pinia")) {
                            return "vue-vendor";
                        }
                        if (id.includes("chart.js")) {
                            return "chart-vendor";
                        }
                        return "vendor";
                    }

                    // Split page components by module
                    if (id.includes("/pages/Projects/")) {
                        return "projects-pages";
                    }
                    if (id.includes("/pages/Budgets/")) {
                        return "budgets-pages";
                    }
                    if (id.includes("/pages/Expenses/")) {
                        return "expenses-pages";
                    }

                    // Split stores
                    if (id.includes("/stores/")) {
                        return "stores";
                    }
                },
            },
        },
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        },
    },
});
