<?php

namespace App\DTOs;

final readonly class PlantStatusDTO
{
    public function __construct(
        public string $plantEmoji,
        public string $growthStage,
    ) {}

    /**
     * Convert to array for easy serialization.
     */
    public function toArray(): array
    {
        return [
            'plant_emoji' => $this->plantEmoji,
            'growth_stage' => $this->growthStage,
        ];
    }
}
