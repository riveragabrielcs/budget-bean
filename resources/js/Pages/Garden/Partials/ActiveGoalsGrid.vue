<script setup>
const props = defineProps({
    activeGoals: Array,
});

const emit = defineEmits(['edit-goal', 'water-goal', 'complete-goal']);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Active Goals -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
            <span class="mr-2">🌱</span>
            Growing Goals ({{ activeGoals.length }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="goal in activeGoals"
                :key="goal.id"
                class="bg-white border border-emerald-100 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden"
            >
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ goal.plant_emoji }}</span>
                            <div>
                                <h4 class="font-semibold text-emerald-800 text-lg">{{ goal.name }}</h4>
                                <p class="text-sm text-emerald-600">{{ goal.growth_stage }}</p>
                            </div>
                        </div>
                        <button
                            @click="$emit('edit-goal', goal)"
                            class="text-stone-400 hover:text-emerald-600 transition-colors p-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                    </div>

                    <div v-if="goal.description" class="mb-4">
                        <p class="text-sm text-stone-600">{{ goal.description }}</p>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-stone-600">Progress</span>
                            <span class="text-sm font-medium text-emerald-700">{{ Math.round(goal.progress_percentage) }}%</span>
                        </div>
                        <div class="w-full bg-stone-200 rounded-full h-3">
                            <div
                                class="bg-gradient-to-r from-emerald-400 to-green-500 h-3 rounded-full transition-all duration-300"
                                :style="{ width: `${Math.min(goal.progress_percentage, 100)}%` }"
                            ></div>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-stone-500">Saved:</span>
                            <span class="text-sm font-medium text-emerald-700">{{ formatCurrency(goal.current_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-stone-500">Target:</span>
                            <span class="text-sm font-medium text-stone-700">{{ formatCurrency(goal.target_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-stone-500">Remaining:</span>
                            <span class="text-sm font-medium text-amber-600">{{ formatCurrency(goal.remaining_amount) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="$emit('water-goal', goal)"
                            class="flex-1 bg-cyan-100 hover:bg-cyan-200 text-cyan-700 font-medium py-2 px-3 rounded-lg transition duration-200 text-sm flex items-center justify-center"
                        >
                            <span class="mr-1">💧</span>
                            Water Plant
                        </button>
                        <button
                            v-if="goal.is_reached"
                            @click="$emit('complete-goal', goal)"
                            class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 font-medium py-2 px-3 rounded-lg transition duration-200 text-sm flex items-center justify-center"
                        >
                            <span class="mr-1">🎉</span>
                            Complete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
