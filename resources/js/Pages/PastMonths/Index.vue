<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    completedMonths: Array,
    stats: Object,
});

// Modal states
const showDeleteModal = ref(false);
const selectedMonth = ref(null);

// Delete form
const deleteForm = useForm({});

// Computed properties
const hasMonths = computed(() => props.completedMonths.length > 0);

// Methods
const openDeleteModal = (month) => {
    selectedMonth.value = month;
    showDeleteModal.value = true;
};

const submitDelete = () => {
    deleteForm.delete(route('past-months.destroy', selectedMonth.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedMonth.value = null;
        },
    });
};

const closeModals = () => {
    showDeleteModal.value = false;
    selectedMonth.value = null;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const getGrowthIcon = (change) => {
    if (change > 0) return '📈';
    if (change < 0) return '📉';
    return '➡️';
};

const getGrowthColor = (change) => {
    if (change > 0) return 'text-green-600';
    if (change < 0) return 'text-red-600';
    return 'text-stone-600';
};

const getMonthEmoji = (monthNumber) => {
    const emojis = ['❄️', '💙', '🌸', '🌷', '🌻', '☀️', '🏖️', '🌽', '🍂', '🎃', '🦃', '🎄'];
    return emojis[monthNumber - 1];
};
</script>

<template>
    <Head title="Past Months" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                        Past Months
                    </h2>
                    <span class="ml-3 text-2xl">📅</span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Overall Stats (if has months) -->
                <div v-if="hasMonths" class="mb-8">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-emerald-100">
                        <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                            <div class="relative">
                                <h3 class="text-xl font-semibold text-emerald-800 mb-4">Your Financial Journey</h3>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-emerald-700">{{ stats.total_months }}</div>
                                        <div class="text-sm text-stone-600">Months Completed</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-700">{{ formatCurrency(stats.total_water_collected) }}</div>
                                        <div class="text-sm text-stone-600">Total Water Collected</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(stats.average_monthly_revenue) }}</div>
                                        <div class="text-sm text-stone-600">Average Revenue</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-stone-700">{{ stats.drought_months }}</div>
                                        <div class="text-sm text-stone-600">Drought Months</div>
                                    </div>
                                </div>

                                <!-- Best Month Highlight -->
                                <div v-if="stats.best_month" class="mt-6 p-4 bg-gradient-to-r from-green-100 to-emerald-100 rounded-lg border border-green-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="text-2xl mr-3">🏆</span>
                                            <div>
                                                <h4 class="font-semibold text-green-800">Best Month</h4>
                                                <p class="text-sm text-green-700">{{ stats.best_month.month_period }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xl font-bold text-green-800">{{ formatCurrency(stats.best_month.water_collected) }}</div>
                                            <div class="text-sm text-green-600">Water Collected</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!hasMonths" class="text-center py-16">
                    <div class="bg-white shadow-lg sm:rounded-xl border border-emerald-100 p-12">
                        <div class="w-24 h-24 bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-emerald-200">
                            <span class="text-4xl text-emerald-500">📅</span>
                        </div>
                        <h3 class="text-2xl font-semibold text-emerald-800 mb-4">No Completed Months Yet</h3>
                        <p class="text-lg text-stone-600 max-w-2xl mx-auto leading-relaxed mb-8">
                            Start your financial journey by completing your first month! When you're ready to end a month
                            and collect your water, use the "End Month & Collect Water" button on your dashboard. 🌱
                        </p>
                        <Link
                            :href="route('dashboard')"
                            class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center mx-auto shadow-md hover:shadow-lg transform hover:-translate-y-0.5 w-fit"
                        >
                            <span class="mr-3 text-lg">🏠</span>
                            Go to Dashboard
                        </Link>
                    </div>
                </div>

                <!-- Completed Months Timeline -->
                <div v-if="hasMonths">
                    <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
                        <span class="mr-2">📊</span>
                        Your Financial History ({{ completedMonths.length }} months)
                    </h3>

                    <div class="space-y-6">
                        <div
                            v-for="month in completedMonths"
                            :key="month.id"
                            class="bg-white border border-emerald-100 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 overflow-hidden"
                        >
                            <div class="p-6">
                                <!-- Month Header -->
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center mr-4 shadow-sm">
                                            <span class="text-2xl">{{ getMonthEmoji(month.month) }}</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xl font-semibold text-emerald-800">{{ month.month_period }}</h4>
                                            <p class="text-sm text-stone-500">Completed {{ month.completed_at_short }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="route('past-months.show', month.id)"
                                            class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center text-sm"
                                        >
                                            <span class="mr-2">👁️</span>
                                            View Details
                                        </Link>
                                        <button
                                            @click="openDeleteModal(month)"
                                            class="bg-red-100 hover:bg-red-200 text-red-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center text-sm"
                                        >
                                            <span class="mr-2">🗑️</span>
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <!-- Financial Summary Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                    <!-- Revenue -->
                                    <div class="text-center p-4 bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg border border-emerald-100">
                                        <div class="text-2xl font-bold text-emerald-700">{{ formatCurrency(month.total_revenue) }}</div>
                                        <div class="text-sm text-emerald-600">Revenue</div>
                                        <div v-if="month.source_description" class="text-xs text-emerald-500 mt-1">
                                            {{ month.source_description }}
                                        </div>
                                    </div>

                                    <!-- Expenses -->
                                    <div class="text-center p-4 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg border border-amber-100">
                                        <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(month.total_expenses) }}</div>
                                        <div class="text-sm text-amber-600">Total Expenses</div>
                                        <div class="text-xs text-amber-500 mt-1">
                                            {{ formatCurrency(month.recurring_bills_total) }} bills + {{ formatCurrency(month.one_time_expenses_total) }} one-time
                                        </div>
                                    </div>

                                    <!-- Savings Goal -->
                                    <div class="text-center p-4 bg-gradient-to-br from-green-50 to-teal-50 rounded-lg border border-green-100">
                                        <div class="text-2xl font-bold text-green-700">{{ formatCurrency(month.monthly_savings_goal) }}</div>
                                        <div class="text-sm text-green-600">Savings Goal</div>
                                        <div class="text-xs text-green-500 mt-1">
                                            {{ Math.round(month.savings_rate) }}% of revenue
                                        </div>
                                    </div>

                                    <!-- Water Collected -->
                                    <div class="text-center p-4 rounded-lg border"
                                         :class="{
                                             'bg-gradient-to-br from-cyan-50 to-blue-50 border-cyan-100': !month.was_drought,
                                             'bg-gradient-to-br from-red-50 to-orange-50 border-red-100': month.was_drought
                                         }">
                                        <div class="text-2xl font-bold"
                                             :class="{
                                                 'text-cyan-700': !month.was_drought,
                                                 'text-red-700': month.was_drought
                                             }">
                                            {{ formatCurrency(month.water_collected) }}
                                        </div>
                                        <div class="text-sm"
                                             :class="{
                                                 'text-cyan-600': !month.was_drought,
                                                 'text-red-600': month.was_drought
                                             }">
                                            {{ month.was_drought ? 'Drought' : 'Water Collected' }}
                                        </div>
                                        <div class="text-xs mt-1"
                                             :class="{
                                                 'text-cyan-500': !month.was_drought,
                                                 'text-red-500': month.was_drought
                                             }">
                                            {{ month.was_drought ? 'Overspent 🏜️' : 'Added to bank 💧' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Growth Comparison (if available) -->
                                <div v-if="month.growth_vs_previous" class="p-4 bg-stone-50 rounded-lg border border-stone-200">
                                    <h5 class="font-medium text-stone-700 mb-3 flex items-center">
                                        <span class="mr-2">📊</span>
                                        vs {{ month.growth_vs_previous.previous_month }}
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Revenue Change -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-stone-600">Revenue:</span>
                                            <div class="flex items-center">
                                                <span :class="getGrowthColor(month.growth_vs_previous.revenue_change)" class="text-sm font-medium">
                                                    {{ getGrowthIcon(month.growth_vs_previous.revenue_change) }}
                                                    {{ formatCurrency(Math.abs(month.growth_vs_previous.revenue_change)) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Expense Change -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-stone-600">Expenses:</span>
                                            <div class="flex items-center">
                                                <span :class="getGrowthColor(-month.growth_vs_previous.expense_change)" class="text-sm font-medium">
                                                    {{ getGrowthIcon(month.growth_vs_previous.expense_change) }}
                                                    {{ formatCurrency(Math.abs(month.growth_vs_previous.expense_change)) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Water Change -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-stone-600">Water:</span>
                                            <div class="flex items-center">
                                                <span :class="getGrowthColor(month.growth_vs_previous.water_change)" class="text-sm font-medium">
                                                    {{ getGrowthIcon(month.growth_vs_previous.water_change) }}
                                                    {{ formatCurrency(Math.abs(month.growth_vs_previous.water_change)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-red-800 flex items-center">
                            <span class="mr-2">⚠️</span>
                            Delete Completed Month
                        </h3>
                        <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
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
                            </div>
                        </div>
                        <p class="text-stone-600 text-sm">
                            Are you sure you want to delete this completed month? This action cannot be undone and you will lose all historical data for this period.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="closeModals"
                            class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitDelete"
                            :disabled="deleteForm.processing"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="deleteForm.processing">Deleting...</span>
                            <span v-else>🗑️ Delete Month</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
