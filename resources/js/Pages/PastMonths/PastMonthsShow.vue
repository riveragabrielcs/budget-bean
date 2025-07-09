<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
// Partials
import MonthDetailOverview from './Partials/MonthDetailOverview.vue';
import MonthGrowthComparison from './Partials/MonthGrowthComparison.vue';
import MonthExpensesBreakdown from './Partials/MonthExpensesBreakdown.vue';
import MonthSummarySection from './Partials/MonthSummarySection.vue';
import DeleteMonthModal from './Partials/DeleteMonthModal.vue';

const props = defineProps({
    completedMonth: Object,
});

// Modal states
const showDeleteModal = ref(false);

// Delete form
const deleteForm = useForm({});

// Computed properties
const recurringBills = computed(() => props.completedMonth.expenses_snapshot.recurring_bills || []);
const oneTimeExpenses = computed(() => props.completedMonth.expenses_snapshot.one_time_expenses || []);
const allExpenses = computed(() => [...recurringBills.value, ...oneTimeExpenses.value]);

// Methods
const openDeleteModal = () => {
    showDeleteModal.value = true;
};

const submitDelete = () => {
    deleteForm.delete(route('past-months.destroy', props.completedMonth.id), {
        onSuccess: () => {
            // Will redirect to past months index
        },
    });
};

const closeModals = () => {
    showDeleteModal.value = false;
};
</script>

<template>
    <Head :title="`${completedMonth.month_period} - Past Month Details`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <Link
                        :href="route('past-months.index')"
                        class="text-emerald-600 hover:text-emerald-800 mr-4"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </Link>
                    <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                        {{ completedMonth.month_period }}
                    </h2>
                    <span class="ml-3 text-2xl">📊</span>
                </div>
                <button
                    @click="openDeleteModal"
                    class="bg-red-100 hover:bg-red-200 text-red-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                >
                    <span class="mr-2">🗑️</span>
                    Delete Month
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Month Overview -->
                <MonthDetailOverview
                    :completed-month="completedMonth"
                    :all-expenses-count="allExpenses.length"
                />

                <!-- Growth Comparison (if available) -->
                <MonthGrowthComparison
                    v-if="completedMonth.growth_vs_previous"
                    :growth-data="completedMonth.growth_vs_previous"
                />

                <!-- Detailed Expenses Section -->
                <MonthExpensesBreakdown
                    :recurring-bills="recurringBills"
                    :one-time-expenses="oneTimeExpenses"
                    :recurring-bills-total="completedMonth.recurring_bills_total"
                    :one-time-expenses-total="completedMonth.one_time_expenses_total"
                />

                <!-- Overall Summary -->
                <MonthSummarySection
                    :completed-month="completedMonth"
                />

            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteMonthModal
            :show="showDeleteModal"
            :selected-month="completedMonth"
            :processing="deleteForm.processing"
            :expense-count="allExpenses.length"
            @close="closeModals"
            @submit="submitDelete"
        />
    </AuthenticatedLayout>
</template>
