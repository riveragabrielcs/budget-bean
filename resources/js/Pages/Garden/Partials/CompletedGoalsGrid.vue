<script setup>
const props = defineProps({
    completedGoals: Array,
});

const emit = defineEmits(['delete-goal']);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Completed Goals -->
    <div>
        <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
            <span class="mr-2">🌳</span>
            Completed Goals ({{ completedGoals.length }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="goal in completedGoals"
                :key="goal.id"
                class="bg-white border border-green-200 rounded-xl shadow-md overflow-hidden opacity-90"
            >
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">🌳</span>
                            <div>
                                <h4 class="font-semibold text-green-800 text-lg">{{ goal.name }}</h4>
                                <p class="text-sm text-green-600">Completed {{ goal.completed_at }}</p>
                            </div>
                        </div>
                        <button
                            @click="$emit('delete-goal', goal)"
                            class="text-stone-400 hover:text-red-500 transition-colors p-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4">
                        <div class="w-full bg-green-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full w-full"></div>
                        </div>
                    </div>

                    <div class="text-center">
                        <span class="text-lg font-bold text-green-700">{{ formatCurrency(goal.target_amount) }}</span>
                        <p class="text-sm text-stone-600">Goal Achieved! 🎉</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
