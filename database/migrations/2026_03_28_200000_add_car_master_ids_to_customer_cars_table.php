<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_cars', function (Blueprint $table) {
            $table->foreignId('car_manufacturer_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('car_manufacturers')
                ->nullOnDelete();
            $table->foreignId('car_model_id')
                ->nullable()
                ->after('car_manufacturer_id')
                ->constrained('car_models')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_cars', function (Blueprint $table) {
            $table->dropForeign(['car_manufacturer_id']);
            $table->dropForeign(['car_model_id']);
        });
    }
};
