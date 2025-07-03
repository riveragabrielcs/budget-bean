<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings based on configuration.
     */
    public function register(): void
    {
        $repositories = config('repositories.mappings', []);

        foreach ($repositories as $interface => $implementations) {
            $this->app->bind($interface, function ($app) use ($implementations) {
                $strategy = $this->getRepositoryStrategy();
                return $app->make($implementations[$strategy]);
            });
        }
    }

    /**
     * Determine repository strategy from config and auth state.
     */
    private function getRepositoryStrategy(): string
    {
        return auth()->check()
            ? config('repositories.default_strategy', 'authenticated')
            : config('repositories.guest_strategy', 'guest');
    }

    public function boot(): void
    {
    }
}
