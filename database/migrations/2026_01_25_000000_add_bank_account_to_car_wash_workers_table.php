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
        Schema::table('car_wash_workers', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('status');
            $table->string('bank_account_title')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bank_account_title');
            $table->string('bank_iban')->nullable()->after('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_wash_workers', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_title', 'bank_account_number', 'bank_iban']);
        });
    }
};
