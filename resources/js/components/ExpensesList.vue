<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useExpenseStore } from "../stores/expenseStore";
import { useRouter } from "vue-router";
import debounce from "lodash/debounce";

const router = useRouter();
const expenseStore = useExpenseStore();

const user = JSON.parse(localStorage.getItem("user") || "{}");

const statusColors = {
    Draft: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
    Submitted: "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300",
    "Under Review":
        "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300",
    Approved:
        "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300",
    Rejected: "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300",
    Paid: "bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300",
};

const searchQuery = ref("");

const debouncedSearch = debounce((value) => {
    expenseStore.setFilter("search", value);
    expenseStore.fetchExpenses(1);
}, 300);

watch(searchQuery, (newValue) => {
    debouncedSearch(newValue);
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const viewExpense = (id) => {
    router.push({ name: "expense-view", params: { id } });
};

const createExpense = () => {
    router.push({ name: "expense-create" });
};

const changePage = (page) => {
    expenseStore.fetchExpenses(page);
};

onMounted(async () => {
    await Promise.all([
        expenseStore.fetchExpenses(),
        expenseStore.fetchCategories(),
    ]);
});
</script>

<template>
    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Expenses
            </h1>
            <button
                @click="createExpense"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
                Submit New Expense
            </button>
        </div>

        <div class="mb-4 flex gap-4">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search expenses..."
                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
            <select
                v-model="expenseStore.filters.status"
                @change="expenseStore.fetchExpenses(1)"
                class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
                <option :value="null">All Statuses</option>
                <option value="Draft">Draft</option>
                <option value="Submitted">Submitted</option>
                <option value="Under Review">Under Review</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
                <option value="Paid">Paid</option>
            </select>
        </div>

        <div v-if="expenseStore.loading" class="text-center">Loading...</div>

        <div
            v-else
            class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800"
        >
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Expense #
                        </th>
                        <th
                            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Date
                        </th>
                        <th
                            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Category
                        </th>
                        <th
                            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Amount
                        </th>
                        <th
                            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Status
                        </th>
                        <th
                            class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr
                        v-for="expense in expenseStore.expenses"
                        :key="expense.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <td
                            class="px-4 py-3 text-sm text-gray-900 dark:text-white"
                        >
                            {{ expense.expense_number }}
                        </td>
                        <td
                            class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300"
                        >
                            {{ formatDate(expense.expense_date) }}
                        </td>
                        <td
                            class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300"
                        >
                            {{ expense.category?.name }}
                        </td>
                        <td
                            class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ formatCurrency(expense.amount) }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                :class="statusColors[expense.status]"
                                class="rounded-full px-2 py-1 text-xs font-medium"
                            >
                                {{ expense.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button
                                @click="viewExpense(expense.id)"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="expenseStore.pagination.last_page > 1"
            class="mt-4 flex justify-center gap-2"
        >
            <button
                v-for="page in expenseStore.pagination.last_page"
                :key="page"
                @click="changePage(page)"
                :class="[
                    'rounded px-3 py-1',
                    page === expenseStore.pagination.current_page
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                ]"
            >
                {{ page }}
            </button>
        </div>
    </div>
</template>
