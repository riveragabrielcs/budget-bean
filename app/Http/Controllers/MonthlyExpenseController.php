<?php

namespace App\Http\Controllers;

use App\Exceptions\ExpenseNotFoundException;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MonthlyExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    /**
     * Store a newly created monthly expense.
     */
    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->expenseService->createExpense(auth()->user(), $request);

        return redirect()->route('dashboard')->with('success', 'Your expense has been added to this month! 💸');
    }

    /**
     * Store a monthly expense via AJAX.
     */
    public function storeAjax(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->expenseService->createExpense(auth()->user(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Expense added successfully!',
            'expense' => new ExpenseResource($expense)
        ]);
    }

    /**
     * Update the specified monthly expense.
     */
    public function update(UpdateExpenseRequest $request, int $monthlyExpenseId): RedirectResponse
    {
        try {
            $this->expenseService->updateExpense(auth()->user(), $monthlyExpenseId, $request);
            return redirect()->route('dashboard')->with('success', 'Your expense has been updated! ✏️');
        } catch (ExpenseNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Remove the specified monthly expense.
     */
    public function destroy(int $monthlyExpenseId): RedirectResponse
    {
        try {
            $result = $this->expenseService->deleteExpense(auth()->user(), $monthlyExpenseId);
            return redirect()->route('dashboard')->with('success', "Your {$result['name']} expense has been deleted.");
        } catch (ExpenseNotFoundException $e) {
            abort(404);
        } catch (\RuntimeException $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to delete expense. Please try again.');
        }
    }

    /**
     * Remove the specified monthly expense via AJAX.
     */
    public function destroyAjax(int $monthlyExpenseId): JsonResponse
    {
        try {
            $result = $this->expenseService->deleteExpense(auth()->user(), $monthlyExpenseId);
            return response()->json([
                'success' => true,
                'message' => "{$result['name']} expense has been deleted.",
            ]);
        } catch (ExpenseNotFoundException $e) {
            abort(404);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense. Please try again.',
            ], 500);
        }
    }
}
