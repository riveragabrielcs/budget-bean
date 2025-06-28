<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRevenue;
use App\Models\SavingsGoal;
use App\Models\WaterBank;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class WaterBankController extends Controller
{
    /**
     * End the current month and transfer potential water to usable water bank.
     */
    public function endMonth(): RedirectResponse
    {
        $user = auth()->user();
        $currentRevenue = $user->currentMonthRevenue();

        if (!$currentRevenue) {
            return redirect()->route('dashboard')->with('error', 'No revenue data found for this month.');
        }

        $potentialWater = $currentRevenue->potential_water_bank;

        if ($potentialWater <= 0) {
            return redirect()->route('dashboard')->with('info', 'No water to deposit - you spent everything this month! 🏜️');
        }

        // Get or create water bank
        $waterBank = WaterBank::getOrCreateForUser($user->id);

        // Add water with description
        $description = "Month-end deposit from " . now()->format('F Y');
        $transaction = $waterBank->addWater($potentialWater, 'month_end', $description);

        $message = "Added " . number_format($potentialWater, 2) . " to your Water Bank! 💧 Total: $" . $waterBank->formatted_balance;

        return redirect()->route('garden.index')->with('success', $message);
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
