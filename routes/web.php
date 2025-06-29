<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Welcome page
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard - main authenticated landing page
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/profile.php';
require __DIR__.'/garden.php';
require __DIR__.'/bills.php';
require __DIR__.'/expenses.php';
require __DIR__.'/revenue.php';
require __DIR__.'/water-bank.php';
require __DIR__.'/past-months.php';
