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
        Schema::create('car_wash_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->json('additional_mobiles')->nullable(); // Array of {name, mobile}
            $table->string('father_name')->nullable();
            $table->string('father_mobile')->nullable();
            $table->json('father_additional_mobiles')->nullable(); // Array of {name, mobile}
            $table->text('location')->nullable(); // Home address
            $table->integer('commission')->default(0); // Commission percentage
            $table->string('id_card_front')->nullable(); // Image path
            $table->string('id_card_back')->nullable(); // Image path
            $table->string('father_card_front')->nullable(); // Image path
            $table->string('father_card_back')->nullable(); // Image path
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_wash_workers');
    }
};
