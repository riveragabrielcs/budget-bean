<?php

namespace App\Data;

final readonly class ExpenseData
{
    public function __construct(
        public string  $name,
        public float   $amount,
        public string  $expense_date,
        public ?string $description,
        public ?int    $budget_month = null,  // Optional - should default to current month
        public ?int    $budget_year = null   // Optional - should default to current year
    ) {
    }
}
