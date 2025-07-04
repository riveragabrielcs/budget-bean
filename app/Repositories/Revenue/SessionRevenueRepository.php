<?php

namespace App\Repositories\Revenue;

use App\Data\RevenueData;
use App\DTOs\RevenueDTO;
use App\Models\User;
use Illuminate\Support\Carbon;

class SessionRevenueRepository implements RevenueRepositoryInterface
{
    /**
     * Get current month revenue from session.
     *
     * @param User|null $user (ignored for session storage)
     * @return RevenueDTO|null
     */
    public function getCurrentMonthRevenue(?User $user): ?RevenueDTO
    {
        $guestRevenue = session('guest_revenue');

        if (!$guestRevenue) {
            return null;
        }

        return $this->mapToDTO($guestRevenue);
    }

    /**
     * Create or update revenue in session.
     */
    public function createOrUpdate(?User $user, RevenueData $data): RevenueDTO
    {
        $now = now();

        $revenueArray = [
            'total_revenue' => $data->total_revenue,
            'calculation_method' => $data->calculation_method,
            'paycheck_amount' => $data->paycheck_amount,
            'paycheck_count' => $data->paycheck_count,
            'monthly_savings_goal' => $data->monthly_savings_goal,
            'revenue_month' => $now->month,
            'revenue_year' => $now->year,
            'created_at' => $now->toDateTimeString(),
        ];

        session(['guest_revenue' => $revenueArray]);

        return $this->mapToDTO($revenueArray);
    }

    /**
     * Delete revenue from session.
     */
    public function delete(?User $user): bool
    {
        if (session()->has('guest_revenue')) {
            session()->forget('guest_revenue');
            return true;
        }

        return false;
    }

    /**
     * Map array to DTO with computed attributes.
     */
    private function mapToDTO(array $raw): RevenueDTO
    {
        // Compute revenue_period (e.g., "March 2025")
        $revenuePeriod = Carbon::createFromDate(
            $raw['revenue_year'],
            $raw['revenue_month'],
            1
        )->format('F Y');

        // Compute source_description if paycheck method
        $sourceDescription = null;
        if ($raw['calculation_method'] === 'paycheck' &&
            isset($raw['paycheck_amount']) &&
            isset($raw['paycheck_count']) &&
            $raw['paycheck_amount'] &&
            $raw['paycheck_count']) {
            $sourceDescription = $raw['paycheck_count'] . ' paychecks × ' . number_format($raw['paycheck_amount'], 2);
        }

        return new RevenueDTO(
            id: null, // Session storage doesn't have IDs
            total_revenue: $raw['total_revenue'],
            calculation_method: $raw['calculation_method'],
            paycheck_amount: $raw['paycheck_amount'] ?? null,
            paycheck_count: $raw['paycheck_count'] ?? null,
            monthly_savings_goal: $raw['monthly_savings_goal'] ?? 0,
            revenue_month: $raw['revenue_month'],
            revenue_year: $raw['revenue_year'],
            revenue_period: $revenuePeriod,
            source_description: $sourceDescription,
            created_at: $raw['created_at'] ?? now()->toDateTimeString()
        );
    }
}
