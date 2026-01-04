<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds unit category/type system (Weight, Quantity, Volume, Length)
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->enum('unit_category', ['weight', 'quantity', 'volume', 'length', 'area', 'time', 'other'])->default('other')->after('short_name');
            $table->boolean('is_base_unit')->default(false)->after('define_base_unit')->comment('True if this is a base unit (kg, piece, liter, meter)');
            $table->integer('sort_order')->default(0)->after('status')->comment('For displaying units in order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['unit_category', 'is_base_unit', 'sort_order']);
        });
    }
};

