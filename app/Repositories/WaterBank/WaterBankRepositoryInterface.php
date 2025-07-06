<?php

namespace App\Repositories\WaterBank;

use App\DTOs\WaterBankDTO;
use App\DTOs\WaterBankTransactionDTO;
use App\Enums\WaterBankSourceEnum;
use App\Models\User;
use Illuminate\Support\Collection;

interface WaterBankRepositoryInterface
{
    /**
     * Get or create water bank for user.
     *
     * @param User|null $user
     * @return WaterBankDTO
     */
    public function getOrCreateForUser(?User $user): WaterBankDTO;

    /**
     * Find water bank for user.
     *
     * @param User|null $user
     * @return WaterBankDTO|null
     */
    public function findForUser(?User $user): ?WaterBankDTO;

    /**
     * Add water to the bank.
     *
     * @param User|null $user
     * @param float $amount
     * @param WaterBankSourceEnum $source
     * @param string|null $description
     * @return WaterBankTransactionDTO
     */
    public function addWater(?User $user, float $amount, WaterBankSourceEnum $source, ?string $description = null): WaterBankTransactionDTO;

    /**
     * Use water from the bank.
     *
     * @param User|null $user
     * @param float $amount
     * @param int $savingsGoalId
     * @param WaterBankSourceEnum $source
     * @return WaterBankTransactionDTO
     */
    public function useWater(?User $user, float $amount, int $savingsGoalId, WaterBankSourceEnum $source = WaterBankSourceEnum::PLANT_WATERING): WaterBankTransactionDTO;

    /**
     * Get recent transactions for water bank.
     *
     * @param User|null $user
     * @param int $limit
     * @return Collection<WaterBankTransactionDTO>
     */
    public function getRecentTransactions(?User $user, int $limit = 5): Collection;

    /**
     * Get current balance for user.
     *
     * @param User|null $user
     * @return float
     */
    public function getBalance(?User $user): float;

    /**
     * Check if user has enough water.
     *
     * @param User|null $user
     * @param float $amount
     * @return bool
     */
    public function hasEnoughWater(?User $user, float $amount): bool;
}
