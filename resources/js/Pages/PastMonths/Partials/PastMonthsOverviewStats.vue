<script setup>
const props = defineProps({
    stats: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Overall Stats -->
    <div class="mb-8">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-emerald-100">
            <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                <div class="relative">
                    <h3 class="text-xl font-semibold text-emerald-800 mb-4">Your Financial Journey</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-emerald-700">{{ stats.total_months }}</div>
                            <div class="text-sm text-stone-600">Months Completed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-700">{{ formatCurrency(stats.total_water_collected) }}</div>
                            <div class="text-sm text-stone-600">Total Water Collected</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(stats.average_monthly_revenue) }}</div>
                            <div class="text-sm text-stone-600">Average Revenue</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-stone-700">{{ stats.drought_months }}</div>
                            <div class="text-sm text-stone-600">Drought Months</div>
                        </div>
                    </div>

                    <!-- Best Month Highlight -->
                    <div v-if="stats.best_month" class="mt-6 p-4 bg-gradient-to-r from-green-100 to-emerald-100 rounded-lg border border-green-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">🏆</span>
                                <div>
                                    <h4 class="font-semibold text-green-800">Best Month</h4>
                                    <p class="text-sm text-green-700">{{ stats.best_month.month_period }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold text-green-800">{{ formatCurrency(stats.best_month.water_collected) }}</div>
                                <div class="text-sm text-green-600">Water Collected</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
