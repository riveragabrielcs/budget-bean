<?php

namespace App\DTOs;

final readonly class SavingsGoalDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public float $target_amount,
        public float $current_amount,
        public bool $is_completed,
        public string $created_at
    ) {}

    /**
     * Calculate progress percentage.
     */
    public function getProgressPercentage(): float
    {
        if ($this->target_amount == 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->target_amount) * 100;
        return min($percentage, 100);
    }

    /**
     * Get remaining amount.
     */
    public function getRemainingAmount(): float
    {
        return max($this->target_amount - $this->current_amount, 0);
    }

    /**
     * Check if the goal has been reached.
     */
    public function isReached(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }
}
