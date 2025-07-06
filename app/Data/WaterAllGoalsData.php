<?php

namespace App\Data;

use App\Enums\FundingSourceEnum;

final readonly class WaterAllGoalsData
{
    public function __construct(
        public float             $total_amount,
        public FundingSourceEnum $source,
    ) {
    }
}
