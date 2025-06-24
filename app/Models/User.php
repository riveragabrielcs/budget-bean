<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's savings goals.
     */
    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class);
    }

    /**
     * Get the user's active (not completed) savings goals.
     */
    public function activeSavingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class)->where('is_completed', false);
    }

    /**
     * Get the user's completed savings goals.
     */
    public function completedSavingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class)->where('is_completed', true);
    }

    /**
     * Get the total amount saved across all goals.
     */
    public function getTotalSavedAttribute(): float
    {
        return $this->savingsGoals()->sum('current_amount');
    }

    /**
     * Get the total target amount across all active goals.
     */
    public function getTotalTargetAttribute(): float
    {
        return $this->activeSavingsGoals()->sum('target_amount');
    }
}
