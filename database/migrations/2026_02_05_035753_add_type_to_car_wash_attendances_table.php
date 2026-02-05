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
        Schema::table('car_wash_attendances', function (Blueprint $table) {
            $table->enum('attendance_type', ['in', 'out'])->default('in')->after('worker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_wash_attendances', function (Blueprint $table) {
            $table->dropColumn('attendance_type');
        });
    }
};
