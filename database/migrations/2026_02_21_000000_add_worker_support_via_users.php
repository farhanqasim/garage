<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Workers = users with role 'worker'. Add commission to users; link jobs and cash accounts to user_id.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('commission')->nullable()->after('salary_percentage')->comment('Car wash commission % for role=worker');
            $table->foreignId('bank_account_id')->nullable()->after('commission')->constrained('bank_accounts')->nullOnDelete();
            $table->string('bank_name')->nullable()->after('bank_account_id');
            $table->string('bank_account_title')->nullable()->after('bank_name');
            $table->string('bank_account_number', 100)->nullable()->after('bank_account_title');
            $table->string('bank_iban', 100)->nullable()->after('bank_account_number');
        });

        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->foreignId('worker_user_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('worker_cash_accounts', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
        });
        Schema::table('worker_cash_accounts', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('car_wash_payments', function (Blueprint $table) {
            $table->foreignId('worker_user_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('worker_cash_transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_cash_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
        Schema::table('car_wash_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('worker_user_id');
        });
        Schema::table('worker_cash_accounts', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('worker_id')->nullable(false)->change();
        });
        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('worker_user_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropColumn(['commission', 'bank_name', 'bank_account_title', 'bank_account_number', 'bank_iban']);
        });
    }
};
