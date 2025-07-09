<script setup>
import { computed } from 'vue';
import NavLink from '@/Components/NavLink.vue';
import { useExpenseTypes } from '@/Composables/useExpenseTypes';

const props = defineProps({
    monthlyExpenses: Array,
    expenseStats: Object,
});

const emit = defineEmits(['openAddExpenseModal', 'deleteExpense']);

const { isRecurringBill } = useExpenseTypes();

// Computed properties
const hasExpenses = computed(() => props.monthlyExpenses.length > 0);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- This Month's Expenses -->
    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-emerald-100">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-emerald-800 mb-6 flex items-center">
                <span class="mr-2">📋</span>
                This Month's Expenses
            </h3>

            <!-- Empty State -->
            <div v-if="!hasExpenses" class="text-center py-12">
                <div class="w-20 h-20 bg-gradient-to-br from-stone-50 to-stone-100 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-stone-200">
                    <span class="text-3xl text-stone-400">🧾</span>
                </div>
                <h4 class="font-medium text-stone-600 mb-2">No expenses yet</h4>
                <p class="text-sm text-stone-500 max-w-sm mx-auto leading-relaxed mb-6">Start tracking your spending to see them here and watch your financial garden grow!</p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button
                        @click="$emit('openAddExpenseModal')"
                        class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                    >
                        <span class="mr-2">💸</span>
                        Add One-Time Expense
                    </button>
                    <NavLink
                        :href="route('bills.index')"
                        class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                    >
                        <span class="mr-2">🔄</span>
                        Add Recurring Bill
                    </NavLink>
                </div>
            </div>

            <!-- Expenses List and Summary -->
            <div v-else>
                <!-- Always Visible Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <button
                        @click="$emit('openAddExpenseModal')"
                        class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                    >
                        <span class="mr-2">➕</span>
                        Add One-Time Expense
                    </button>
                    <NavLink
                        :href="route('bills.index')"
                        class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                    >
                        <span class="mr-2">🔄</span>
                        Manage Recurring Bills
                    </NavLink>
                </div>

                <!-- Scrollable Expenses List -->
                <div class="space-y-3 max-h-64 overflow-y-auto pr-2 mb-6">
                    <div
                        v-for="expense in monthlyExpenses"
                        :key="`${expense.type}-${expense.id}`"
                        class="border border-emerald-100 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center flex-1">
                                <span class="text-lg mr-3">{{ isRecurringBill(expense.type) ? '💳' : '💸' }}</span>
                                <div class="flex-1">
                                    <h4 class="font-medium text-emerald-800">{{ expense.name }}</h4>
                                    <div class="flex items-center gap-2">
                                        <span v-if="isRecurringBill(expense.type)"
                                              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                            🔄 Recurring Bill
                                        </span>
                                        <span v-else
                                              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            💸 One-Time Expense
                                        </span>
                                        <span v-if="expense.formatted_bill_date" class="text-xs text-emerald-600">
                                            Due: {{ expense.formatted_bill_date }}
                                        </span>
                                        <span v-if="expense.formatted_expense_date" class="text-xs text-emerald-600">
                                            {{ expense.formatted_expense_date }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <div class="text-lg font-bold text-stone-700">
                                        {{ formatCurrency(expense.amount) }}
                                    </div>
                                </div>
                                <!-- Delete button for one-time expenses only -->
                                <button
                                    v-if="expense.type === 'one_time_expense'"
                                    @click="$emit('deleteExpense', expense)"
                                    class="text-stone-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50"
                                    title="Delete expense"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div v-if="expense.description" class="text-sm text-stone-500 mt-2">
                            {{ expense.description }}
                        </div>
                    </div>
                </div>

                <!-- Always Visible Summary (Outside Scroll) -->
                <div class="border-t border-emerald-100 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-stone-600">Monthly Total:</span>
                        <span class="text-xl font-bold text-emerald-800">{{ formatCurrency(expenseStats.total_monthly_expenses) }}</span>
                    </div>
                    <div class="text-sm text-stone-500 mt-1">
                        {{ expenseStats.recurring_bills_count }} recurring bills
                        <span v-if="expenseStats.one_time_expenses_count > 0">
                            + {{ expenseStats.one_time_expenses_count }} one-time expenses
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
