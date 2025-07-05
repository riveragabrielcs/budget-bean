<?php

namespace App\Data;

use App\Enums\FundingSource;

final readonly class AddSavingsData
{
    public function __construct(
        public float         $amount,
        public FundingSource $source,
    ) {
    }
}
