<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * User workers use user_id; legacy workers use worker_id. Allow null worker_id.
     */
    public function up(): void
    {
        Schema::table('worker_cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->unsignedBigInteger('worker_id')->nullable()->change();
            $table->foreign('worker_id')->references('id')->on('car_wash_workers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_cash_transactions', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->unsignedBigInteger('worker_id')->nullable(false)->change();
            $table->foreign('worker_id')->references('id')->on('car_wash_workers')->onDelete('cascade');
        });
    }
};
