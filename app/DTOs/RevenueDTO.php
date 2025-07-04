<?php

namespace App\DTOs;

final readonly class RevenueDTO
{
    public function __construct(
        public ?int $id,
        public float $total_revenue,
        public string $calculation_method,
        public ?float $paycheck_amount,
        public ?int $paycheck_count,
        public float $monthly_savings_goal,
        public int $revenue_month,
        public int $revenue_year,
        public string $revenue_period,        // Computed: "March 2025"
        public ?string $source_description,   // Computed: "4 paychecks × 1,250.00"
        public ?string $created_at = null
    ) {}

    /**
     * Calculate net income after savings goal.
     */
    public function getNetIncome(): float
    {
        return max($this->total_revenue - $this->monthly_savings_goal, 0);
    }

    /**
     * Convert to array for frontend compatibility.
     */
    public function toArray(): array
    {
        return [
            'total_revenue' => $this->total_revenue,
            'calculation_method' => $this->calculation_method,
            'paycheck_amount' => $this->paycheck_amount,
            'paycheck_count' => $this->paycheck_count,
            'monthly_savings_goal' => $this->monthly_savings_goal,
            'source_description' => $this->source_description,
            'revenue_period' => $this->revenue_period,
        ];
    }
}
