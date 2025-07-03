<?php

namespace App\Repositories\SavingsGoal;

use App\DTOs\SavingsGoalDTO;
use App\Models\User;
use Illuminate\Support\Collection;

interface SavingsGoalRepositoryInterface
{
    /**
     * Retrieve active savings goals for a user.
     *
     * @param User|null $user
     * @return Collection<SavingsGoalDTO>
     */
    public function getActiveSavingsGoals(?User $user): Collection;

    /**
     * Find a specific savings goal by ID for the user.
     *
     * @param User|null $user
     * @param int $goalId
     * @return SavingsGoalDTO|null
     */
    public function findForUser(?User $user, int $goalId): ?SavingsGoalDTO;

    /**
     * Create a new savings goal for the user.
     *
     * @param User|null $user
     * @param array $data
     * @return SavingsGoalDTO
     */
    public function create(?User $user, array $data): SavingsGoalDTO;

    /**
     * Update an existing savings goal.
     *
     * @param User|null $user
     * @param int $goalId
     * @param array $data
     * @return SavingsGoalDTO
     */
    public function update(?User $user, int $goalId, array $data): SavingsGoalDTO;

    /**
     * Delete a savings goal.
     *
     * @param User|null $user
     * @param int $goalId
     * @return bool
     */
    public function delete(?User $user, int $goalId): bool;

    /**
     * Add amount to a savings goal.
     *
     * @param User|null $user
     * @param int $goalId
     * @param float $amount
     * @return SavingsGoalDTO
     */
    public function addSavings(?User $user, int $goalId, float $amount): SavingsGoalDTO;
}
