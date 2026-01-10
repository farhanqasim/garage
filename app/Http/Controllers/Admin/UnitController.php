<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
     public function all_units()
     {
        $units = Unit::with(['baseUnit', 'baseUnits'])->get();
        // return $units;
        return view('admin.unit.unit', compact('units'));
     }

     public function unit_manager()
     {
        $units = Unit::with(['baseUnit', 'baseUnits'])->get();
        // Format units for frontend
        $formattedUnits = $units->map(function($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
                'allow_decimal' => $unit->allow_decimal,
                'decimal_after_point_digit' => $unit->decimal_after_point_digit,
                'is_base_unit' => $unit->is_base_unit,
                'base_units' => $unit->baseUnits->map(function($bu) {
                    return [
                        'id' => $bu->id,
                        'name' => $bu->name,
                        'short_name' => $bu->short_name,
                        'multiplier' => $bu->pivot->multiplier ?? 1
                    ];
                })->toArray()
            ];
        });
        return view('admin.unit.unit-manager', compact('units', 'formattedUnits'));
     }

    public function post_units(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'allow_decimal' => 'required|string|in:0,1',
            'decimal_after_point_digit' => 'nullable|integer|min:0|max:10',
        ]);

        $unit = Unit::create([
            'name' => strtoupper(trim($request->name)),
            'short_name' => strtoupper(trim($request->short_name)),
            'allow_decimal' => $request->allow_decimal ?? '0',
            'decimal_after_point_digit' => $request->decimal_after_point_digit ?? ($request->allow_decimal == '1' ? ($request->decimal_after_point_digit ?? 2) : 0),
            'status' => 'active',
        ]);
        
        // Save multiple base units if provided
        if ($request->has('base_units') && is_array($request->base_units)) {
            $baseUnitsData = [];
            foreach ($request->base_units as $baseUnit) {
                if (!empty($baseUnit['base_unit_id']) && !empty($baseUnit['multiplier']) && is_numeric($baseUnit['multiplier'])) {
                    $baseUnitsData[$baseUnit['base_unit_id']] = [
                        'multiplier' => (float) $baseUnit['multiplier']
                    ];
                }
            }
            if (!empty($baseUnitsData)) {
                $unit->baseUnits()->sync($baseUnitsData);
            }
        }

        // Load base units for response
        $unit->load('baseUnits');
        
        // Format unit data for frontend
        $unitData = [
            'id' => $unit->id,
            'name' => $unit->name,
            'short_name' => $unit->short_name,
            'allow_decimal' => $unit->allow_decimal,
            'decimal_after_point_digit' => $unit->decimal_after_point_digit,
            'status' => $unit->status,
            'base_units' => $unit->baseUnits->map(function($bu) {
                return [
                    'id' => $bu->id,
                    'name' => $bu->name,
                    'short_name' => $bu->short_name,
                    'multiplier' => $bu->pivot->multiplier ?? 1
                ];
            })->toArray()
        ];

        return response()->json([
            'success' => true,
            'id' => $unit->id,
            'unit' => $unitData,
            'base_units_count' => $unit->baseUnits()->count(),
            'message' => 'Unit created successfully with ' . $unit->baseUnits()->count() . ' base unit(s)'
        ]);
    }

       public function update_units(Request $request, Unit $unit)
        {
            $request->validate([
                'name' => 'required|string|max:255',
                'short_name' => 'required|string|max:255',
                'allow_decimal' => 'required|string|in:0,1',
                'decimal_after_point_digit' => 'nullable|integer|min:0|max:10',
            ]);
            
            $unit->update([
                'name' => strtoupper(trim($request->name)),
                'short_name' => strtoupper(trim($request->short_name)),
                'allow_decimal' => $request->allow_decimal ?? '0',
                'decimal_after_point_digit' => $request->decimal_after_point_digit ?? ($request->allow_decimal == '1' ? ($request->decimal_after_point_digit ?? 2) : 0),
            ]);
            
            // Update multiple base units if provided
            if ($request->has('base_units') && is_array($request->base_units)) {
                $baseUnitsData = [];
                foreach ($request->base_units as $baseUnit) {
                    if (!empty($baseUnit['base_unit_id']) && !empty($baseUnit['multiplier']) && is_numeric($baseUnit['multiplier'])) {
                        $baseUnitsData[$baseUnit['base_unit_id']] = [
                            'multiplier' => (float) $baseUnit['multiplier']
                        ];
                    }
                }
                // Sync will replace all existing relationships
                $unit->baseUnits()->sync($baseUnitsData);
            } else {
                // If no base units provided, remove all base unit relationships
                $unit->baseUnits()->detach();
            }
            
            // Load base units for response
            $unit->load('baseUnits');
            
            // Format unit data for frontend
            $unitData = [
                'id' => $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
                'allow_decimal' => $unit->allow_decimal,
                'decimal_after_point_digit' => $unit->decimal_after_point_digit,
                'status' => $unit->status,
                'base_units' => $unit->baseUnits->map(function($bu) {
                    return [
                        'id' => $bu->id,
                        'name' => $bu->name,
                        'short_name' => $bu->short_name,
                        'multiplier' => $bu->pivot->multiplier ?? 1
                    ];
                })->toArray()
            ];
            
            return response()->json([
                'success' => true,
                'id' => $unit->id,
                'unit' => $unitData,
                'base_units_count' => $unit->baseUnits()->count(),
                'message' => 'Unit updated successfully with ' . $unit->baseUnits()->count() . ' base unit(s)'
            ]);
        }

    public function show_unit(Unit $unit)
    {
        $unit->load('baseUnits');
        
        // Format unit data for frontend
        $unitData = [
            'id' => $unit->id,
            'name' => $unit->name,
            'short_name' => $unit->short_name,
            'allow_decimal' => $unit->allow_decimal,
            'decimal_after_point_digit' => $unit->decimal_after_point_digit,
            'status' => $unit->status,
            'base_units' => $unit->baseUnits->map(function($bu) {
                return [
                    'id' => $bu->id,
                    'name' => $bu->name,
                    'short_name' => $bu->short_name,
                    'multiplier' => $bu->pivot->multiplier ?? 1
                ];
            })->toArray()
        ];
        
        return response()->json([
            'success' => true,
            'unit' => $unitData,
            'base_units_count' => $unit->baseUnits()->count()
        ]);
    }

       public function destroy_units(Unit $unit)
        {
            $unit->delete();
            return response()->json([
                'success' => true,
                'message'=>"Unit Destory Successfully"
            ]);
        }


    public function post_units_detail(Request $request){
        $unit = new Unit();
        $unit->name = $request->name;
        $unit->short_name = $request->short_name;
        $unit->allow_decimal = $request->allow_decimal;
        $unit->is_base_unit = $request->is_base_unit ? 1 : 0;
        $unit->decimal_after_point_digit = $request->decimal_after_point_digit ?? ($request->allow_decimal == 1 ? ($request->decimal_precision ?? 2) : 0);
        
        $unit->save();
        
        // Save multiple base units if provided
        if ($request->is_base_unit && $request->has('base_units')) {
            $baseUnitsData = [];
            foreach ($request->base_units as $baseUnit) {
                if (!empty($baseUnit['base_unit_id']) && !empty($baseUnit['multiplier'])) {
                    $baseUnitsData[$baseUnit['base_unit_id']] = [
                        'multiplier' => $baseUnit['multiplier']
                    ];
                }
            }
            if (!empty($baseUnitsData)) {
                $unit->baseUnits()->sync($baseUnitsData);
            }
        }
        
        $conversionsCount = $unit->conversions()->count();
        return redirect()->back()->with('success', "Unit Saved Successfully with {$conversionsCount} conversion(s)");
    }


    public function updateunitsStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Unit::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'status updated successfully');
    }

    public function updateunits(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->name = $request->name;
        $unit->short_name = $request->short_name;
        $unit->allow_decimal = $request->allow_decimal;
        $unit->is_base_unit = $request->is_base_unit ? 1 : 0;
        $unit->decimal_after_point_digit = $request->decimal_after_point_digit ?? ($request->allow_decimal == 1 ? ($request->decimal_precision ?? 2) : 0);
        
        $unit->update();
        
        // Update multiple base units if provided
        if ($request->is_base_unit && $request->has('base_units')) {
            $baseUnitsData = [];
            foreach ($request->base_units as $baseUnit) {
                if (!empty($baseUnit['base_unit_id']) && !empty($baseUnit['multiplier'])) {
                    $baseUnitsData[$baseUnit['base_unit_id']] = [
                        'multiplier' => $baseUnit['multiplier']
                    ];
                }
            }
            // Sync will replace all existing relationships
            $unit->baseUnits()->sync($baseUnitsData);
        } else {
            // If checkbox is unchecked, remove all base unit relationships
            $unit->baseUnits()->detach();
        }
        
        $conversionsCount = $unit->conversions()->count();
        return redirect()->back()->with('success', "Unit updated successfully with {$conversionsCount} conversion(s).");
    }


    public function deleteunits($id)
        {
            $units = Unit::findOrFail($id);
            $units->delete();
            return redirect()->back()->with('success', 'Unit deleted successfully.');

        }
}
