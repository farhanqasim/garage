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
        Schema::create('car_wash_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('car_wash_jobs')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            
            // Inspection items - store as JSON with status for each item
            $table->json('inspection_items')->nullable(); // {item_id: {status: 'excellent'|'good'|'average'|'poor', comment: '...'}}
            
            // Status
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('job_id');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_wash_inspections');
    }
};
