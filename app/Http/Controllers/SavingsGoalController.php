<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use App\Models\WaterBank;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SavingsGoalController extends Controller
{
    /**
     * Display the user's savings goals garden.
     */
    public function index(): Response
    {
        $user = auth()->user();

        $savingsGoals = $user->savingsGoals()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'description' => $goal->description,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'remaining_amount' => $goal->remaining_amount,
                    'progress_percentage' => $goal->progress_percentage,
                    'plant_emoji' => $goal->plant_emoji,
                    'growth_stage' => $goal->growth_stage,
                    'is_completed' => $goal->is_completed,
                    'is_reached' => $goal->is_reached,
                    'completed_at' => $goal->completed_at?->format('M d, Y'),
                    'created_at' => $goal->created_at->format('M d, Y'),
                ];
            });

        $stats = [
            'total_goals' => $savingsGoals->count(),
            'active_goals' => $savingsGoals->where('is_completed', false)->count(),
            'completed_goals' => $savingsGoals->where('is_completed', true)->count(),
            'total_saved' => $user->total_saved,
            'total_target' => $user->total_target,
            'overall_progress' => $user->total_target > 0
                ? min(($user->total_saved / $user->total_target) * 100, 100)
                : 0,
        ];

        // Get water bank data
        $waterBank = WaterBank::getOrCreateForUser($user->id);
        $waterBankData = [
            'balance' => $waterBank->balance,
            'formatted_balance' => $waterBank->formatted_balance,
            'recent_transactions' => $waterBank->transactions()
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
                        'date_short' => $transaction->created_at->format('M j'),
                    ];
                }),
        ];

        return Inertia::render('Garden/MyGarden', [
            'savingsGoals' => $savingsGoals,
            'stats' => $stats,
            'waterBank' => $waterBankData,
        ]);
    }

    /**
     * Store a newly created savings goal.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'target_amount' => 'required|numeric|min:0.01|max:9999999.99',
        ]);

        auth()->user()->savingsGoals()->create($validated);

        return redirect()->route('garden.index')->with('success', 'Your new savings goal has been planted! 🌱');
    }

    /**
     * Update the specified savings goal.
     */
    public function update(Request $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:500',
            'target_amount' => 'sometimes|required|numeric|min:0.01|max:9999999.99',
        ]);

        $savingsGoal->update($validated);

        return redirect()->route('garden.index')->with('success', 'Your savings goal has been updated! 🌿');
    }

    /**
     * Add money to a savings goal.
     */
    public function addSavings(Request $request, SavingsGoal $savingsGoal): RedirectResponse
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

        return redirect()->route('garden.index')->with('success', $message);
    }

    /**
     * Mark a savings goal as completed.
     */
    public function complete(SavingsGoal $savingsGoal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403);
        }

        if ($savingsGoal->is_completed) {
            return redirect()->route('garden.index')->with('info', 'This goal is already completed! 🎉');
        }

        $savingsGoal->markAsCompleted();

        return redirect()->route('garden.index')->with('success', "Congratulations! You've completed your {$savingsGoal->name} goal! 🌳🎉");
    }

    /**
     * Remove the specified savings goal.
     */
    public function destroy(SavingsGoal $savingsGoal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403);
        }

        $goalName = $savingsGoal->name;
        $savingsGoal->delete();

        return redirect()->route('garden.index')->with('success', "Your {$goalName} goal has been removed from your garden.");
    }

    /**
     * Withdraw money from a savings goal.
     */
    public function withdraw(Request $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $withdrawAmount = $validated['amount'];

        if ($withdrawAmount > $savingsGoal->current_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Cannot withdraw more than the current saved amount.',
            ]);
        }

        $newAmount = $savingsGoal->current_amount - $withdrawAmount;

        $savingsGoal->update([
            'current_amount' => $newAmount,
            'is_completed' => false, // Unmark as completed if it was
        ]);

        return redirect()->route('garden.index')->with('success', "Withdrew $" . number_format($withdrawAmount, 2) . " from your {$savingsGoal->name} goal.");
    }
}
