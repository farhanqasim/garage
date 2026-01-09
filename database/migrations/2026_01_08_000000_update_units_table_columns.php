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
        Schema::table('units', function (Blueprint $table) {
            // Rename define_base_unit to is_base_unit
            if (Schema::hasColumn('units', 'define_base_unit')) {
                $table->renameColumn('define_base_unit', 'is_base_unit');
            }
            
            // Add decimal_after_point_digit column if it doesn't exist
            if (!Schema::hasColumn('units', 'decimal_after_point_digit')) {
                $table->integer('decimal_after_point_digit')->default(0)->after('allow_decimal')->comment('For displaying units in order');
            }
            
            // Drop old columns if they exist (for backward compatibility, we'll keep them commented)
            // $table->dropColumn(['base_unit_multiplier', 'base_unit_id']); // Uncomment if you want to remove these
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Rename back
            if (Schema::hasColumn('units', 'is_base_unit')) {
                $table->renameColumn('is_base_unit', 'define_base_unit');
            }
            
            // Drop decimal_after_point_digit if it exists
            if (Schema::hasColumn('units', 'decimal_after_point_digit')) {
                $table->dropColumn('decimal_after_point_digit');
            }
        });
    }
};

