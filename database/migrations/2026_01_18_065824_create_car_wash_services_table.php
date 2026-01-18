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
        Schema::create('car_wash_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('label'); // Service name (e.g., "FULL WASH", "ENGINE DETAIL")
            $table->decimal('base_price', 10, 2)->default(0); // Base price in RS
            $table->json('additional_prices')->nullable(); // Array of {label, amount} for extra services
            $table->string('icon')->default('car'); // Icon identifier
            $table->string('color')->default('bg-blue-600'); // Tailwind color class
            $table->string('color_value')->default('#3b82f6'); // Hex color value
            $table->boolean('is_default')->default(false); // Whether it's a default service
            $table->boolean('status')->default(true); // Active/Inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_wash_services');
    }
};
