<?php

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Setting;

if (!function_exists('saveSingleFile')) {
    function saveSingleFile($file, $path)
    {
        try {
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded.');
            }
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $fullPath = public_path($path); // e.g., public/item
            // Ensure the directory exists
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            $file->move($fullPath, $filename);
            return $path . '/' . $filename; // e.g., item/1234567890.jpg
        } catch (\Exception $e) {
            \Log::error('saveSingleFile failed: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'path' => $path
            ]);
            throw $e;
        }
    }
}

if (!function_exists('saveMultipleFiles')) {
    function saveMultipleFiles($files, $path)
    {
        $savedFilePaths = [];
        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        foreach ($files as $file) {
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded: ' . $file->getClientOriginalName());
            }
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($fullPath, $filename);
            $savedFilePaths[] = $path . '/' . $filename;
        }
        return $savedFilePaths;
    }
}

if (!function_exists('genrateSlug')) {
    function genrateSlug($name, $table_name)
    {
        return \Str::slug($name) . '-' . DB::select("SHOW TABLE STATUS LIKE '$table_name'")[0]->Auto_increment;
    }
}

if (!function_exists('getStatusName')) {
    function getStatusName($status)
    {
        $statuss = [
            'active' => __('Active'),
            'in-active' => __('In-Active'),
        ];
        return $statuss[$status] ?? $status;
    }
}

if (!function_exists('setting_value')) {
    function setting_value($key, $optional = null)
    {
        return Setting::where('key', $key)->first()->value ?? $optional;
    }
}
