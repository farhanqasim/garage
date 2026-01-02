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
        Schema::create('vehical_types', function (Blueprint $table) {
            $table->id();
            $table->string('car_model_name')->nullable();
            $table->string('car_manufactured_country')->nullable();
            $table->string('car_manufacturer')->nullable();
            $table->string('carmanufactured_year')->nullable();
            $table->string('engine_cc')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehical_types');
    }
};
