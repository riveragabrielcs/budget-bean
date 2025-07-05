<?php

use App\Http\Controllers\SavingsGoalController;
use Illuminate\Support\Facades\Route;

// Savings Goals/Garden feature routes
Route::middleware('auth')->prefix('garden')->name('garden.')->group(function () {
    Route::get('/', [SavingsGoalController::class, 'index'])->name('index');
    Route::post('/', [SavingsGoalController::class, 'store'])->name('store');
    Route::patch('/{goalId}', [SavingsGoalController::class, 'update'])->name('update');
    Route::delete('/{goalId}', [SavingsGoalController::class, 'destroy'])->name('destroy');

    // Savings actions
    Route::post('/{goalId}/add-savings', [SavingsGoalController::class, 'addSavings'])->name('add-savings');
    Route::post('/{goalId}/withdraw', [SavingsGoalController::class, 'withdraw'])->name('withdraw');
    Route::post('/{goalId}/complete', [SavingsGoalController::class, 'complete'])->name('complete');
});
