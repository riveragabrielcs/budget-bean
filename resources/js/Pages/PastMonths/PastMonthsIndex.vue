<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
// Partials
import PastMonthsOverviewStats from './Partials/PastMonthsOverviewStats.vue';
import EmptyPastMonthsState from './Partials/EmptyPastMonthsState.vue';
import CompletedMonthsTimeline from './Partials/CompletedMonthsTimeline.vue';
import DeleteMonthModal from './Partials/DeleteMonthModal.vue';

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
                <PastMonthsOverviewStats
                    v-if="hasMonths"
                    :stats="stats"
                />

                <!-- Empty State -->
                <EmptyPastMonthsState
                    v-if="!hasMonths"
                />

                <!-- Completed Months Timeline -->
                <CompletedMonthsTimeline
                    v-if="hasMonths"
                    :completed-months="completedMonths"
                    @delete-month="openDeleteModal"
                />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteMonthModal
            :show="showDeleteModal"
            :selected-month="selectedMonth"
            :processing="deleteForm.processing"
            @close="closeModals"
            @submit="submitDelete"
        />
    </AuthenticatedLayout>
</template>
