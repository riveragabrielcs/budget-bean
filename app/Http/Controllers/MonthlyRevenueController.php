<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRevenueRequest;
use App\Http\Requests\UpdateSavingsGoalRequest;
use App\Services\RevenueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MonthlyRevenueController extends Controller
{
    public function __construct(
        private RevenueService $revenueService
    ) {}

    /**
     * Store or update monthly revenue for current month.
     */
    public function store(StoreRevenueRequest $request): RedirectResponse
    {
        $this->revenueService->storeRevenue(auth()->user(), $request);

        return redirect()->route('dashboard')->with('success', 'Your monthly revenue has been updated! 📊');
    }

    /**
     * Update savings goal for current month.
     */
    public function updateSavingsGoal(UpdateSavingsGoalRequest $request): RedirectResponse
    {
        $this->revenueService->updateSavingsGoal(
            auth()->user(),
            $request->validated('monthly_savings_goal')
        );

        return redirect()->route('dashboard')->with('success', 'Your savings goal has been updated! 🏆');
    }

    /**
     * Store or update monthly revenue via AJAX.
     */
    public function storeAjax(StoreRevenueRequest $request): JsonResponse
    {
        $revenue = $this->revenueService->storeRevenue(auth()->user(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Revenue updated successfully!',
            'revenue' => $revenue->toArray()
        ]);
    }

    /**
     * Get current month's revenue data.
     */
    public function getCurrent(): JsonResponse
    {
        $revenue = $this->revenueService->getCurrentRevenue(auth()->user());

        return response()->json([
            'success' => true,
            'revenue' => $revenue?->toArray(),
        ]);
    }

    /**
     * Remove the current month's revenue.
     */
    public function destroy(): RedirectResponse
    {
        $deleted = $this->revenueService->deleteRevenue(auth()->user());

        if ($deleted) {
            return redirect()->route('dashboard')->with('success', 'Monthly revenue has been cleared.');
        }

        return redirect()->route('dashboard')->with('info', 'No revenue data to delete.');
    }
}
