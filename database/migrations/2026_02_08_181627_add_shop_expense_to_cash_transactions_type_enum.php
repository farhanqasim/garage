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
        // Add 'shop_expense' to the type enum in cash_transactions table
        \DB::statement("ALTER TABLE `cash_transactions` MODIFY COLUMN `type` ENUM('job_payment', 'cash_transfer', 'bank_transfer', 'commission', 'admin_adjustment', 'shop_expense') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'shop_expense' from the type enum (only if no records exist with this type)
        // Note: This will fail if there are existing shop_expense transactions
        \DB::statement("ALTER TABLE `cash_transactions` MODIFY COLUMN `type` ENUM('job_payment', 'cash_transfer', 'bank_transfer', 'commission', 'admin_adjustment') NOT NULL");
    }
};
