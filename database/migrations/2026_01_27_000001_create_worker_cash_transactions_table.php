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
        Schema::create('worker_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('car_wash_workers')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('type', 20); // credit, debit
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_cash_transactions');
    }
};
