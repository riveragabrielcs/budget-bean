<?php

namespace App\DTOs;

final readonly class BillDTO
{
    public function __construct(
        public int     $id,
        public string  $name,
        public float   $amount,
        public ?string $date,
        public ?string $description,
        public string $created_at
    )
    {
    }
}
