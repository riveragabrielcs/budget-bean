<?php

namespace App\Data;

use App\Enums\FundingSourceEnum;

final readonly class WaterGoalData
{
    public function __construct(
        public float             $amount,
        public FundingSourceEnum $source,
    ) {
    }
}
