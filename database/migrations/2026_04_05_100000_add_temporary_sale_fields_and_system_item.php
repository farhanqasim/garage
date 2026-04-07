<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'temporary_item_name')) {
                $table->string('temporary_item_name', 500)->nullable();
            }
            if (! Schema::hasColumn('sale_items', 'temporary_quality')) {
                $table->string('temporary_quality', 255)->nullable();
            }
            if (! Schema::hasColumn('sale_items', 'voice_transcript')) {
                $table->text('voice_transcript')->nullable();
            }
            if (! Schema::hasColumn('sale_items', 'voice_data')) {
                $table->longText('voice_data')->nullable();
            }
        });

        $userId = (int) (User::query()->orderBy('id')->value('id') ?? 1);
        $exists = DB::table('items')->where('bar_code', '__SALE_TEMPORARY__')->exists();
        if (! $exists) {
            DB::table('items')->insert([
                'user_id' => $userId,
                'bar_code' => '__SALE_TEMPORARY__',
                'short_disc' => 'Temporary sale — add to inventory later',
                'pro_dis' => 'Non-stock line; does not affect warehouse quantity until converted to a real product.',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            foreach (['voice_data', 'voice_transcript', 'temporary_quality', 'temporary_item_name'] as $col) {
                if (Schema::hasColumn('sale_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        DB::table('items')->where('bar_code', '__SALE_TEMPORARY__')->delete();
    }
};
