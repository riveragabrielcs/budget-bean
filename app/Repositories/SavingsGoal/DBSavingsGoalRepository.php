<?php

namespace App\Repositories\SavingsGoal;

use App\DTOs\SavingsGoalDTO;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Support\Collection;

class DBSavingsGoalRepository implements SavingsGoalRepositoryInterface
{
    /**
     * Retrieve all savings goals for a user from the database.
     *
     * @param User|null $user
     * @return Collection<SavingsGoalDTO>
     * @throws \InvalidArgumentException
     */
    public function getAllSavingsGoals(?User $user): Collection
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        return $user->savingsGoals()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($goal) => $this->mapToDTO($goal));
    }

    /**
     * Retrieve active savings goals for a user from the database.
     *
     * @param User|null $user
     * @return Collection<SavingsGoalDTO>
     * @throws \InvalidArgumentException
     */
    public function getActiveSavingsGoals(?User $user): Collection
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        return $user->activeSavingsGoals()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($goal) => $this->mapToDTO($goal));
    }

    /**
     * Find a specific savings goal by ID for the user.
     */
    public function findForUser(?User $user, int $goalId): ?SavingsGoalDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $goal = $user->savingsGoals()->find($goalId);

        return $goal ? $this->mapToDTO($goal) : null;
    }

    /**
     * Create a new savings goal for the user.
     */
    public function create(?User $user, array $data): SavingsGoalDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $goal = $user->savingsGoals()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'target_amount' => $data['target_amount'],
            'current_amount' => $data['current_amount'] ?? 0,
            'is_completed' => false, // Explicitly set to false to ensure new goals are not completed
        ]);

        return $this->mapToDTO($goal);
    }

    /**
     * Update an existing savings goal.
     */
    public function update(?User $user, int $goalId, array $data): SavingsGoalDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $goal = $user->savingsGoals()->findOrFail($goalId);

        $goal->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'target_amount' => $data['target_amount'],
            'current_amount' => $data['current_amount'] ?? $goal->current_amount,
        ]);

        return $this->mapToDTO($goal->fresh());
    }

    /**
     * Delete a savings goal.
     */
    public function delete(?User $user, int $goalId): bool
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $goal = $user->savingsGoals()->findOrFail($goalId);
        return $goal->delete();
    }

    /**
     * Add amount to a savings goal.
     */
    public function addSavings(?User $user, int $goalId, float $amount): SavingsGoalDTO
    {
        if (!$user) {
            throw new \InvalidArgumentException('User is required for database operations');
        }

        $goal = $user->savingsGoals()->findOrFail($goalId);
        $goal->addSavings($amount);

        return $this->mapToDTO($goal->fresh());
    }

    /**
     * Map Eloquent model to DTO.
     */
    private function mapToDTO(SavingsGoal $goal): SavingsGoalDTO
    {
        return new SavingsGoalDTO(
            id: $goal->id,
            name: $goal->name,
            description: $goal->description,
            target_amount: $goal->target_amount,
            current_amount: $goal->current_amount,
            is_completed: (bool) $goal->is_completed, // Cast to bool to be safe
            created_at: $goal->created_at->toDateTimeString()
        );
    }
}
