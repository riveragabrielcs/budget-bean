<?php

namespace App\Services;

use App\DTOs\BillDTO;
use App\DTOs\DashboardDTO;
use App\Enums\ExpenseTypeEnum;
use App\Models\User;
use App\Repositories\Bill\BillRepositoryInterface;
use App\Repositories\Expense\ExpenseRepositoryInterface;
use App\Repositories\Revenue\RevenueRepositoryInterface;
use App\Repositories\SavingsGoal\SavingsGoalRepositoryInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private BillRepositoryInterface        $billRepository,
        private SavingsGoalRepositoryInterface $savingsGoalRepository,
        private ExpenseRepositoryInterface     $expenseRepository,
        private RevenueRepositoryInterface     $revenueRepository,
        private PlantGrowthService             $plantGrowthService,
    )
    {
    }

    /**
     * Get all dashboard data for the current month.
     *
     * @param User|null $user
     * @return DashboardDTO
     */
    public function getDashboardSummary(?User $user): DashboardDTO
    {
        $savingsGoals = $this->getSavingsGoals($user);
        $recurringBills = $this->billRepository->forUser($user);
        $oneTimeExpenses = $this->getOneTimeExpenses($user);
        $monthlyExpenses = $this->combineExpenses($recurringBills, $oneTimeExpenses);
        $expenseStats = $this->calculateExpenseStats($recurringBills, $oneTimeExpenses);
        $currentRevenue = $this->getCurrentRevenue($user);
        $budgetData = $this->calculateBudgetData($user, $currentRevenue, $expenseStats);

        return new DashboardDTO(
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
    private function getSavingsGoals(?User $user): array
    {
        return $this->savingsGoalRepository->getActiveSavingsGoals($user)
            ->map(function ($goal) {
                $progressPercentage = $goal->getProgressPercentage();
                $plantStatus = $this->plantGrowthService->getPlantStatus($progressPercentage);

                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'progress_percentage' => $progressPercentage,
                    'plant_emoji' => $plantStatus->plantEmoji,
                    'growth_stage' => $plantStatus->growthStage,
                    'remaining_amount' => $goal->getRemainingAmount(),
                ];
            })
            ->toArray();
    }

    /**
     * Get one-time expenses for current month formatted for dashboard.
     */
    private function getOneTimeExpenses(?User $user): array
    {
        return $this->expenseRepository->getCurrentMonthExpenses($user)
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'amount' => $expense->amount,
                    'expense_date' => $expense->expense_date,
                    'formatted_expense_date' => $expense->formatted_expense_date,
                    'description' => $expense->description,
                    'type' => ExpenseTypeEnum::ONE_TIME->value,
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
                'type' => ExpenseTypeEnum::RECURRING->value,
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
     * Get current month revenue data using repository pattern.
     *
     * @param User|null $user
     * @return array|null
     */
    private function getCurrentRevenue(?User $user): ?array
    {
        $revenueDTO = $this->revenueRepository->getCurrentMonthRevenue($user);

        return $revenueDTO?->toArray();
    }

    /**
     * Calculate budget data including drought status.
     *
     * @param User|null $user
     * @param array|null $currentRevenue
     * @param array $expenseStats
     * @return array
     */
    private function calculateBudgetData(?User $user, ?array $currentRevenue, array $expenseStats): array
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
