<?php

namespace App\DTOs;

use App\Enums\WaterBankSource;
use App\Enums\WaterBankTransactionType;

final readonly class WaterBankTransactionDTO
{
    public function __construct(
        public int $id,
        public int $water_bank_id,
        public WaterBankTransactionType $type,
        public float $amount,
        public WaterBankSource $source,
        public ?string $description,
        public ?int $savings_goal_id,
        public float $balance_after,
        public string $created_at,
        public ?string $savings_goal_name = null
    ) {}

    /**
     * Get formatted amount with appropriate prefix.
     */
    public function getFormattedAmount(): string
    {
        $prefix = $this->type->amountPrefix();
        return $prefix . '$' . number_format($this->amount, 2);
    }

    /**
     * Get transaction icon.
     */
    public function getIcon(): string
    {
        return $this->source->icon();
    }

    /**
     * Get user-friendly description.
     */
    public function getDisplayDescription(): string
    {
        if ($this->description) {
            return $this->description;
        }

        if ($this->type === WaterBankTransactionType::WITHDRAWAL && $this->savings_goal_name) {
            return "Watered {$this->savings_goal_name}";
        }

        return $this->source->label();
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDate(): string
    {
        return date('M j, Y', strtotime($this->created_at));
    }
}
