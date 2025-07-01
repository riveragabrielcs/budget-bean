<?php

namespace App\Services;

use App\DTOs\BillDTO;
use App\DTOs\ThisMonthDTO;
use App\Models\User;
use App\Repositories\Contracts\BillRepositoryInterface;
use Illuminate\Support\Collection;

class ThisMonthService
{
    public function __construct(
        private BillRepositoryInterface $bills,
    ){}
    /**
     * Get all dashboard data for the current month.
     */
    public function getThisMonthData(User $user): ThisMonthDTO
    {
        $savingsGoals = $this->getSavingsGoals($user);
        $recurringBills = $this->bills->forUser($user);
        $oneTimeExpenses = $this->getOneTimeExpenses($user);
        $monthlyExpenses = $this->combineExpenses($recurringBills, $oneTimeExpenses);
        $expenseStats = $this->calculateExpenseStats($recurringBills, $oneTimeExpenses);
        $currentRevenue = $this->getCurrentRevenue($user);
        $budgetData = $this->calculateBudgetData($user, $currentRevenue, $expenseStats);

        return new ThisMonthDTO(
            savingsGoals: $savingsGoals,
            monthlyExpenses: $monthlyExpenses,
            expenseStats: $expenseStats,
            currentRevenue: $currentRevenue,
            budgetData: $budgetData
        );
    }

    /**
     * Get active savings goals formatted for dashboard.
     */
    private function getSavingsGoals(User $user): array
    {
        return $user->activeSavingsGoals()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'progress_percentage' => $goal->progress_percentage,
                    'plant_emoji' => $goal->plant_emoji,
                    'growth_stage' => $goal->growth_stage,
                    'remaining_amount' => $goal->remaining_amount,
                ];
            })
            ->toArray();
    }

    /**
     * Get one-time expenses for current month formatted for dashboard.
     */
    private function getOneTimeExpenses(User $user): array
    {
        return $user->currentMonthExpenses()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'amount' => $expense->amount,
                    'expense_date' => $expense->expense_date,
                    'formatted_expense_date' => $expense->formatted_expense_date,
                    'description' => $expense->description,
                    'type' => 'one_time_expense',
                    'created_at' => $expense->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Combine and sort all expenses by creation date.
     *
     * @param Collection<BillDTO> $recurringBills
     * @param array $oneTimeExpenses
     * @return array
     */
    private function combineExpenses(Collection $recurringBills, array $oneTimeExpenses): array
    {
        // Convert BillDTOs to arrays for frontend compatibility
        $billsArray = $recurringBills->map(function ($bill) {
            return [
                'id' => $bill->id,
                'name' => $bill->name,
                'amount' => $bill->amount,
                'bill_date' => $bill->date,
                'formatted_bill_date' => $bill->date,
                'description' => $bill->description,
                'type' => 'recurring_bill',
                'created_at' => $bill->created_at,
            ];
        })->toArray();

        $allExpenses = collect($billsArray)
            ->concat($oneTimeExpenses)
            ->sortByDesc('created_at')
            ->values();

        return $allExpenses->toArray();
    }

    /**
     * Calculate expense statistics.
     *
     * @param Collection<BillDTO> $recurringBills
     * @param array $oneTimeExpenses
     * @return array
     */
    private function calculateExpenseStats(Collection $recurringBills, array $oneTimeExpenses): array
    {
        $totalRecurringBills = $recurringBills->sum('amount');
        $totalOneTimeExpenses = collect($oneTimeExpenses)->sum('amount');

        return [
            'total_recurring_bills' => $totalRecurringBills,
            'total_one_time_expenses' => $totalOneTimeExpenses,
            'total_monthly_expenses' => $totalRecurringBills + $totalOneTimeExpenses,
            'recurring_bills_count' => $recurringBills->count(),
            'one_time_expenses_count' => count($oneTimeExpenses),
        ];
    }

    /**
     * Get current month revenue data.
     */
    private function getCurrentRevenue(User $user): ?array
    {
        $currentRevenue = $user->currentMonthRevenue();

        if (!$currentRevenue) {
            return null;
        }

        return [
            'total_revenue' => $currentRevenue->total_revenue,
            'calculation_method' => $currentRevenue->calculation_method,
            'paycheck_amount' => $currentRevenue->paycheck_amount,
            'paycheck_count' => $currentRevenue->paycheck_count,
            'monthly_savings_goal' => $currentRevenue->monthly_savings_goal,
            'source_description' => $currentRevenue->source_description,
            'revenue_period' => $currentRevenue->revenue_period,
        ];
    }

    /**
     * Calculate budget data including drought status.
     */
    private function calculateBudgetData(User $user, ?array $currentRevenue, array $expenseStats): array
    {
        if ($currentRevenue) {
            $monthlyBudget = max($currentRevenue['total_revenue'] - ($currentRevenue['monthly_savings_goal'] ?? 0), 0);
            $budgetRemaining = $monthlyBudget - $expenseStats['total_monthly_expenses'];
            $potentialWaterBank = max($currentRevenue['total_revenue'] - $expenseStats['total_monthly_expenses'], 0);
            $isDrought = $budgetRemaining < 0;

            return [
                'monthly_budget' => $monthlyBudget,
                'budget_remaining' => $budgetRemaining,
                'potential_water_bank' => $potentialWaterBank,
                'is_drought' => $isDrought,
            ];
        }

        // Default values when no revenue is set
        return [
            'monthly_budget' => 0,
            'budget_remaining' => -$expenseStats['total_monthly_expenses'],
            'potential_water_bank' => 0,
            'is_drought' => $expenseStats['total_monthly_expenses'] > 0,
        ];
    }
}
