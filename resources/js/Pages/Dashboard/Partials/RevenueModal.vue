<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    form: Object,
    hasRevenue: Boolean,
});

const emit = defineEmits(['close', 'submit', 'update:form']);

const calculatedTotal = computed(() => {
    if (props.form.calculation_method === 'paycheck' && props.form.paycheck_amount && props.form.paycheck_count) {
        return (parseFloat(props.form.paycheck_amount) * parseInt(props.form.paycheck_count)).toFixed(2);
    }
    return '0.00';
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Revenue Modal -->
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">📊</span>
                        {{ hasRevenue ? 'Update Monthly Revenue' : 'Set Monthly Revenue' }}
                    </h3>
                    <button @click="$emit('close')" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="$emit('submit')" class="space-y-4">
                    <!-- Calculation Method -->
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-3">How would you like to calculate your revenue?</label>
                        <div class="space-y-3">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    :checked="form.calculation_method === 'paycheck'"
                                    @change="$emit('update:form', { ...form, calculation_method: 'paycheck' })"
                                    type="radio"
                                    name="calculation_method"
                                    value="paycheck"
                                    class="text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                                />
                                <span class="ml-3 text-sm text-stone-700">💰 Paycheck Calculator</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input
                                    :checked="form.calculation_method === 'custom'"
                                    @change="$emit('update:form', { ...form, calculation_method: 'custom' })"
                                    type="radio"
                                    name="calculation_method"
                                    value="custom"
                                    class="text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                                />
                                <span class="ml-3 text-sm text-stone-700">✏️ Custom Amount</span>
                            </label>
                        </div>
                    </div>

                    <!-- Paycheck Method -->
                    <div v-if="form.calculation_method === 'paycheck'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Paycheck Amount *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-stone-500">$</span>
                                <input
                                    :value="form.paycheck_amount"
                                    @input="$emit('update:form', { ...form, paycheck_amount: $event.target.value })"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="2500.00"
                                    class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    required
                                />
                            </div>
                            <div v-if="form.errors?.paycheck_amount" class="text-red-600 text-sm mt-1">
                                {{ form.errors.paycheck_amount }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Number of Paychecks This Month *</label>
                            <input
                                :value="form.paycheck_count"
                                @input="$emit('update:form', { ...form, paycheck_count: $event.target.value })"
                                type="number"
                                min="1"
                                max="10"
                                placeholder="2"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                            <div v-if="form.errors?.paycheck_count" class="text-red-600 text-sm mt-1">
                                {{ form.errors.paycheck_count }}
                            </div>
                        </div>

                        <!-- Live Calculation Preview -->
                        <div v-if="form.paycheck_amount && form.paycheck_count"
                             class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-emerald-700">Total Revenue:</span>
                                <span class="text-lg font-bold text-emerald-800">{{ formatCurrency(calculatedTotal) }}</span>
                            </div>
                            <div class="text-xs text-emerald-600 mt-1">
                                {{ form.paycheck_count }} paychecks × {{ formatCurrency(form.paycheck_amount) }}
                            </div>
                        </div>
                    </div>

                    <!-- Custom Method -->
                    <div v-if="form.calculation_method === 'custom'">
                        <label class="block text-sm font-medium text-stone-700 mb-2">Total Monthly Revenue *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-stone-500">$</span>
                            <input
                                :value="form.total_revenue"
                                @input="$emit('update:form', { ...form, total_revenue: $event.target.value })"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="5000.00"
                                class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                        </div>
                        <div v-if="form.errors?.total_revenue" class="text-red-600 text-sm mt-1">
                            {{ form.errors.total_revenue }}
                        </div>
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
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>📊 {{ hasRevenue ? 'Update' : 'Set' }} Revenue</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
