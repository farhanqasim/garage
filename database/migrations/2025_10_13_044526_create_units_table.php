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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('short_name');
            $table->boolean('allow_decimal')->default(0);
            $table->boolean('define_base_unit')->default(0);
            $table->decimal('base_unit_multiplier', 20, 4)->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->foreign('base_unit_id')
                ->references('id')->on('units')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
