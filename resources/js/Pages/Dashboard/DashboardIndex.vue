<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, useForm, router, usePage} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import { useAuthGuard } from '@/Composables/useAuthGuard'
import AuthRequiredModal from '@/Components/AuthRequiredModal.vue'
// Partials
import WelcomeSection from './Partials/WelcomeSection.vue';
import StatsGrid from './Partials/StatsGrid.vue';
import ExpensesSection from './Partials/ExpensesSection.vue';
import GardenSection from './Partials/GardenSection.vue';
import AddExpenseModal from './Partials/AddExpenseModal.vue';
import RevenueModal from './Partials/RevenueModal.vue';
import SavingsGoalModal from './Partials/SavingsGoalModal.vue';
import EndMonthModal from './Partials/EndMonthModal.vue';

const { requireAuth, showAuthModal, closeAuthModal } = useAuthGuard()

const props = defineProps({
    savingsGoals: Array,
    monthlyExpenses: Array,
    expenseStats: Object,
    currentRevenue: Object,
    budgetData: Object,
});

// Modal states
const showAddExpenseModal = ref(false);
const showRevenueModal = ref(false);
const showSavingsGoalModal = ref(false);
const showEndMonthModal = ref(false);

// Form for adding one-time expenses
const addExpenseForm = useForm({
    name: '',
    amount: '',
    description: '',
    expense_date: '',
});

// Form for revenue
const revenueForm = useForm({
    calculation_method: 'custom',
    total_revenue: '',
    paycheck_amount: '',
    paycheck_count: '',
});

// Form for savings goal
const savingsGoalForm = useForm({
    monthly_savings_goal: '',
});

// Form for ending month
const endMonthForm = useForm({
    month: '',
    year: '',
    override_existing: false,
});

// Computed properties
const hasGoals = computed(() => props.savingsGoals.length > 0);
const hasRevenue = computed(() => props.currentRevenue !== null);
const hasSavingsGoal = computed(() => props.currentRevenue?.monthly_savings_goal > 0);

const calculatedTotal = computed(() => {
    if (revenueForm.calculation_method === 'paycheck' && revenueForm.paycheck_amount && revenueForm.paycheck_count) {
        return (parseFloat(revenueForm.paycheck_amount) * parseInt(revenueForm.paycheck_count)).toFixed(2);
    }
    return '0.00';
});

// End month modal state
const monthExists = ref(false);
const showOverrideConfirmation = ref(false);
const overrideText = ref('');

// Watch for calculation method changes
watch(() => revenueForm.calculation_method, (newMethod) => {
    if (newMethod === 'paycheck') {
        revenueForm.total_revenue = calculatedTotal.value;
    }
});

// Watch for paycheck calculations
watch([() => revenueForm.paycheck_amount, () => revenueForm.paycheck_count], () => {
    if (revenueForm.calculation_method === 'paycheck') {
        revenueForm.total_revenue = calculatedTotal.value;
    }
});

// Watch for month/year changes in end month form
watch([() => endMonthForm.month, () => endMonthForm.year], () => {
    checkIfMonthExists();
});

// Methods
const openAddExpenseModal = () => {
    addExpenseForm.reset();
    addExpenseForm.expense_date = new Date().toISOString().split('T')[0]; // Set to today
    showAddExpenseModal.value = true;
};

const openRevenueModal = () => {
    revenueForm.reset();

    // Pre-fill with existing revenue data if available
    if (props.currentRevenue) {
        revenueForm.calculation_method = props.currentRevenue.calculation_method;
        revenueForm.total_revenue = props.currentRevenue.total_revenue;
        revenueForm.paycheck_amount = props.currentRevenue.paycheck_amount || '';
        revenueForm.paycheck_count = props.currentRevenue.paycheck_count || '';
    } else {
        revenueForm.calculation_method = 'custom';
    }

    showRevenueModal.value = true;
};

const openSavingsGoalModal = () => {
    savingsGoalForm.reset();

    // Pre-fill with existing savings goal if available
    if (props.currentRevenue?.monthly_savings_goal) {
        savingsGoalForm.monthly_savings_goal = props.currentRevenue.monthly_savings_goal;
    }

    showSavingsGoalModal.value = true;
};

const openEndMonthModal = () => {
    endMonthForm.reset();

    // Pre-fill with current month/year
    const now = new Date();
    endMonthForm.month = now.getMonth() + 1; // JavaScript months are 0-indexed
    endMonthForm.year = now.getFullYear();

    monthExists.value = false;
    showOverrideConfirmation.value = false;
    overrideText.value = '';

    showEndMonthModal.value = true;

    // Check if current month already exists
    checkIfMonthExists();
};

const checkIfMonthExists = async () => {
    if (!endMonthForm.month || !endMonthForm.year) return;

    try {
        // Use a GET request instead of POST to avoid CSRF issues
        const response = await fetch(`/past-months/check-exists?month=${endMonthForm.month}&year=${endMonthForm.year}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        monthExists.value = data.exists;
        showOverrideConfirmation.value = false;
        overrideText.value = '';
    } catch (error) {
        console.error('Error checking month existence:', error);
        monthExists.value = false;
    }
};

const submitAddExpense = () => {
    addExpenseForm.post(route('expenses.store'), {
        onSuccess: () => {
            showAddExpenseModal.value = false;
            addExpenseForm.reset();
        },
    });
};

const submitRevenue = () => {
    revenueForm.post(route('revenue.store'), {
        onSuccess: () => {
            showRevenueModal.value = false;
        },
    });
};

const submitSavingsGoal = () => {
    savingsGoalForm.patch(route('revenue.savings-goal'), {
        onSuccess: () => {
            showSavingsGoalModal.value = false;
        },
    });
};

const handleEndMonth = () => {
    if (monthExists.value && !showOverrideConfirmation.value) {
        showOverrideConfirmation.value = true;
        return;
    }

    if (monthExists.value && overrideText.value !== 'OVERRIDE') {
        return; // Don't submit if override text is wrong
    }

    endMonthForm.override_existing = monthExists.value;
    endMonthForm.post(route('water-bank.end-month'), {
        onSuccess: () => {
            showEndMonthModal.value = false;
            endMonthForm.reset();
        },
        onError: () => {
            // Error handling is managed by the flash messages
        }
    });
};

const deleteExpense = (expense) => {
    if (confirm(`Are you sure you want to delete "${expense.name}"? This action cannot be undone.`)) {
        router.delete(route('expenses.destroy', expense.id));
    }
};

const closeModals = () => {
    showAddExpenseModal.value = false;
    showRevenueModal.value = false;
    showSavingsGoalModal.value = false;
    showEndMonthModal.value = false;
    addExpenseForm.reset();
    revenueForm.reset();
    savingsGoalForm.reset();
    endMonthForm.reset();
    monthExists.value = false;
    showOverrideConfirmation.value = false;
    overrideText.value = '';
};

// Form update handlers for modals
const updateAddExpenseForm = (newForm) => {
    Object.assign(addExpenseForm, newForm);
};

const updateRevenueForm = (newForm) => {
    Object.assign(revenueForm, newForm);
};

const updateSavingsGoalForm = (newForm) => {
    Object.assign(savingsGoalForm, newForm);
};

const updateEndMonthForm = (newForm) => {
    Object.assign(endMonthForm, newForm);
};

const updateOverrideText = (newText) => {
    overrideText.value = newText;
};

const showOverrideConfirmationHandler = () => {
    showOverrideConfirmation.value = true;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};
</script>

<template>
    <Head title="This Month"/>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                    This Month
                </h2>
                <span class="ml-3 text-2xl">🌱</span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <WelcomeSection />

                <!-- Stats Grid -->
                <StatsGrid
                    :current-revenue="currentRevenue"
                    :budget-data="budgetData"
                />

                <!-- Budget Warning Banner -->
                <div
                    v-if="hasRevenue && hasSavingsGoal && currentRevenue.monthly_savings_goal > currentRevenue.total_revenue"
                    class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <div class="flex items-center">
                        <span class="text-amber-600 text-xl mr-3">⚠️</span>
                        <div>
                            <h4 class="font-medium text-amber-800">Impossible Budget Alert</h4>
                            <p class="text-sm text-amber-700">Your savings goal is higher than your revenue. Consider
                                adjusting one of them.</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- This Month's Expenses -->
                    <ExpensesSection
                        :monthly-expenses="monthlyExpenses"
                        :expense-stats="expenseStats"
                        @open-add-expense-modal="openAddExpenseModal"
                        @delete-expense="deleteExpense"
                    />

                    <!-- My Garden -->
                    <GardenSection
                        :savings-goals="savingsGoals"
                        :budget-data="budgetData"
                        :require-auth="requireAuth"
                        @open-end-month-modal="openEndMonthModal"
                    />
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                    <button
                        @click="openRevenueModal"
                        class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                    >
                        <span class="mr-3 text-lg">📊</span>
                        {{ hasRevenue ? 'Update Revenue' : 'Set Revenue' }}
                    </button>
                    <button
                        @click="openSavingsGoalModal"
                        class="bg-green-100 hover:bg-green-200 text-green-700 font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                    >
                        <span class="mr-3 text-lg">🏆</span>
                        {{ hasSavingsGoal ? 'Update Savings Goal' : 'Set Savings Goal' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal Components -->
    <AddExpenseModal
        :show="showAddExpenseModal"
        :form="addExpenseForm"
        @close="closeModals"
        @submit="submitAddExpense"
        @update:form="updateAddExpenseForm"
    />

    <RevenueModal
        :show="showRevenueModal"
        :form="revenueForm"
        :has-revenue="hasRevenue"
        @close="closeModals"
        @submit="submitRevenue"
        @update:form="updateRevenueForm"
    />

    <SavingsGoalModal
        :show="showSavingsGoalModal"
        :form="savingsGoalForm"
        :savings-goals="savingsGoals"
        :has-savings-goal="hasSavingsGoal"
        @close="closeModals"
        @submit="submitSavingsGoal"
        @update:form="updateSavingsGoalForm"
    />

    <EndMonthModal
        :show="showEndMonthModal"
        :form="endMonthForm"
        :month-exists="monthExists"
        :show-override-confirmation="showOverrideConfirmation"
        :override-text="overrideText"
        @close="closeModals"
        @submit="handleEndMonth"
        @update:form="updateEndMonthForm"
        @show-override-confirmation="showOverrideConfirmationHandler"
        @update:override-text="updateOverrideText"
    />

    <AuthRequiredModal :show="showAuthModal" @close="closeAuthModal" />
</template>
