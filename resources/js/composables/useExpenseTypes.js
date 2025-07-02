import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable for expense type constants and helpers
 */
export function useExpenseTypes() {
    const page = usePage();

    // Constants from backend
    const EXPENSE_TYPES = computed(() => page.props.constants?.expenseTypes || {});
    const RECURRING_BILL = computed(() => EXPENSE_TYPES.value.RECURRING);
    const ONE_TIME_EXPENSE = computed(() => EXPENSE_TYPES.value.ONE_TIME);

    // Helper functions
    const isRecurringBill = (type) => type === RECURRING_BILL.value;
    const isOneTimeExpense = (type) => type === ONE_TIME_EXPENSE.value;

    return {
        // Constants
        EXPENSE_TYPES,
        RECURRING_BILL,
        ONE_TIME_EXPENSE,

        // Functions
        isRecurringBill,
        isOneTimeExpense,
    };
}
