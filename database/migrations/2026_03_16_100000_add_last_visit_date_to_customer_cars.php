<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Date of last visit/sale when service data was recorded (for early-completion / actual daily average).
     */
    public function up(): void
    {
        Schema::table('customer_cars', function (Blueprint $table) {
            $table->date('last_visit_date')->nullable()->after('last_service_interval_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_cars', function (Blueprint $table) {
            $table->dropColumn('last_visit_date');
        });
    }
};
