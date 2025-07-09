<script setup>
const props = defineProps({
    growthData: Object,
});

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

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Growth Comparison -->
    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-stone-200 mb-8">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-stone-800 mb-4 flex items-center">
                <span class="mr-2">📊</span>
                Month-over-Month Growth vs {{ growthData.previous_month }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Revenue Change -->
                <div class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-stone-700">Revenue Change</span>
                        <span :class="getGrowthColor(growthData.revenue_change)" class="text-lg">
                            {{ getGrowthIcon(growthData.revenue_change) }}
                        </span>
                    </div>
                    <div :class="getGrowthColor(growthData.revenue_change)" class="text-xl font-bold">
                        {{ growthData.revenue_change >= 0 ? '+' : '' }}{{ formatCurrency(growthData.revenue_change) }}
                    </div>
                    <div class="text-sm text-stone-500 mt-1">
                        {{ growthData.revenue_change_percent >= 0 ? '+' : '' }}{{ Math.round(growthData.revenue_change_percent) }}%
                    </div>
                </div>

                <!-- Expense Change -->
                <div class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-stone-700">Expense Change</span>
                        <span :class="getGrowthColor(-growthData.expense_change)" class="text-lg">
                            {{ getGrowthIcon(growthData.expense_change) }}
                        </span>
                    </div>
                    <div :class="getGrowthColor(-growthData.expense_change)" class="text-xl font-bold">
                        {{ growthData.expense_change >= 0 ? '+' : '' }}{{ formatCurrency(growthData.expense_change) }}
                    </div>
                    <div class="text-sm text-stone-500 mt-1">
                        {{ growthData.expense_change_percent >= 0 ? '+' : '' }}{{ Math.round(growthData.expense_change_percent) }}%
                    </div>
                </div>

                <!-- Water Change -->
                <div class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-stone-700">Water Change</span>
                        <span :class="getGrowthColor(growthData.water_change)" class="text-lg">
                            {{ getGrowthIcon(growthData.water_change) }}
                        </span>
                    </div>
                    <div :class="getGrowthColor(growthData.water_change)" class="text-xl font-bold">
                        {{ growthData.water_change >= 0 ? '+' : '' }}{{ formatCurrency(growthData.water_change) }}
                    </div>
                    <div class="text-sm text-stone-500 mt-1">
                        {{ growthData.water_change_percent >= 0 ? '+' : '' }}{{ Math.round(growthData.water_change_percent) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
