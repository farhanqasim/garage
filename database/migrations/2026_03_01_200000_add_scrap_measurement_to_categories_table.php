<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * For scrap categories: 'weight' = by weight (KG), 'count' = by quantity (pieces).
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('scrap_measurement', 20)->nullable()->after('type')->comment('weight=by KG, count=by quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('scrap_measurement');
        });
    }
};
