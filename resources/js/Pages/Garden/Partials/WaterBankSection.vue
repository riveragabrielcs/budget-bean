<script setup>
import { computed } from 'vue';

const props = defineProps({
    waterBank: Object,
    hasActiveGoals: Boolean,
});

const emit = defineEmits(['open-water-all-modal']);

const hasWaterBank = computed(() => props.waterBank.balance > 0);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Water Bank Section -->
    <div class="mb-8">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-cyan-100">
            <div class="p-6 bg-gradient-to-br from-cyan-50 via-blue-50 to-indigo-50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>

                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-cyan-800 flex items-center">
                            <span class="mr-2">💧</span>
                            Water Bank
                        </h3>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-cyan-800">{{ formatCurrency(waterBank.balance) }}</div>
                            <div class="text-sm text-cyan-600">Available Water</div>
                        </div>
                    </div>

                    <div v-if="!hasWaterBank" class="text-center py-4">
                        <p class="text-cyan-600 mb-2">Your water bank is empty! 🏜️</p>
                        <p class="text-xs text-cyan-500">End a month with unspent money to fill your water bank.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Water Actions -->
                        <div>
                            <h4 class="font-medium text-cyan-700 mb-3 flex items-center">
                                <span class="mr-2">🚿</span>
                                Watering Actions
                            </h4>
                            <div class="space-y-2">
                                <button
                                    v-if="hasActiveGoals"
                                    @click="$emit('open-water-all-modal')"
                                    class="w-full bg-cyan-100 hover:bg-cyan-200 text-cyan-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                                >
                                    <span class="mr-2">🌊</span>
                                    Water All Goals Equally
                                </button>
                                <p class="text-xs text-cyan-600">
                                    Or use the "Water Plant" button on individual goals below
                                </p>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        <div v-if="waterBank.recent_transactions.length > 0">
                            <h4 class="font-medium text-cyan-700 mb-3 flex items-center">
                                <span class="mr-2">📊</span>
                                Recent Activity
                            </h4>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                <div
                                    v-for="transaction in waterBank.recent_transactions"
                                    :key="transaction.id"
                                    class="flex items-center justify-between text-xs"
                                >
                                    <div class="flex items-center">
                                        <span class="mr-2">{{ transaction.icon }}</span>
                                        <span class="text-cyan-700">{{ transaction.description }}</span>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="font-medium"
                                              :class="transaction.type === 'deposit' ? 'text-green-600' : 'text-red-600'">
                                            {{ transaction.formatted_amount }}
                                        </span>
                                        <span class="text-cyan-500">{{ transaction.date_short }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
