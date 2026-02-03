<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_wash_attendances', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->change();
            $table->foreignId('attended_user_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('car_wash_attendances', function (Blueprint $table) {
            $table->dropForeign(['attended_user_id']);
            $table->foreignId('worker_id')->nullable(false)->change();
        });
    }
};
