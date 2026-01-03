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
        Schema::create('unit_base_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('base_unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('multiplier', 20, 4)->default(1.0000);
            $table->timestamps();
            
            // Prevent duplicate combinations
            $table->unique(['unit_id', 'base_unit_id', 'multiplier'], 'unique_unit_base_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_base_units');
    }
};
