<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    savingsGoals: Array,
    stats: Object,
});

// Modal states
const showAddGoalModal = ref(false);
const showAddSavingsModal = ref(false);
const showEditGoalModal = ref(false);
const selectedGoal = ref(null);

// Forms
const addGoalForm = useForm({
    name: '',
    description: '',
    target_amount: '',
});

const addSavingsForm = useForm({
    amount: '',
});

const editGoalForm = useForm({
    name: '',
    description: '',
    target_amount: '',
});

const withdrawForm = useForm({
    amount: '',
});

// Computed properties
const activeGoals = computed(() => props.savingsGoals.filter(goal => !goal.is_completed));
const completedGoals = computed(() => props.savingsGoals.filter(goal => goal.is_completed));
const hasGoals = computed(() => props.savingsGoals.length > 0);

// Methods
const openAddGoalModal = () => {
    addGoalForm.reset();
    showAddGoalModal.value = true;
};

const openAddSavingsModal = (goal) => {
    selectedGoal.value = goal;
    addSavingsForm.reset();
    showAddSavingsModal.value = true;
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

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const closeModals = () => {
    showAddGoalModal.value = false;
    showAddSavingsModal.value = false;
    showEditGoalModal.value = false;
    selectedGoal.value = null;
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
                <div v-if="hasGoals" class="mb-8">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-emerald-100">
                        <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                            <div class="relative">
                                <h3 class="text-xl font-semibold text-emerald-800 mb-4">Your Garden Overview</h3>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-emerald-700">{{ stats.total_goals }}</div>
                                        <div class="text-sm text-stone-600">Total Plants</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-700">{{ stats.active_goals }}</div>
                                        <div class="text-sm text-stone-600">Growing</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-amber-700">{{ stats.completed_goals }}</div>
                                        <div class="text-sm text-stone-600">Fully Grown</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-emerald-800">{{ formatCurrency(stats.total_saved) }}</div>
                                        <div class="text-sm text-stone-600">Total Saved</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!hasGoals" class="text-center py-16">
                    <div class="bg-white shadow-lg sm:rounded-xl border border-emerald-100 p-12">
                        <div class="w-24 h-24 bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-emerald-200">
                            <span class="text-4xl text-emerald-500">🌱</span>
                        </div>
                        <h3 class="text-2xl font-semibold text-emerald-800 mb-4">Welcome to Your Garden!</h3>
                        <p class="text-lg text-stone-600 max-w-2xl mx-auto leading-relaxed mb-8">
                            Plant your first savings goal and watch it grow! Whether it's a dream vacation, a new car,
                            or an emergency fund, every financial goal starts with a single seed. 🌱
                        </p>
                        <button
                            @click="openAddGoalModal"
                            class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center mx-auto shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                        >
                            <span class="mr-3 text-lg">🌱</span>
                            Plant Your First Goal
                        </button>
                    </div>
                </div>

                <!-- Active Goals -->
                <div v-if="activeGoals.length > 0" class="mb-8">
                    <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
                        <span class="mr-2">🌱</span>
                        Growing Goals ({{ activeGoals.length }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="goal in activeGoals"
                            :key="goal.id"
                            class="bg-white border border-emerald-100 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden"
                        >
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">{{ goal.plant_emoji }}</span>
                                        <div>
                                            <h4 class="font-semibold text-emerald-800 text-lg">{{ goal.name }}</h4>
                                            <p class="text-sm text-emerald-600">{{ goal.growth_stage }}</p>
                                        </div>
                                    </div>
                                    <button
                                        @click="openEditGoalModal(goal)"
                                        class="text-stone-400 hover:text-emerald-600 transition-colors p-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div v-if="goal.description" class="mb-4">
                                    <p class="text-sm text-stone-600">{{ goal.description }}</p>
                                </div>

                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-stone-600">Progress</span>
                                        <span class="text-sm font-medium text-emerald-700">{{ Math.round(goal.progress_percentage) }}%</span>
                                    </div>
                                    <div class="w-full bg-stone-200 rounded-full h-3">
                                        <div
                                            class="bg-gradient-to-r from-emerald-400 to-green-500 h-3 rounded-full transition-all duration-300"
                                            :style="{ width: `${Math.min(goal.progress_percentage, 100)}%` }"
                                        ></div>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-stone-500">Saved:</span>
                                        <span class="text-sm font-medium text-emerald-700">{{ formatCurrency(goal.current_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-stone-500">Target:</span>
                                        <span class="text-sm font-medium text-stone-700">{{ formatCurrency(goal.target_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-stone-500">Remaining:</span>
                                        <span class="text-sm font-medium text-amber-600">{{ formatCurrency(goal.remaining_amount) }}</span>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        @click="openAddSavingsModal(goal)"
                                        class="flex-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium py-2 px-3 rounded-lg transition duration-200 text-sm flex items-center justify-center"
                                    >
                                        <span class="mr-1">💰</span>
                                        Add Money
                                    </button>
                                    <button
                                        v-if="goal.is_reached"
                                        @click="completeGoal(goal)"
                                        class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 font-medium py-2 px-3 rounded-lg transition duration-200 text-sm flex items-center justify-center"
                                    >
                                        <span class="mr-1">🎉</span>
                                        Complete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Goals -->
                <div v-if="completedGoals.length > 0">
                    <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
                        <span class="mr-2">🌳</span>
                        Completed Goals ({{ completedGoals.length }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="goal in completedGoals"
                            :key="goal.id"
                            class="bg-white border border-green-200 rounded-xl shadow-md overflow-hidden opacity-90"
                        >
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">🌳</span>
                                        <div>
                                            <h4 class="font-semibold text-green-800 text-lg">{{ goal.name }}</h4>
                                            <p class="text-sm text-green-600">Completed {{ goal.completed_at }}</p>
                                        </div>
                                    </div>
                                    <button
                                        @click="deleteGoal(goal)"
                                        class="text-stone-400 hover:text-red-500 transition-colors p-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="mb-4">
                                    <div class="w-full bg-green-200 rounded-full h-3">
                                        <div class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full w-full"></div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <span class="text-lg font-bold text-green-700">{{ formatCurrency(goal.target_amount) }}</span>
                                    <p class="text-sm text-stone-600">Goal Achieved! 🎉</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Goal Modal -->
        <div v-if="showAddGoalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                            <span class="mr-2">🌱</span>
                            Plant a New Goal
                        </h3>
                        <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitAddGoal" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Goal Name</label>
                            <input
                                v-model="addGoalForm.name"
                                type="text"
                                placeholder="e.g., Dream Vacation, Emergency Fund, New Car"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                            <div v-if="addGoalForm.errors.name" class="text-red-600 text-sm mt-1">{{ addGoalForm.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Target Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-stone-500">$</span>
                                <input
                                    v-model="addGoalForm.target_amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="5000.00"
                                    class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    required
                                />
                            </div>
                            <div v-if="addGoalForm.errors.target_amount" class="text-red-600 text-sm mt-1">{{ addGoalForm.errors.target_amount }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Description (Optional)</label>
                            <textarea
                                v-model="addGoalForm.description"
                                placeholder="Tell us more about this goal..."
                                rows="3"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                            <div v-if="addGoalForm.errors.description" class="text-red-600 text-sm mt-1">{{ addGoalForm.errors.description }}</div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="closeModals"
                                class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="addGoalForm.processing"
                                class="flex-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                            >
                                <span v-if="addGoalForm.processing">Planting...</span>
                                <span v-else>🌱 Plant Goal</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Savings Modal -->
        <div v-if="showAddSavingsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                            <span class="mr-2">💰</span>
                            Water Your Plant
                        </h3>
                        <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div v-if="selectedGoal" class="mb-6 p-4 bg-emerald-50 rounded-lg">
                        <div class="flex items-center mb-2">
                            <span class="text-lg mr-2">{{ selectedGoal.plant_emoji }}</span>
                            <span class="font-medium text-emerald-800">{{ selectedGoal.name }}</span>
                        </div>
                        <div class="text-sm text-stone-600">
                            Current: {{ formatCurrency(selectedGoal.current_amount) }} / {{ formatCurrency(selectedGoal.target_amount) }}
                        </div>
                    </div>

                    <form @submit.prevent="submitAddSavings" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Amount to Add</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-stone-500">$</span>
                                <input
                                    v-model="addSavingsForm.amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="100.00"
                                    class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    required
                                />
                            </div>
                            <div v-if="addSavingsForm.errors.amount" class="text-red-600 text-sm mt-1">{{ addSavingsForm.errors.amount }}</div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="closeModals"
                                class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="addSavingsForm.processing"
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                            >
                                <span v-if="addSavingsForm.processing">Adding...</span>
                                <span v-else>💰 Add Money</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Goal Modal -->
        <div v-if="showEditGoalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                            <span class="mr-2">✏️</span>
                            Edit Goal
                        </h3>
                        <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitEditGoal" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Goal Name</label>
                            <input
                                v-model="editGoalForm.name"
                                type="text"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                            <div v-if="editGoalForm.errors.name" class="text-red-600 text-sm mt-1">{{ editGoalForm.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Target Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-stone-500">$</span>
                                <input
                                    v-model="editGoalForm.target_amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    required
                                />
                            </div>
                            <div v-if="editGoalForm.errors.target_amount" class="text-red-600 text-sm mt-1">{{ editGoalForm.errors.target_amount }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Description</label>
                            <textarea
                                v-model="editGoalForm.description"
                                rows="3"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                            <div v-if="editGoalForm.errors.description" class="text-red-600 text-sm mt-1">{{ editGoalForm.errors.description }}</div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="deleteGoal(selectedGoal)"
                                class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                            >
                                Delete
                            </button>
                            <button
                                type="button"
                                @click="closeModals"
                                class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="editGoalForm.processing"
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                            >
                                <span v-if="editGoalForm.processing">Saving...</span>
                                <span v-else>Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
