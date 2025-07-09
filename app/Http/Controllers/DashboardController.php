<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display This Month Dashboard.
     */
    public function index(DashboardService $dashboardService): Response
    {
        $user = auth()->user();
        $dashboardDTO = $dashboardService->getDashboardSummary($user);

        return Inertia::render('Dashboard/DashboardIndex', $dashboardDTO->toArray());
    }
}
