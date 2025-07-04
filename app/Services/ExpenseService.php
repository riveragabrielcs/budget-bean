<?php

namespace App\Services;

use App\Data\ExpenseData;
use App\DTOs\ExpenseDTO;
use App\Exceptions\ExpenseNotFoundException;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\User;
use App\Repositories\Expense\ExpenseRepositoryInterface;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expensesRepo
    ) {}

    /**
     * Create a new expense for the current month.
     */
    public function createExpense(?User $user, StoreExpenseRequest $request): ExpenseDTO
    {
        $validated = $request->validated();

        $expenseData = new ExpenseData(
            name: $validated['name'],
            amount: $validated['amount'],
            expense_date: $validated['expense_date'] ?? now()->toDateString(),
            description: $validated['description'] ?? null,
            budget_month: now()->month,
            budget_year: now()->year
        );

        return $this->expensesRepo->create($user, $expenseData);
    }

    /**
     * Update an existing expense.
     *
     * @throws ExpenseNotFoundException
     */
    public function updateExpense(?User $user, int $expenseId, UpdateExpenseRequest $request): ExpenseDTO
    {
        $validated = $request->validated();

        // Get existing expense to preserve unchanged fields
        $existingExpense = $this->expensesRepo->findForUser($user, $expenseId);

        if (!$existingExpense) {
            throw new ExpenseNotFoundException("Expense with ID {$expenseId} not found");
        }

        $expenseData = new ExpenseData(
            name: $validated['name'] ?? $existingExpense->name,
            amount: $validated['amount'] ?? $existingExpense->amount,
            expense_date: $validated['expense_date'] ?? $existingExpense->expense_date,
            description: $validated['description'] ?? $existingExpense->description,
            budget_month: $existingExpense->budget_month,
            budget_year: $existingExpense->budget_year
        );

        return $this->expensesRepo->update($user, $expenseId, $expenseData);
    }

    /**
     * Delete an expense.
     *
     * @throws ExpenseNotFoundException
     */
    public function deleteExpense(?User $user, int $expenseId): array
    {
        // Get expense info before deletion for response message
        $expense = $this->expensesRepo->findForUser($user, $expenseId);

        if (!$expense) {
            throw new ExpenseNotFoundException("Expense with ID {$expenseId} not found");
        }

        $expenseName = $expense->name;
        $deleted = $this->expensesRepo->delete($user, $expenseId);

        if (!$deleted) {
            throw new \RuntimeException('Failed to delete expense');
        }

        return [
            'name' => $expenseName,
            'deleted' => true
        ];
    }

    /**
     * Get current month expenses for user.
     */
    public function getCurrentMonthExpenses(?User $user): Collection
    {
        return $this->expensesRepo->getCurrentMonthExpenses($user);
    }

    /**
     * Find a specific expense for user.
     */
    public function findExpenseForUser(?User $user, int $expenseId): ?ExpenseDTO
    {
        return $this->expensesRepo->findForUser($user, $expenseId);
    }

    /**
     * Format expense for API response.
     */
    public function formatForApiResponse(ExpenseDTO $expense): array
    {
        return [
            'id' => $expense->id,
            'name' => $expense->name,
            'amount' => $expense->amount,
            'formatted_expense_date' => $expense->formatted_expense_date,
            'description' => $expense->description,
            'type' => 'one_time_expense',
            'created_at' => $expense->created_at,
        ];
    }
}
