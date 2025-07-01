<?php

namespace App\DTOs;

readonly class BillDTO
{
    public function __construct(
        public int     $id,
        public string  $name,
        public float   $amount,
        public ?string $date,
        public string  $type,           // “recurring” vs “one_time”
        public ?string $description,
        public string $created_at
    )
    {
    }
}
