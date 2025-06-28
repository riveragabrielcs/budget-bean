<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
     * Get the user's recurring bills.
     */
    public function recurringBills(): HasMany
    {
        return $this->hasMany(RecurringBill::class);
    }

    /**
     * Get the user's monthly expenses.
     */
    public function monthlyExpenses(): HasMany
    {
        return $this->hasMany(MonthlyExpense::class);
    }

    /**
     * Get the user's monthly expenses for current month.
     */
    public function currentMonthExpenses(): HasMany
    {
        $now = now();
        return $this->hasMany(MonthlyExpense::class)
            ->where('budget_month', $now->month)
            ->where('budget_year', $now->year);
    }

    /**
     * Get the user's monthly revenues.
     */
    public function monthlyRevenues(): HasMany
    {
        return $this->hasMany(MonthlyRevenue::class);
    }

    /**
     * Get the user's current month revenue.
     */
    public function currentMonthRevenue(): ?MonthlyRevenue
    {
        $now = now();
        return $this->hasMany(MonthlyRevenue::class)
            ->where('revenue_month', $now->month)
            ->where('revenue_year', $now->year)
            ->first();
    }

    /**
     * Get the user's water bank.
     */
    public function waterBank(): HasOne
    {
        return $this->hasOne(WaterBank::class);
    }

    /**
     * Get the user's completed months.
     */
    public function completedMonths(): HasMany
    {
        return $this->hasMany(CompletedMonth::class);
    }

    /**
     * Get or create the user's water bank.
     */
    public function getOrCreateWaterBank(): WaterBank
    {
        return WaterBank::getOrCreateForUser($this->id);
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

    /**
     * Get the total monthly amount for recurring bills.
     */
    public function getTotalMonthlyBillsAttribute(): float
    {
        return $this->recurringBills()->sum('amount');
    }
}
