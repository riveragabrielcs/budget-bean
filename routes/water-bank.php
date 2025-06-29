<?php

use App\Http\Controllers\WaterBankController;
use Illuminate\Support\Facades\Route;

// Water Bank feature routes
Route::middleware('auth')->prefix('water-bank')->name('water-bank.')->group(function () {
    Route::post('/end-month', [WaterBankController::class, 'endMonth'])->name('end-month');
    Route::post('/water-goal/{savingsGoal}', [WaterBankController::class, 'waterGoal'])->name('water-goal');
    Route::post('/water-all', [WaterBankController::class, 'waterAllGoals'])->name('water-all');
    Route::post('/add-manual', [WaterBankController::class, 'addManualWater'])->name('add-manual');
    Route::get('/status', [WaterBankController::class, 'getStatus'])->name('status');
});
