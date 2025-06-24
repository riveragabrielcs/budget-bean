<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Get active savings goals for the dashboard (limit to 4 for clean display)
        $savingsGoals = $user->activeSavingsGoals()
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'progress_percentage' => $goal->progress_percentage,
                    'plant_emoji' => $goal->plant_emoji,
                    'growth_stage' => $goal->growth_stage,
                    'remaining_amount' => $goal->remaining_amount,
                ];
            });

        return Inertia::render('Dashboard', [
            'savingsGoals' => $savingsGoals,
        ]);
    }
}
