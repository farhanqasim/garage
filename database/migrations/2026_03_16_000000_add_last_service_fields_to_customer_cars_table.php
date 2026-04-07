<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Store last oil-change/service reminder from previous sale for comparison on next visit.
     */
    public function up(): void
    {
        Schema::table('customer_cars', function (Blueprint $table) {
            $table->decimal('last_service_current_km', 15, 2)->nullable()->after('year');
            $table->decimal('last_service_next_km', 15, 2)->nullable()->after('last_service_current_km');
            $table->date('last_service_next_date')->nullable()->after('last_service_next_km');
            $table->decimal('last_service_daily_run_km', 12, 2)->nullable()->after('last_service_next_date');
            $table->decimal('last_service_interval_days', 10, 2)->nullable()->after('last_service_daily_run_km');
            $table->decimal('last_service_interval_months', 10, 2)->nullable()->after('last_service_interval_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_cars', function (Blueprint $table) {
            $table->dropColumn([
                'last_service_current_km',
                'last_service_next_km',
                'last_service_next_date',
                'last_service_daily_run_km',
                'last_service_interval_days',
                'last_service_interval_months',
            ]);
        });
    }
};
