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
            if (! Schema::hasColumn('sale_items', 'line_note')) {
                $table->text('line_note')->nullable()->after('warranty');
            }
            if (! Schema::hasColumn('sale_items', 'line_image')) {
                $table->longText('line_image')->nullable()->after('line_note');
            }
        });

        $userId = (int) (User::query()->orderBy('id')->value('id') ?? 1);
        $exists = DB::table('items')->where('bar_code', '__SALE_PLACEHOLDER__')->exists();
        if (! $exists) {
            DB::table('items')->insert([
                'user_id' => $userId,
                'bar_code' => '__SALE_PLACEHOLDER__',
                'short_disc' => 'Placeholder — set product later',
                'pro_dis' => 'Temporary placeholder line; replace with a real product when reviewing.',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'line_image')) {
                $table->dropColumn('line_image');
            }
            if (Schema::hasColumn('sale_items', 'line_note')) {
                $table->dropColumn('line_note');
            }
        });

        DB::table('items')->where('bar_code', '__SALE_PLACEHOLDER__')->delete();
    }
};
