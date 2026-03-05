<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * entry_type: 'purchase' | 'return' | 'scrap' for cart item (so RETURN/SCRAP badge shows on reload).
     */
    public function up(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_cart', 'entry_type')) {
                $table->string('entry_type', 20)->default('purchase')->after('item_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_cart', 'entry_type')) {
                $table->dropColumn('entry_type');
            }
        });
    }
};
