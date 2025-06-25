<?php

namespace App\Http\Controllers;

use App\Models\MonthlyExpense;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MonthlyExpenseController extends Controller
{
    /**
     * Store a newly created monthly expense.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'description' => 'nullable|string|max:500',
            'expense_date' => 'nullable|date|before_or_equal:today',
        ]);

        // Create expense for current month
        auth()->user()->monthlyExpenses()->create([
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'expense_date' => $validated['expense_date'] ?? now()->toDateString(),
            'budget_month' => now()->month,
            'budget_year' => now()->year,
        ]);

        return redirect()->route('dashboard')->with('success', 'Your expense has been added to this month! 💸');
    }

    /**
     * Store a monthly expense via AJAX.
     */
    public function storeAjax(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'description' => 'nullable|string|max:500',
            'expense_date' => 'nullable|date|before_or_equal:today',
        ]);

        $expense = auth()->user()->monthlyExpenses()->create([
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'expense_date' => $validated['expense_date'] ?? now()->toDateString(),
            'budget_month' => now()->month,
            'budget_year' => now()->year,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense added successfully!',
            'expense' => [
                'id' => $expense->id,
                'name' => $expense->name,
                'amount' => $expense->amount,
                'formatted_expense_date' => $expense->formatted_expense_date,
                'description' => $expense->description,
                'type' => 'one_time_expense',
                'created_at' => $expense->created_at,
            ]
        ]);
    }

    /**
     * Update the specified monthly expense.
     */
    public function update(Request $request, MonthlyExpense $monthlyExpense): RedirectResponse
    {
        // Ensure the expense belongs to the authenticated user
        if ($monthlyExpense->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01|max:9999999.99',
            'description' => 'sometimes|nullable|string|max:500',
            'expense_date' => 'sometimes|nullable|date|before_or_equal:today',
        ]);

        $monthlyExpense->update($validated);

        return redirect()->route('dashboard')->with('success', 'Your expense has been updated! ✏️');
    }

    /**
     * Remove the specified monthly expense.
     */
    public function destroy(MonthlyExpense $monthlyExpense): RedirectResponse
    {
        // Ensure the expense belongs to the authenticated user
        if ($monthlyExpense->user_id !== auth()->id()) {
            abort(403);
        }

        $expenseName = $monthlyExpense->name;
        $monthlyExpense->delete();

        return redirect()->route('dashboard')->with('success', "Your {$expenseName} expense has been deleted.");
    }

    /**
     * Remove the specified monthly expense via AJAX.
     */
    public function destroyAjax(MonthlyExpense $monthlyExpense): JsonResponse
    {
        // Ensure the expense belongs to the authenticated user
        if ($monthlyExpense->user_id !== auth()->id()) {
            abort(403);
        }

        $expenseName = $monthlyExpense->name;
        $monthlyExpense->delete();

        return response()->json([
            'success' => true,
            'message' => "{$expenseName} expense has been deleted.",
        ]);
    }
}
