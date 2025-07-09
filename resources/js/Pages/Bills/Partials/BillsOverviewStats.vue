<script setup>
const props = defineProps({
    stats: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <!-- Bills Overview Stats -->
    <div class="mb-8">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-emerald-100">
            <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                <div class="relative">
                    <h3 class="text-xl font-semibold text-emerald-800 mb-4">Your Monthly Bills Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-emerald-700">{{ stats.total_bills }}</div>
                            <div class="text-sm text-stone-600">Total Bills</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(stats.total_monthly_amount) }}</div>
                            <div class="text-sm text-stone-600">Monthly Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-stone-700">{{ formatCurrency(stats.average_bill_amount) }}</div>
                            <div class="text-sm text-stone-600">Average Bill</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
