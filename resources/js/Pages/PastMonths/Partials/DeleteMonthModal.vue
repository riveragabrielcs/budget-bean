<script setup>
const props = defineProps({
    show: Boolean,
    selectedMonth: Object,
    processing: Boolean,
    expenseCount: Number, // Optional - for show page which has detailed expense info
});

const emit = defineEmits(['close', 'submit']);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Delete Confirmation Modal -->
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-red-800 flex items-center">
                        <span class="mr-2">⚠️</span>
                        Delete Completed Month
                    </h3>
                    <button @click="$emit('close')" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div v-if="selectedMonth" class="mb-6">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg mb-4">
                        <h4 class="font-medium text-red-800 mb-2">{{ selectedMonth.month_period }}</h4>
                        <div class="text-sm text-red-700 space-y-1">
                            <div>Revenue: {{ formatCurrency(selectedMonth.total_revenue) }}</div>
                            <div>Expenses: {{ formatCurrency(selectedMonth.total_expenses) }}</div>
                            <div>Water Collected: {{ formatCurrency(selectedMonth.water_collected) }}</div>
                            <div v-if="expenseCount">{{ expenseCount }} expense records</div>
                        </div>
                    </div>
                    <p class="text-stone-600 text-sm">
                        Are you sure you want to delete this completed month? This action cannot be undone and you will lose all historical data for this period.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="$emit('submit')"
                        :disabled="processing"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                    >
                        <span v-if="processing">Deleting...</span>
                        <span v-else>🗑️ Delete Month</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
