<?php

namespace App\Data;

use App\Enums\ExpenseType;

final readonly class BillData
{
    public function __construct(
        public string  $name,
        public float   $amount,
        public ?string $date,
        public ?string $description,
    ) {
    }
}
