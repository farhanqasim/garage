<?php

namespace App\Support;

/**
 * Human-readable product type for item display lines (e.g. PART - BRAKE PAD • A+ • HONDA).
 */
class ItemProductTypeLabel
{
    /**
     * Prefer category name, then subcategory, then mapped item->type (breakpad, filters, …).
     */
    public static function resolve(?string $categoryName, ?string $itemType, ?string $subcategoryName = null): ?string
    {
        $cat = trim((string) $categoryName);
        if ($cat !== '' && ! preg_match('/^(other|misc|general|n\/a)$/i', $cat)) {
            return $cat;
        }
        $sub = trim((string) $subcategoryName);
        if ($sub !== '' && ! preg_match('/^(other|misc)$/i', $sub)) {
            return $sub;
        }
        $t = strtolower(trim((string) $itemType));
        if ($t === '') {
            return null;
        }
        $map = [
            'breakpad' => 'BRAKE PAD',
            'filters' => 'FILTER',
            'parts' => 'PART',
            'oil' => 'OIL',
            'battery' => 'BATTERY',
            'scrap' => 'SCRAP',
        ];
        if (isset($map[$t])) {
            return $map[$t];
        }

        return strtoupper(str_replace(['_', '-'], ' ', $t));
    }
}
