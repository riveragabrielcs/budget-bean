<?php

namespace App\Http\Controllers;

use App\Services\ThisMonthService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display This Month Dashboard.
     */
    public function index(ThisMonthService $thisMonthService): Response
    {
        $user = auth()->user();
        $thisMonthDTO = $thisMonthService->getThisMonthData($user);

        return Inertia::render('Dashboard', $thisMonthDTO->toArray());
    }
}
