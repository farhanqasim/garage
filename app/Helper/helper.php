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

if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        $ones = array(
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen'
        );
        
        $tens = array(
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        );
        
        $number = (int)$number;
        
        if ($number < 20) {
            return $ones[$number];
        } elseif ($number < 100) {
            $tensDigit = (int)($number / 10);
            $onesDigit = $number % 10;
            return $tens[$tensDigit] . ($onesDigit > 0 ? ' ' . $ones[$onesDigit] : '');
        } elseif ($number < 1000) {
            $hundreds = (int)($number / 100);
            $remainder = $number % 100;
            $result = $ones[$hundreds] . ' Hundred';
            if ($remainder > 0) {
                $result .= ' ' . numberToWords($remainder);
            }
            return $result;
        } elseif ($number < 100000) {
            $thousands = (int)($number / 1000);
            $remainder = $number % 1000;
            $result = numberToWords($thousands) . ' Thousand';
            if ($remainder > 0) {
                $result .= ' ' . numberToWords($remainder);
            }
            return $result;
        } elseif ($number < 10000000) {
            $lakhs = (int)($number / 100000);
            $remainder = $number % 100000;
            $result = numberToWords($lakhs) . ' Lakh';
            if ($remainder > 0) {
                $result .= ' ' . numberToWords($remainder);
            }
            return $result;
        } else {
            $crores = (int)($number / 10000000);
            $remainder = $number % 10000000;
            $result = numberToWords($crores) . ' Crore';
            if ($remainder > 0) {
                $result .= ' ' . numberToWords($remainder);
            }
            return $result;
        }
    }
}
