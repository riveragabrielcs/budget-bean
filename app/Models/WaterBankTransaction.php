<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterBankTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'amount',
        'source',
        'description',
        'savings_goal_id',
        'balance_after',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    /**
     * Get the water bank that owns the transaction.
     */
    public function waterBank(): BelongsTo
    {
        return $this->belongsTo(WaterBank::class);
    }

    /**
     * Get the savings goal if this was a plant watering transaction.
     */
    public function savingsGoal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class);
    }

    /**
     * Get formatted amount with appropriate icon.
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'deposit' ? '+' : '-';
        return $prefix . '$' . number_format($this->amount, 2);
    }

    /**
     * Get transaction icon based on type and source.
     */
    public function getIconAttribute(): string
    {
        if ($this->type === 'deposit') {
            return match($this->source) {
                'month_end' => '📅',
                'manual_add' => '➕',
                'rollover' => '🔄',
                default => '💧'
            };
        }

        return match($this->source) {
            'plant_watering' => '🌱',
            'manual_withdrawal' => '➖',
            default => '💸'
        };
    }

    /**
     * Get user-friendly description.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        if ($this->type === 'deposit') {
            return match($this->source) {
                'month_end' => 'Month-end water deposit',
                'manual_add' => 'Manual water addition',
                'rollover' => 'Water carried from previous month',
                default => 'Water deposit'
            };
        }

        if ($this->type === 'withdrawal' && $this->savingsGoal) {
            return "Watered {$this->savingsGoal->name}";
        }

        return 'Water withdrawal';
    }
}
