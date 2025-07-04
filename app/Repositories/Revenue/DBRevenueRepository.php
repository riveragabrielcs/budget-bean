<?php

namespace App\Repositories\Revenue;

use App\Data\RevenueData;
use App\DTOs\RevenueDTO;
use App\Models\MonthlyRevenue;
use App\Models\User;

class DBRevenueRepository implements RevenueRepositoryInterface
{
    /**
     * Get current month revenue for a user from the database.
     *
     * @param User|null $user
     * @return RevenueDTO|null
     * @throws \InvalidArgumentException
     */
    public function getCurrentMonthRevenue(?User $user): ?RevenueDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $revenue = $user->currentMonthRevenue();

        return $revenue ? $this->mapToDTO($revenue) : null;
    }

    /**
     * Create or update revenue for the current month.
     */
    public function createOrUpdate(?User $user, RevenueData $data): RevenueDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $now = now();

        $revenue = $user->monthlyRevenues()->updateOrCreate(
            [
                'revenue_month' => $now->month,
                'revenue_year' => $now->year,
            ],
            [
                'total_revenue' => $data->total_revenue,
                'calculation_method' => $data->calculation_method,
                'paycheck_amount' => $data->paycheck_amount,
                'paycheck_count' => $data->paycheck_count,
                'monthly_savings_goal' => $data->monthly_savings_goal,
                'revenue_month' => $now->month,
                'revenue_year' => $now->year,
            ]
        );

        return $this->mapToDTO($revenue->fresh());
    }

    /**
     * Delete current month revenue.
     */
    public function delete(?User $user): bool
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $revenue = $user->currentMonthRevenue();

        return $revenue ? $revenue->delete() : false;
    }

    /**
     * Map Eloquent model to DTO.
     */
    private function mapToDTO(MonthlyRevenue $revenue): RevenueDTO
    {
        return new RevenueDTO(
            id: $revenue->id,
            total_revenue: $revenue->total_revenue,
            calculation_method: $revenue->calculation_method,
            paycheck_amount: $revenue->paycheck_amount,
            paycheck_count: $revenue->paycheck_count,
            monthly_savings_goal: $revenue->monthly_savings_goal,
            revenue_month: $revenue->revenue_month,
            revenue_year: $revenue->revenue_year,
            revenue_period: $revenue->revenue_period,           // Computed attribute
            source_description: $revenue->source_description,   // Computed attribute
            created_at: $revenue->created_at->toDateTimeString()
        );
    }
}
