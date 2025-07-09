<script setup>
import { computed } from 'vue';
import NavLink from '@/Components/NavLink.vue';

const props = defineProps({
    savingsGoals: Array,
    budgetData: Object,
    requireAuth: Function,
});

const emit = defineEmits(['openEndMonthModal']);

// Computed properties
const hasGoals = computed(() => props.savingsGoals.length > 0);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
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

            <!-- Water Bank Section -->
            <div v-if="budgetData && budgetData.potential_water_bank > 0"
                 class="mb-6 p-4 bg-gradient-to-br from-cyan-50 to-blue-50 border border-cyan-200 rounded-lg">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">💧</span>
                        <div>
                            <h4 class="font-semibold text-cyan-800">Water Bank Preview</h4>
                            <p class="text-xs text-cyan-600">All unspent money available for watering plants</p>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-cyan-800">
                        {{ formatCurrency(budgetData.potential_water_bank) }}
                    </div>
                </div>
                <div class="text-xs text-cyan-600 mb-3">
                    Includes your savings goal + any money you didn't spend
                </div>
                <div class="flex justify-center">
                    <button
                        @click="requireAuth(() => $emit('openEndMonthModal'))"
                        class="bg-cyan-500 hover:bg-cyan-600 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                    >
                        <span class="mr-2">🏁</span>
                        End Month & Collect Water
                    </button>
                </div>
            </div>

            <!-- Drought Warning -->
            <div v-else-if="budgetData?.is_drought"
                 class="mb-6 p-4 bg-gradient-to-br from-red-50 to-orange-50 border border-red-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">🏜️</span>
                        <div>
                            <h4 class="font-semibold text-red-800">Drought Mode</h4>
                            <p class="text-xs text-red-600">No water available - you've overspent this month</p>
                        </div>
                    </div>
                    <button
                        @click="$emit('openEndMonthModal')"
                        class="bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center text-sm"
                    >
                        <span class="mr-2">🏁</span>
                        End Month
                    </button>
                </div>
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
            <div v-else class="space-y-4 max-h-64 overflow-y-auto pr-2">
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
                            <div class="text-sm font-medium text-stone-700">
                                {{ formatCurrency(goal.current_amount) }}
                            </div>
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
</template>
