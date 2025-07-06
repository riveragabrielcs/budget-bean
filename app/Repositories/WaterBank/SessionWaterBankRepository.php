<?php

namespace App\Repositories\WaterBank;

use App\DTOs\WaterBankDTO;
use App\DTOs\WaterBankTransactionDTO;
use App\Enums\WaterBankSource;
use App\Enums\WaterBankTransactionType;
use App\Exceptions\InsufficientWaterException;
use App\Models\User;
use Illuminate\Support\Collection;

class SessionWaterBankRepository implements WaterBankRepositoryInterface
{
    private const SESSION_KEY = 'guest_water_bank';
    private const TRANSACTIONS_KEY = 'guest_water_bank_transactions';

    /**
     * Get or create water bank for guest user.
     */
    public function getOrCreateForUser(?User $user): WaterBankDTO
    {
        $waterBank = session(self::SESSION_KEY, [
            'id' => 1,
            'user_id' => 0,
            'balance' => 0.0,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return $this->mapToDTO($waterBank);
    }

    /**
     * Find water bank for guest user.
     */
    public function findForUser(?User $user): ?WaterBankDTO
    {
        $waterBank = session(self::SESSION_KEY);

        return $waterBank ? $this->mapToDTO($waterBank) : null;
    }

    /**
     * Add water to the bank.
     */
    public function addWater(?User $user, float $amount, WaterBankSource $source, ?string $description = null): WaterBankTransactionDTO
    {
        $waterBank = session(self::SESSION_KEY, [
            'id' => 1,
            'user_id' => 0,
            'balance' => 0.0,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $newBalance = $waterBank['balance'] + $amount;
        $waterBank['balance'] = $newBalance;
        $waterBank['updated_at'] = now()->toDateTimeString();

        session([self::SESSION_KEY => $waterBank]);

        $transaction = $this->createTransaction(
            WaterBankTransactionType::DEPOSIT,
            $amount,
            $source,
            $description,
            null,
            $newBalance
        );

        return $transaction;
    }

    /**
     * Use water from the bank.
     */
    public function useWater(?User $user, float $amount, int $savingsGoalId, WaterBankSource $source = WaterBankSource::PLANT_WATERING): WaterBankTransactionDTO
    {
        $waterBank = session(self::SESSION_KEY, [
            'id' => 1,
            'user_id' => 0,
            'balance' => 0.0,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        if ($amount > $waterBank['balance']) {
            throw new InsufficientWaterException();
        }

        $newBalance = $waterBank['balance'] - $amount;
        $waterBank['balance'] = $newBalance;
        $waterBank['updated_at'] = now()->toDateTimeString();

        session([self::SESSION_KEY => $waterBank]);

        $transaction = $this->createTransaction(
            WaterBankTransactionType::WITHDRAWAL,
            $amount,
            $source,
            null,
            $savingsGoalId,
            $newBalance
        );

        return $transaction;
    }

    /**
     * Get recent transactions for water bank.
     */
    public function getRecentTransactions(?User $user, int $limit = 5): Collection
    {
        $transactions = session(self::TRANSACTIONS_KEY, []);

        return collect($transactions)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->map(fn($raw) => $this->mapTransactionToDTO($raw));
    }

    /**
     * Get current balance for guest user.
     */
    public function getBalance(?User $user): float
    {
        $waterBank = session(self::SESSION_KEY);

        return $waterBank ? $waterBank['balance'] : 0.0;
    }

    /**
     * Check if guest has enough water.
     */
    public function hasEnoughWater(?User $user, float $amount): bool
    {
        return $this->getBalance($user) >= $amount;
    }

    /**
     * Create a transaction and store in session.
     */
    private function createTransaction(
        WaterBankTransactionType $type,
        float $amount,
        WaterBankSource $source,
        ?string $description,
        ?int $savingsGoalId,
        float $balanceAfter
    ): WaterBankTransactionDTO {
        $transactions = session(self::TRANSACTIONS_KEY, []);

        $transaction = [
            'id' => $this->getNextTransactionId($transactions),
            'water_bank_id' => 1,
            'type' => $type->value,
            'amount' => $amount,
            'source' => $source->value,
            'description' => $description,
            'savings_goal_id' => $savingsGoalId,
            'balance_after' => $balanceAfter,
            'created_at' => now()->toDateTimeString(),
            'savings_goal_name' => $this->getSavingsGoalName($savingsGoalId),
        ];

        $transactions[] = $transaction;
        session([self::TRANSACTIONS_KEY => $transactions]);

        return $this->mapTransactionToDTO($transaction);
    }

    /**
     * Get next transaction ID.
     */
    private function getNextTransactionId(array $transactions): int
    {
        if (empty($transactions)) {
            return 1;
        }

        return collect($transactions)->max('id') + 1;
    }

    /**
     * Get savings goal name from session.
     */
    private function getSavingsGoalName(?int $savingsGoalId): ?string
    {
        if (!$savingsGoalId) {
            return null;
        }

        $goals = session('guest_savings_goals', []);
        $goal = collect($goals)->first(fn($g) => $g['id'] === $savingsGoalId);

        return $goal ? $goal['name'] : null;
    }

    /**
     * Map array to WaterBankDTO.
     */
    private function mapToDTO(array $waterBank): WaterBankDTO
    {
        return new WaterBankDTO(
            id: $waterBank['id'],
            user_id: $waterBank['user_id'],
            balance: $waterBank['balance'],
            created_at: $waterBank['created_at'],
            updated_at: $waterBank['updated_at']
        );
    }

    /**
     * Map array to WaterBankTransactionDTO.
     */
    private function mapTransactionToDTO(array $transaction): WaterBankTransactionDTO
    {
        return new WaterBankTransactionDTO(
            id: $transaction['id'],
            water_bank_id: $transaction['water_bank_id'],
            type: WaterBankTransactionType::from($transaction['type']),
            amount: $transaction['amount'],
            source: WaterBankSource::from($transaction['source']),
            description: $transaction['description'],
            savings_goal_id: $transaction['savings_goal_id'],
            balance_after: $transaction['balance_after'],
            created_at: $transaction['created_at'],
            savings_goal_name: $transaction['savings_goal_name'] ?? null
        );
    }
}
