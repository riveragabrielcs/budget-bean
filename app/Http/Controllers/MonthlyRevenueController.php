<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRevenue;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MonthlyRevenueController extends Controller
{
    /**
     * Store or update monthly revenue for current month.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'calculation_method' => 'required|in:paycheck,custom',
            'total_revenue' => 'required|numeric|min:0|max:9999999.99',
            'paycheck_amount' => 'nullable|numeric|min:0|max:9999999.99',
            'paycheck_count' => 'nullable|integer|min:1|max:100',
        ]);

        // If paycheck method, ensure required fields are present
        if ($validated['calculation_method'] === 'paycheck') {
            $request->validate([
                'paycheck_amount' => 'required|numeric|min:0.01|max:9999999.99',
                'paycheck_count' => 'required|integer|min:1|max:100',
            ]);
        }

        MonthlyRevenue::setForCurrentMonth($validated);

        return redirect()->route('dashboard')->with('success', 'Your monthly revenue has been updated! 📊');
    }

    /**
     * Update savings goal for current month.
     */
    public function updateSavingsGoal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_savings_goal' => 'required|numeric|min:0|max:9999999.99',
        ]);

        $currentRevenue = MonthlyRevenue::getCurrentMonthRevenue();

        if ($currentRevenue) {
            $currentRevenue->update($validated);
        } else {
            // Create new record with just savings goal
            MonthlyRevenue::setForCurrentMonth(array_merge($validated, [
                'total_revenue' => 0,
                'calculation_method' => 'custom',
            ]));
        }

        return redirect()->route('dashboard')->with('success', 'Your savings goal has been updated! 🏆');
    }

    /**
     * Store or update monthly revenue via AJAX.
     */
    public function storeAjax(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'calculation_method' => 'required|in:paycheck,custom',
            'total_revenue' => 'required|numeric|min:0|max:9999999.99',
            'paycheck_amount' => 'nullable|numeric|min:0|max:9999999.99',
            'paycheck_count' => 'nullable|integer|min:1|max:100',
        ]);

        // If paycheck method, ensure required fields are present
        if ($validated['calculation_method'] === 'paycheck') {
            $request->validate([
                'paycheck_amount' => 'required|numeric|min:0.01|max:9999999.99',
                'paycheck_count' => 'required|integer|min:1|max:100',
            ]);
        }

        $revenue = MonthlyRevenue::setForCurrentMonth($validated);

        return response()->json([
            'success' => true,
            'message' => 'Revenue updated successfully!',
            'revenue' => [
                'total_revenue' => $revenue->total_revenue,
                'calculation_method' => $revenue->calculation_method,
                'paycheck_amount' => $revenue->paycheck_amount,
                'paycheck_count' => $revenue->paycheck_count,
                'source_description' => $revenue->source_description,
                'revenue_period' => $revenue->revenue_period,
            ]
        ]);
    }

    /**
     * Get current month's revenue data.
     */
    public function getCurrent(): JsonResponse
    {
        $revenue = MonthlyRevenue::getCurrentMonthRevenue();

        if (!$revenue) {
            return response()->json([
                'success' => true,
                'revenue' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'revenue' => [
                'total_revenue' => $revenue->total_revenue,
                'calculation_method' => $revenue->calculation_method,
                'paycheck_amount' => $revenue->paycheck_amount,
                'paycheck_count' => $revenue->paycheck_count,
                'source_description' => $revenue->source_description,
                'revenue_period' => $revenue->revenue_period,
            ]
        ]);
    }

    /**
     * Remove the current month's revenue.
     */
    public function destroy(): RedirectResponse
    {
        $revenue = MonthlyRevenue::getCurrentMonthRevenue();

        if ($revenue) {
            $revenue->delete();
            return redirect()->route('dashboard')->with('success', 'Monthly revenue has been cleared.');
        }

        return redirect()->route('dashboard')->with('info', 'No revenue data to delete.');
    }
}
