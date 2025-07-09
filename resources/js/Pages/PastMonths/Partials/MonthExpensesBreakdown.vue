<script setup>
const props = defineProps({
    recurringBills: Array,
    oneTimeExpenses: Array,
    recurringBillsTotal: [Number, String],
    oneTimeExpensesTotal: [Number, String],
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Detailed Expenses Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recurring Bills -->
        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-amber-100">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-amber-800 mb-6 flex items-center">
                    <span class="mr-2">🔄</span>
                    Recurring Bills ({{ recurringBills.length }})
                </h3>

                <div v-if="recurringBills.length === 0" class="text-center py-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-amber-200">
                        <span class="text-2xl text-amber-500">💳</span>
                    </div>
                    <h4 class="font-medium text-stone-600 mb-2">No Recurring Bills</h4>
                    <p class="text-sm text-stone-500">No recurring bills were recorded for this month.</p>
                </div>

                <div v-else class="space-y-3 max-h-96 overflow-y-auto pr-2">
                    <div
                        v-for="bill in recurringBills"
                        :key="`bill-${bill.id}`"
                        class="border border-amber-100 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center flex-1">
                                <span class="text-lg mr-3">💳</span>
                                <div class="flex-1">
                                    <h4 class="font-medium text-amber-800">{{ bill.name }}</h4>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                            🔄 Recurring Bill
                                        </span>
                                        <span v-if="bill.formatted_bill_date" class="text-xs text-amber-600">
                                            Due: {{ bill.formatted_bill_date }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-stone-700">{{ formatCurrency(bill.amount) }}</div>
                            </div>
                        </div>

                        <div v-if="bill.description" class="text-sm text-stone-500 mt-2">
                            {{ bill.description }}
                        </div>
                    </div>
                </div>

                <!-- Bills Summary -->
                <div v-if="recurringBills.length > 0" class="border-t border-amber-100 pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-stone-600">Bills Total:</span>
                        <span class="text-xl font-bold text-amber-800">{{ formatCurrency(recurringBillsTotal) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- One-Time Expenses -->
        <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-emerald-100">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-emerald-800 mb-6 flex items-center">
                    <span class="mr-2">💸</span>
                    One-Time Expenses ({{ oneTimeExpenses.length }})
                </h3>

                <div v-if="oneTimeExpenses.length === 0" class="text-center py-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-emerald-200">
                        <span class="text-2xl text-emerald-500">💸</span>
                    </div>
                    <h4 class="font-medium text-stone-600 mb-2">No One-Time Expenses</h4>
                    <p class="text-sm text-stone-500">No one-time expenses were recorded for this month.</p>
                </div>

                <div v-else class="space-y-3 max-h-96 overflow-y-auto pr-2">
                    <div
                        v-for="expense in oneTimeExpenses"
                        :key="`expense-${expense.id}`"
                        class="border border-emerald-100 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center flex-1">
                                <span class="text-lg mr-3">💸</span>
                                <div class="flex-1">
                                    <h4 class="font-medium text-emerald-800">{{ expense.name }}</h4>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            💸 One-Time Expense
                                        </span>
                                        <span v-if="expense.formatted_expense_date" class="text-xs text-emerald-600">
                                            {{ expense.formatted_expense_date }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-stone-700">{{ formatCurrency(expense.amount) }}</div>
                            </div>
                        </div>

                        <div v-if="expense.description" class="text-sm text-stone-500 mt-2">
                            {{ expense.description }}
                        </div>
                    </div>
                </div>

                <!-- Expenses Summary -->
                <div v-if="oneTimeExpenses.length > 0" class="border-t border-emerald-100 pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-stone-600">Expenses Total:</span>
                        <span class="text-xl font-bold text-emerald-800">{{ formatCurrency(oneTimeExpensesTotal) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
