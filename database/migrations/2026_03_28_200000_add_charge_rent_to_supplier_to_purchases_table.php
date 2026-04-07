<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchases')) {
            return;
        }
        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'charge_rent_to_supplier')) {
                // Default true: existing behaviour (rent reduced supplier payable) until user turns OFF on new bills.
                $table->boolean('charge_rent_to_supplier')->default(true)->after('rent_paid');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchases')) {
            return;
        }
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'charge_rent_to_supplier')) {
                $table->dropColumn('charge_rent_to_supplier');
            }
        });
    }
};
