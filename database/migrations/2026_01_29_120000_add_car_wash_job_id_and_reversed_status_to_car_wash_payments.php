<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('car_wash_payments', function (Blueprint $table) {
            $table->foreignId('car_wash_job_id')->nullable()->after('worker_id')->constrained('car_wash_jobs')->onDelete('set null');
        });

        DB::statement("ALTER TABLE car_wash_payments MODIFY COLUMN status ENUM('pending', 'completed', 'failed', 'cancelled', 'reversed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_wash_payments', function (Blueprint $table) {
            $table->dropForeign(['car_wash_job_id']);
        });

        DB::statement("ALTER TABLE car_wash_payments MODIFY COLUMN status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending'");
    }
};
