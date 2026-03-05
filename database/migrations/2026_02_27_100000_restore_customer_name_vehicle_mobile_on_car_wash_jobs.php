<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore customer_name, vehicle_no, mobile on car_wash_jobs for storing
     * typed/voice name and display when no customer is linked (or as snapshot).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('car_wash_jobs', 'customer_name')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->string('customer_name')->nullable()->after('customer_car_id');
            });
        }
        if (!Schema::hasColumn('car_wash_jobs', 'vehicle_no')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->string('vehicle_no')->nullable()->after('customer_name');
            });
        }
        if (!Schema::hasColumn('car_wash_jobs', 'mobile')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->string('mobile')->nullable()->after('vehicle_no');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('car_wash_jobs', 'customer_name')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->dropColumn('customer_name');
            });
        }
        if (Schema::hasColumn('car_wash_jobs', 'vehicle_no')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->dropColumn('vehicle_no');
            });
        }
        if (Schema::hasColumn('car_wash_jobs', 'mobile')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->dropColumn('mobile');
            });
        }
    }
};
