<?php

use App\Http\Controllers\RecurringBillController;
use Illuminate\Support\Facades\Route;

// Recurring Bills management routes
Route::middleware('auth')->prefix('bills')->name('bills.')->group(function () {
    Route::get('/', [RecurringBillController::class, 'index'])->name('index');
    Route::post('/', [RecurringBillController::class, 'store'])->name('store');
    Route::patch('/{recurringBill}', [RecurringBillController::class, 'update'])->name('update');
    Route::delete('/{recurringBill}', [RecurringBillController::class, 'destroy'])->name('destroy');
});
