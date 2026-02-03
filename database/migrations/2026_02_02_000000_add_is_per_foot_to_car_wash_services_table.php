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
        Schema::table('car_wash_services', function (Blueprint $table) {
            $table->boolean('is_per_foot')->default(false)->after('inspection_compulsory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_wash_services', function (Blueprint $table) {
            $table->dropColumn('is_per_foot');
        });
    }
};
