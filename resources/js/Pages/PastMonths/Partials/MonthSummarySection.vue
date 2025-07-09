<script setup>
const props = defineProps({
    completedMonth: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
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
</template>
