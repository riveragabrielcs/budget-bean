<?php

namespace App\Models;

use App\Enums\WaterBankSourceEnum;
use App\Enums\WaterBankTransactionTypeEnum;
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
            'type' => WaterBankTransactionTypeEnum::class,
            'source' => WaterBankSourceEnum::class,
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
        $prefix = $this->type->amountPrefix();
        return $prefix . '$' . number_format($this->amount, 2);
    }

    /**
     * Get transaction icon based on type and source.
     */
    public function getIconAttribute(): string
    {
        return $this->source->icon();
    }

    /**
     * Get user-friendly description.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        if ($this->type === WaterBankTransactionTypeEnum::WITHDRAWAL && $this->savingsGoal) {
            return "Watered {$this->savingsGoal->name}";
        }

        return $this->source->label();
    }
}
