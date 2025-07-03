<?php

namespace App\Repositories\Expense;

use App\Data\ExpenseData;
use App\DTOs\ExpenseDTO;
use App\Models\MonthlyExpense;
use App\Models\User;
use Illuminate\Support\Collection;

class DBExpenseRepository implements ExpenseRepositoryInterface
{
    /**
     * Retrieve current month expenses for a user from the database.
     *
     * @param User|null $user
     * @return Collection<ExpenseDTO>
     * @throws \InvalidArgumentException
     */
    public function getCurrentMonthExpenses(?User $user): Collection
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        return $user->currentMonthExpenses()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($expense) => $this->mapToDTO($expense));
    }

    /**
     * Create a new expense for the user.
     */
    public function create(?User $user, ExpenseData $data): ExpenseDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        // If budget_month/year are provided, use them; otherwise use createForCurrentMonth
        if ($data->budget_month && $data->budget_year) {
            $expense = $user->monthlyExpenses()->create([
                'name' => $data->name,
                'amount' => $data->amount,
                'expense_date' => $data->expense_date,
                'description' => $data->description,
                'budget_month' => $data->budget_month,
                'budget_year' => $data->budget_year,
            ]);
        } else {
            $expense = MonthlyExpense::createForCurrentMonth([
                'user_id' => $user->id,
                'name' => $data->name,
                'amount' => $data->amount,
                'expense_date' => $data->expense_date,
                'description' => $data->description,
            ]);
        }

        return $this->mapToDTO($expense);
    }

    /**
     * Update an existing expense.
     */
    public function update(?User $user, int $expenseId, ExpenseData $data): ExpenseDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $expense = $user->monthlyExpenses()->findOrFail($expenseId);

        $updateData = [
            'name' => $data->name,
            'amount' => $data->amount,
            'expense_date' => $data->expense_date,
            'description' => $data->description,
        ];

        // Only update budget period if provided
        if ($data->budget_month && $data->budget_year) {
            $updateData['budget_month'] = $data->budget_month;
            $updateData['budget_year'] = $data->budget_year;
        }

        $expense->update($updateData);

        return $this->mapToDTO($expense->fresh());
    }

    /**
     * Delete an expense.
     */
    public function delete(?User $user, int $expenseId): bool
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $expense = $user->monthlyExpenses()->findOrFail($expenseId);
        return $expense->delete();
    }

    /**
     * Find a specific expense by ID for the user.
     */
    public function findForUser(?User $user, int $expenseId): ?ExpenseDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $expense = $user->monthlyExpenses()->find($expenseId);

        return $expense ? $this->mapToDTO($expense) : null;
    }

    /**
     * Map Eloquent model to DTO.
     */
    private function mapToDTO(MonthlyExpense $expense): ExpenseDTO
    {
        return new ExpenseDTO(
            id: $expense->id,
            name: $expense->name,
            amount: $expense->amount,
            expense_date: $expense->expense_date->toDateString(),
            formatted_expense_date: $expense->formatted_expense_date,
            description: $expense->description,
            budget_month: $expense->budget_month,
            budget_year: $expense->budget_year,
            budget_period: $expense->budget_period,
            created_at: $expense->created_at->toDateTimeString()
        );
    }
}
