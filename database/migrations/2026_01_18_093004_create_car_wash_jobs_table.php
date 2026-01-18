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
        Schema::create('car_wash_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('car_wash_services')->onDelete('set null');
            $table->foreignId('worker_id')->nullable()->constrained('car_wash_workers')->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('mobile')->nullable();
            $table->string('service_name'); // Service label at the time of job
            $table->decimal('price', 10, 2)->default(0); // Total price
            $table->json('additional_prices')->nullable(); // Selected additional services
            $table->string('worker_name')->nullable(); // Worker name at the time of job
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_seconds')->nullable(); // Duration in seconds
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_wash_jobs');
    }
};
