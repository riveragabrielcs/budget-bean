<?php

namespace App\Services;

use App\Data\AddManualWaterData;
use App\Data\EndMonthData;
use App\Data\WaterAllGoalsData;
use App\Data\WaterGoalData;
use App\DTOs\SavingsGoalDTO;
use App\DTOs\WaterBankDTO;
use App\DTOs\WaterBankTransactionDTO;
use App\Enums\FundingSourceEnum;
use App\Enums\WaterBankSourceEnum;
use App\Exceptions\InsufficientWaterException;
use App\Exceptions\SavingsGoalNotFoundException;
use App\Models\CompletedMonth;
use App\Models\User;
use App\Repositories\SavingsGoal\SavingsGoalRepositoryInterface;
use App\Repositories\WaterBank\WaterBankRepositoryInterface;
use Illuminate\Support\Collection;

class WaterBankService
{
    public function __construct(
        private WaterBankRepositoryInterface $waterBankRepository,
        private SavingsGoalRepositoryInterface $savingsGoalRepository
    ) {
    }

    /**
     * End the specified month and transfer potential water to usable water bank.
     */
    public function endMonth(User $user, EndMonthData $data): array
    {
        $targetDate = now()->setYear($data->year)->setMonth($data->month)->startOfMonth();

        if ($targetDate->isFuture()) {
            throw new \InvalidArgumentException('Cannot complete a future month! 📅');
        }

        // Create completed month record (this handles duplicate checking)
        $completedMonth = CompletedMonth::createFromCurrentMonth(
            $user,
            $data->month,
            $data->year,
            $data->override_existing
        );

        $waterToAdd = $completedMonth->water_collected;

        // Add water to bank if there's any to add
        $transaction = null;
        if ($waterToAdd > 0) {
            $description = "Month-end deposit from {$completedMonth->month_period}";
            $transaction = $this->waterBankRepository->addWater(
                $user,
                $waterToAdd,
                WaterBankSourceEnum::MONTH_END,
                $description
            );
        }

        // Reset the month: Clear one-time expenses but keep everything else
        $user->currentMonthExpenses()->delete();

        $message = $waterToAdd > 0
            ? "Completed {$completedMonth->month_period}! Added " . number_format($waterToAdd, 2) . " to your Water Bank! 💧"
            : "Completed {$completedMonth->month_period}! No water to collect this time. 🏜️";

        return [
            'message' => $message,
            'water_added' => $waterToAdd,
            'transaction' => $transaction,
            'completed_month' => $completedMonth
        ];
    }

    /**
     * Water a specific savings goal.
     */
    public function waterGoal(User $user, int $goalId, WaterGoalData $data): array
    {
        $goal = $this->findGoalOrFail($user, $goalId);
        $waterCalculation = $this->calculateWaterUsage($goal, $data->amount);

        $transaction = $this->processWaterBankTransaction($user, $goalId, $data, $waterCalculation);
        $updatedGoal = $this->updateGoalSavings($user, $goalId, $goal, $waterCalculation);

        return [
            'message' => $this->buildWaterGoalMessage($goal, $data->source, $waterCalculation),
            'goal' => $updatedGoal,
            'transaction' => $transaction,
            'actual_amount_used' => $waterCalculation['actual_amount_used'],
            'amount_returned' => $waterCalculation['excess_amount'],
        ];
    }

    /**
     * Find goal or throw exception.
     */
    private function findGoalOrFail(User $user, int $goalId): SavingsGoalDTO
    {
        $goal = $this->savingsGoalRepository->findForUser($user, $goalId);

        if (!$goal) {
            throw new SavingsGoalNotFoundException();
        }

        return $goal;
    }

    /**
     * Calculate how much water should actually be used.
     */
    private function calculateWaterUsage(SavingsGoalDTO $goal, float $requestedAmount): array
    {
        $remainingNeeded = max($goal->target_amount - $goal->current_amount, 0);
        $actualAmountToUse = min($requestedAmount, $remainingNeeded);
        $excessAmount = $requestedAmount - $actualAmountToUse;

        return [
            'remaining_needed' => $remainingNeeded,
            'actual_amount_used' => $actualAmountToUse,
            'excess_amount' => $excessAmount,
        ];
    }

    /**
     * Process water bank transaction if using water bank as source.
     */
    private function processWaterBankTransaction(
        User $user,
        int $goalId,
        WaterGoalData $data,
        array $waterCalculation
    ): ?WaterBankTransactionDTO {
        if ($data->source !== FundingSourceEnum::WATER_BANK) {
            return null;
        }

        $this->ensureSufficientWaterBalance($user, $data->amount);

        // Only use the amount actually needed
        if ($waterCalculation['actual_amount_used'] > 0) {
            return $this->waterBankRepository->useWater($user, $waterCalculation['actual_amount_used'], $goalId);
        }

        return null;
    }

    /**
     * Ensure user has sufficient water balance.
     */
    private function ensureSufficientWaterBalance(User $user, float $requestedAmount): void
    {
        if (!$this->waterBankRepository->hasEnoughWater($user, $requestedAmount)) {
            throw new InsufficientWaterException();
        }
    }

    /**
     * Update goal savings if there's an amount to add.
     */
    private function updateGoalSavings(
        User $user,
        int $goalId,
        SavingsGoalDTO $goal,
        array $waterCalculation
    ): SavingsGoalDTO {
        if ($waterCalculation['actual_amount_used'] > 0) {
            return $this->savingsGoalRepository->addSavings($user, $goalId, $waterCalculation['actual_amount_used']);
        }

        return $goal; // No changes needed
    }

    /**
     * Build the success message for watering a goal.
     */
    private function buildWaterGoalMessage(
        SavingsGoalDTO $goal,
        FundingSourceEnum $source,
        array $waterCalculation
    ): string {
        $sourceText = $source === FundingSourceEnum::WATER_BANK ? 'Water Bank' : 'other funds';

        if ($waterCalculation['actual_amount_used'] > 0) {
            $message = "Watered {$goal->name} with " . number_format($waterCalculation['actual_amount_used'], 2) . " from {$sourceText}!";

            if ($waterCalculation['excess_amount'] > 0) {
                $message .= " Only used what was needed, saved " . number_format($waterCalculation['excess_amount'], 2) . " water.";
            }

            $message .= " 🌱💧";
        } else {
            $message = "Goal {$goal->name} is already completed! No water used. 🌳";
        }

        return $message;
    }

    /**
     * Water all goals equally.
     */
    public function waterAllGoals(User $user, WaterAllGoalsData $data): array
    {
        $activeGoals = $this->savingsGoalRepository->getActiveSavingsGoals($user);

        if ($activeGoals->isEmpty()) {
            throw new \InvalidArgumentException('No active goals to water! Plant some goals first. 🌱');
        }

        if ($data->source === FundingSourceEnum::WATER_BANK) {
            if (!$this->waterBankRepository->hasEnoughWater($user, $data->total_amount)) {
                throw new InsufficientWaterException();
            }
        }

        // Divide amount equally among active goals
        $amountPerGoal = $data->total_amount / $activeGoals->count();
        $wateredGoals = [];
        $transactions = [];

        foreach ($activeGoals as $goal) {
            if ($data->source === FundingSourceEnum::WATER_BANK) {
                $transactions[] = $this->waterBankRepository->useWater($user, $amountPerGoal, $goal->id);
            }

            $this->savingsGoalRepository->addSavings($user, $goal->id, $amountPerGoal);
            $wateredGoals[] = $goal->name;
        }

        $sourceText = $data->source === FundingSourceEnum::WATER_BANK ? 'Water Bank' : 'other funds';
        $message = "Watered " . count($wateredGoals) . " goals equally with " . number_format($data->total_amount, 2) . " from {$sourceText}! 🌻💧";

        return [
            'message' => $message,
            'watered_goals' => $wateredGoals,
            'transactions' => $transactions,
            'amount_per_goal' => $amountPerGoal
        ];
    }

    /**
     * Add manual water to bank (for gifts, bonuses, etc.).
     */
    public function addManualWater(User $user, AddManualWaterData $data): array
    {
        $description = $data->description ?: 'Manual water addition';

        $transaction = $this->waterBankRepository->addWater(
            $user,
            $data->amount,
            WaterBankSourceEnum::MANUAL_ADD,
            $description
        );

        $message = "Added " . number_format($data->amount, 2) . " to your Water Bank! 💧";

        return [
            'message' => $message,
            'transaction' => $transaction
        ];
    }

    /**
     * Get current water bank status with recent transactions.
     */
    public function getWaterBankStatus(User $user): array
    {
        $waterBank = $this->waterBankRepository->getOrCreateForUser($user);
        $recentTransactions = $this->waterBankRepository->getRecentTransactions($user, 5);

        $formattedTransactions = $recentTransactions->map(function (WaterBankTransactionDTO $transaction) {
            return [
                'id' => $transaction->id,
                'type' => $transaction->type->value,
                'amount' => $transaction->amount,
                'formatted_amount' => $transaction->getFormattedAmount(),
                'icon' => $transaction->getIcon(),
                'description' => $transaction->getDisplayDescription(),
                'date' => $transaction->getFormattedDate(),
            ];
        });

        return [
            'balance' => $waterBank->balance,
            'formatted_balance' => $waterBank->getFormattedBalance(),
            'recent_transactions' => $formattedTransactions,
        ];
    }

    /**
     * Get water bank data for frontend consumption.
     */
    public function getWaterBankData(User $user): array
    {
        return $this->getWaterBankStatus($user);
    }
}
