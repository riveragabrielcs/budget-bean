<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsGoal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'target_amount',
        'current_amount',
        'is_completed',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the savings goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the progress percentage of the savings goal.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount == 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->target_amount) * 100;
        return min($percentage, 100); // Cap at 100%
    }

    /**
     * Get the remaining amount needed to reach the goal.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max($this->target_amount - $this->current_amount, 0);
    }

    /**
     * Check if the goal has been reached.
     */
    public function getIsReachedAttribute(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Get a plant emoji based on progress.
     */
    public function getPlantEmojiAttribute(): string
    {
        $progress = $this->progress_percentage;

        if ($progress >= 100) {
            return '🌳'; // Full grown tree
        } elseif ($progress >= 75) {
            return '🌿'; // Flourishing plant
        } elseif ($progress >= 50) {
            return '🌱'; // Growing sprout
        } elseif ($progress >= 25) {
            return '🌾'; // Young plant
        } else {
            return '🌰'; // Seed
        }
    }

    /**
     * Get growth stage text based on progress.
     */
    public function getGrowthStageAttribute(): string
    {
        $progress = $this->progress_percentage;

        if ($progress >= 100) {
            return 'Fully Grown!';
        } elseif ($progress >= 75) {
            return 'Almost There!';
        } elseif ($progress >= 50) {
            return 'Growing Strong';
        } elseif ($progress >= 25) {
            return 'Taking Root';
        } else {
            return 'Just Planted';
        }
    }

    /**
     * Mark the goal as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'current_amount' => $this->target_amount,
        ]);
    }

    /**
     * Add amount to current savings.
     */
    public function addSavings(float $amount): void
    {
        $newAmount = $this->current_amount + $amount;

        $this->update([
            'current_amount' => $newAmount,
        ]);

        // Auto-complete if target reached
        if ($newAmount >= $this->target_amount && !$this->is_completed) {
            $this->markAsCompleted();
        }
    }
}
