<?php

namespace App\Http\Controllers;

use App\Models\CompletedMonth;
use App\Models\MonthlyRevenue;
use App\Models\SavingsGoal;
use App\Models\WaterBank;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class WaterBankController extends Controller
{
    /**
     * End the specified month and transfer potential water to usable water bank.
     */
    public function endMonth(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'override_existing' => 'sometimes|boolean',
        ]);

        $user = auth()->user();
        $month = $validated['month'];
        $year = $validated['year'];
        $overrideExisting = $validated['override_existing'] ?? false;

        // Validation: No future months
        $targetDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        if ($targetDate->isFuture()) {
            return back()->with('error', 'Cannot complete a future month! 📅');
        }

        try {
            // Create completed month record (this handles duplicate checking)
            $completedMonth = CompletedMonth::createFromCurrentMonth($user, $month, $year, $overrideExisting);

            // Get current revenue to calculate water
            $currentRevenue = $user->currentMonthRevenue();
            $waterToAdd = $completedMonth->water_collected;

            // Add water to bank if there's any to add
            if ($waterToAdd > 0) {
                $waterBank = WaterBank::getOrCreateForUser($user->id);
                $description = "Month-end deposit from {$completedMonth->month_period}";
                $waterBank->addWater($waterToAdd, 'month_end', $description);
            }

            // Reset the month: Clear one-time expenses but keep everything else
            $user->currentMonthExpenses()->delete();

            $message = $waterToAdd > 0
                ? "Completed {$completedMonth->month_period}! Added " . number_format($waterToAdd, 2) . " to your Water Bank! 💧"
                : "Completed {$completedMonth->month_period}! No water to collect this time. 🏜️";

            return redirect()->route('past-months.index')->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Water a specific savings goal.
     */
    public function waterGoal(Request $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'source' => 'required|in:water_bank,other',
        ]);

        $user = auth()->user();
        $amount = $validated['amount'];
        $source = $validated['source'];

        if ($source === 'water_bank') {
            $waterBank = WaterBank::getOrCreateForUser($user->id);

            if (!$waterBank->hasEnoughWater($amount)) {
                return back()->with('error', 'Not enough water in your Water Bank! 💧');
            }

            // Use water from bank and add to goal
            $waterBank->useWater($amount, $savingsGoal->id);
            $savingsGoal->addSavings($amount);

            $message = "Watered {$savingsGoal->name} with " . number_format($amount, 2) . " from your Water Bank! 🌱💧";
        } else {
            // Other money (gifts, cash, etc.)
            $savingsGoal->addSavings($amount);
            $message = "Added " . number_format($amount, 2) . " to {$savingsGoal->name}! 💰";
        }

        return back()->with('success', $message);
    }

    /**
     * Water all goals equally.
     */
    public function waterAllGoals(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0.01|max:9999999.99',
            'source' => 'required|in:water_bank,other',
        ]);

        $user = auth()->user();
        $totalAmount = $validated['total_amount'];
        $source = $validated['source'];

        $activeGoals = $user->activeSavingsGoals()->get();

        if ($activeGoals->isEmpty()) {
            return back()->with('error', 'No active goals to water! Plant some goals first. 🌱');
        }

        if ($source === 'water_bank') {
            $waterBank = WaterBank::getOrCreateForUser($user->id);

            if (!$waterBank->hasEnoughWater($totalAmount)) {
                return back()->with('error', 'Not enough water in your Water Bank! 💧');
            }
        }

        // Divide amount equally among active goals
        $amountPerGoal = $totalAmount / $activeGoals->count();
        $wateredGoals = [];

        foreach ($activeGoals as $goal) {
            if ($source === 'water_bank') {
                $waterBank->useWater($amountPerGoal, $goal->id);
            }
            $goal->addSavings($amountPerGoal);
            $wateredGoals[] = $goal->name;
        }

        $sourceText = $source === 'water_bank' ? 'Water Bank' : 'other funds';
        $message = "Watered " . count($wateredGoals) . " goals equally with " . number_format($totalAmount, 2) . " from {$sourceText}! 🌻💧";

        return back()->with('success', $message);
    }

    /**
     * Get current water bank status.
     */
    public function getStatus(): JsonResponse
    {
        $user = auth()->user();
        $waterBank = WaterBank::getOrCreateForUser($user->id);

        $recentTransactions = $waterBank->transactions()
            ->with('savingsGoal')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'formatted_amount' => $transaction->formatted_amount,
                    'icon' => $transaction->icon,
                    'description' => $transaction->display_description,
                    'date' => $transaction->created_at->format('M j, Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'water_bank' => [
                'balance' => $waterBank->balance,
                'formatted_balance' => $waterBank->formatted_balance,
                'recent_transactions' => $recentTransactions,
            ]
        ]);
    }

    /**
     * Add manual water to bank (for gifts, bonuses, etc.).
     */
    public function addManualWater(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'description' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $waterBank = WaterBank::getOrCreateForUser($user->id);

        $description = $validated['description'] ?: 'Manual water addition';
        $waterBank->addWater($validated['amount'], 'manual_add', $description);

        $message = "Added " . number_format($validated['amount'], 2) . " to your Water Bank! 💧";

        return back()->with('success', $message);
    }
}
