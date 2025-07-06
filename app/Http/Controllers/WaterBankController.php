<?php

namespace App\Http\Controllers;

use App\Data\AddManualWaterData;
use App\Data\EndMonthData;
use App\Data\WaterAllGoalsData;
use App\Data\WaterGoalData;
use App\Enums\FundingSourceEnum;
use App\Exceptions\InsufficientWaterException;
use App\Exceptions\SavingsGoalNotFoundException;
use App\Http\Requests\WaterBank\AddManualWaterRequest;
use App\Http\Requests\WaterBank\EndMonthRequest;
use App\Http\Requests\WaterBank\WaterAllGoalsRequest;
use App\Http\Requests\WaterBank\WaterGoalRequest;
use App\Http\Resources\WaterBankResource;
use App\Models\SavingsGoal;
use App\Services\WaterBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class WaterBankController extends Controller
{
    public function __construct(
        private WaterBankService $waterBankService
    ) {
    }

    /**
     * End the specified month and transfer potential water to usable water bank.
     */
    public function endMonth(EndMonthRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();
            $data = new EndMonthData(
                month: $request->validated('month'),
                year: $request->validated('year'),
                override_existing: $request->validated('override_existing', false)
            );

            $result = $this->waterBankService->endMonth($user, $data);

            return redirect()->route('past-months.index')
                ->with('success', $result['message']);

        } catch (\InvalidArgumentException|\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Water a specific savings goal.
     */
    public function waterGoal(WaterGoalRequest $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        // Ensure the goal belongs to the authenticated user
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $user = auth()->user();
            $data = new WaterGoalData(
                amount: $request->validated('amount'),
                source: $request->validated('source')
            );

            $result = $this->waterBankService->waterGoal($user, $savingsGoal->id, $data);

            return back()->with('success', $result['message']);

        } catch (SavingsGoalNotFoundException $e) {
            return back()->with('error', $e->getMessage());
        } catch (InsufficientWaterException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to water goal: ' . $e->getMessage());
        }
    }

    /**
     * Water all goals equally.
     */
    public function waterAllGoals(WaterAllGoalsRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();
            $data = new WaterAllGoalsData(
                total_amount: $request->validated('total_amount'),
                source: FundingSourceEnum::from($request->validated('source'))
            );

            $result = $this->waterBankService->waterAllGoals($user, $data);

            return back()->with('success', $result['message']);

        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (InsufficientWaterException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to water goals: ' . $e->getMessage());
        }
    }

    /**
     * Get current water bank status.
     */
    public function getStatus(): JsonResponse
    {
        try {
            $user = auth()->user();
            $waterBankData = $this->waterBankService->getWaterBankStatus($user);

            return response()->json([
                'success' => true,
                'water_bank' => $waterBankData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch water bank status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add manual water to bank (for gifts, bonuses, etc.).
     */
    public function addManualWater(AddManualWaterRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();
            $data = new AddManualWaterData(
                amount: $request->validated('amount'),
                description: $request->validated('description')
            );

            $result = $this->waterBankService->addManualWater($user, $data);

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add water: ' . $e->getMessage());
        }
    }
}
