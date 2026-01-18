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
        Schema::create('car_wash_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('car_wash_jobs')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            
            // Expense items - store as JSON array
            $table->json('expense_items')->nullable(); // [{name: 'Tea', icon: '☕', quantity: 2, price: 50, total: 100}, ...]
            
            // Total expense amount
            $table->decimal('total_amount', 10, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('job_id');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_wash_expenses');
    }
};
