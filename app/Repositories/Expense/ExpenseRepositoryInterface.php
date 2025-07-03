<?php

namespace App\Repositories\Expense;

use App\Data\ExpenseData;
use App\DTOs\ExpenseDTO;
use App\Models\User;
use Illuminate\Support\Collection;

interface ExpenseRepositoryInterface
{
    /**
     * Retrieve current month expenses for a user.
     *
     * @param User|null $user
     * @return Collection<ExpenseDTO>
     */
    public function getCurrentMonthExpenses(?User $user): Collection;

    /**
     * Create a new expense for the user.
     *
     * @param User|null $user
     * @param ExpenseData $data
     * @return ExpenseDTO
     */
    public function create(?User $user, ExpenseData $data): ExpenseDTO;

    /**
     * Update an existing expense.
     *
     * @param User|null $user
     * @param int $expenseId
     * @param ExpenseData $data
     * @return ExpenseDTO
     */
    public function update(?User $user, int $expenseId, ExpenseData $data): ExpenseDTO;

    /**
     * Delete an expense.
     *
     * @param User|null $user
     * @param int $expenseId
     * @return bool
     */
    public function delete(?User $user, int $expenseId): bool;

    /**
     * Find a specific expense by ID for the user.
     *
     * @param User|null $user
     * @param int $expenseId
     * @return ExpenseDTO|null
     */
    public function findForUser(?User $user, int $expenseId): ?ExpenseDTO;
}
