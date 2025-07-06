<?php

namespace App\Repositories\WaterBank;

use App\DTOs\WaterBankDTO;
use App\DTOs\WaterBankTransactionDTO;
use App\Enums\WaterBankSource;
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
     * @param WaterBankSource $source
     * @param string|null $description
     * @return WaterBankTransactionDTO
     */
    public function addWater(?User $user, float $amount, WaterBankSource $source, ?string $description = null): WaterBankTransactionDTO;

    /**
     * Use water from the bank.
     *
     * @param User|null $user
     * @param float $amount
     * @param int $savingsGoalId
     * @param WaterBankSource $source
     * @return WaterBankTransactionDTO
     */
    public function useWater(?User $user, float $amount, int $savingsGoalId, WaterBankSource $source = WaterBankSource::PLANT_WATERING): WaterBankTransactionDTO;

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
