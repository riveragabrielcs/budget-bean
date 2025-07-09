<script setup>
import { computed } from 'vue';

const props = defineProps({
    completedMonth: Object,
    allExpensesCount: Number,
});

const budgetStatus = computed(() => {
    const remaining = props.completedMonth.budget_remaining;
    if (remaining < 0) return 'negative';
    if (remaining < 200) return 'warning';
    return 'positive';
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
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
                            {{ allExpensesCount }} items
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
</template>
