<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('completed_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('month'); // 1-12
            $table->integer('year'); // e.g., 2025

            // Revenue data snapshot
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->enum('calculation_method', ['paycheck', 'custom'])->default('custom');
            $table->decimal('paycheck_amount', 10, 2)->nullable();
            $table->integer('paycheck_count')->nullable();
            $table->decimal('monthly_savings_goal', 10, 2)->default(0);

            // Expense data snapshot
            $table->decimal('total_expenses', 10, 2)->default(0);
            $table->decimal('recurring_bills_total', 10, 2)->default(0);
            $table->decimal('one_time_expenses_total', 10, 2)->default(0);
            $table->json('expenses_snapshot'); // Store all expense details

            // Financial calculations
            $table->decimal('water_collected', 10, 2)->default(0);
            $table->decimal('budget_remaining', 10, 2)->default(0);
            $table->boolean('was_drought', false);

            $table->timestamps();

            // Ensure one record per user per month/year
            $table->unique(['user_id', 'month', 'year']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('completed_months');
    }
};
