<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Get active savings goals for the dashboard (limit to 4 for clean display)
        $savingsGoals = $user->activeSavingsGoals()
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'progress_percentage' => $goal->progress_percentage,
                    'plant_emoji' => $goal->plant_emoji,
                    'growth_stage' => $goal->growth_stage,
                    'remaining_amount' => $goal->remaining_amount,
                ];
            });

        // Get recurring bills for this month's expenses
        $recurringBills = $user->recurringBills()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'name' => $bill->name,
                    'amount' => $bill->amount,
                    'bill_date' => $bill->bill_date,
                    'formatted_bill_date' => $bill->formatted_bill_date,
                    'description' => $bill->description,
                    'type' => 'recurring_bill',
                    'created_at' => $bill->created_at,
                ];
            });

        // Get one-time expenses for this month
        $oneTimeExpenses = $user->currentMonthExpenses()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'amount' => $expense->amount,
                    'expense_date' => $expense->expense_date,
                    'formatted_expense_date' => $expense->formatted_expense_date,
                    'description' => $expense->description,
                    'type' => 'one_time_expense',
                    'created_at' => $expense->created_at,
                ];
            });

        // Calculate totals
        $totalRecurringBills = $recurringBills->sum('amount');
        $totalOneTimeExpenses = $oneTimeExpenses->sum('amount');
        $allExpenses = $recurringBills->concat($oneTimeExpenses)
            ->sortByDesc('created_at')
            ->values();

        $stats = [
            'total_recurring_bills' => $totalRecurringBills,
            'total_one_time_expenses' => $totalOneTimeExpenses,
            'total_monthly_expenses' => $totalRecurringBills + $totalOneTimeExpenses,
            'recurring_bills_count' => $recurringBills->count(),
            'one_time_expenses_count' => $oneTimeExpenses->count(),
        ];

        // Get current month's revenue
        $currentRevenue = $user->currentMonthRevenue();
        $revenueData = null;

        if ($currentRevenue) {
            $revenueData = [
                'total_revenue' => $currentRevenue->total_revenue,
                'calculation_method' => $currentRevenue->calculation_method,
                'paycheck_amount' => $currentRevenue->paycheck_amount,
                'paycheck_count' => $currentRevenue->paycheck_count,
                'source_description' => $currentRevenue->source_description,
                'revenue_period' => $currentRevenue->revenue_period,
            ];
        }

        return Inertia::render('Dashboard', [
            'savingsGoals' => $savingsGoals,
            'monthlyExpenses' => $allExpenses,
            'expenseStats' => $stats,
            'currentRevenue' => $revenueData,
        ]);
    }
}
