<?php

namespace App\Data;

final readonly class AddManualWaterData
{
    public function __construct(
        public float $amount,
        public ?string $description = null,
    ) {
    }
}
