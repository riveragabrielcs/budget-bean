<?php

namespace App\Repositories\Expense;

use App\Data\ExpenseData;
use App\DTOs\ExpenseDTO;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class SessionExpenseRepository implements ExpenseRepositoryInterface
{
    /**
     * Retrieve current month expenses from the session.
     *
     * @param User|null $user (ignored for session storage)
     * @return Collection<ExpenseDTO>
     */
    public function getCurrentMonthExpenses(?User $user): Collection
    {
        return collect(session('guest_one_time_expenses', []))
            ->sortByDesc('created_at')
            ->values()
            ->map(fn($raw) => $this->mapToDTO($raw));
    }

    /**
     * Create a new expense in session.
     */
    public function create(?User $user, ExpenseData $data): ExpenseDTO
    {
        $expenses = session('guest_one_time_expenses', []);
        $now = now();

        // For session storage, default to current month/year
        $budgetMonth = $data->budget_month ?? $now->month;
        $budgetYear = $data->budget_year ?? $now->year;

        $newExpense = [
            'id' => $this->getNextId($expenses),
            'name' => $data->name,
            'amount' => $data->amount,
            'expense_date' => $data->expense_date,
            'formatted_expense_date' => $now->parse($data->expense_date)->format('M j, Y'),
            'description' => $data->description,
            'budget_month' => $budgetMonth,
            'budget_year' => $budgetYear,
            'budget_period' => Carbon::createFromDate($budgetYear, $budgetMonth, 1)->format('F Y'),
            'created_at' => $now->toDateTimeString(),
        ];

        $expenses[] = $newExpense;
        session(['guest_one_time_expenses' => $expenses]);

        return $this->mapToDTO($newExpense);
    }

    /**
     * Update an existing expense.
     */
    public function update(?User $user, int $expenseId, ExpenseData $data): ExpenseDTO
    {
        $expenses = session('guest_one_time_expenses', []);
        $index = collect($expenses)->search(fn($expense) => $expense['id'] === $expenseId);

        if ($index === false) {
            throw new \Exception("Expense not found");
        }

        $now = now();
        $budgetMonth = $data->budget_month ?? $expenses[$index]['budget_month'];
        $budgetYear = $data->budget_year ?? $expenses[$index]['budget_year'];

        $expenses[$index] = array_merge($expenses[$index], [
            'name' => $data->name,
            'amount' => $data->amount,
            'expense_date' => $data->expense_date,
            'formatted_expense_date' => $now->parse($data->expense_date)->format('M j, Y'),
            'description' => $data->description,
            'budget_month' => $budgetMonth,
            'budget_year' => $budgetYear,
            'budget_period' => Carbon::createFromDate($budgetYear, $budgetMonth, 1)->format('F Y'),
        ]);

        session(['guest_one_time_expenses' => $expenses]);

        return $this->mapToDTO($expenses[$index]);
    }

    /**
     * Delete an expense.
     */
    public function delete(?User $user, int $expenseId): bool
    {
        $expenses = session('guest_one_time_expenses', []);
        $filtered = collect($expenses)->reject(fn($expense) => $expense['id'] === $expenseId);

        if ($filtered->count() === count($expenses)) {
            return false; // Expense not found
        }

        session(['guest_one_time_expenses' => $filtered->values()->toArray()]);
        return true;
    }

    /**
     * Find a specific expense by ID.
     */
    public function findForUser(?User $user, int $expenseId): ?ExpenseDTO
    {
        $expenses = session('guest_one_time_expenses', []);
        $expense = collect($expenses)->first(fn($expense) => $expense['id'] === $expenseId);

        return $expense ? $this->mapToDTO($expense) : null;
    }

    /**
     * Get next available ID for session expenses.
     */
    private function getNextId(array $expenses): int
    {
        if (empty($expenses)) {
            return 1;
        }

        return collect($expenses)->max('id') + 1;
    }

    /**
     * Map array to DTO.
     */
    private function mapToDTO(array $raw): ExpenseDTO
    {
        return new ExpenseDTO(
            id: $raw['id'],
            name: $raw['name'],
            amount: $raw['amount'],
            expense_date: $raw['expense_date'],
            formatted_expense_date: $raw['formatted_expense_date'],
            description: $raw['description'] ?? null,
            budget_month: $raw['budget_month'],
            budget_year: $raw['budget_year'],
            budget_period: $raw['budget_period'],
            created_at: $raw['created_at'] ?? now()->toDateTimeString(),
        );
    }
}
