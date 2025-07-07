<?php

namespace App\Services;

use App\Data\AddSavingsData;
use App\Data\SavingsGoalData;
use App\Data\WaterGoalData;
use App\DTOs\SavingsGoalDTO;
use App\Enums\FundingSourceEnum;
use App\Enums\WaterBankSourceEnum;
use App\Exceptions\InsufficientWaterException;
use App\Exceptions\SavingsGoalNotFoundException;
use App\Http\Requests\SavingsGoal\AddSavingsRequest;
use App\Http\Requests\SavingsGoal\StoreSavingsGoalRequest;
use App\Http\Requests\SavingsGoal\UpdateSavingsGoalRequest;
use App\Http\Requests\SavingsGoal\WithdrawSavingsRequest;
use App\Models\User;
use App\Repositories\SavingsGoal\SavingsGoalRepositoryInterface;
use App\Repositories\WaterBank\WaterBankRepositoryInterface;
use Illuminate\Support\Collection;

class SavingsGoalService
{
    public function __construct(
        private SavingsGoalRepositoryInterface $savingsGoalRepo,
        private WaterBankRepositoryInterface $waterBankRepo,
        private WaterBankService $waterBankService
    ) {}

    /**
     * Create a new savings goal.
     */
    public function createSavingsGoal(?User $user, StoreSavingsGoalRequest $request): SavingsGoalDTO
    {
        $validated = $request->validated();

        $savingsGoalData = new SavingsGoalData(
            name: $validated['name'],
            description: $validated['description'] ?? null,
            target_amount: $validated['target_amount']
        );

        return $this->savingsGoalRepo->create($user, [
            'name' => $savingsGoalData->name,
            'description' => $savingsGoalData->description,
            'target_amount' => $savingsGoalData->target_amount,
            'current_amount' => $savingsGoalData->current_amount,
        ]);
    }

    /**
     * Update an existing savings goal.
     *
     * @throws SavingsGoalNotFoundException
     */
    public function updateSavingsGoal(?User $user, int $goalId, UpdateSavingsGoalRequest $request): SavingsGoalDTO
    {
        $validated = $request->validated();

        // Get existing goal to preserve unchanged fields
        $existingGoal = $this->savingsGoalRepo->findForUser($user, $goalId);

        if (!$existingGoal) {
            throw new SavingsGoalNotFoundException("Savings goal with ID {$goalId} not found");
        }

        $savingsGoalData = new SavingsGoalData(
            name: $validated['name'] ?? $existingGoal->name,
            description: $validated['description'] ?? $existingGoal->description,
            target_amount: $validated['target_amount'] ?? $existingGoal->target_amount,
            current_amount: $existingGoal->current_amount
        );

        return $this->savingsGoalRepo->update($user, $goalId, [
            'name' => $savingsGoalData->name,
            'description' => $savingsGoalData->description,
            'target_amount' => $savingsGoalData->target_amount,
            'current_amount' => $savingsGoalData->current_amount,
        ]);
    }

    /**
     * Delete a savings goal.
     *
     * @throws SavingsGoalNotFoundException
     */
    public function deleteSavingsGoal(?User $user, int $goalId): array
    {
        // Get goal info before deletion for response message
        $goal = $this->savingsGoalRepo->findForUser($user, $goalId);

        if (!$goal) {
            throw new SavingsGoalNotFoundException("Savings goal with ID {$goalId} not found");
        }

        $goalName = $goal->name;
        $deleted = $this->savingsGoalRepo->delete($user, $goalId);

        if (!$deleted) {
            throw new \RuntimeException('Failed to delete savings goal');
        }

        return [
            'name' => $goalName,
            'deleted' => true
        ];
    }

    /**
     * Add savings to a goal.
     *
     * @throws SavingsGoalNotFoundException
     */
    public function addSavings(?User $user, int $goalId, AddSavingsRequest $request): array
    {
        $validated = $request->validated();

        $goal = $this->savingsGoalRepo->findForUser($user, $goalId);
        if (!$goal) {
            throw new SavingsGoalNotFoundException("Savings goal with ID {$goalId} not found");
        }

        $addSavingsData = new AddSavingsData(
            amount: $validated['amount'],
            source: FundingSourceEnum::from($validated['source'])
        );

        if ($addSavingsData->source === FundingSourceEnum::WATER_BANK) {
            return $this->addSavingsFromWaterBank($user, $goalId, $addSavingsData, $goal->name);
        } else {
            return $this->addSavingsFromOther($user, $goalId, $addSavingsData, $goal->name);
        }
    }

    /**
     * Withdraw money from a savings goal.
     *
     * @throws SavingsGoalNotFoundException
     */
    public function withdrawSavings(?User $user, int $goalId, WithdrawSavingsRequest $request): SavingsGoalDTO
    {
        $validated = $request->validated();
        $withdrawAmount = $validated['amount'];

        $goal = $this->savingsGoalRepo->findForUser($user, $goalId);
        if (!$goal) {
            throw new SavingsGoalNotFoundException("Savings goal with ID {$goalId} not found");
        }

        if ($withdrawAmount > $goal->current_amount) {
            throw new \InvalidArgumentException('Cannot withdraw more than the current saved amount.');
        }

        $newAmount = $goal->current_amount - $withdrawAmount;

        return $this->savingsGoalRepo->update($user, $goalId, [
            'name' => $goal->name,
            'description' => $goal->description,
            'target_amount' => $goal->target_amount,
            'current_amount' => $newAmount,
        ]);
    }

    /**
     * Mark a savings goal as completed.
     *
     * @throws SavingsGoalNotFoundException
     */
    public function completeSavingsGoal(?User $user, int $goalId): SavingsGoalDTO
    {
        $goal = $this->savingsGoalRepo->findForUser($user, $goalId);
        if (!$goal) {
            throw new SavingsGoalNotFoundException("Savings goal with ID {$goalId} not found");
        }

        if ($goal->is_completed) {
            throw new \InvalidArgumentException('This goal is already completed!');
        }

        // For session-based goals, we need to handle completion manually
        // For DB-based goals, the model handles this
        return $this->savingsGoalRepo->update($user, $goalId, [
            'name' => $goal->name,
            'description' => $goal->description,
            'target_amount' => $goal->target_amount,
            'current_amount' => $goal->target_amount, // Set to target to complete
        ]);
    }

    /**
     * Get all savings goals for user (both active and completed).
     */
    public function getSavingsGoalsForUser(?User $user): Collection
    {
        return $this->savingsGoalRepo->getAllSavingsGoals($user);
    }

    /**
     * Get only active savings goals for user.
     */
    public function getActiveSavingsGoalsForUser(?User $user): Collection
    {
        return $this->savingsGoalRepo->getActiveSavingsGoals($user);
    }

    /**
     * Find a specific savings goal for user.
     */
    public function findSavingsGoalForUser(?User $user, int $goalId): ?SavingsGoalDTO
    {
        return $this->savingsGoalRepo->findForUser($user, $goalId);
    }

    /**
     * Calculate savings goal statistics.
     */
    public function calculateSavingsGoalStats(?User $user): array
    {
        $goals = $this->getSavingsGoalsForUser($user);

        $totalSaved = $goals->sum('current_amount');
        $totalTarget = $goals->sum('target_amount');

        return [
            'total_goals' => $goals->count(),
            'active_goals' => $goals->where('is_completed', false)->count(),
            'completed_goals' => $goals->where('is_completed', true)->count(),
            'total_saved' => $totalSaved,
            'total_target' => $totalTarget,
            'overall_progress' => $totalTarget > 0
                ? min(($totalSaved / $totalTarget) * 100, 100)
                : 0,
        ];
    }

    /**
     * Get water bank data for user.
     */
    public function getWaterBankData(?User $user): array
    {
        if (!$user) {
            // Return empty water bank for guest users
            return [
                'balance' => 0,
                'formatted_balance' => '$0.00',
                'recent_transactions' => [],
            ];
        }

        $waterBank = $this->waterBankRepo->getOrCreateForUser($user);
        $recentTransactions = $this->waterBankRepo->getRecentTransactions($user, 5);

        return [
            'balance' => $waterBank->balance,
            'formatted_balance' => $waterBank->getFormattedBalance(),
            'recent_transactions' => $recentTransactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type->value,
                    'amount' => $transaction->amount,
                    'formatted_amount' => $transaction->getFormattedAmount(),
                    'icon' => $transaction->getIcon(),
                    'description' => $transaction->getDisplayDescription(),
                    'date' => $transaction->getFormattedDate(),
                    'date_short' => date('M j', strtotime($transaction->created_at)),
                ];
            }),
        ];
    }

    /**
     * Add savings from water bank.
     */
    private function addSavingsFromWaterBank(?User $user, int $goalId, AddSavingsData $data, string $goalName): array
    {
        if (!$user) {
            throw new \InvalidArgumentException('Water bank is only available for authenticated users');
        }

        $waterGoalData = new WaterGoalData(
            amount: $data->amount,
            source: $data->source
        );

        $result = $this->waterBankService->waterGoal($user, $goalId, $waterGoalData);

        return [
            'goal' => $result['goal'],
            'message' => $result['message']
        ];
    }

    /**
     * Add savings from other sources.
     */
    private function addSavingsFromOther(?User $user, int $goalId, AddSavingsData $data, string $goalName): array
    {
        $updatedGoal = $this->savingsGoalRepo->addSavings($user, $goalId, $data->amount);

        return [
            'goal' => $updatedGoal,
            'message' => "Added $" . number_format($data->amount, 2) . " to {$goalName}! 💰"
        ];
    }
}
