<?php

namespace App\Services;

use App\DTOs\PlantStatusDTO;

class PlantGrowthService
{
    /**
     * Get plant emoji based on progress percentage.
     *
     * @param float $progressPercentage
     * @return string
     */
    public function getPlantEmoji(float $progressPercentage): string
    {
        if ($progressPercentage >= 100) {
            return '🌳'; // Full grown tree
        } elseif ($progressPercentage >= 75) {
            return '🌿'; // Flourishing plant
        } elseif ($progressPercentage >= 50) {
            return '🌱'; // Growing sprout
        } elseif ($progressPercentage >= 25) {
            return '🌾'; // Young plant
        } else {
            return '🌰'; // Seed
        }
    }

    /**
     * Get growth stage text based on progress percentage.
     *
     * @param float $progressPercentage
     * @return string
     */
    public function getGrowthStage(float $progressPercentage): string
    {
        if ($progressPercentage >= 100) {
            return 'Fully Grown!';
        } elseif ($progressPercentage >= 75) {
            return 'Almost There!';
        } elseif ($progressPercentage >= 50) {
            return 'Growing Strong';
        } elseif ($progressPercentage >= 25) {
            return 'Taking Root';
        } else {
            return 'Just Planted';
        }
    }

    /**
     * Get both plant emoji and growth stage for a given progress.
     *
     * @param float $progressPercentage
     * @return PlantStatusDTO
     *
     */
    public function getPlantStatus(float $progressPercentage): PlantStatusDTO
    {
        return new PlantStatusDTO(
            plantEmoji: $this->getPlantEmoji($progressPercentage),
            growthStage: $this->getGrowthStage($progressPercentage),
        );
    }
}
