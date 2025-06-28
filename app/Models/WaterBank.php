<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaterBank extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'balance',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the water bank.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the water bank transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WaterBankTransaction::class);
    }

    /**
     * Add water to the bank.
     */
    public function addWater(float $amount, string $source = 'month_end', ?string $description = null): WaterBankTransaction
    {
        $this->increment('balance', $amount);

        return $this->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'source' => $source,
            'description' => $description,
            'balance_after' => $this->fresh()->balance,
        ]);
    }

    /**
     * Use water from the bank.
     */
    public function useWater(float $amount, int $savingsGoalId, string $source = 'plant_watering'): WaterBankTransaction
    {
        if ($amount > $this->balance) {
            throw new \Exception('Insufficient water in bank');
        }

        $this->decrement('balance', $amount);

        return $this->transactions()->create([
            'type' => 'withdrawal',
            'amount' => $amount,
            'source' => $source,
            'savings_goal_id' => $savingsGoalId,
            'balance_after' => $this->fresh()->balance,
        ]);
    }

    /**
     * Get or create water bank for user.
     */
    public static function getOrCreateForUser($userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            ['user_id' => $userId, 'balance' => 0]
        );
    }

    /**
     * Check if user has enough water for amount.
     */
    public function hasEnoughWater(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get formatted balance.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }
}
