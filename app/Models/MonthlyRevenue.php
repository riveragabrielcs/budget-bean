<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MonthlyRevenue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'total_revenue',
        'calculation_method',
        'paycheck_amount',
        'paycheck_count',
        'monthly_savings_goal',
        'revenue_month',
        'revenue_year',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_revenue' => 'decimal:2',
            'paycheck_amount' => 'decimal:2',
            'monthly_savings_goal' => 'decimal:2',
            'paycheck_count' => 'integer',
            'revenue_month' => 'integer',
            'revenue_year' => 'integer',
        ];
    }

    /**
     * Get the user that owns the monthly revenue.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the revenue period string (e.g., "March 2025").
     */
    public function getRevenuePeriodAttribute(): string
    {
        return Carbon::createFromDate($this->revenue_year, $this->revenue_month, 1)->format('F Y');
    }

    /**
     * Get formatted description of how revenue was calculated.
     */
    public function getSourceDescriptionAttribute(): ?string
    {
        if ($this->calculation_method === 'paycheck' && $this->paycheck_amount && $this->paycheck_count) {
            return "{$this->paycheck_count} paychecks × " . number_format($this->paycheck_amount, 2);
        }

        return null;
    }

    /**
     * Scope to filter revenue for a specific month/year.
     */
    public function scopeForRevenuePeriod($query, int $month, int $year)
    {
        return $query->where('revenue_month', $month)
            ->where('revenue_year', $year);
    }

    /**
     * Scope to filter revenue for the current month.
     */
    public function scopeForCurrentMonth($query)
    {
        $now = now();
        return $query->forRevenuePeriod($now->month, $now->year);
    }

    /**
     * Create or update revenue for the current month.
     */
    public static function setForCurrentMonth(array $attributes): self
    {
        $now = now();
        $user = auth()->user();

        return $user->monthlyRevenues()->updateOrCreate(
            [
                'revenue_month' => $now->month,
                'revenue_year' => $now->year,
            ],
            array_merge($attributes, [
                'revenue_month' => $now->month,
                'revenue_year' => $now->year,
            ])
        );
    }

    /**
     * Get current month's revenue for a user.
     */
    public static function getCurrentMonthRevenue()
    {
        $now = now();
        return auth()->user()
            ->monthlyRevenues()
            ->forRevenuePeriod($now->month, $now->year)
            ->first();
    }

    /**
     * Get the calculated monthly budget (Revenue - Savings Goal).
     */
    public function getMonthlyBudgetAttribute(): float
    {
        return max($this->total_revenue - ($this->monthly_savings_goal ?? 0), 0);
    }

    /**
     * Calculate budget remaining after expenses.
     */
    public function getBudgetRemainingAttribute(): float
    {
        $user = $this->user;
        $currentExpenses = $user->recurringBills()->sum('amount') +
            $user->currentMonthExpenses()->sum('amount');

        return $this->monthly_budget - $currentExpenses;
    }

    /**
     * Get potential water bank amount (all unspent money).
     */
    public function getPotentialWaterBankAttribute(): float
    {
        $user = $this->user;
        $currentExpenses = $user->recurringBills()->sum('amount') +
            $user->currentMonthExpenses()->sum('amount');

        return max($this->total_revenue - $currentExpenses, 0);
    }

    /**
     * Check if budget is negative (drought mode).
     */
    public function getIsDroughtAttribute(): bool
    {
        return $this->budget_remaining < 0;
    }
}
