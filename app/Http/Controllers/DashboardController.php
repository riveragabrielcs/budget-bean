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

        // Calculate totals
        $totalRecurringBills = $recurringBills->sum('amount');

        // TODO: Add one-time expenses here when that feature is implemented
        $oneTimeExpenses = collect([]); // Empty for now
        $totalOneTimeExpenses = 0;

        // Combine and sort all expenses by date (most recent first)
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

        return Inertia::render('Dashboard', [
            'savingsGoals' => $savingsGoals,
            'monthlyExpenses' => $allExpenses,
            'expenseStats' => $stats,
        ]);
    }
}
