<?php

use App\Http\Controllers\MonthlyRevenueController;
use Illuminate\Support\Facades\Route;

// Monthly Revenue management routes
Route::middleware('auth')->prefix('revenue')->name('revenue.')->group(function () {
    Route::post('/', [MonthlyRevenueController::class, 'store'])->name('store');
    Route::post('/ajax', [MonthlyRevenueController::class, 'storeAjax'])->name('store.ajax');
    Route::get('/current', [MonthlyRevenueController::class, 'getCurrent'])->name('current');
    Route::patch('/savings-goal', [MonthlyRevenueController::class, 'updateSavingsGoal'])->name('savings-goal');
    Route::delete('/', [MonthlyRevenueController::class, 'destroy'])->name('destroy');
});
