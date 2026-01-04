<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores the unit configuration for each item:
     * - One base (primary) unit per item
     * - Multiple secondary units per item with conversion factors
     * 
     * This is the CORE of Vyapar/Tally-style UOM system
     */
    public function up(): void
    {
        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->id();
            
            // Item reference
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            
            // Unit reference
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            
            // Unit role: 'base' or 'secondary'
            $table->enum('unit_role', ['base', 'secondary'])->default('secondary');
            
            // Conversion factor: How many base units = 1 of this unit
            // Example: If base unit is 'kg' and this is 'g', then conversion_factor = 0.001
            // Example: If base unit is 'piece' and this is 'box' (12 pieces), then conversion_factor = 12
            $table->decimal('conversion_factor', 20, 8)->default(1.00000000)
                ->comment('How many base units = 1 of this unit. For base unit, this is always 1');
            
            // Display order for UI
            $table->integer('display_order')->default(0);
            
            // Is this unit enabled for this item?
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Unique constraint: One base unit per item, but multiple secondary units allowed
            $table->unique(['item_id', 'unit_role'], 'unique_item_base_unit');
            
            // Index for faster lookups
            $table->index(['item_id', 'is_active']);
            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_unit_conversions');
    }
};

