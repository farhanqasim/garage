<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Store display name from search selection so edit shows same name.
     */
    public function up(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            $table->string('item_name', 255)->nullable()->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            $table->dropColumn('item_name');
        });
    }
};
