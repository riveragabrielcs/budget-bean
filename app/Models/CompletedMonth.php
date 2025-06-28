<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CompletedMonth extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'total_revenue',
        'calculation_method',
        'paycheck_amount',
        'paycheck_count',
        'monthly_savings_goal',
        'total_expenses',
        'recurring_bills_total',
        'one_time_expenses_total',
        'expenses_snapshot',
        'water_collected',
        'budget_remaining',
        'was_drought',
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
            'total_expenses' => 'decimal:2',
            'recurring_bills_total' => 'decimal:2',
            'one_time_expenses_total' => 'decimal:2',
            'water_collected' => 'decimal:2',
            'budget_remaining' => 'decimal:2',
            'was_drought' => 'boolean',
            'expenses_snapshot' => 'array',
            'month' => 'integer',
            'year' => 'integer',
            'paycheck_count' => 'integer',
        ];
    }

    /**
     * Get the user that owns the completed month.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the month period string (e.g., "March 2025").
     */
    public function getMonthPeriodAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Get formatted month and year for display.
     */
    public function getFormattedMonthYearAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('M Y');
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
     * Get the calculated monthly budget (Revenue - Savings Goal).
     */
    public function getMonthlyBudgetAttribute(): float
    {
        return max($this->total_revenue - ($this->monthly_savings_goal ?? 0), 0);
    }

    /**
     * Get savings rate percentage.
     */
    public function getSavingsRateAttribute(): float
    {
        if ($this->total_revenue == 0) {
            return 0;
        }

        return ($this->monthly_savings_goal / $this->total_revenue) * 100;
    }

    /**
     * Check if this month had financial growth vs previous month.
     */
    public function getGrowthVsPreviousAttribute(): ?array
    {
        $previousMonth = static::where('user_id', $this->user_id)
            ->where(function ($query) {
                $query->where('year', $this->year)
                    ->where('month', '<', $this->month);
            })
            ->orWhere(function ($query) {
                $query->where('year', '<', $this->year);
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        if (!$previousMonth) {
            return null;
        }

        $revenueChange = $this->total_revenue - $previousMonth->total_revenue;
        $expenseChange = $this->total_expenses - $previousMonth->total_expenses;
        $waterChange = $this->water_collected - $previousMonth->water_collected;

        return [
            'revenue_change' => $revenueChange,
            'revenue_change_percent' => $previousMonth->total_revenue > 0
                ? ($revenueChange / $previousMonth->total_revenue) * 100
                : 0,
            'expense_change' => $expenseChange,
            'expense_change_percent' => $previousMonth->total_expenses > 0
                ? ($expenseChange / $previousMonth->total_expenses) * 100
                : 0,
            'water_change' => $waterChange,
            'water_change_percent' => $previousMonth->water_collected > 0
                ? ($waterChange / $previousMonth->water_collected) * 100
                : 0,
            'previous_month' => $previousMonth->formatted_month_year,
        ];
    }

    /**
     * Scope to order by most recent first.
     */
    public function scopeRecentFirst($query)
    {
        return $query->orderBy('year', 'desc')->orderBy('month', 'desc');
    }

    /**
     * Create a completed month from current user data.
     */
    public static function createFromCurrentMonth($user, $month, $year, $overrideExisting = false): self
    {
        // Check if month already exists
        $existing = static::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing && !$overrideExisting) {
            throw new \Exception("Month {$month}/{$year} has already been completed.");
        }

        // Get current revenue data
        $currentRevenue = $user->currentMonthRevenue();

        // Get recurring bills
        $recurringBills = $user->recurringBills()->get();
        $recurringBillsTotal = $recurringBills->sum('amount');

        // Get one-time expenses
        $oneTimeExpenses = $user->currentMonthExpenses()->get();
        $oneTimeExpensesTotal = $oneTimeExpenses->sum('amount');

        $totalExpenses = $recurringBillsTotal + $oneTimeExpensesTotal;

        // Create expenses snapshot
        $expensesSnapshot = [
            'recurring_bills' => $recurringBills->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'name' => $bill->name,
                    'amount' => $bill->amount,
                    'bill_date' => $bill->bill_date,
                    'formatted_bill_date' => $bill->formatted_bill_date,
                    'description' => $bill->description,
                    'type' => 'recurring_bill',
                ];
            })->toArray(),
            'one_time_expenses' => $oneTimeExpenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'amount' => $expense->amount,
                    'expense_date' => $expense->expense_date->toDateString(),
                    'formatted_expense_date' => $expense->formatted_expense_date,
                    'description' => $expense->description,
                    'type' => 'one_time_expense',
                ];
            })->toArray(),
        ];

        // Calculate water collected
        $revenue = $currentRevenue ? $currentRevenue->total_revenue : 0;
        $savingsGoal = $currentRevenue ? $currentRevenue->monthly_savings_goal : 0;
        $waterCollected = max($revenue - $totalExpenses, 0);
        $budgetRemaining = max($revenue - $savingsGoal - $totalExpenses, 0);
        $wasDrought = $budgetRemaining < 0;

        $data = [
            'user_id' => $user->id,
            'month' => $month,
            'year' => $year,
            'total_revenue' => $revenue,
            'calculation_method' => $currentRevenue ? $currentRevenue->calculation_method : 'custom',
            'paycheck_amount' => $currentRevenue ? $currentRevenue->paycheck_amount : null,
            'paycheck_count' => $currentRevenue ? $currentRevenue->paycheck_count : null,
            'monthly_savings_goal' => $savingsGoal,
            'total_expenses' => $totalExpenses,
            'recurring_bills_total' => $recurringBillsTotal,
            'one_time_expenses_total' => $oneTimeExpensesTotal,
            'expenses_snapshot' => $expensesSnapshot,
            'water_collected' => $waterCollected,
            'budget_remaining' => $budgetRemaining,
            'was_drought' => $wasDrought,
        ];

        if ($existing && $overrideExisting) {
            $existing->update($data);
            return $existing;
        }

        return static::create($data);
    }
}
