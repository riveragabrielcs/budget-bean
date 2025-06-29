<?php

use App\Http\Controllers\MonthlyExpenseController;
use Illuminate\Support\Facades\Route;

// Monthly Expenses management routes
Route::middleware('auth')->prefix('expenses')->name('expenses.')->group(function () {
    Route::post('/', [MonthlyExpenseController::class, 'store'])->name('store');
    Route::post('/ajax', [MonthlyExpenseController::class, 'storeAjax'])->name('store.ajax');
    Route::patch('/{monthlyExpense}', [MonthlyExpenseController::class, 'update'])->name('update');
    Route::delete('/{monthlyExpense}', [MonthlyExpenseController::class, 'destroy'])->name('destroy');
    Route::delete('/{monthlyExpense}/ajax', [MonthlyExpenseController::class, 'destroyAjax'])->name('destroy.ajax');
});
