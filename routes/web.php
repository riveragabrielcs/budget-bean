<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonthlyExpenseController;
use App\Http\Controllers\MonthlyRevenueController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringBillController;
use App\Http\Controllers\SavingsGoalController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/test-401', function() { abort(401); });
Route::get('/test-403', function() { abort(403); });
Route::get('/test-419', function() { abort(419); });
Route::get('/test-429', function() { abort(429); });
Route::get('/test-500', function() { abort(500); });
Route::get('/test-503', function() { abort(503); });

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Savings Goals / Garden routes
    Route::get('/garden', [SavingsGoalController::class, 'index'])->name('garden.index');
    Route::post('/garden', [SavingsGoalController::class, 'store'])->name('garden.store');
    Route::patch('/garden/{savingsGoal}', [SavingsGoalController::class, 'update'])->name('garden.update');
    Route::delete('/garden/{savingsGoal}', [SavingsGoalController::class, 'destroy'])->name('garden.destroy');

    // Savings actions
    Route::post('/garden/{savingsGoal}/add-savings', [SavingsGoalController::class, 'addSavings'])->name('garden.add-savings');
    Route::post('/garden/{savingsGoal}/withdraw', [SavingsGoalController::class, 'withdraw'])->name('garden.withdraw');
    Route::post('/garden/{savingsGoal}/complete', [SavingsGoalController::class, 'complete'])->name('garden.complete');
    // Recurring Bills routes
    Route::get('/bills', [RecurringBillController::class, 'index'])->name('bills.index');
    Route::post('/bills', [RecurringBillController::class, 'store'])->name('bills.store');
    Route::patch('/bills/{recurringBill}', [RecurringBillController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{recurringBill}', [RecurringBillController::class, 'destroy'])->name('bills.destroy');

    // Monthly Expenses routes
    Route::post('/expenses', [MonthlyExpenseController::class, 'store'])->name('expenses.store');
    Route::post('/expenses/ajax', [MonthlyExpenseController::class, 'storeAjax'])->name('expenses.store.ajax');
    Route::patch('/expenses/{monthlyExpense}', [MonthlyExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{monthlyExpense}', [MonthlyExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::delete('/expenses/{monthlyExpense}/ajax', [MonthlyExpenseController::class, 'destroyAjax'])->name('expenses.destroy.ajax');

    // Monthly Revenue routes
    Route::post('/revenue', [MonthlyRevenueController::class, 'store'])->name('revenue.store');
    Route::post('/revenue/ajax', [MonthlyRevenueController::class, 'storeAjax'])->name('revenue.store.ajax');
    Route::get('/revenue/current', [MonthlyRevenueController::class, 'getCurrent'])->name('revenue.current');
    Route::patch('/revenue/savings-goal', [MonthlyRevenueController::class, 'updateSavingsGoal'])->name('revenue.savings-goal');
    Route::delete('/revenue', [MonthlyRevenueController::class, 'destroy'])->name('revenue.destroy');
});

require __DIR__.'/auth.php';
