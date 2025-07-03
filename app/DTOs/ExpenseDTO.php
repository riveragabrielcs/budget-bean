<?php

namespace App\DTOs;

final readonly class ExpenseDTO
{
    public function __construct(
        public int     $id,
        public string  $name,
        public float   $amount,
        public string  $expense_date,
        public string  $formatted_expense_date,
        public ?string $description,
        public int     $budget_month,
        public int     $budget_year,
        public string  $budget_period,              // e.g., "March 2025"
        public string  $created_at
    )
    {
    }
}
