<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    form: Object,
    savingsGoals: Array,
    hasSavingsGoal: Boolean,
});

const emit = defineEmits(['close', 'submit', 'update:form']);

const hasGoals = computed(() => props.savingsGoals.length > 0);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Savings Goal Modal -->
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">🏆</span>
                        {{ hasSavingsGoal ? 'Update Savings Goal' : 'Set Savings Goal' }}
                    </h3>
                    <button @click="$emit('close')" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Garden Goals Context -->
                <div v-if="hasGoals" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h4 class="font-medium text-green-800 mb-2 flex items-center">
                        <span class="mr-2">🌻</span>
                        Your Garden Goals
                    </h4>
                    <div class="space-y-2">
                        <div v-for="goal in savingsGoals.slice(0, 3)" :key="goal.id"
                             class="flex justify-between text-sm">
                            <span class="text-green-700">{{ goal.plant_emoji }} {{ goal.name }}</span>
                            <span class="text-green-600">{{ formatCurrency(goal.remaining_amount) }} needed</span>
                        </div>
                        <div v-if="savingsGoals.length > 3" class="text-xs text-green-600">
                            +{{ savingsGoals.length - 3 }} more goals
                        </div>
                    </div>
                    <p class="text-xs text-green-600 mt-2">Your monthly savings will become Water Bank for your plants! 💧</p>
                </div>

                <form @submit.prevent="$emit('submit')" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Monthly Savings Goal *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-stone-500">$</span>
                            <input
                                :value="form.monthly_savings_goal"
                                @input="$emit('update:form', { ...form, monthly_savings_goal: $event.target.value })"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="1000.00"
                                class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                        </div>
                        <div v-if="form.errors?.monthly_savings_goal" class="text-red-600 text-sm mt-1">
                            {{ form.errors.monthly_savings_goal }}
                        </div>
                        <p class="text-xs text-stone-500 mt-1">This amount will be set aside each month to water your garden plants.</p>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>🏆 {{ hasSavingsGoal ? 'Update' : 'Set' }} Goal</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
