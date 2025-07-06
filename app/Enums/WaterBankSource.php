<?php

namespace App\Enums;

enum WaterBankSource: string
{
    case MONTH_END = 'month_end';
    case MANUAL_ADD = 'manual_add';
    case ROLLOVER = 'rollover';
    case PLANT_WATERING = 'plant_watering';
    case MANUAL_WITHDRAWAL = 'manual_withdrawal';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::MONTH_END => 'Month-end deposit',
            self::MANUAL_ADD => 'Manual addition',
            self::ROLLOVER => 'Rollover from previous month',
            self::PLANT_WATERING => 'Plant watering',
            self::MANUAL_WITHDRAWAL => 'Manual withdrawal',
        };
    }

    /**
     * Get icon for source.
     */
    public function icon(): string
    {
        return match($this) {
            self::MONTH_END => '📅',
            self::MANUAL_ADD => '➕',
            self::ROLLOVER => '🔄',
            self::PLANT_WATERING => '🌱',
            self::MANUAL_WITHDRAWAL => '➖',
        };
    }

    /**
     * Check if this source is a deposit.
     */
    public function isDeposit(): bool
    {
        return match($this) {
            self::MONTH_END, self::MANUAL_ADD, self::ROLLOVER => true,
            self::PLANT_WATERING, self::MANUAL_WITHDRAWAL => false,
        };
    }
}
