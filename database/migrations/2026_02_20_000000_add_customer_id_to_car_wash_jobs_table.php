<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Jobs table: use customer_id (+ customer_car_id) instead of customer_name, vehicle_no, mobile.
     * Safe to run after 2026_02_18_200000_ensure_* (skips if columns already exist).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('car_wash_jobs', 'customer_id')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('worker_id');
            });
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            });
        }
        if (!Schema::hasColumn('car_wash_jobs', 'customer_car_id')) {
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_car_id')->nullable()->after('customer_id');
            });
            Schema::table('car_wash_jobs', function (Blueprint $table) {
                $table->foreign('customer_car_id')->references('id')->on('customer_cars')->onDelete('set null');
            });
        }
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('worker_id');
            $table->string('vehicle_no')->nullable()->after('customer_name');
            $table->string('mobile')->nullable()->after('vehicle_no');
        });

        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['customer_car_id']);
            $table->dropColumn(['customer_id', 'customer_car_id']);
        });
    }
};
