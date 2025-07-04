<?php

namespace App\Repositories\Revenue;

use App\Data\RevenueData;
use App\DTOs\RevenueDTO;
use App\Models\User;

interface RevenueRepositoryInterface
{
    /**
     * Get current month revenue for a user.
     *
     * @param User|null $user
     * @return RevenueDTO|null
     */
    public function getCurrentMonthRevenue(?User $user): ?RevenueDTO;

    /**
     * Create or update revenue for the current month.
     *
     * @param User|null $user
     * @param RevenueData $data
     * @return RevenueDTO
     */
    public function createOrUpdate(?User $user, RevenueData $data): RevenueDTO;

    /**
     * Delete current month revenue.
     *
     * @param User|null $user
     * @return bool
     */
    public function delete(?User $user): bool;
}
