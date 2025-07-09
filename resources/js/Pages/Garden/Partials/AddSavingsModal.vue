<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    form: Object,
    selectedGoal: Object,
    waterBank: Object,
});

const emit = defineEmits(['close', 'submit', 'update:form']);

const hasWaterBank = computed(() => props.waterBank.balance > 0);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Add Savings Modal -->
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">💧</span>
                        Water Your Plant
                    </h3>
                    <button @click="$emit('close')" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div v-if="selectedGoal" class="mb-6 p-4 bg-emerald-50 rounded-lg">
                    <div class="flex items-center mb-2">
                        <span class="text-lg mr-2">{{ selectedGoal.plant_emoji }}</span>
                        <span class="font-medium text-emerald-800">{{ selectedGoal.name }}</span>
                    </div>
                    <div class="text-sm text-stone-600">
                        Current: {{ formatCurrency(selectedGoal.current_amount) }} / {{ formatCurrency(selectedGoal.target_amount) }}
                    </div>
                </div>

                <form @submit.prevent="$emit('submit')" class="space-y-4">
                    <!-- Source Selection -->
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-3">Water Source</label>
                        <div class="space-y-2">
                            <label class="flex items-center cursor-pointer" :class="{ 'opacity-50': !hasWaterBank }">
                                <input
                                    :checked="form.source === 'water_bank'"
                                    @change="$emit('update:form', { ...form, source: 'water_bank' })"
                                    type="radio"
                                    name="source"
                                    value="water_bank"
                                    :disabled="!hasWaterBank"
                                    class="text-cyan-500 focus:ring-cyan-500 cursor-pointer"
                                />
                                <span class="ml-3 text-sm text-stone-700">
                                    💧 Water Bank ({{ formatCurrency(waterBank.balance) }} available)
                                </span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input
                                    :checked="form.source === 'other'"
                                    @change="$emit('update:form', { ...form, source: 'other' })"
                                    type="radio"
                                    name="source"
                                    value="other"
                                    class="text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                                />
                                <span class="ml-3 text-sm text-stone-700">💰 Other Money (gifts, cash, etc.)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Amount to Add</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-stone-500">$</span>
                            <input
                                :value="form.amount"
                                @input="$emit('update:form', { ...form, amount: $event.target.value })"
                                type="number"
                                step="0.01"
                                min="0.01"
                                :max="form.source === 'water_bank' ? waterBank.balance : 9999999.99"
                                placeholder="100.00"
                                class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                        </div>
                        <div v-if="form.errors?.amount" class="text-red-600 text-sm mt-1">{{ form.errors.amount }}</div>
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
                            class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Adding...</span>
                            <span v-else>{{ form.source === 'water_bank' ? '💧' : '💰' }} Add Money</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
