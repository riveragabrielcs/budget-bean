<?php

namespace App\DTOs;

final readonly class ThisMonthDTO
{
    public function __construct(
        public array $savingsGoals,
        public array $monthlyExpenses,
        public array $expenseStats,
        public ?array $currentRevenue,
        public array $budgetData
    ) {}

    public function toArray(): array
    {
        return [
            'savingsGoals' => $this->savingsGoals,
            'monthlyExpenses' => $this->monthlyExpenses,
            'expenseStats' => $this->expenseStats,
            'currentRevenue' => $this->currentRevenue,
            'budgetData' => $this->budgetData,
        ];
    }
}
