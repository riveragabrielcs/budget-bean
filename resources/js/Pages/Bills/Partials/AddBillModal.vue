<script setup>
const props = defineProps({
    show: Boolean,
    form: Object,
});

const emit = defineEmits(['close', 'submit', 'update:form']);
</script>

<template>
    <!-- Add Bill Modal -->
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">➕</span>
                        Add New Bill
                    </h3>
                    <button @click="$emit('close')" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="$emit('submit')" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Bill Name *</label>
                        <input
                            :value="form.name"
                            @input="$emit('update:form', { ...form, name: $event.target.value })"
                            type="text"
                            placeholder="e.g., Electricity, Gym Membership, Student Loan"
                            class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            required
                        />
                        <div v-if="form.errors?.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Monthly Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-stone-500">$</span>
                            <input
                                :value="form.amount"
                                @input="$emit('update:form', { ...form, amount: $event.target.value })"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="150.00"
                                class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                        </div>
                        <div v-if="form.errors?.amount" class="text-red-600 text-sm mt-1">{{ form.errors.amount }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Due Date (Optional)</label>
                        <select
                            :value="form.bill_date"
                            @change="$emit('update:form', { ...form, bill_date: $event.target.value })"
                            class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">Select day of month...</option>
                            <option v-for="day in 31" :key="day" :value="day">{{ day }}{{ day === 1 ? 'st' : day === 2 ? 'nd' : day === 3 ? 'rd' : 'th' }} of each month</option>
                        </select>
                        <div v-if="form.errors?.bill_date" class="text-red-600 text-sm mt-1">{{ form.errors.bill_date }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Description (Optional)</label>
                        <textarea
                            :value="form.description"
                            @input="$emit('update:form', { ...form, description: $event.target.value })"
                            placeholder="Any notes about this bill..."
                            rows="3"
                            class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        ></textarea>
                        <div v-if="form.errors?.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
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
                            class="flex-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Adding...</span>
                            <span v-else>➕ Add Bill</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
