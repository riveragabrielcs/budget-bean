<?php

namespace App\Services;

use App\Data\RevenueData;
use App\DTOs\RevenueDTO;
use App\Http\Requests\StoreRevenueRequest;
use App\Models\User;
use App\Repositories\Revenue\RevenueRepositoryInterface;

class RevenueService
{
    public function __construct(
        private RevenueRepositoryInterface $revenuesRepo
    ) {}

    /**
     * Store or update revenue for the current month.
     */
    public function storeRevenue(?User $user, StoreRevenueRequest $request): RevenueDTO
    {
        $validated = $request->validated();

        // Preserve existing savings goal when updating revenue
        $existingRevenue = $this->revenuesRepo->getCurrentMonthRevenue($user);
        $savingsGoal = $existingRevenue ? $existingRevenue->monthly_savings_goal : 0;

        $revenueData = new RevenueData(
            total_revenue: $validated['total_revenue'],
            calculation_method: $validated['calculation_method'],
            paycheck_amount: $validated['paycheck_amount'] ?? null,
            paycheck_count: $validated['paycheck_count'] ?? null,
            monthly_savings_goal: $savingsGoal
        );

        return $this->revenuesRepo->createOrUpdate($user, $revenueData);
    }

    /**
     * Update savings goal for current month.
     */
    public function updateSavingsGoal(?User $user, float $savingsGoal): RevenueDTO
    {
        $currentRevenue = $this->revenuesRepo->getCurrentMonthRevenue($user);

        if ($currentRevenue) {
            // Update existing revenue with new savings goal
            $revenueData = new RevenueData(
                total_revenue: $currentRevenue->total_revenue,
                calculation_method: $currentRevenue->calculation_method,
                paycheck_amount: $currentRevenue->paycheck_amount,
                paycheck_count: $currentRevenue->paycheck_count,
                monthly_savings_goal: $savingsGoal
            );
        } else {
            // Create new record with just savings goal
            $revenueData = new RevenueData(
                total_revenue: 0,
                calculation_method: 'custom',
                paycheck_amount: null,
                paycheck_count: null,
                monthly_savings_goal: $savingsGoal
            );
        }

        return $this->revenuesRepo->createOrUpdate($user, $revenueData);
    }

    /**
     * Get current month's revenue.
     */
    public function getCurrentRevenue(?User $user): ?RevenueDTO
    {
        return $this->revenuesRepo->getCurrentMonthRevenue($user);
    }

    /**
     * Delete current month's revenue.
     */
    public function deleteRevenue(?User $user): bool
    {
        return $this->revenuesRepo->delete($user);
    }
}
