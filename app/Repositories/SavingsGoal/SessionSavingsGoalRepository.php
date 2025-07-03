<?php

namespace App\Repositories\SavingsGoal;

use App\DTOs\SavingsGoalDTO;
use App\Models\User;
use App\Services\PlantGrowthService;
use Illuminate\Support\Collection;

class SessionSavingsGoalRepository implements SavingsGoalRepositoryInterface
{
    public function __construct(
        private PlantGrowthService $plantGrowthService
    ) {}
    /**
     * Retrieve active savings goals from the session.
     *
     * @param User|null $user (ignored for session storage)
     * @return Collection<SavingsGoalDTO>
     */
    public function getActiveSavingsGoals(?User $user): Collection
    {
        return collect(session('guest_savings_goals', []))
            ->filter(fn($goal) => !$goal['is_completed'])
            ->sortByDesc('created_at')
            ->values()
            ->map(fn($raw) => $this->mapToDTO($raw));
    }

    /**
     * Find a specific savings goal by ID.
     */
    public function findForUser(?User $user, int $goalId): ?SavingsGoalDTO
    {
        $goals = session('guest_savings_goals', []);
        $goal = collect($goals)->first(fn($goal) => $goal['id'] === $goalId);

        return $goal ? $this->mapToDTO($goal) : null;
    }

    /**
     * Create a new savings goal in session.
     */
    public function create(?User $user, array $data): SavingsGoalDTO
    {
        $goals = session('guest_savings_goals', []);

        $newGoal = [
            'id' => $this->getNextId($goals),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'target_amount' => $data['target_amount'],
            'current_amount' => $data['current_amount'] ?? 0,
            'is_completed' => false,
            'completed_at' => null,
            'created_at' => now()->toDateTimeString(),
        ];

        $goals[] = $newGoal;
        session(['guest_savings_goals' => $goals]);

        return $this->mapToDTO($newGoal);
    }

    /**
     * Update an existing savings goal.
     */
    public function update(?User $user, int $goalId, array $data): SavingsGoalDTO
    {
        $goals = session('guest_savings_goals', []);
        $index = collect($goals)->search(fn($goal) => $goal['id'] === $goalId);

        if ($index === false) {
            throw new \Exception("Savings goal not found");
        }

        $goals[$index] = array_merge($goals[$index], [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'target_amount' => $data['target_amount'],
            'current_amount' => $data['current_amount'] ?? $goals[$index]['current_amount'],
        ]);

        session(['guest_savings_goals' => $goals]);

        return $this->mapToDTO($goals[$index]);
    }

    /**
     * Delete a savings goal.
     */
    public function delete(?User $user, int $goalId): bool
    {
        $goals = session('guest_savings_goals', []);
        $filtered = collect($goals)->reject(fn($goal) => $goal['id'] === $goalId);

        if ($filtered->count() === count($goals)) {
            return false; // Goal not found
        }

        session(['guest_savings_goals' => $filtered->values()->toArray()]);
        return true;
    }

    /**
     * Add amount to a savings goal.
     */
    public function addSavings(?User $user, int $goalId, float $amount): SavingsGoalDTO
    {
        $goals = session('guest_savings_goals', []);
        $index = collect($goals)->search(fn($goal) => $goal['id'] === $goalId);

        if ($index === false) {
            throw new \Exception("Savings goal not found");
        }

        $goal = $goals[$index];
        $newAmount = $goal['current_amount'] + $amount;

        $goals[$index]['current_amount'] = $newAmount;

        // Auto-complete if target reached
        if ($newAmount >= $goal['target_amount'] && !$goal['is_completed']) {
            $goals[$index]['is_completed'] = true;
            $goals[$index]['completed_at'] = now()->toDateTimeString();
            $goals[$index]['current_amount'] = $goal['target_amount'];
        }

        session(['guest_savings_goals' => $goals]);

        return $this->mapToDTO($goals[$index]);
    }

    /**
     * Get next available ID for session goals.
     */
    private function getNextId(array $goals): int
    {
        if (empty($goals)) {
            return 1;
        }

        return collect($goals)->max('id') + 1;
    }

    /**
     * Map array to DTO with calculated properties.
     */
    private function mapToDTO(array $raw): SavingsGoalDTO
    {
        return new SavingsGoalDTO(
            id: $raw['id'],
            name: $raw['name'],
            description: $raw['description'],
            target_amount: $raw['target_amount'],
            current_amount: $raw['current_amount'],
            is_completed: $raw['is_completed'],
            created_at: $raw['created_at'] ?? now()->toDateTimeString()
        );
    }

}
