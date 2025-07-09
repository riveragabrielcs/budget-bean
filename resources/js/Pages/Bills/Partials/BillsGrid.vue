<script setup>
const props = defineProps({
    recurringBills: Array,
});

const emit = defineEmits(['edit-bill']);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Bills List -->
    <div>
        <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
            <span class="mr-2">📋</span>
            Your Recurring Bills ({{ recurringBills.length }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="bill in recurringBills"
                :key="bill.id"
                class="bg-white border border-emerald-100 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden"
            >
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">💳</span>
                            <div>
                                <h4 class="font-semibold text-emerald-800 text-lg">{{ bill.name }}</h4>
                                <p v-if="bill.next_due_date" class="text-sm text-emerald-600">Next due: {{ bill.next_due_date }}</p>
                                <p v-else-if="bill.formatted_bill_date" class="text-sm text-emerald-600">Due: {{ bill.formatted_bill_date }} of each month</p>
                            </div>
                        </div>
                        <button
                            @click="$emit('edit-bill', bill)"
                            class="text-stone-400 hover:text-emerald-600 transition-colors p-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                    </div>

                    <div v-if="bill.description" class="mb-4">
                        <p class="text-sm text-stone-600">{{ bill.description }}</p>
                    </div>

                    <div class="mb-4">
                        <div class="text-center p-4 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg border border-amber-100">
                            <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(bill.amount) }}</div>
                            <div class="text-sm text-stone-600">per month</div>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs text-stone-500">
                        <div class="flex justify-between">
                            <span>Added:</span>
                            <span>{{ bill.created_at }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
