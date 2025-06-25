<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MonthlyExpense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'amount',
        'description',
        'expense_date',
        'budget_month',
        'budget_year',
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
            'expense_date' => 'date',
            'budget_month' => 'integer',
            'budget_year' => 'integer',
        ];
    }

    /**
     * Get the user that owns the monthly expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted expense date for display.
     */
    public function getFormattedExpenseDateAttribute(): string
    {
        return $this->expense_date->format('M j, Y');
    }

    /**
     * Get the budget period string (e.g., "March 2025").
     */
    public function getBudgetPeriodAttribute(): string
    {
        return Carbon::createFromDate($this->budget_year, $this->budget_month, 1)->format('F Y');
    }

    /**
     * Scope to filter expenses for a specific budget month/year.
     */
    public function scopeForBudgetPeriod($query, int $month, int $year)
    {
        return $query->where('budget_month', $month)
            ->where('budget_year', $year);
    }

    /**
     * Scope to filter expenses for the current budget month.
     */
    public function scopeForCurrentMonth($query)
    {
        $now = now();
        return $query->forBudgetPeriod($now->month, $now->year);
    }

    /**
     * Create a monthly expense for the current budget period.
     */
    public static function createForCurrentMonth(array $attributes): self
    {
        $now = now();

        return static::create(array_merge($attributes, [
            'budget_month' => $now->month,
            'budget_year' => $now->year,
            'expense_date' => $attributes['expense_date'] ?? $now->toDateString(),
        ]));
    }
}
