<?php

namespace App\Http\Controllers;

use App\Models\CompletedMonth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompletedMonthController extends Controller
{
    /**
     * Display the user's past completed months.
     */
    public function index(): Response
    {
        $user = auth()->user();

        $completedMonths = $user->completedMonths()
            ->recentFirst()
            ->get()
            ->map(function ($month) {
                $growth = $month->growth_vs_previous;

                return [
                    'id' => $month->id,
                    'month' => $month->month,
                    'year' => $month->year,
                    'month_period' => $month->month_period,
                    'formatted_month_year' => $month->formatted_month_year,

                    // Financial data
                    'total_revenue' => $month->total_revenue,
                    'total_expenses' => $month->total_expenses,
                    'recurring_bills_total' => $month->recurring_bills_total,
                    'one_time_expenses_total' => $month->one_time_expenses_total,
                    'monthly_savings_goal' => $month->monthly_savings_goal,
                    'water_collected' => $month->water_collected,
                    'budget_remaining' => $month->budget_remaining,
                    'monthly_budget' => $month->monthly_budget,
                    'was_drought' => $month->was_drought,
                    'savings_rate' => $month->savings_rate,

                    // Revenue details
                    'calculation_method' => $month->calculation_method,
                    'source_description' => $month->source_description,

                    // Growth comparison
                    'growth_vs_previous' => $growth,

                    // Timestamps
                    'completed_at' => $month->created_at->format('M j, Y \a\t g:i A'),
                    'completed_at_short' => $month->created_at->format('M j'),
                ];
            });

        // Calculate overall stats
        $stats = [
            'total_months' => $completedMonths->count(),
            'total_revenue_all_time' => $completedMonths->sum('total_revenue'),
            'total_expenses_all_time' => $completedMonths->sum('total_expenses'),
            'total_water_collected' => $completedMonths->sum('water_collected'),
            'average_monthly_revenue' => $completedMonths->count() > 0
                ? $completedMonths->avg('total_revenue')
                : 0,
            'average_monthly_expenses' => $completedMonths->count() > 0
                ? $completedMonths->avg('total_expenses')
                : 0,
            'best_month' => $completedMonths->sortByDesc('water_collected')->first(),
            'drought_months' => $completedMonths->where('was_drought', true)->count(),
        ];

        return Inertia::render('PastMonths/Index', [
            'completedMonths' => $completedMonths,
            'stats' => $stats,
        ]);
    }

    /**
     * Show detailed view of a specific completed month.
     */
    public function show(CompletedMonth $completedMonth): Response
    {
        // Ensure the month belongs to the authenticated user
        if ($completedMonth->user_id !== auth()->id()) {
            abort(403);
        }

        $monthData = [
            'id' => $completedMonth->id,
            'month' => $completedMonth->month,
            'year' => $completedMonth->year,
            'month_period' => $completedMonth->month_period,
            'formatted_month_year' => $completedMonth->formatted_month_year,

            // Financial data
            'total_revenue' => $completedMonth->total_revenue,
            'total_expenses' => $completedMonth->total_expenses,
            'recurring_bills_total' => $completedMonth->recurring_bills_total,
            'one_time_expenses_total' => $completedMonth->one_time_expenses_total,
            'monthly_savings_goal' => $completedMonth->monthly_savings_goal,
            'water_collected' => $completedMonth->water_collected,
            'budget_remaining' => $completedMonth->budget_remaining,
            'monthly_budget' => $completedMonth->monthly_budget,
            'was_drought' => $completedMonth->was_drought,
            'savings_rate' => $completedMonth->savings_rate,

            // Revenue details
            'calculation_method' => $completedMonth->calculation_method,
            'source_description' => $completedMonth->source_description,

            // Detailed expenses
            'expenses_snapshot' => $completedMonth->expenses_snapshot,

            // Growth comparison
            'growth_vs_previous' => $completedMonth->growth_vs_previous,

            // Timestamps
            'completed_at' => $completedMonth->created_at->format('M j, Y \a\t g:i A'),
        ];

        return Inertia::render('PastMonths/Show', [
            'completedMonth' => $monthData,
        ]);
    }

    /**
     * Delete a completed month.
     */
    public function destroy(CompletedMonth $completedMonth): RedirectResponse
    {
        // Ensure the month belongs to the authenticated user
        if ($completedMonth->user_id !== auth()->id()) {
            abort(403);
        }

        $monthPeriod = $completedMonth->month_period;
        $completedMonth->delete();

        return redirect()->route('past-months.index')
            ->with('success', "Deleted completed month: {$monthPeriod}");
    }

    /**
     * Check if a month already exists for validation.
     */
    public function checkMonthExists(Request $request)
    {
        // Handle both GET and POST requests
        $month = $request->input('month') ?? $request->query('month');
        $year = $request->input('year') ?? $request->query('year');

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $user = auth()->user();
        $exists = CompletedMonth::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->exists();

        return response()->json([
            'exists' => $exists,
            'month_period' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'),
        ]);
    }
}
