<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->string('payment_method', 50)->nullable()->after('notes')->comment('cash or bank');
        });
    }

    public function down(): void
    {
        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
