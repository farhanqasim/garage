<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_wash_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('car_wash_workers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // who marked
            $table->string('captured_photo')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('accuracy', 10, 2)->nullable(); // meters
            $table->text('address')->nullable();
            $table->string('maps_link', 500)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->json('device_info')->nullable();
            $table->boolean('is_mock_location_detected')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_wash_attendances');
    }
};
