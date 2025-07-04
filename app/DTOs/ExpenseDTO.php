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

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'formatted_expense_date' => $this->formatted_expense_date,
            'description' => $this->description,
            'budget_month' => $this->budget_month,
            'budget_year' => $this->budget_year,
            'budget_period' => $this->budget_period,
            'created_at' => $this->created_at,
        ];
    }
}
