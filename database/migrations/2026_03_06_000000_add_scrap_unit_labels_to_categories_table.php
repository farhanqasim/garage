<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Editable display names for scrap measurement (e.g. "KG", "Pounds", "count", "pieces").
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('scrap_weight_label', 100)->nullable()->after('scrap_measurement')->comment('Display name for weight unit, e.g. KG, Pounds');
            $table->string('scrap_count_label', 100)->nullable()->after('scrap_weight_label')->comment('Display name for quantity unit, e.g. count, pieces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['scrap_weight_label', 'scrap_count_label']);
        });
    }
};
