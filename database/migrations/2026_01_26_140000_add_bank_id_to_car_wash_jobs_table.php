<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->after('payment_method')->constrained('banks')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('car_wash_jobs', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
        });
    }
};
