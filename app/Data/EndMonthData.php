<?php

namespace App\Data;

final readonly class EndMonthData
{
    public function __construct(
        public int $month,
        public int $year,
        public bool $override_existing = false,
    ) {
    }
}
