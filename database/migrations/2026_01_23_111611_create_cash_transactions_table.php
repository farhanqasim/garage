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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->enum('direction', ['credit', 'debit']);
            $table->enum('type', ['job_payment', 'cash_transfer', 'bank_transfer', 'commission', 'admin_adjustment']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_table')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
