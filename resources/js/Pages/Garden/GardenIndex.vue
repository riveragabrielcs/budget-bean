<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
// Partials
import GardenOverviewStats from './Partials/GardenOverviewStats.vue';
import WaterBankSection from './Partials/WaterBankSection.vue';
import EmptyGardenState from './Partials/EmptyGardenState.vue';
import ActiveGoalsGrid from './Partials/ActiveGoalsGrid.vue';
import CompletedGoalsGrid from './Partials/CompletedGoalsGrid.vue';
import AddGoalModal from './Partials/AddGoalModal.vue';
import AddSavingsModal from './Partials/AddSavingsModal.vue';
import WaterAllModal from './Partials/WaterAllModal.vue';
import EditGoalModal from './Partials/EditGoalModal.vue';

const props = defineProps({
    savingsGoals: Array,
    stats: Object,
    waterBank: Object,
});

// Modal states
const showAddGoalModal = ref(false);
const showAddSavingsModal = ref(false);
const showEditGoalModal = ref(false);
const showWaterAllModal = ref(false);
const selectedGoal = ref(null);

// Forms
const addGoalForm = useForm({
    name: '',
    description: '',
    target_amount: '',
});

const addSavingsForm = useForm({
    amount: '',
    source: 'water_bank',
});

const editGoalForm = useForm({
    name: '',
    description: '',
    target_amount: '',
});

const waterAllForm = useForm({
    total_amount: '',
    source: 'water_bank',
});

// Computed properties
const activeGoals = computed(() => props.savingsGoals.filter(goal => !goal.is_completed));
const completedGoals = computed(() => props.savingsGoals.filter(goal => goal.is_completed));
const hasGoals = computed(() => props.savingsGoals.length > 0);
const hasActiveGoals = computed(() => activeGoals.value.length > 0);
const hasWaterBank = computed(() => props.waterBank.balance > 0);

// Methods
const openAddGoalModal = () => {
    addGoalForm.reset();
    showAddGoalModal.value = true;
};

const openAddSavingsModal = (goal) => {
    selectedGoal.value = goal;
    addSavingsForm.reset();
    addSavingsForm.source = hasWaterBank.value ? 'water_bank' : 'other';
    showAddSavingsModal.value = true;
};

const openWaterAllModal = () => {
    waterAllForm.reset();
    waterAllForm.source = hasWaterBank.value ? 'water_bank' : 'other';
    showWaterAllModal.value = true;
};

const openEditGoalModal = (goal) => {
    selectedGoal.value = goal;
    editGoalForm.name = goal.name;
    editGoalForm.description = goal.description || '';
    editGoalForm.target_amount = goal.target_amount;
    showEditGoalModal.value = true;
};

const submitAddGoal = () => {
    addGoalForm.post(route('garden.store'), {
        onSuccess: () => {
            showAddGoalModal.value = false;
            addGoalForm.reset();
        },
    });
};

const submitAddSavings = () => {
    addSavingsForm.post(route('garden.add-savings', selectedGoal.value.id), {
        onSuccess: () => {
            showAddSavingsModal.value = false;
            addSavingsForm.reset();
            selectedGoal.value = null;
        },
    });
};

const submitWaterAll = () => {
    waterAllForm.post(route('water-bank.water-all'), {
        onSuccess: () => {
            showWaterAllModal.value = false;
            waterAllForm.reset();
        },
    });
};

const submitEditGoal = () => {
    editGoalForm.patch(route('garden.update', selectedGoal.value.id), {
        onSuccess: () => {
            showEditGoalModal.value = false;
            editGoalForm.reset();
            selectedGoal.value = null;
        },
    });
};

const deleteGoal = (goal) => {
    if (confirm(`Are you sure you want to remove "${goal.name}" from your garden? This action cannot be undone.`)) {
        useForm().delete(route('garden.destroy', goal.id));
    }
};

const completeGoal = (goal) => {
    if (confirm(`Mark "${goal.name}" as completed? This will set the current amount to the target amount.`)) {
        useForm().post(route('garden.complete', goal.id));
    }
};

const closeModals = () => {
    showAddGoalModal.value = false;
    showAddSavingsModal.value = false;
    showEditGoalModal.value = false;
    showWaterAllModal.value = false;
    selectedGoal.value = null;
};

// Form update handlers for modals
const updateAddGoalForm = (newForm) => {
    Object.assign(addGoalForm, newForm);
};

const updateAddSavingsForm = (newForm) => {
    Object.assign(addSavingsForm, newForm);
};

const updateEditGoalForm = (newForm) => {
    Object.assign(editGoalForm, newForm);
};

const updateWaterAllForm = (newForm) => {
    Object.assign(waterAllForm, newForm);
};

const handleDeleteGoalFromModal = () => {
    deleteGoal(selectedGoal.value);
    closeModals();
};
</script>

<template>
    <Head title="My Garden" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                        My Garden
                    </h2>
                    <span class="ml-3 text-2xl">🌻</span>
                </div>
                <button
                    v-if="hasGoals"
                    @click="openAddGoalModal"
                    class="mt-4 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                >
                    <span class="mr-2">🌱</span>
                    Plant New Goal
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Garden Overview Stats (if has goals) -->
                <GardenOverviewStats
                    v-if="hasGoals"
                    :stats="stats"
                />

                <!-- Water Bank Section -->
                <WaterBankSection
                    v-if="hasGoals"
                    :water-bank="waterBank"
                    :has-active-goals="hasActiveGoals"
                    @open-water-all-modal="openWaterAllModal"
                />

                <!-- Empty State -->
                <EmptyGardenState
                    v-if="!hasGoals"
                    @open-add-goal-modal="openAddGoalModal"
                />

                <!-- Active Goals -->
                <ActiveGoalsGrid
                    v-if="activeGoals.length > 0"
                    :active-goals="activeGoals"
                    @edit-goal="openEditGoalModal"
                    @water-goal="openAddSavingsModal"
                    @complete-goal="completeGoal"
                />

                <!-- Completed Goals -->
                <CompletedGoalsGrid
                    v-if="completedGoals.length > 0"
                    :completed-goals="completedGoals"
                    @delete-goal="deleteGoal"
                />
            </div>
        </div>

        <!-- Modal Components -->
        <AddGoalModal
            :show="showAddGoalModal"
            :form="addGoalForm"
            @close="closeModals"
            @submit="submitAddGoal"
            @update:form="updateAddGoalForm"
        />

        <AddSavingsModal
            :show="showAddSavingsModal"
            :form="addSavingsForm"
            :selected-goal="selectedGoal"
            :water-bank="waterBank"
            @close="closeModals"
            @submit="submitAddSavings"
            @update:form="updateAddSavingsForm"
        />

        <WaterAllModal
            :show="showWaterAllModal"
            :form="waterAllForm"
            :active-goals="activeGoals"
            :water-bank="waterBank"
            @close="closeModals"
            @submit="submitWaterAll"
            @update:form="updateWaterAllForm"
        />

        <EditGoalModal
            :show="showEditGoalModal"
            :form="editGoalForm"
            @close="closeModals"
            @submit="submitEditGoal"
            @update:form="updateEditGoalForm"
            @delete-goal="handleDeleteGoalFromModal"
        />
    </AuthenticatedLayout>
</template>
