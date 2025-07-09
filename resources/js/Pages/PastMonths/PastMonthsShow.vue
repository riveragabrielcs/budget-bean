<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    completedMonth: Object,
});

// Modal states
const showDeleteModal = ref(false);

// Delete form
const deleteForm = useForm({});

// Computed properties
const recurringBills = computed(() => props.completedMonth.expenses_snapshot.recurring_bills || []);
const oneTimeExpenses = computed(() => props.completedMonth.expenses_snapshot.one_time_expenses || []);
const allExpenses = computed(() => [...recurringBills.value, ...oneTimeExpenses.value]);

const budgetStatus = computed(() => {
    const remaining = props.completedMonth.budget_remaining;
    if (remaining < 0) return 'negative';
    if (remaining < 200) return 'warning';
    return 'positive';
});

// Methods
const openDeleteModal = () => {
    showDeleteModal.value = true;
};

const submitDelete = () => {
    deleteForm.delete(route('past-months.destroy', props.completedMonth.id), {
        onSuccess: () => {
            // Will redirect to past months index
        },
    });
};

const closeModals = () => {
    showDeleteModal.value = false;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const getGrowthIcon = (change) => {
    if (change > 0) return '📈';
    if (change < 0) return '📉';
    return '➡️';
};

const getGrowthColor = (change) => {
    if (change > 0) return 'text-green-600';
    if (change < 0) return 'text-red-600';
    return 'text-stone-600';
};
</script>

<template>
    <Head :title="`${completedMonth.month_period} - Past Month Details`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <Link
                        :href="route('past-months.index')"
                        class="text-emerald-600 hover:text-emerald-800 mr-4"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </Link>
                    <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                        {{ completedMonth.month_period }}
                    </h2>
                    <span class="ml-3 text-2xl">📊</span>
                </div>
                <button
                    @click="openDeleteModal"
                    class="bg-red-100 hover:bg-red-200 text-red-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                >
                    <span class="mr-2">🗑️</span>
                    Delete Month
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Month Overview -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-emerald-100 mb-8">
                    <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                        <div class="relative">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-2xl font-semibold text-emerald-800">{{ completedMonth.month_period }}</h3>
                                    <p class="text-stone-600 mt-1">Completed {{ completedMonth.completed_at }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold"
                                         :class="{
                                             'text-cyan-700': !completedMonth.was_drought,
                                             'text-red-700': completedMonth.was_drought
                                         }">
                                        {{ formatCurrency(completedMonth.water_collected) }}
                                    </div>
                                    <div class="text-sm"
                                         :class="{
                                             'text-cyan-600': !completedMonth.was_drought,
                                             'text-red-600': completedMonth.was_drought
                                         }">
                                        {{ completedMonth.was_drought ? 'Drought Month 🏜️' : 'Water Collected 💧' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Key Financial Metrics -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Revenue -->
                                <div class="text-center p-4 bg-white rounded-lg shadow-sm border border-emerald-100">
                                    <div class="text-2xl font-bold text-emerald-700">{{ formatCurrency(completedMonth.total_revenue) }}</div>
                                    <div class="text-sm text-emerald-600">Total Revenue</div>
                                    <div v-if="completedMonth.source_description" class="text-xs text-emerald-500 mt-1">
                                        {{ completedMonth.source_description }}
                                    </div>
                                </div>

                                <!-- Expenses -->
                                <div class="text-center p-4 bg-white rounded-lg shadow-sm border border-amber-100">
                                    <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(completedMonth.total_expenses) }}</div>
                                    <div class="text-sm text-amber-600">Total Expenses</div>
                                    <div class="text-xs text-amber-500 mt-1">
                                        {{ allExpenses.length }} items
                                    </div>
                                </div>

                                <!-- Savings Goal -->
                                <div class="text-center p-4 bg-white rounded-lg shadow-sm border border-green-100">
                                    <div class="text-2xl font-bold text-green-700">{{ formatCurrency(completedMonth.monthly_savings_goal) }}</div>
                                    <div class="text-sm text-green-600">Savings Goal</div>
                                    <div class="text-xs text-green-500 mt-1">
                                        {{ Math.round(completedMonth.savings_rate) }}% of revenue
                                    </div>
                                </div>

                                <!-- Budget Remaining -->
                                <div class="text-center p-4 bg-white rounded-lg shadow-sm"
                                     :class="{
                                         'border-emerald-100': budgetStatus === 'positive',
                                         'border-amber-200': budgetStatus === 'warning',
                                         'border-red-200': budgetStatus === 'negative'
                                     }">
                                    <div class="text-2xl font-bold"
                                         :class="{
                                             'text-emerald-700': budgetStatus === 'positive',
                                             'text-amber-700': budgetStatus === 'warning',
                                             'text-red-700': budgetStatus === 'negative'
                                         }">
                                        {{ formatCurrency(completedMonth.budget_remaining) }}
                                    </div>
                                    <div class="text-sm"
                                         :class="{
                                             'text-emerald-600': budgetStatus === 'positive',
                                             'text-amber-600': budgetStatus === 'warning',
                                             'text-red-600': budgetStatus === 'negative'
                                         }">
                                        Budget Left
                                    </div>
                                    <div class="text-xs mt-1"
                                         :class="{
                                             'text-emerald-500': budgetStatus === 'positive',
                                             'text-amber-500': budgetStatus === 'warning',
                                             'text-red-500': budgetStatus === 'negative'
                                         }">
                                        {{ completedMonth.was_drought ? 'Overspent' : 'Within budget' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Growth Comparison (if available) -->
                <div v-if="completedMonth.growth_vs_previous" class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-stone-200 mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-stone-800 mb-4 flex items-center">
                            <span class="mr-2">📊</span>
                            Month-over-Month Growth vs {{ completedMonth.growth_vs_previous.previous_month }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Revenue Change -->
                            <div class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-stone-700">Revenue Change</span>
                                    <span :class="getGrowthColor(completedMonth.growth_vs_previous.revenue_change)" class="text-lg">
                                        {{ getGrowthIcon(completedMonth.growth_vs_previous.revenue_change) }}
                                    </span>
                                </div>
                                <div :class="getGrowthColor(completedMonth.growth_vs_previous.revenue_change)" class="text-xl font-bold">
                                    {{ completedMonth.growth_vs_previous.revenue_change >= 0 ? '+' : '' }}{{ formatCurrency(completedMonth.growth_vs_previous.revenue_change) }}
                                </div>
                                <div class="text-sm text-stone-500 mt-1">
                                    {{ completedMonth.growth_vs_previous.revenue_change_percent >= 0 ? '+' : '' }}{{ Math.round(completedMonth.growth_vs_previous.revenue_change_percent) }}%
                                </div>
                            </div>

                            <!-- Expense Change -->
                            <div class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-stone-700">Expense Change</span>
                                    <span :class="getGrowthColor(-completedMonth.growth_vs_previous.expense_change)" class="text-lg">
                                        {{ getGrowthIcon(completedMonth.growth_vs_previous.expense_change) }}
                                    </span>
                                </div>
                                <div :class="getGrowthColor(-completedMonth.growth_vs_previous.expense_change)" class="text-xl font-bold">
                                    {{ completedMonth.growth_vs_previous.expense_change >= 0 ? '+' : '' }}{{ formatCurrency(completedMonth.growth_vs_previous.expense_change) }}
                                </div>
                                <div class="text-sm text-stone-500 mt-1">
                                    {{ completedMonth.growth_vs_previous.expense_change_percent >= 0 ? '+' : '' }}{{ Math.round(completedMonth.growth_vs_previous.expense_change_percent) }}%
                                </div>
                            </div>

                            <!-- Water Change -->
                            <div class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-stone-700">Water Change</span>
                                    <span :class="getGrowthColor(completedMonth.growth_vs_previous.water_change)" class="text-lg">
                                        {{ getGrowthIcon(completedMonth.growth_vs_previous.water_change) }}
                                    </span>
                                </div>
                                <div :class="getGrowthColor(completedMonth.growth_vs_previous.water_change)" class="text-xl font-bold">
                                    {{ completedMonth.growth_vs_previous.water_change >= 0 ? '+' : '' }}{{ formatCurrency(completedMonth.growth_vs_previous.water_change) }}
                                </div>
                                <div class="text-sm text-stone-500 mt-1">
                                    {{ completedMonth.growth_vs_previous.water_change_percent >= 0 ? '+' : '' }}{{ Math.round(completedMonth.growth_vs_previous.water_change_percent) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                    <span class="text-xl font-bold text-amber-800">{{ formatCurrency(completedMonth.recurring_bills_total) }}</span>
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
                                    <span class="text-xl font-bold text-emerald-800">{{ formatCurrency(completedMonth.one_time_expenses_total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Summary -->
                <div class="mt-8 bg-white overflow-hidden shadow-md sm:rounded-xl border border-stone-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-stone-800 mb-6 flex items-center">
                            <span class="mr-2">📋</span>
                            Month Summary
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Financial Breakdown -->
                            <div>
                                <h4 class="font-medium text-stone-700 mb-4">Financial Breakdown</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2 border-b border-stone-100">
                                        <span class="text-stone-600">Total Revenue:</span>
                                        <span class="font-medium text-emerald-700">{{ formatCurrency(completedMonth.total_revenue) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-stone-100">
                                        <span class="text-stone-600">Monthly Savings Goal:</span>
                                        <span class="font-medium text-green-700">{{ formatCurrency(completedMonth.monthly_savings_goal) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-stone-100">
                                        <span class="text-stone-600">Available Budget:</span>
                                        <span class="font-medium text-stone-700">{{ formatCurrency(completedMonth.monthly_budget) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-stone-100">
                                        <span class="text-stone-600">Total Expenses:</span>
                                        <span class="font-medium text-amber-700">{{ formatCurrency(completedMonth.total_expenses) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-stone-600">Money Left Over:</span>
                                        <span class="font-bold"
                                              :class="{
                                                  'text-cyan-700': !completedMonth.was_drought,
                                                  'text-red-700': completedMonth.was_drought
                                              }">
                                            {{ formatCurrency(completedMonth.water_collected) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance Metrics -->
                            <div>
                                <h4 class="font-medium text-stone-700 mb-4">Performance Metrics</h4>
                                <div class="space-y-4">
                                    <div class="p-3 bg-stone-50 rounded-lg border border-stone-200">
                                        <div class="text-sm text-stone-600 mb-1">Savings Rate</div>
                                        <div class="text-xl font-bold text-green-700">{{ Math.round(completedMonth.savings_rate) }}%</div>
                                        <div class="text-xs text-stone-500">of total revenue</div>
                                    </div>

                                    <div class="p-3 bg-stone-50 rounded-lg border border-stone-200">
                                        <div class="text-sm text-stone-600 mb-1">Budget Utilization</div>
                                        <div class="text-xl font-bold text-amber-700">
                                            {{ Math.round((completedMonth.total_expenses / Math.max(completedMonth.monthly_budget, 1)) * 100) }}%
                                        </div>
                                        <div class="text-xs text-stone-500">of available budget</div>
                                    </div>

                                    <div class="p-3 rounded-lg border"
                                         :class="{
                                             'bg-cyan-50 border-cyan-200': !completedMonth.was_drought,
                                             'bg-red-50 border-red-200': completedMonth.was_drought
                                         }">
                                        <div class="text-sm mb-1"
                                             :class="{
                                                 'text-cyan-600': !completedMonth.was_drought,
                                                 'text-red-600': completedMonth.was_drought
                                             }">Month Status</div>
                                        <div class="text-xl font-bold"
                                             :class="{
                                                 'text-cyan-700': !completedMonth.was_drought,
                                                 'text-red-700': completedMonth.was_drought
                                             }">
                                            {{ completedMonth.was_drought ? 'Drought' : 'Successful' }}
                                        </div>
                                        <div class="text-xs"
                                             :class="{
                                                 'text-cyan-500': !completedMonth.was_drought,
                                                 'text-red-500': completedMonth.was_drought
                                             }">
                                            {{ completedMonth.was_drought ? 'Overspent budget 🏜️' : 'Stayed within budget 💧' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-red-800 flex items-center">
                            <span class="mr-2">⚠️</span>
                            Delete Completed Month
                        </h3>
                        <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6">
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg mb-4">
                            <h4 class="font-medium text-red-800 mb-2">{{ completedMonth.month_period }}</h4>
                            <div class="text-sm text-red-700 space-y-1">
                                <div>Revenue: {{ formatCurrency(completedMonth.total_revenue) }}</div>
                                <div>Expenses: {{ formatCurrency(completedMonth.total_expenses) }}</div>
                                <div>Water Collected: {{ formatCurrency(completedMonth.water_collected) }}</div>
                                <div>{{ allExpenses.length }} expense records</div>
                            </div>
                        </div>
                        <p class="text-stone-600 text-sm">
                            Are you sure you want to delete this completed month? This action cannot be undone and you will lose all historical data for this period.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="closeModals"
                            class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitDelete"
                            :disabled="deleteForm.processing"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="deleteForm.processing">Deleting...</span>
                            <span v-else>🗑️ Delete Month</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
