<?php

namespace App\Http\Controllers;

use App\Exceptions\SavingsGoalNotFoundException;
use App\Http\Requests\SavingsGoal\AddSavingsRequest;
use App\Http\Requests\SavingsGoal\StoreSavingsGoalRequest;
use App\Http\Requests\SavingsGoal\UpdateSavingsGoalRequest;
use App\Http\Requests\SavingsGoal\WithdrawSavingsRequest;
use App\Http\Resources\SavingsGoalResource;
use App\Services\PlantGrowthService;
use App\Services\SavingsGoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavingsGoalController extends Controller
{
    public function __construct(
        private SavingsGoalService $savingsGoalService,
        private PlantGrowthService $plantGrowthService
    )
    {
    }

    /**
     * Display the user's savings goals garden.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = auth()->user();
        $savingsGoals = $this->savingsGoalService->getSavingsGoalsForUser($user);
        $stats = $this->savingsGoalService->calculateSavingsGoalStats($user);
        $waterBankData = $this->savingsGoalService->getWaterBankData($user);

        // Format savings goals for frontend
        $formattedGoals = $savingsGoals->map(function ($goal) {
            $progressPercentage = $goal->getProgressPercentage();

            return [
                'id' => $goal->id,
                'name' => $goal->name,
                'description' => $goal->description,
                'target_amount' => $goal->target_amount,
                'current_amount' => $goal->current_amount,
                'remaining_amount' => $goal->getRemainingAmount(),
                'progress_percentage' => $progressPercentage,
                'plant_emoji' => $this->plantGrowthService->getPlantEmoji($progressPercentage),
                'growth_stage' => $this->plantGrowthService->getGrowthStage($progressPercentage),
                'is_completed' => $goal->is_completed,
                'is_reached' => $goal->current_amount >= $goal->target_amount,
                'completed_at' => null, // Would need to add this to DTO if needed
                'created_at' => $goal->created_at,
            ];
        });

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'savings_goals' => SavingsGoalResource::collection($savingsGoals),
                    'stats' => $stats,
                    'water_bank' => $waterBankData
                ]
            ]);
        }

        return Inertia::render('Garden/MyGarden', [
            'savingsGoals' => $formattedGoals,
            'stats' => $stats,
            'waterBank' => $waterBankData,
        ]);
    }

    /**
     * Store a newly created savings goal.
     */
    public function store(StoreSavingsGoalRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $goal = $this->savingsGoalService->createSavingsGoal($user, $request);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your new savings goal has been planted! 🌱',
                    'data' => new SavingsGoalResource($goal)
                ], 201);
            }

            return redirect()->route('garden.index')
                ->with('success', 'Your new savings goal has been planted! 🌱');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to create savings goal.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to create savings goal: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified savings goal.
     */
    public function update(UpdateSavingsGoalRequest $request, int $goalId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $goal = $this->savingsGoalService->updateSavingsGoal($user, $goalId, $request);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your savings goal has been updated! 🌿',
                    'data' => new SavingsGoalResource($goal)
                ]);
            }

            return redirect()->route('garden.index')
                ->with('success', 'Your savings goal has been updated! 🌿');

        } catch (SavingsGoalNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('garden.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to update savings goal.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update savings goal: ' . $e->getMessage()]);
        }
    }

    /**
     * Add money to a savings goal.
     */
    public function addSavings(AddSavingsRequest $request, int $goalId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $result = $this->savingsGoalService->addSavings($user, $goalId, $request);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $result['message'],
                    'data' => new SavingsGoalResource($result['goal'])
                ]);
            }

            return redirect()->route('garden.index')
                ->with('success', $result['message']);

        } catch (SavingsGoalNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('garden.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 400);
            }

            return back()->with('error', $e->getMessage());

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to add savings.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to add savings: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark a savings goal as completed.
     */
    public function complete(Request $request, int $goalId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $goal = $this->savingsGoalService->completeSavingsGoal($user, $goalId);

            $message = "Congratulations! You've completed your {$goal->name} goal! 🌳🎉";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'data' => new SavingsGoalResource($goal)
                ]);
            }

            return redirect()->route('garden.index')
                ->with('success', $message);

        } catch (SavingsGoalNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('garden.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 400);
            }

            return redirect()->route('garden.index')
                ->with('info', $e->getMessage());

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to complete savings goal.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to complete savings goal: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified savings goal.
     */
    public function destroy(Request $request, int $goalId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $result = $this->savingsGoalService->deleteSavingsGoal($user, $goalId);

            $message = "Your {$result['name']} goal has been removed from your garden.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'data' => $result
                ]);
            }

            return redirect()->route('garden.index')
                ->with('success', $message);

        } catch (SavingsGoalNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('garden.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to delete savings goal.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to delete savings goal: ' . $e->getMessage()]);
        }
    }

    /**
     * Withdraw money from a savings goal.
     */
    public function withdraw(WithdrawSavingsRequest $request, int $goalId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            $goal = $this->savingsGoalService->withdrawSavings($user, $goalId, $request);

            $withdrawAmount = $request->validated()['amount'];
            $message = "Withdrew $" . number_format($withdrawAmount, 2) . " from your {$goal->name} goal.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'data' => new SavingsGoalResource($goal)
                ]);
            }

            return redirect()->route('garden.index')
                ->with('success', $message);

        } catch (SavingsGoalNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 404);
            }

            return redirect()->route('garden.index')
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 400);
            }

            return back()->withErrors(['amount' => $e->getMessage()]);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to withdraw from savings goal.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to withdraw: ' . $e->getMessage()]);
        }
    }
}
