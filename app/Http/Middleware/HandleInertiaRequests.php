<?php

namespace App\Http\Middleware;

use App\Enums\ExpenseTypeEnum;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'constants' => [
                'expenseTypes' => [
                    'RECURRING' => ExpenseTypeEnum::RECURRING->value,
                    'ONE_TIME' => ExpenseTypeEnum::ONE_TIME->value,
                ],
            ],
            // Timestamps ensure uniqueness of flash messages which then allows repeated messages
            'flash' => [
                'success' => $request->session()->get('success') ? [
                    'message' => $request->session()->get('success'),
                    'timestamp' => now()->timestamp
                ] : null,
                'error' => $request->session()->get('error') ? [
                    'message' => $request->session()->get('error'),
                    'timestamp' => now()->timestamp
                ] : null,
                'info' => $request->session()->get('info') ? [
                    'message' => $request->session()->get('info'),
                    'timestamp' => now()->timestamp
                ] : null,
                'warning' => $request->session()->get('warning') ? [
                    'message' => $request->session()->get('warning'),
                    'timestamp' => now()->timestamp
                ] : null,
            ],
        ];
    }
}
