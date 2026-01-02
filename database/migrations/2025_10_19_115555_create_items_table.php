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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_deactive')->default(false);
            $table->boolean('is_dead')->default(false);

            $table->string('bar_code')->unique();
            $table->string('name');
            $table->string('mileage')->nullable();
            $table->string('type')->nullable();
            $table->string('plato')->nullable();
            $table->string('amphors')->nullable();
            $table->string('lineitems')->nullable();
            $table->string('company')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('veh_type')->nullable();
            $table->string('p_brochure')->nullable();

            $table->string('image')->nullable(); // thumbnail
            $table->json('images')->nullable();

            $table->string('car_company')->nullable();
            $table->string('car_name')->nullable();
            $table->string('car_model_name')->nullable();
            $table->string('car_manufactur_country')->nullable();
            $table->date('car_manufacture_year')->nullable();
            $table->string('volt')->nullable();
            $table->string('cca')->nullable();
            $table->string('minus_pool_direction')->nullable();
            $table->string('tecnology')->nullable();
            $table->string('grade')->nullable();
            $table->string('farmula')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('battery_size')->nullable();


            $table->string('bussiness_location')->nullable();
            $table->string('p_quality')->nullable();
            $table->string('pro_part_number')->nullable();
            $table->string('l_stock')->nullable(); // low stock threshold
            $table->string('m_stock')->nullable(); // maintain stock level
            $table->string('product_unit')->nullable();
            $table->string('packing')->nullable();
            $table->string('scale')->nullable();
            $table->decimal('filling', 8, 2)->nullable();
            $table->decimal('weight_for_delivery', 8, 2)->nullable();


            $table->decimal('packing_purchase_rate', 10, 2)->default(0.00);

            // Other Info
            $table->integer('min_qty')->default(0);
            $table->integer('max_qty')->default(0);
            $table->date('update_date')->nullable();
            $table->string('rack')->nullable();
            $table->string('supplier')->nullable();

            // Description
            $table->longText('pro_dis')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
