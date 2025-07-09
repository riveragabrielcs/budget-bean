<script setup>
const emit = defineEmits(['open-add-bill-modal']);
</script>

<template>
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="bg-white shadow-lg sm:rounded-xl border border-emerald-100 p-12">
            <div class="w-24 h-24 bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-emerald-200">
                <span class="text-4xl text-emerald-500">💳</span>
            </div>
            <h3 class="text-2xl font-semibold text-emerald-800 mb-4">Set Up Your Recurring Bills</h3>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto leading-relaxed mb-8">
                Add your monthly recurring bills like rent, utilities, subscriptions, and loans.
                This will help you automatically track your monthly expenses without having to enter them each time! 💰
            </p>
            <button
                @click="$emit('open-add-bill-modal')"
                class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center mx-auto shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
                <span class="mr-3 text-lg">➕</span>
                Add Your First Bill
            </button>
        </div>
    </div>
</template>
