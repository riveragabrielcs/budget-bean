<?php

namespace App\Data;

final readonly class RevenueData
{
    public function __construct(
        public float $total_revenue,
        public string $calculation_method,
        public ?float $paycheck_amount = null,
        public ?int $paycheck_count = null,
        public float $monthly_savings_goal = 0,
    ) {}
}
