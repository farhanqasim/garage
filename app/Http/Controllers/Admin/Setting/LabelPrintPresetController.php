<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LabelPrintPresetController extends Controller
{
    private const PRESETS_KEY = 'label_print_presets';

    private const DEFAULT_KEY = 'label_print_default_preset_id';

    private function defaultPresets(): array
    {
        return [
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
    }

    private function ensureSettingsExist(): void
    {
        Setting::firstOrCreate(
            ['key' => self::PRESETS_KEY],
            ['label' => 'Label print presets', 'type' => 'json', 'value' => json_encode($this->defaultPresets()), 'options' => null]
        );
        Setting::firstOrCreate(
            ['key' => self::DEFAULT_KEY],
            ['label' => 'Default label print preset', 'type' => 'text', 'value' => '2x1_in', 'options' => null]
        );
    }

    public function index()
    {
        $this->ensureSettingsExist();

        $presetsRow = Setting::where('key', self::PRESETS_KEY)->first();
        $defaultRow = Setting::where('key', self::DEFAULT_KEY)->first();

        $presets = [];
        try {
            $decoded = json_decode((string) ($presetsRow->value ?? '[]'), true);
            $presets = is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            $presets = [];
        }

        return response()->json([
            'success' => true,
            'default_preset_id' => (string) ($defaultRow->value ?? '2x1_in'),
            'presets' => $presets,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureSettingsExist();

        $presets = $request->input('presets', []);
        $defaultPresetId = (string) $request->input('default_preset_id', '');

        if (! is_array($presets)) {
            return response()->json(['success' => false, 'message' => 'Invalid presets payload.'], 422);
        }

        $normalized = [];
        foreach ($presets as $p) {
            if (! is_array($p)) {
                continue;
            }
            $id = trim((string) ($p['id'] ?? ''));
            $name = trim((string) ($p['name'] ?? ''));
            $unit = strtolower(trim((string) ($p['unit'] ?? 'in')));
            $width = (float) ($p['width'] ?? 0);
            $height = (float) ($p['height'] ?? 0);

            if ($id === '') {
                $id = Str::slug($name ?: 'preset').'_'.Str::random(6);
            }
            if ($name === '') {
                continue;
            }
            if (! in_array($unit, ['in', 'mm'], true)) {
                $unit = 'in';
            }
            if ($width <= 0 || $height <= 0) {
                continue;
            }

            $margin = $p['margin'] ?? [];
            $padding = (float) ($p['padding'] ?? 0);
            $barcodeHeight = (float) ($p['barcode_height'] ?? 0);
            $font = $p['font'] ?? [];

            $normalized[] = [
                'id' => $id,
                'name' => $name,
                'unit' => $unit,
                'width' => $width,
                'height' => $height,
                'margin' => [
                    'top' => (float) ($margin['top'] ?? 0),
                    'right' => (float) ($margin['right'] ?? 0),
                    'bottom' => (float) ($margin['bottom'] ?? 0),
                    'left' => (float) ($margin['left'] ?? 0),
                ],
                'padding' => max(0, $padding),
                'barcode_height' => max(0, $barcodeHeight),
                'font' => [
                    'line1' => (int) ($font['line1'] ?? 14),
                    'line2' => (int) ($font['line2'] ?? 12),
                    'rate' => (int) ($font['rate'] ?? 11),
                ],
            ];
        }

        if ($normalized === []) {
            return response()->json(['success' => false, 'message' => 'At least one valid preset is required.'], 422);
        }

        $ids = array_map(fn ($x) => $x['id'], $normalized);
        if ($defaultPresetId === '' || ! in_array($defaultPresetId, $ids, true)) {
            $defaultPresetId = $ids[0];
        }

        Setting::where('key', self::PRESETS_KEY)->update(['value' => json_encode($normalized)]);
        Setting::where('key', self::DEFAULT_KEY)->update(['value' => $defaultPresetId]);

        return response()->json([
            'success' => true,
            'default_preset_id' => $defaultPresetId,
            'presets' => $normalized,
        ]);
    }
}
