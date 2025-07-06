<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

final readonly class WaterBankDTO
{
    public function __construct(
        public int $id,
        public int $user_id,
        public float $balance,
        public string $created_at,
        public string $updated_at,
        public ?Collection $recent_transactions = null
    ) {}

    /**
     * Get formatted balance.
     */
    public function getFormattedBalance(): string
    {
        return '$' . number_format($this->balance, 2);
    }

    /**
     * Check if user has enough water for amount.
     */
    public function hasEnoughWater(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get balance as a float for calculations.
     */
    public function getBalance(): float
    {
        return $this->balance;
    }
}
