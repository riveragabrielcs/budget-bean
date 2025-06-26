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
        Schema::create('monthly_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_revenue', 10, 2);
            $table->enum('calculation_method', ['paycheck', 'custom'])->default('custom');
            $table->decimal('paycheck_amount', 10, 2)->nullable();
            $table->integer('paycheck_count')->nullable();
            $table->integer('revenue_month'); // 1-12
            $table->integer('revenue_year'); // e.g., 2025
            $table->timestamps();

            // Ensure one revenue record per user per month
            $table->unique(['user_id', 'revenue_month', 'revenue_year']);
            $table->index(['user_id', 'revenue_month', 'revenue_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_revenues');
    }
};
