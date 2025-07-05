<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ------------------------------------
// Main Page - This Month's Dashboard
// ------------------------------------
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/profile.php';
require __DIR__.'/garden.php';
require __DIR__.'/bills.php';
require __DIR__.'/expenses.php';
require __DIR__.'/revenue.php';
require __DIR__.'/water-bank.php';
require __DIR__.'/past-months.php';
