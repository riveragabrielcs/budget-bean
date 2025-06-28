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
        Schema::create('water_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('water_bank_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 10, 2);
            $table->string('source'); // month_end, manual_add, plant_watering, etc.
            $table->text('description')->nullable();
            $table->foreignId('savings_goal_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('balance_after', 10, 2);
            $table->timestamps();

            $table->index(['water_bank_id', 'created_at']);
            $table->index(['type', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_bank_transactions');
    }
};
