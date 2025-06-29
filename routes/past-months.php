<?php

use App\Http\Controllers\CompletedMonthController;
use Illuminate\Support\Facades\Route;

// Completed Months/History routes
Route::middleware('auth')->prefix('past-months')->name('past-months.')->group(function () {
    Route::get('/', [CompletedMonthController::class, 'index'])->name('index');
    Route::match(['get', 'post'], '/check-exists', [CompletedMonthController::class, 'checkMonthExists'])->name('check-exists');
    Route::get('/{completedMonth}', [CompletedMonthController::class, 'show'])->name('show');
    Route::delete('/{completedMonth}', [CompletedMonthController::class, 'destroy'])->name('destroy');
});
