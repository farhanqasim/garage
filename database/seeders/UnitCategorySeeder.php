<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

/**
 * Unit Category Seeder
 * 
 * Seeds standard units organized by category (Weight, Quantity, Volume, Length)
 * Similar to how Vyapar/Tally organizes units
 */
class UnitCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing units if needed (optional - comment out if you want to keep existing)
        // Unit::truncate();
        
        $units = [
            // WEIGHT CATEGORY
            [
                'name' => 'Kilogram',
                'short_name' => 'kg',
                'unit_category' => 'weight',
                'is_base_unit' => true, // Base unit for weight
                'allow_decimal' => true,
                'define_base_unit' => false,
                'base_unit_multiplier' => null,
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Gram',
                'short_name' => 'g',
                'unit_category' => 'weight',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 0.001, // 1g = 0.001 kg
                'base_unit_id' => null, // Will be set after kg is created
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Milligram',
                'short_name' => 'mg',
                'unit_category' => 'weight',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 0.000001, // 1mg = 0.000001 kg
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Quintal',
                'short_name' => 'Quintal',
                'unit_category' => 'weight',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 100, // 1 Quintal = 100 kg
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Metric Ton',
                'short_name' => 'MT',
                'unit_category' => 'weight',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 1000, // 1 MT = 1000 kg
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 5,
            ],
            [
                'name' => 'Pound',
                'short_name' => 'lb',
                'unit_category' => 'weight',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 0.453592, // 1 lb = 0.453592 kg
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 6,
            ],
            
            // QUANTITY CATEGORY
            [
                'name' => 'Piece',
                'short_name' => 'Pcs',
                'unit_category' => 'quantity',
                'is_base_unit' => true, // Base unit for quantity
                'allow_decimal' => false,
                'define_base_unit' => false,
                'base_unit_multiplier' => null,
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Dozen',
                'short_name' => 'Dz',
                'unit_category' => 'quantity',
                'is_base_unit' => false,
                'allow_decimal' => false,
                'define_base_unit' => true,
                'base_unit_multiplier' => 12, // 1 Dozen = 12 pieces
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Box',
                'short_name' => 'Box',
                'unit_category' => 'quantity',
                'is_base_unit' => false,
                'allow_decimal' => false,
                'define_base_unit' => true,
                'base_unit_multiplier' => 12, // 1 Box = 12 pieces (can be customized per item)
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Packet',
                'short_name' => 'Pkt',
                'unit_category' => 'quantity',
                'is_base_unit' => false,
                'allow_decimal' => false,
                'define_base_unit' => true,
                'base_unit_multiplier' => 10, // 1 Packet = 10 pieces (can be customized per item)
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Carton',
                'short_name' => 'Ctn',
                'unit_category' => 'quantity',
                'is_base_unit' => false,
                'allow_decimal' => false,
                'define_base_unit' => true,
                'base_unit_multiplier' => 24, // 1 Carton = 24 pieces (can be customized per item)
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 5,
            ],
            
            // VOLUME CATEGORY
            [
                'name' => 'Liter',
                'short_name' => 'L',
                'unit_category' => 'volume',
                'is_base_unit' => true, // Base unit for volume
                'allow_decimal' => true,
                'define_base_unit' => false,
                'base_unit_multiplier' => null,
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Milliliter',
                'short_name' => 'ml',
                'unit_category' => 'volume',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 0.001, // 1ml = 0.001 L
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kiloliter',
                'short_name' => 'kL',
                'unit_category' => 'volume',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 1000, // 1kL = 1000 L
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 3,
            ],
            
            // LENGTH CATEGORY
            [
                'name' => 'Meter',
                'short_name' => 'm',
                'unit_category' => 'length',
                'is_base_unit' => true, // Base unit for length
                'allow_decimal' => true,
                'define_base_unit' => false,
                'base_unit_multiplier' => null,
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Centimeter',
                'short_name' => 'cm',
                'unit_category' => 'length',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 0.01, // 1cm = 0.01 m
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Kilometer',
                'short_name' => 'km',
                'unit_category' => 'length',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 1000, // 1km = 1000 m
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Feet',
                'short_name' => 'ft',
                'unit_category' => 'length',
                'is_base_unit' => false,
                'allow_decimal' => true,
                'define_base_unit' => true,
                'base_unit_multiplier' => 0.3048, // 1ft = 0.3048 m
                'base_unit_id' => null,
                'status' => 'active',
                'sort_order' => 4,
            ],
        ];

        // Create base units first
        $baseUnits = [];
        foreach ($units as $unitData) {
            if ($unitData['is_base_unit']) {
                $baseUnit = Unit::create($unitData);
                $baseUnits[$unitData['unit_category']] = $baseUnit->id;
            }
        }

        // Create secondary units with proper base_unit_id
        foreach ($units as $unitData) {
            if (!$unitData['is_base_unit'] && isset($baseUnits[$unitData['unit_category']])) {
                $unitData['base_unit_id'] = $baseUnits[$unitData['unit_category']];
                Unit::create($unitData);
            }
        }
    }
}

