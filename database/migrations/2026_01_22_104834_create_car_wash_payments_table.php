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
        Schema::create('car_wash_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('worker_id')->nullable()->constrained('car_wash_workers')->onDelete('set null');
            $table->enum('payment_type', ['commission', 'cash_transfer', 'bank_transfer', 'expense', 'other'])->default('commission');
            $table->decimal('amount', 15, 2);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null');
            $table->foreignId('from_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null'); // For cash transfers
            $table->foreignId('to_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null'); // For cash transfers
            $table->string('transaction_id')->nullable();
            $table->date('payment_date');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['branch_id', 'payment_date']);
            $table->index(['worker_id', 'payment_date']);
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_wash_payments');
    }
};
