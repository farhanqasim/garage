<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('worker_cash_accounts', function (Blueprint $table) {
            $table->decimal('total_earned', 15, 2)->default(0)->after('balance');
            $table->decimal('total_paid', 15, 2)->default(0)->after('total_earned');
        });

        // Backfill: existing balance is unpaid (total_earned), total_paid = 0
        DB::table('worker_cash_accounts')->update([
            'total_earned' => DB::raw('balance'),
            'total_paid' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_cash_accounts', function (Blueprint $table) {
            $table->dropColumn(['total_earned', 'total_paid']);
        });
    }
};
