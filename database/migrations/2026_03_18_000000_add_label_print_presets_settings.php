<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            [
                'id' => '2x1_in',
                'name' => '2×1 Label',
                'unit' => 'in',
                'width' => 2.0,
                'height' => 1.0,
                'margin' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                'padding' => 0.08,
                'barcode_height' => 0.48,
                'font' => ['line1' => 14, 'line2' => 12, 'rate' => 11],
            ],
            [
                'id' => '2x4_in',
                'name' => '2×4 Label',
                'unit' => 'in',
                'width' => 2.0,
                'height' => 4.0,
                'margin' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                'padding' => 0.10,
                'barcode_height' => 0.62,
                'font' => ['line1' => 18, 'line2' => 14, 'rate' => 12],
            ],
        ];

        $exists = DB::table('settings')->where('key', 'label_print_presets')->exists();
        if (! $exists) {
            DB::table('settings')->insert([
                'label' => 'Label print presets',
                'key' => 'label_print_presets',
                'value' => json_encode($defaults),
                'type' => 'json',
                'options' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $existsDefault = DB::table('settings')->where('key', 'label_print_default_preset_id')->exists();
        if (! $existsDefault) {
            DB::table('settings')->insert([
                'label' => 'Default label print preset',
                'key' => 'label_print_default_preset_id',
                'value' => '2x1_in',
                'type' => 'text',
                'options' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'label_print_presets',
            'label_print_default_preset_id',
        ])->delete();
    }
};
