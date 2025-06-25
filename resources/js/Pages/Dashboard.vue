<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NavLink from '@/Components/NavLink.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    savingsGoals: Array,
    monthlyExpenses: Array,
    expenseStats: Object,
});

const hasGoals = computed(() => props.savingsGoals.length > 0);
const hasExpenses = computed(() => props.monthlyExpenses.length > 0);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <Head title="This Month" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                    This Month
                </h2>
                <span class="ml-3 text-2xl">🌱</span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Card -->
                <div
                    class="overflow-hidden bg-white shadow-lg sm:rounded-xl border border-emerald-100 mb-6"
                >
                    <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                        <!-- Subtle decorative elements -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                        <div class="flex items-center relative">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-emerald-800">
                                    Welcome back! 🌱
                                </h3>
                                <p class="text-stone-600 mt-2 text-lg">
                                    Ready to grow your savings like a healthy garden?
                                </p>
                            </div>
                            <div class="hidden sm:block">
                                <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center shadow-md border border-amber-200">
                                    <span class="text-3xl">💰</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- This Month Card -->
                    <div class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl border border-emerald-100">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center shadow-sm">
                                        <span class="text-emerald-600 text-lg">📊</span>
                                    </div>
                                </div>
                                <dl class="ml-4">
                                    <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">This Month's Revenue</dt>
                                    <dd class="text-3xl font-bold text-emerald-800 mt-1">$0.00</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Remaining Card -->
                    <div class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl border border-amber-100">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-amber-100 to-amber-200 rounded-xl flex items-center justify-center shadow-sm">
                                        <span class="text-amber-600 text-lg">🎯</span>
                                    </div>
                                </div>
                                <dl class="ml-4">
                                    <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">Month's Budget Left</dt>
                                    <dd class="text-3xl font-bold text-amber-700 mt-1">$0.00</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Savings Goal Card -->
                    <div class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl border border-green-100">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center shadow-sm">
                                        <span class="text-green-600 text-lg">🏆</span>
                                    </div>
                                </div>
                                <dl class="ml-4">
                                    <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">Month's Savings Goal</dt>
                                    <dd class="text-3xl font-bold text-green-800 mt-1">$0.00</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                                    <button class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
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
                                <!-- Scrollable Expenses List -->
                                <div class="space-y-3 max-h-64 overflow-y-auto pr-2 mb-6">
                                    <div
                                        v-for="expense in monthlyExpenses"
                                        :key="`${expense.type}-${expense.id}`"
                                        class="border border-emerald-100 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200"
                                    >
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center">
                                                <span class="text-lg mr-3">{{ expense.type === 'recurring_bill' ? '💳' : '💸' }}</span>
                                                <div>
                                                    <h4 class="font-medium text-emerald-800">{{ expense.name }}</h4>
                                                    <div class="flex items-center gap-2">
                                    <span v-if="expense.type === 'recurring_bill'"
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                        🔄 Recurring Bill
                                    </span>
                                                        <span v-if="expense.formatted_bill_date" class="text-xs text-emerald-600">
                                        Due: {{ expense.formatted_bill_date }}
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

                                <!-- Always Visible Summary -->
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

                    <!-- My Garden -->
                    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-emerald-100">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-emerald-800 flex items-center">
                                    <span class="mr-2">🌻</span>
                                    My Garden
                                </h3>
                                <NavLink
                                    :href="route('garden.index')"
                                    class="text-sm bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-3 py-1 rounded-lg transition duration-200 flex items-center"
                                >
                                    <span class="mr-1">🌱</span>
                                    {{ hasGoals ? 'View All' : 'Plant a Goal' }}
                                </NavLink>
                            </div>

                            <!-- Empty State -->
                            <div v-if="!hasGoals" class="text-center py-8">
                                <div class="w-16 h-16 bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-emerald-200">
                                    <span class="text-2xl text-emerald-500">🌱</span>
                                </div>
                                <h4 class="font-medium text-stone-600 mb-2">Plant your first savings goal</h4>
                                <p class="text-sm text-stone-500 max-w-sm mx-auto leading-relaxed">Start growing your dreams! Add savings goals and watch them bloom!</p>
                            </div>

                            <!-- Goals List -->
                            <div v-else class="space-y-4 max-h-80 overflow-y-auto pr-2">
                                <div
                                    v-for="goal in savingsGoals"
                                    :key="goal.id"
                                    class="border border-emerald-100 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <span class="text-lg mr-2">{{ goal.plant_emoji }}</span>
                                            <div>
                                                <h4 class="font-medium text-emerald-800">{{ goal.name }}</h4>
                                                <p class="text-xs text-emerald-600">{{ goal.growth_stage }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-stone-700">{{ formatCurrency(goal.current_amount) }}</div>
                                            <div class="text-xs text-stone-500">of {{ formatCurrency(goal.target_amount) }}</div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs text-stone-500">Progress</span>
                                            <span class="text-xs font-medium text-emerald-700">{{ Math.round(goal.progress_percentage) }}%</span>
                                        </div>
                                        <div class="w-full bg-stone-200 rounded-full h-2">
                                            <div
                                                class="bg-gradient-to-r from-emerald-400 to-green-500 h-2 rounded-full transition-all duration-300"
                                                :style="{ width: `${Math.min(goal.progress_percentage, 100)}%` }"
                                            ></div>
                                        </div>
                                    </div>

                                    <div class="text-xs text-stone-500">
                                        {{ formatCurrency(goal.remaining_amount) }} remaining
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                    <button class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="mr-3 text-lg">📊</span>
                        Update Revenue
                    </button>
                    <button class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="mr-3 text-lg">🎯</span>
                        Change Budget
                    </button>
                    <button class="bg-green-500 hover:bg-green-600 text-white font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="mr-3 text-lg">🏆</span>
                        Change Savings Goal
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
