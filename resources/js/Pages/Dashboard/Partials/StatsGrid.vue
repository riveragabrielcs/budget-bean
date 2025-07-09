<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentRevenue: Object,
    budgetData: Object,
});

// Computed properties
const hasRevenue = computed(() => props.currentRevenue !== null);
const hasSavingsGoal = computed(() => props.currentRevenue?.monthly_savings_goal > 0);

const budgetStatus = computed(() => {
    if (!props.budgetData) return 'neutral';
    const remaining = props.budgetData.budget_remaining;
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
    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- This Month's Revenue Card -->
        <div class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl border border-emerald-100">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center shadow-sm">
                            <span class="text-emerald-600 text-lg">📊</span>
                        </div>
                    </div>
                    <dl class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">This Month's Revenue</dt>
                        <dd class="text-3xl font-bold text-emerald-800 mt-1">
                            {{ hasRevenue ? formatCurrency(currentRevenue.total_revenue) : '$0.00' }}
                        </dd>
                        <div v-if="hasRevenue && currentRevenue.source_description" class="text-xs text-emerald-600 mt-1">
                            {{ currentRevenue.source_description }}
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Budget Remaining Card -->
        <div class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl"
             :class="{
                 'border-emerald-100': budgetStatus === 'positive',
                 'border-amber-200': budgetStatus === 'warning',
                 'border-red-200': budgetStatus === 'negative',
                 'border-stone-200': budgetStatus === 'neutral'
             }">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                             :class="{
                                 'bg-gradient-to-br from-emerald-100 to-emerald-200': budgetStatus === 'positive',
                                 'bg-gradient-to-br from-amber-100 to-amber-200': budgetStatus === 'warning',
                                 'bg-gradient-to-br from-red-100 to-red-200': budgetStatus === 'negative',
                                 'bg-gradient-to-br from-stone-100 to-stone-200': budgetStatus === 'neutral'
                             }">
                            <span class="text-lg"
                                  :class="{
                                      'text-emerald-600': budgetStatus === 'positive',
                                      'text-amber-600': budgetStatus === 'warning',
                                      'text-red-600': budgetStatus === 'negative',
                                      'text-stone-600': budgetStatus === 'neutral'
                                  }">
                                {{ budgetData?.is_drought ? '🏜️' : '🎯' }}
                            </span>
                        </div>
                    </div>
                    <dl class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">Month's Budget Left</dt>
                        <dd class="text-3xl font-bold mt-1"
                            :class="{
                                'text-emerald-800': budgetStatus === 'positive',
                                'text-amber-700': budgetStatus === 'warning',
                                'text-red-700': budgetStatus === 'negative',
                                'text-stone-700': budgetStatus === 'neutral'
                            }">
                            {{ budgetData ? formatCurrency(budgetData.budget_remaining) : '$0.00' }}
                        </dd>
                        <div v-if="budgetData?.is_drought" class="text-xs text-red-600 mt-1">
                            Drought Mode - You've overspent! 🏜️
                        </div>
                        <div v-else-if="budgetStatus === 'warning'" class="text-xs text-amber-600 mt-1">
                            Running low on budget
                        </div>
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
                    <dl class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">Month's Savings Goal</dt>
                        <dd class="text-3xl font-bold text-green-800 mt-1">
                            {{ hasSavingsGoal ? formatCurrency(currentRevenue.monthly_savings_goal) : '$0.00' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>
