<?php

namespace App\Data;

final readonly class SavingsGoalData
{
    public function __construct(
        public string  $name,
        public ?string $description,
        public float   $target_amount,
        public float   $current_amount = 0.0,
    ) {
    }
}
