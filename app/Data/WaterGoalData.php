<?php

namespace App\Data;

use App\Enums\FundingSource;

final readonly class WaterGoalData
{
    public function __construct(
        public float $amount,
        public FundingSource $source,
    ) {
    }
}
