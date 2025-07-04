<?php

namespace App\Providers;

use App\Repositories\Bill\BillRepositoryInterface;
use App\Repositories\Bill\DBBillRepository;
use App\Repositories\Bill\SessionBillRepository;
use App\Repositories\SavingsGoal\DBSavingsGoalRepository;
use App\Repositories\SavingsGoal\SavingsGoalRepositoryInterface;
use App\Repositories\SavingsGoal\SessionSavingsGoalRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        JsonResource::withoutWrapping();
    }
}
