<?php

namespace App\Enums;

enum FundingSourceEnum: string
{
    case WATER_BANK = 'water_bank';
    case OTHER = 'other';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::WATER_BANK => 'Water Bank',
            self::OTHER => 'Other Funds',
        };
    }

    /**
     * Get icon for funding source.
     */
    public function icon(): string
    {
        return match($this) {
            self::WATER_BANK => '💧',
            self::OTHER => '💰',
        };
    }
}
