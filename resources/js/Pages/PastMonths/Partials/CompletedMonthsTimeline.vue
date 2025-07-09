<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    completedMonths: Array,
});

const emit = defineEmits(['delete-month']);

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

const getMonthEmoji = (monthNumber) => {
    const emojis = ['❄️', '💙', '🌸', '🌷', '🌻', '☀️', '🏖️', '🌽', '🍂', '🎃', '🦃', '🎄'];
    return emojis[monthNumber - 1];
};
</script>

<template>
    <!-- Completed Months Timeline -->
    <div>
        <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
            <span class="mr-2">📊</span>
            Your Financial History ({{ completedMonths.length }} months)
        </h3>

        <div class="space-y-6">
            <div
                v-for="month in completedMonths"
                :key="month.id"
                class="bg-white border border-emerald-100 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 overflow-hidden"
            >
                <div class="p-6">
                    <!-- Month Header -->
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                <span class="text-2xl">{{ getMonthEmoji(month.month) }}</span>
                            </div>
                            <div>
                                <h4 class="text-xl font-semibold text-emerald-800">{{ month.month_period }}</h4>
                                <p class="text-sm text-stone-500">Completed {{ month.completed_at_short }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('past-months.show', month.id)"
                                class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center text-sm"
                            >
                                <span class="mr-2">👁️</span>
                                View Details
                            </Link>
                            <button
                                @click="$emit('delete-month', month)"
                                class="bg-red-100 hover:bg-red-200 text-red-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center text-sm"
                            >
                                <span class="mr-2">🗑️</span>
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Financial Summary Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <!-- Revenue -->
                        <div class="text-center p-4 bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg border border-emerald-100">
                            <div class="text-2xl font-bold text-emerald-700">{{ formatCurrency(month.total_revenue) }}</div>
                            <div class="text-sm text-emerald-600">Revenue</div>
                            <div v-if="month.source_description" class="text-xs text-emerald-500 mt-1">
                                {{ month.source_description }}
                            </div>
                        </div>

                        <!-- Expenses -->
                        <div class="text-center p-4 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg border border-amber-100">
                            <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(month.total_expenses) }}</div>
                            <div class="text-sm text-amber-600">Total Expenses</div>
                            <div class="text-xs text-amber-500 mt-1">
                                {{ formatCurrency(month.recurring_bills_total) }} bills + {{ formatCurrency(month.one_time_expenses_total) }} one-time
                            </div>
                        </div>

                        <!-- Savings Goal -->
                        <div class="text-center p-4 bg-gradient-to-br from-green-50 to-teal-50 rounded-lg border border-green-100">
                            <div class="text-2xl font-bold text-green-700">{{ formatCurrency(month.monthly_savings_goal) }}</div>
                            <div class="text-sm text-green-600">Savings Goal</div>
                            <div class="text-xs text-green-500 mt-1">
                                {{ Math.round(month.savings_rate) }}% of revenue
                            </div>
                        </div>

                        <!-- Water Collected -->
                        <div class="text-center p-4 rounded-lg border"
                             :class="{
                                 'bg-gradient-to-br from-cyan-50 to-blue-50 border-cyan-100': !month.was_drought,
                                 'bg-gradient-to-br from-red-50 to-orange-50 border-red-100': month.was_drought
                             }">
                            <div class="text-2xl font-bold"
                                 :class="{
                                     'text-cyan-700': !month.was_drought,
                                     'text-red-700': month.was_drought
                                 }">
                                {{ formatCurrency(month.water_collected) }}
                            </div>
                            <div class="text-sm"
                                 :class="{
                                     'text-cyan-600': !month.was_drought,
                                     'text-red-600': month.was_drought
                                 }">
                                {{ month.was_drought ? 'Drought' : 'Water Collected' }}
                            </div>
                            <div class="text-xs mt-1"
                                 :class="{
                                     'text-cyan-500': !month.was_drought,
                                     'text-red-500': month.was_drought
                                 }">
                                {{ month.was_drought ? 'Overspent 🏜️' : 'Added to bank 💧' }}
                            </div>
                        </div>
                    </div>

                    <!-- Growth Comparison (if available) -->
                    <div v-if="month.growth_vs_previous" class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                        <h5 class="font-medium text-stone-700 mb-3 flex items-center">
                            <span class="mr-2">📊</span>
                            vs {{ month.growth_vs_previous.previous_month }}
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Revenue Change -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-stone-600">Revenue:</span>
                                <div class="flex items-center">
                                    <span :class="getGrowthColor(month.growth_vs_previous.revenue_change)" class="text-sm font-medium">
                                        {{ getGrowthIcon(month.growth_vs_previous.revenue_change) }}
                                        {{ formatCurrency(Math.abs(month.growth_vs_previous.revenue_change)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Expense Change -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-stone-600">Expenses:</span>
                                <div class="flex items-center">
                                    <span :class="getGrowthColor(-month.growth_vs_previous.expense_change)" class="text-sm font-medium">
                                        {{ getGrowthIcon(month.growth_vs_previous.expense_change) }}
                                        {{ formatCurrency(Math.abs(month.growth_vs_previous.expense_change)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Water Change -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-stone-600">Water:</span>
                                <div class="flex items-center">
                                    <span :class="getGrowthColor(month.growth_vs_previous.water_change)" class="text-sm font-medium">
                                        {{ getGrowthIcon(month.growth_vs_previous.water_change) }}
                                        {{ formatCurrency(Math.abs(month.growth_vs_previous.water_change)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
