<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_wash_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('worker_id')->constrained('car_wash_workers')->cascadeOnDelete();
            $table->string('worker_name');
            $table->string('captured_photo')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->decimal('accuracy', 10, 2)->nullable()->comment('Accuracy in meters');
            $table->text('address')->nullable();
            $table->string('maps_link', 500)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->string('device_info', 500)->nullable();
            $table->boolean('is_mock_location_detected')->default(false);
            $table->foreignId('marked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_wash_attendance');
    }
};
