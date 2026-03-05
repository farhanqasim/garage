<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Adds po_status for Purchase Order workflow if column is missing.
     */
    public function up(): void
    {
        if (Schema::hasColumn('purchases', 'po_status')) {
            return;
        }
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('po_status', 20)->nullable()->after('is_purchase_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('purchases', 'po_status')) {
            return;
        }
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('po_status');
        });
    }
};
