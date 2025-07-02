<?php

namespace App\Providers;

use App\Repositories\Bill\BillRepositoryInterface;
use App\Repositories\Bill\DBBillRepository;
use App\Repositories\Bill\SessionBillRepository;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BillRepositoryInterface::class, function ($app) {
            return auth()->check()
                ? $app->make(DBBillRepository::class)
                : $app->make(SessionBillRepository::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
