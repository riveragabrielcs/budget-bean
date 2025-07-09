<script setup>
const props = defineProps({
    show: Boolean,
    form: Object,
    monthExists: Boolean,
    showOverrideConfirmation: Boolean,
    overrideText: String,
});

const emit = defineEmits(['close', 'submit', 'update:form', 'show-override-confirmation', 'update:override-text']);

const getMonthName = (monthNumber) => {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return months[monthNumber - 1];
};
</script>

<template>
    <!-- End Month Modal -->
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">🏁</span>
                        End Month & Collect Water
                    </h3>
                    <button @click="$emit('close')" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-6 p-4 bg-cyan-50 border border-cyan-200 rounded-lg">
                    <h4 class="font-medium text-cyan-800 mb-2">What happens when you end a month?</h4>
                    <ul class="text-sm text-cyan-700 space-y-1">
                        <li>• Your unspent money goes to the Water Bank 💧</li>
                        <li>• One-time expenses are cleared 🗑️</li>
                        <li>• Recurring bills and goals stay for next month ✅</li>
                        <li>• Fresh start for tracking new expenses 🌱</li>
                    </ul>
                </div>

                <form @submit.prevent="$emit('submit')" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Month *</label>
                            <select
                                :value="form.month"
                                @change="$emit('update:form', { ...form, month: $event.target.value })"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            >
                                <option value="">Select month...</option>
                                <option v-for="month in 12" :key="month" :value="month">
                                    {{ getMonthName(month) }}
                                </option>
                            </select>
                            <div v-if="form.errors?.month" class="text-red-600 text-sm mt-1">
                                {{ form.errors.month }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Year *</label>
                            <select
                                :value="form.year"
                                @change="$emit('update:form', { ...form, year: $event.target.value })"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            >
                                <option value="">Select year...</option>
                                <option v-for="year in [2023, 2024, 2025, 2026]" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                            <div v-if="form.errors?.year" class="text-red-600 text-sm mt-1">
                                {{ form.errors.year }}
                            </div>
                        </div>
                    </div>

                    <!-- Month Already Exists Warning -->
                    <div v-if="monthExists && !showOverrideConfirmation"
                         class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <span class="text-amber-600 text-xl mr-3">⚠️</span>
                            <div>
                                <h4 class="font-medium text-amber-800">Month Already Completed</h4>
                                <p class="text-sm text-amber-700">
                                    {{ getMonthName(form.month) }} {{ form.year }} has already been recorded.
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="$emit('show-override-confirmation')"
                            class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium px-4 py-2 rounded-lg transition duration-200"
                        >
                            Override Previous Record
                        </button>
                    </div>

                    <!-- Override Confirmation -->
                    <div v-if="showOverrideConfirmation" class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-medium text-red-800 mb-3">⚠️ Override Confirmation Required</h4>
                        <p class="text-sm text-red-700 mb-4">
                            You will lose all previous data for {{ getMonthName(form.month) }} {{ form.year }}.
                            Type <strong>OVERRIDE</strong> to confirm:
                        </p>
                        <input
                            :value="overrideText"
                            @input="$emit('update:override-text', $event.target.value)"
                            type="text"
                            placeholder="Type OVERRIDE to confirm"
                            class="w-full px-4 py-3 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        />
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
                            :disabled="form.processing || (showOverrideConfirmation && overrideText !== 'OVERRIDE')"
                            class="flex-1 bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Processing...</span>
                            <span v-else-if="monthExists && !showOverrideConfirmation">Month Already Exists</span>
                            <span v-else-if="showOverrideConfirmation && overrideText !== 'OVERRIDE'">Type OVERRIDE to confirm</span>
                            <span v-else>🏁 End Month & Collect Water</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
