<?php

namespace App\Repositories\WaterBank;

use App\DTOs\WaterBankDTO;
use App\DTOs\WaterBankTransactionDTO;
use App\Enums\WaterBankSource;
use App\Enums\WaterBankTransactionType;
use App\Exceptions\InsufficientWaterException;
use App\Models\User;
use App\Models\WaterBank;
use Illuminate\Support\Collection;

class DBWaterBankRepository implements WaterBankRepositoryInterface
{
    /**
     * Get or create water bank for user.
     */
    public function getOrCreateForUser(?User $user): WaterBankDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $waterBank = WaterBank::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        return $this->mapToDTO($waterBank);
    }

    /**
     * Find water bank for user.
     */
    public function findForUser(?User $user): ?WaterBankDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $waterBank = WaterBank::where('user_id', $user->id)->first();

        return $waterBank ? $this->mapToDTO($waterBank) : null;
    }

    /**
     * Add water to the bank.
     */
    public function addWater(?User $user, float $amount, WaterBankSource $source, ?string $description = null): WaterBankTransactionDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $waterBank = WaterBank::getOrCreateForUser($user->id);
        $waterBank->increment('balance', $amount);

        $transaction = $waterBank->transactions()->create([
            'type' => WaterBankTransactionType::DEPOSIT->value,
            'amount' => $amount,
            'source' => $source->value,
            'description' => $description,
            'balance_after' => $waterBank->fresh()->balance,
        ]);

        return $this->mapTransactionToDTO($transaction);
    }

    /**
     * Use water from the bank.
     */
    public function useWater(?User $user, float $amount, int $savingsGoalId, WaterBankSource $source = WaterBankSource::PLANT_WATERING): WaterBankTransactionDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $waterBank = WaterBank::getOrCreateForUser($user->id);

        if ($amount > $waterBank->balance) {
            throw new InsufficientWaterException();
        }

        $waterBank->decrement('balance', $amount);

        $transaction = $waterBank->transactions()->create([
            'type' => WaterBankTransactionType::WITHDRAWAL->value,
            'amount' => $amount,
            'source' => $source->value,
            'savings_goal_id' => $savingsGoalId,
            'balance_after' => $waterBank->fresh()->balance,
        ]);

        return $this->mapTransactionToDTO($transaction);
    }

    /**
     * Get recent transactions for water bank.
     */
    public function getRecentTransactions(?User $user, int $limit = 5): Collection
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $waterBank = WaterBank::where('user_id', $user->id)->first();

        if (!$waterBank) {
            return collect();
        }

        return $waterBank->transactions()
            ->with('savingsGoal')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($transaction) => $this->mapTransactionToDTO($transaction));
    }

    /**
     * Get current balance for user.
     */
    public function getBalance(?User $user): float
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $waterBank = WaterBank::where('user_id', $user->id)->first();

        return $waterBank ? $waterBank->balance : 0.0;
    }

    /**
     * Check if user has enough water.
     */
    public function hasEnoughWater(?User $user, float $amount): bool
    {
        return $this->getBalance($user) >= $amount;
    }

    /**
     * Map WaterBank model to DTO.
     */
    private function mapToDTO(WaterBank $waterBank): WaterBankDTO
    {
        return new WaterBankDTO(
            id: $waterBank->id,
            user_id: $waterBank->user_id,
            balance: $waterBank->balance,
            created_at: $waterBank->created_at->toDateTimeString(),
            updated_at: $waterBank->updated_at->toDateTimeString()
        );
    }

    /**
     * Map WaterBankTransaction model to DTO.
     */
    private function mapTransactionToDTO($transaction): WaterBankTransactionDTO
    {
        return new WaterBankTransactionDTO(
            id: $transaction->id,
            water_bank_id: $transaction->water_bank_id,
            type: $transaction->type, // Already cast to enum by model
            amount: $transaction->amount,
            source: $transaction->source, // Already cast to enum by model
            description: $transaction->description,
            savings_goal_id: $transaction->savings_goal_id,
            balance_after: $transaction->balance_after,
            created_at: $transaction->created_at->toDateTimeString(),
            savings_goal_name: $transaction->savingsGoal?->name
        );
    }
}
