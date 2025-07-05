<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NavLink from '@/Components/NavLink.vue';
import {Head, useForm, router, usePage} from '@inertiajs/vue3';
import {computed, ref, watch} from 'vue';
import {useExpenseTypes} from '@/composables/useExpenseTypes';
import { useAuthGuard } from '@/composables/useAuthGuard'
import AuthRequiredModal from '@/Components/AuthRequiredModal.vue'


const { requireAuth, showAuthModal, closeAuthModal } = useAuthGuard()

const props = defineProps({
    savingsGoals: Array,
    monthlyExpenses: Array,
    expenseStats: Object,
    currentRevenue: Object,
    budgetData: Object,
});

const { isRecurringBill } = useExpenseTypes();

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
const hasExpenses = computed(() => props.monthlyExpenses.length > 0);
const hasRevenue = computed(() => props.currentRevenue !== null);
const hasSavingsGoal = computed(() => props.currentRevenue?.monthly_savings_goal > 0);

const calculatedTotal = computed(() => {
    if (revenueForm.calculation_method === 'paycheck' && revenueForm.paycheck_amount && revenueForm.paycheck_count) {
        return (parseFloat(revenueForm.paycheck_amount) * parseInt(revenueForm.paycheck_count)).toFixed(2);
    }
    return '0.00';
});

// Budget status for styling
const budgetStatus = computed(() => {
    if (!props.budgetData) return 'neutral';
    const remaining = props.budgetData.budget_remaining;
    if (remaining < 0) return 'negative';
    if (remaining < 200) return 'warning';
    return 'positive';
});

const welcomeMessage = computed(() => {
    const user = usePage().props.auth?.user;

    if (user) {
        return {
            title: "Welcome back! 🌱",
            subtitle: "Ready to grow your savings like a healthy garden?"
        };
    } else {
        return {
            title: "Welcome to BudgetBean! 🌱",
            subtitle: "Feel free to test out the app and see how it works. Create an account to save your financial garden's progress!"
        };
    }
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

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const getMonthName = (monthNumber) => {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return months[monthNumber - 1];
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
                <!-- Welcome Card -->
                <div
                    class="overflow-hidden bg-white shadow-lg sm:rounded-xl border border-emerald-100 mb-6"
                >
                    <div
                        class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                        <!-- Subtle decorative elements -->
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                        <div
                            class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                        <div class="flex items-center relative">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-emerald-800">
                                    {{ welcomeMessage.title }}
                                </h3>
                                <p class="text-stone-600 mt-2 text-lg">
                                    {{ welcomeMessage.subtitle }}
                                </p>
                            </div>
                            <div class="hidden sm:block">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center shadow-md border border-amber-200">
                                    <span class="text-3xl">💰</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- This Month's Revenue Card -->
                    <div
                        class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl border border-emerald-100">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center shadow-sm">
                                        <span class="text-emerald-600 text-lg">📊</span>
                                    </div>
                                </div>
                                <dl class="ml-4 flex-1">
                                    <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">This Month's
                                        Revenue
                                    </dt>
                                    <dd class="text-3xl font-bold text-emerald-800 mt-1">
                                        {{ hasRevenue ? formatCurrency(currentRevenue.total_revenue) : '$0.00' }}
                                    </dd>
                                    <div v-if="hasRevenue && currentRevenue.source_description"
                                         class="text-xs text-emerald-600 mt-1">
                                        {{ currentRevenue.source_description }}
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Remaining Card -->
                    <div
                        class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl"
                        :class="{
                             'border-emerald-100': budgetStatus === 'positive',
                             'border-amber-200': budgetStatus === 'warning',
                             'border-red-200': budgetStatus === 'negative',
                             'border-stone-200': budgetStatus === 'neutral'
                         }">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                                         :class="{
                                             'bg-gradient-to-br from-emerald-100 to-emerald-200': budgetStatus === 'positive',
                                             'bg-gradient-to-br from-amber-100 to-amber-200': budgetStatus === 'warning',
                                             'bg-gradient-to-br from-red-100 to-red-200': budgetStatus === 'negative',
                                             'bg-gradient-to-br from-stone-100 to-stone-200': budgetStatus === 'neutral'
                                         }">
                                        <span class="text-lg"
                                              :class="{
                                                  'text-emerald-600': budgetStatus === 'positive',
                                                  'text-amber-600': budgetStatus === 'warning',
                                                  'text-red-600': budgetStatus === 'negative',
                                                  'text-stone-600': budgetStatus === 'neutral'
                                              }">
                                            {{ budgetData?.is_drought ? '🏜️' : '🎯' }}
                                        </span>
                                    </div>
                                </div>
                                <dl class="ml-4 flex-1">
                                    <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">Month's
                                        Budget Left
                                    </dt>
                                    <dd class="text-3xl font-bold mt-1"
                                        :class="{
                                            'text-emerald-800': budgetStatus === 'positive',
                                            'text-amber-700': budgetStatus === 'warning',
                                            'text-red-700': budgetStatus === 'negative',
                                            'text-stone-700': budgetStatus === 'neutral'
                                        }">
                                        {{ budgetData ? formatCurrency(budgetData.budget_remaining) : '$0.00' }}
                                    </dd>
                                    <div v-if="budgetData?.is_drought" class="text-xs text-red-600 mt-1">
                                        Drought Mode - You've overspent! 🏜️
                                    </div>
                                    <div v-else-if="budgetStatus === 'warning'" class="text-xs text-amber-600 mt-1">
                                        Running low on budget
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Savings Goal Card -->
                    <div
                        class="bg-white overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 sm:rounded-xl border border-green-100">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center shadow-sm">
                                        <span class="text-green-600 text-lg">🏆</span>
                                    </div>
                                </div>
                                <dl class="ml-4 flex-1">
                                    <dt class="text-sm font-medium text-stone-500 uppercase tracking-wide">Month's
                                        Savings Goal
                                    </dt>
                                    <dd class="text-3xl font-bold text-green-800 mt-1">
                                        {{
                                            hasSavingsGoal ? formatCurrency(currentRevenue.monthly_savings_goal) : '$0.00'
                                        }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

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
                    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-emerald-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-emerald-800 mb-6 flex items-center">
                                <span class="mr-2">📋</span>
                                This Month's Expenses
                            </h3>

                            <!-- Empty State -->
                            <div v-if="!hasExpenses" class="text-center py-12">
                                <div
                                    class="w-20 h-20 bg-gradient-to-br from-stone-50 to-stone-100 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-stone-200">
                                    <span class="text-3xl text-stone-400">🧾</span>
                                </div>
                                <h4 class="font-medium text-stone-600 mb-2">No expenses yet</h4>
                                <p class="text-sm text-stone-500 max-w-sm mx-auto leading-relaxed mb-6">Start tracking
                                    your spending to see them here and watch your financial garden grow!</p>

                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <button
                                        @click="openAddExpenseModal"
                                        class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                                    >
                                        <span class="mr-2">💸</span>
                                        Add One-Time Expense
                                    </button>
                                    <NavLink
                                        :href="route('bills.index')"
                                        class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                                    >
                                        <span class="mr-2">🔄</span>
                                        Add Recurring Bill
                                    </NavLink>
                                </div>
                            </div>

                            <!-- Expenses List and Summary -->
                            <div v-else>
                                <!-- Always Visible Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                                    <button
                                        @click="openAddExpenseModal"
                                        class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                                    >
                                        <span class="mr-2">➕</span>
                                        Add One-Time Expense
                                    </button>
                                    <NavLink
                                        :href="route('bills.index')"
                                        class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center"
                                    >
                                        <span class="mr-2">🔄</span>
                                        Manage Recurring Bills
                                    </NavLink>
                                </div>

                                <!-- Scrollable Expenses List -->
                                <div class="space-y-3 max-h-64 overflow-y-auto pr-2 mb-6">
                                    <div
                                        v-for="expense in monthlyExpenses"
                                        :key="`${expense.type}-${expense.id}`"
                                        class="border border-emerald-100 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200"
                                    >
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center flex-1">
                                                <span class="text-lg mr-3">{{
                                                        isRecurringBill(expense.type) ? '💳' : '💸'
                                                    }}</span>
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-emerald-800">{{ expense.name }}</h4>
                                                    <div class="flex items-center gap-2">
                                                        <span v-if="isRecurringBill(expense.type)"
                                                              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                                            🔄 Recurring Bill
                                                        </span>
                                                        <span v-else
                                                              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                            💸 One-Time Expense
                                                        </span>
                                                        <span v-if="expense.formatted_bill_date"
                                                              class="text-xs text-emerald-600">
                                                            Due: {{ expense.formatted_bill_date }}
                                                        </span>
                                                        <span v-if="expense.formatted_expense_date"
                                                              class="text-xs text-emerald-600">
                                                            {{ expense.formatted_expense_date }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="text-right">
                                                    <div class="text-lg font-bold text-stone-700">
                                                        {{ formatCurrency(expense.amount) }}
                                                    </div>
                                                </div>
                                                <!-- Delete button for one-time expenses only -->
                                                <button
                                                    v-if="expense.type === 'one_time_expense'"
                                                    @click="deleteExpense(expense)"
                                                    class="text-stone-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50"
                                                    title="Delete expense"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div v-if="expense.description" class="text-sm text-stone-500 mt-2">
                                            {{ expense.description }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Always Visible Summary (Outside Scroll) -->
                                <div class="border-t border-emerald-100 pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-stone-600">Monthly Total:</span>
                                        <span class="text-xl font-bold text-emerald-800">{{
                                                formatCurrency(expenseStats.total_monthly_expenses)
                                            }}</span>
                                    </div>
                                    <div class="text-sm text-stone-500 mt-1">
                                        {{ expenseStats.recurring_bills_count }} recurring bills
                                        <span v-if="expenseStats.one_time_expenses_count > 0">
                                            + {{ expenseStats.one_time_expenses_count }} one-time expenses
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Garden -->
                    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-emerald-100">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-emerald-800 flex items-center">
                                    <span class="mr-2">🌻</span>
                                    My Garden
                                </h3>
                                <NavLink
                                    :href="route('garden.index')"
                                    class="text-sm bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-3 py-1 rounded-lg transition duration-200 flex items-center"
                                >
                                    <span class="mr-1">🌱</span>
                                    {{ hasGoals ? 'View All' : 'Plant a Goal' }}
                                </NavLink>
                            </div>

                            <!-- Water Bank Section -->
                            <div v-if="budgetData && budgetData.potential_water_bank > 0"
                                 class="mb-6 p-4 bg-gradient-to-br from-cyan-50 to-blue-50 border border-cyan-200 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">💧</span>
                                        <div>
                                            <h4 class="font-semibold text-cyan-800">Water Bank Preview</h4>
                                            <p class="text-xs text-cyan-600">All unspent money available for watering
                                                plants</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold text-cyan-800">
                                        {{ formatCurrency(budgetData.potential_water_bank) }}
                                    </div>
                                </div>
                                <div class="text-xs text-cyan-600 mb-3">
                                    Includes your savings goal + any money you didn't spend
                                </div>
                                <div class="flex justify-center">
                                    <button
                                        @click="requireAuth(openEndMonthModal)"
                                        class="bg-cyan-500 hover:bg-cyan-600 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                                    >
                                        <span class="mr-2">🏁</span>
                                        End Month & Collect Water
                                    </button>
                                </div>
                            </div>

                            <!-- Drought Warning -->
                            <div v-else-if="budgetData?.is_drought"
                                 class="mb-6 p-4 bg-gradient-to-br from-red-50 to-orange-50 border border-red-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">🏜️</span>
                                        <div>
                                            <h4 class="font-semibold text-red-800">Drought Mode</h4>
                                            <p class="text-xs text-red-600">No water available - you've overspent this
                                                month</p>
                                        </div>
                                    </div>
                                    <button
                                        @click="openEndMonthModal"
                                        class="bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center text-sm"
                                    >
                                        <span class="mr-2">🏁</span>
                                        End Month
                                    </button>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="!hasGoals" class="text-center py-8">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-emerald-200">
                                    <span class="text-2xl text-emerald-500">🌱</span>
                                </div>
                                <h4 class="font-medium text-stone-600 mb-2">Plant your first savings goal</h4>
                                <p class="text-sm text-stone-500 max-w-sm mx-auto leading-relaxed">Start growing your
                                    dreams! Add savings goals and watch them bloom!</p>
                            </div>

                            <!-- Goals List -->
                            <div v-else class="space-y-4 max-h-64 overflow-y-auto pr-2">
                                <div
                                    v-for="goal in savingsGoals"
                                    :key="goal.id"
                                    class="border border-emerald-100 rounded-lg p-4 hover:shadow-sm transition-shadow duration-200"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <span class="text-lg mr-2">{{ goal.plant_emoji }}</span>
                                            <div>
                                                <h4 class="font-medium text-emerald-800">{{ goal.name }}</h4>
                                                <p class="text-xs text-emerald-600">{{ goal.growth_stage }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-stone-700">
                                                {{ formatCurrency(goal.current_amount) }}
                                            </div>
                                            <div class="text-xs text-stone-500">of {{
                                                    formatCurrency(goal.target_amount)
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs text-stone-500">Progress</span>
                                            <span class="text-xs font-medium text-emerald-700">{{
                                                    Math.round(goal.progress_percentage)
                                                }}%</span>
                                        </div>
                                        <div class="w-full bg-stone-200 rounded-full h-2">
                                            <div
                                                class="bg-gradient-to-r from-emerald-400 to-green-500 h-2 rounded-full transition-all duration-300"
                                                :style="{ width: `${Math.min(goal.progress_percentage, 100)}%` }"
                                            ></div>
                                        </div>
                                    </div>

                                    <div class="text-xs text-stone-500">
                                        {{ formatCurrency(goal.remaining_amount) }} remaining
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

    <!-- Add One-Time Expense Modal -->
    <div v-if="showAddExpenseModal"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">💸</span>
                        Add One-Time Expense
                    </h3>
                    <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitAddExpense" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Expense Name *</label>
                        <input
                            v-model="addExpenseForm.name"
                            type="text"
                            placeholder="e.g., Fast Food, Gas, Concert Tickets"
                            class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            required
                        />
                        <div v-if="addExpenseForm.errors.name" class="text-red-600 text-sm mt-1">
                            {{ addExpenseForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-stone-500">$</span>
                            <input
                                v-model="addExpenseForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="25.50"
                                class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                        </div>
                        <div v-if="addExpenseForm.errors.amount" class="text-red-600 text-sm mt-1">
                            {{ addExpenseForm.errors.amount }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Date</label>
                        <input
                            v-model="addExpenseForm.expense_date"
                            type="date"
                            class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                        <div v-if="addExpenseForm.errors.expense_date" class="text-red-600 text-sm mt-1">
                            {{ addExpenseForm.errors.expense_date }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Description (Optional)</label>
                        <textarea
                            v-model="addExpenseForm.description"
                            placeholder="Any notes about this expense..."
                            rows="3"
                            class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        ></textarea>
                        <div v-if="addExpenseForm.errors.description" class="text-red-600 text-sm mt-1">
                            {{ addExpenseForm.errors.description }}
                        </div>
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
                            :disabled="addExpenseForm.processing"
                            class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="addExpenseForm.processing">Adding...</span>
                            <span v-else>💸 Add Expense</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Revenue Modal -->
    <div v-if="showRevenueModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">📊</span>
                        {{ hasRevenue ? 'Update Monthly Revenue' : 'Set Monthly Revenue' }}
                    </h3>
                    <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitRevenue" class="space-y-4">
                    <!-- Calculation Method -->
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-3">How would you like to calculate
                            your revenue?</label>
                        <div class="space-y-3">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    v-model="revenueForm.calculation_method"
                                    type="radio"
                                    value="paycheck"
                                    class="text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                                />
                                <span class="ml-3 text-sm text-stone-700">💰 Paycheck Calculator</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input
                                    v-model="revenueForm.calculation_method"
                                    type="radio"
                                    value="custom"
                                    class="text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                                />
                                <span class="ml-3 text-sm text-stone-700">✏️ Custom Amount</span>
                            </label>
                        </div>
                    </div>

                    <!-- Paycheck Method -->
                    <div v-if="revenueForm.calculation_method === 'paycheck'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Paycheck Amount *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-stone-500">$</span>
                                <input
                                    v-model="revenueForm.paycheck_amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="2500.00"
                                    class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    required
                                />
                            </div>
                            <div v-if="revenueForm.errors.paycheck_amount" class="text-red-600 text-sm mt-1">
                                {{ revenueForm.errors.paycheck_amount }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Number of Paychecks This Month
                                *</label>
                            <input
                                v-model="revenueForm.paycheck_count"
                                type="number"
                                min="1"
                                max="10"
                                placeholder="2"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                            <div v-if="revenueForm.errors.paycheck_count" class="text-red-600 text-sm mt-1">
                                {{ revenueForm.errors.paycheck_count }}
                            </div>
                        </div>

                        <!-- Live Calculation Preview -->
                        <div v-if="revenueForm.paycheck_amount && revenueForm.paycheck_count"
                             class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-emerald-700">Total Revenue:</span>
                                <span class="text-lg font-bold text-emerald-800">{{
                                        formatCurrency(calculatedTotal)
                                    }}</span>
                            </div>
                            <div class="text-xs text-emerald-600 mt-1">
                                {{ revenueForm.paycheck_count }} paychecks ×
                                {{ formatCurrency(revenueForm.paycheck_amount) }}
                            </div>
                        </div>
                    </div>

                    <!-- Custom Method -->
                    <div v-if="revenueForm.calculation_method === 'custom'">
                        <label class="block text-sm font-medium text-stone-700 mb-2">Total Monthly Revenue *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-stone-500">$</span>
                            <input
                                v-model="revenueForm.total_revenue"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="5000.00"
                                class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                        </div>
                        <div v-if="revenueForm.errors.total_revenue" class="text-red-600 text-sm mt-1">
                            {{ revenueForm.errors.total_revenue }}
                        </div>
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
                            :disabled="revenueForm.processing"
                            class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="revenueForm.processing">Saving...</span>
                            <span v-else>📊 {{ hasRevenue ? 'Update' : 'Set' }} Revenue</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Savings Goal Modal -->
    <div v-if="showSavingsGoalModal"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">🏆</span>
                        {{ hasSavingsGoal ? 'Update Savings Goal' : 'Set Savings Goal' }}
                    </h3>
                    <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Garden Goals Context -->
                <div v-if="hasGoals" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h4 class="font-medium text-green-800 mb-2 flex items-center">
                        <span class="mr-2">🌻</span>
                        Your Garden Goals
                    </h4>
                    <div class="space-y-2">
                        <div v-for="goal in savingsGoals.slice(0, 3)" :key="goal.id"
                             class="flex justify-between text-sm">
                            <span class="text-green-700">{{ goal.plant_emoji }} {{ goal.name }}</span>
                            <span class="text-green-600">{{ formatCurrency(goal.remaining_amount) }} needed</span>
                        </div>
                        <div v-if="savingsGoals.length > 3" class="text-xs text-green-600">
                            +{{ savingsGoals.length - 3 }} more goals
                        </div>
                    </div>
                    <p class="text-xs text-green-600 mt-2">Your monthly savings will become Water Bank for your plants!
                        💧</p>
                </div>

                <form @submit.prevent="submitSavingsGoal" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-2">Monthly Savings Goal *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-stone-500">$</span>
                            <input
                                v-model="savingsGoalForm.monthly_savings_goal"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="1000.00"
                                class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                        </div>
                        <div v-if="savingsGoalForm.errors.monthly_savings_goal" class="text-red-600 text-sm mt-1">
                            {{ savingsGoalForm.errors.monthly_savings_goal }}
                        </div>
                        <p class="text-xs text-stone-500 mt-1">This amount will be set aside each month to water your
                            garden plants.</p>
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
                            :disabled="savingsGoalForm.processing"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="savingsGoalForm.processing">Saving...</span>
                            <span v-else>🏆 {{ hasSavingsGoal ? 'Update' : 'Set' }} Goal</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- End Month Modal -->
    <div v-if="showEndMonthModal"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                        <span class="mr-2">🏁</span>
                        End Month & Collect Water
                    </h3>
                    <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
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

                <form @submit.prevent="handleEndMonth" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Month *</label>
                            <select
                                v-model="endMonthForm.month"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            >
                                <option value="">Select month...</option>
                                <option v-for="month in 12" :key="month" :value="month">
                                    {{ getMonthName(month) }}
                                </option>
                            </select>
                            <div v-if="endMonthForm.errors.month" class="text-red-600 text-sm mt-1">
                                {{ endMonthForm.errors.month }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Year *</label>
                            <select
                                v-model="endMonthForm.year"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            >
                                <option value="">Select year...</option>
                                <option v-for="year in [2023, 2024, 2025, 2026]" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                            <div v-if="endMonthForm.errors.year" class="text-red-600 text-sm mt-1">
                                {{ endMonthForm.errors.year }}
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
                                    {{ getMonthName(endMonthForm.month) }} {{ endMonthForm.year }} has already been
                                    recorded.
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="showOverrideConfirmation = true"
                            class="bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium px-4 py-2 rounded-lg transition duration-200"
                        >
                            Override Previous Record
                        </button>
                    </div>

                    <!-- Override Confirmation -->
                    <div v-if="showOverrideConfirmation" class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-medium text-red-800 mb-3">⚠️ Override Confirmation Required</h4>
                        <p class="text-sm text-red-700 mb-4">
                            You will lose all previous data for {{ getMonthName(endMonthForm.month) }}
                            {{ endMonthForm.year }}.
                            Type <strong>OVERRIDE</strong> to confirm:
                        </p>
                        <input
                            v-model="overrideText"
                            type="text"
                            placeholder="Type OVERRIDE to confirm"
                            class="w-full px-4 py-3 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        />
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
                            :disabled="endMonthForm.processing || (showOverrideConfirmation && overrideText !== 'OVERRIDE')"
                            class="flex-1 bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="endMonthForm.processing">Processing...</span>
                            <span v-else-if="monthExists && !showOverrideConfirmation">Month Already Exists</span>
                            <span v-else-if="showOverrideConfirmation && overrideText !== 'OVERRIDE'">Type OVERRIDE to confirm</span>
                            <span v-else>🏁 End Month & Collect Water</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <AuthRequiredModal :show="showAuthModal" @close="closeAuthModal" />
</template>
