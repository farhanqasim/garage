<?php

namespace App\Helpers;

use App\Models\Item;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Unit Conversion Helper Class
 * 
 * This class handles ALL unit conversions in the system,
 * exactly like Vyapar/Tally does internally.
 * 
 * CORE PRINCIPLE:
 * - All calculations (stock, purchase, sale, GST) are done in BASE UNIT
 * - Secondary units are only for display and user input
 * - Automatic conversion happens before any database operation
 */
class UnitConversionHelper
{
    /**
     * Get base unit for an item
     * 
     * @param int $itemId
     * @return Unit|null
     */
    public static function getBaseUnit($itemId)
    {
        $conversion = DB::table('item_unit_conversions')
            ->where('item_id', $itemId)
            ->where('unit_role', 'base')
            ->where('is_active', true)
            ->first();
        
        if (!$conversion) {
            return null;
        }
        
        return Unit::find($conversion->unit_id);
    }
    
    /**
     * Get all secondary units for an item
     * 
     * @param int $itemId
     * @return \Illuminate\Support\Collection
     */
    public static function getSecondaryUnits($itemId)
    {
        $conversions = DB::table('item_unit_conversions')
            ->where('item_id', $itemId)
            ->where('unit_role', 'secondary')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        $units = collect();
        foreach ($conversions as $conversion) {
            $unit = Unit::find($conversion->unit_id);
            if ($unit) {
                $unit->conversion_factor = $conversion->conversion_factor;
                $unit->display_order = $conversion->display_order;
                $units->push($unit);
            }
        }
        
        return $units;
    }
    
    /**
     * Get all units (base + secondary) for an item
     * 
     * @param int $itemId
     * @return array ['base' => Unit, 'secondary' => Collection]
     */
    public static function getAllUnits($itemId)
    {
        return [
            'base' => self::getBaseUnit($itemId),
            'secondary' => self::getSecondaryUnits($itemId)
        ];
    }
    
    /**
     * Convert quantity from any unit to base unit
     * 
     * FORMULA: base_quantity = quantity × conversion_factor
     * 
     * Examples:
     * - Base: kg, Input: 500g, conversion_factor: 0.001 → 500 × 0.001 = 0.5 kg
     * - Base: piece, Input: 2 boxes, conversion_factor: 12 → 2 × 12 = 24 pieces
     * 
     * @param float $quantity Quantity in source unit
     * @param int $itemId Item ID
     * @param int|null $sourceUnitId Unit ID of source (null means base unit)
     * @return float Quantity in base unit
     */
    public static function convertToBaseUnit($quantity, $itemId, $sourceUnitId = null)
    {
        // If no source unit specified, assume it's already in base unit
        if (!$sourceUnitId) {
            return (float) $quantity;
        }
        
        // Get conversion factor for this item-unit combination
        $conversion = DB::table('item_unit_conversions')
            ->where('item_id', $itemId)
            ->where('unit_id', $sourceUnitId)
            ->where('is_active', true)
            ->first();
        
        if (!$conversion) {
            Log::warning("Unit conversion not found for item {$itemId} and unit {$sourceUnitId}");
            // Return as-is if conversion not found (fallback)
            return (float) $quantity;
        }
        
        // Convert to base unit
        $baseQuantity = (float) $quantity * (float) $conversion->conversion_factor;
        
        return round($baseQuantity, 8); // High precision for accurate calculations
    }
    
    /**
     * Convert quantity from base unit to any other unit
     * 
     * FORMULA: converted_quantity = base_quantity ÷ conversion_factor
     * 
     * Examples:
     * - Base: 0.5 kg, Target: g, conversion_factor: 0.001 → 0.5 ÷ 0.001 = 500 g
     * - Base: 24 pieces, Target: box, conversion_factor: 12 → 24 ÷ 12 = 2 boxes
     * 
     * @param float $baseQuantity Quantity in base unit
     * @param int $itemId Item ID
     * @param int $targetUnitId Target unit ID
     * @return float Quantity in target unit
     */
    public static function convertFromBaseUnit($baseQuantity, $itemId, $targetUnitId)
    {
        // If target unit is base unit, return as-is
        $baseUnit = self::getBaseUnit($itemId);
        if ($baseUnit && $baseUnit->id == $targetUnitId) {
            return (float) $baseQuantity;
        }
        
        // Get conversion factor
        $conversion = DB::table('item_unit_conversions')
            ->where('item_id', $itemId)
            ->where('unit_id', $targetUnitId)
            ->where('is_active', true)
            ->first();
        
        if (!$conversion) {
            Log::warning("Unit conversion not found for item {$itemId} and unit {$targetUnitId}");
            return (float) $baseQuantity;
        }
        
        // Convert from base unit
        $convertedQuantity = (float) $baseQuantity / (float) $conversion->conversion_factor;
        
        return round($convertedQuantity, 8);
    }
    
    /**
     * Convert quantity from one unit to another unit (both may be non-base)
     * 
     * Process:
     * 1. Convert source unit to base unit
     * 2. Convert base unit to target unit
     * 
     * @param float $quantity Quantity in source unit
     * @param int $itemId Item ID
     * @param int $sourceUnitId Source unit ID
     * @param int $targetUnitId Target unit ID
     * @return float Quantity in target unit
     */
    public static function convertBetweenUnits($quantity, $itemId, $sourceUnitId, $targetUnitId)
    {
        // Step 1: Convert to base unit
        $baseQuantity = self::convertToBaseUnit($quantity, $itemId, $sourceUnitId);
        
        // Step 2: Convert from base to target
        return self::convertFromBaseUnit($baseQuantity, $itemId, $targetUnitId);
    }
    
    /**
     * Format quantity with unit name for display
     * 
     * @param float $quantity
     * @param Unit $unit
     * @param int $decimals Number of decimal places
     * @return string
     */
    public static function formatQuantity($quantity, $unit, $decimals = 2)
    {
        $formattedQty = number_format($quantity, $decimals, '.', '');
        
        // Remove trailing zeros if unit doesn't allow decimals
        if (!$unit->allow_decimal) {
            $formattedQty = rtrim(rtrim($formattedQty, '0'), '.');
        }
        
        return $formattedQty . ' ' . $unit->short_name;
    }
    
    /**
     * Validate if a unit can be used for an item
     * 
     * @param int $itemId
     * @param int $unitId
     * @return bool
     */
    public static function isValidUnitForItem($itemId, $unitId)
    {
        $conversion = DB::table('item_unit_conversions')
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->exists();
        
        return $conversion;
    }
    
    /**
     * Get conversion factor for a specific item-unit combination
     * 
     * @param int $itemId
     * @param int $unitId
     * @return float|null
     */
    public static function getConversionFactor($itemId, $unitId)
    {
        $conversion = DB::table('item_unit_conversions')
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->first();
        
        return $conversion ? (float) $conversion->conversion_factor : null;
    }
    
    /**
     * Calculate price per base unit from any unit
     * 
     * Example: If item is sold at Rs. 120 per box (12 pieces),
     * and base unit is piece, then price per piece = 120 / 12 = Rs. 10
     * 
     * @param float $price Price in source unit
     * @param int $itemId
     * @param int|null $sourceUnitId
     * @return float Price per base unit
     */
    public static function convertPriceToBaseUnit($price, $itemId, $sourceUnitId = null)
    {
        if (!$sourceUnitId) {
            return (float) $price;
        }
        
        $conversionFactor = self::getConversionFactor($itemId, $sourceUnitId);
        if (!$conversionFactor) {
            return (float) $price;
        }
        
        return (float) $price / (float) $conversionFactor;
    }
    
    /**
     * Calculate price per unit from base unit price
     * 
     * Example: If price per piece (base) is Rs. 10,
     * and box has 12 pieces, then price per box = 10 × 12 = Rs. 120
     * 
     * @param float $basePrice Price per base unit
     * @param int $itemId
     * @param int $targetUnitId
     * @return float Price per target unit
     */
    public static function convertPriceFromBaseUnit($basePrice, $itemId, $targetUnitId)
    {
        $conversionFactor = self::getConversionFactor($itemId, $targetUnitId);
        if (!$conversionFactor) {
            return (float) $basePrice;
        }
        
        return (float) $basePrice * (float) $conversionFactor;
    }
}

