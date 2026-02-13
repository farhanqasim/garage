<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->string('statement_reference')->nullable();
            $table->foreignId('matched_payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->boolean('reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
