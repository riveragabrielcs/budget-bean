<?php

namespace App\Enums;

enum WaterBankTransactionTypeEnum: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::DEPOSIT => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
        };
    }

    /**
     * Get formatted prefix for amounts.
     */
    public function amountPrefix(): string
    {
        return match($this) {
            self::DEPOSIT => '+',
            self::WITHDRAWAL => '-',
        };
    }
}
