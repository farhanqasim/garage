<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
     public function all_units()
     {
        $units = Unit::with(['baseUnits'])->get();
        // return $units;
        return view('admin.unit.unit', compact('units'));
     }

    public function post_units(Request $request)
    {
        // Format name: trim and capitalize first letter of each word
        $name = trim($request->name);
        $name = ucwords(strtolower($name));
        
        // Check for duplicate name (case-insensitive)
        $existingUnit = Unit::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])
            ->first();
        
        if ($existingUnit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit with this name already exists. Please use a different name.'
            ], 422);
        }
        
        // Format short_name: trim and uppercase
        $shortName = strtoupper(trim($request->short_name));
        
        // Check for duplicate short_name (case-insensitive)
        $existingShortName = Unit::whereRaw('UPPER(TRIM(short_name)) = ?', [$shortName])
            ->first();
        
        if ($existingShortName) {
            return response()->json([
                'success' => false,
                'message' => 'Unit with this short name already exists. Please use a different short name.'
            ], 422);
        }
        
        $unit = Unit::create([
            'name' => $name,
            'short_name' => $shortName,
            'allow_decimal' => $request->allow_decimal,
            'decimal_places' => $request->decimal_places ?? ($request->allow_decimal ? 2 : 0),
        ]);
        
        // Save multiple base units if provided
        // Use attach() to allow same base_unit_id with different multipliers
        if ($request->has('base_units') && is_array($request->base_units)) {
            foreach ($request->base_units as $baseUnit) {
                if (!empty($baseUnit['base_unit_id']) && !empty($baseUnit['multiplier'])) {
                    // Attach each base unit separately (allows same base_unit_id with different multipliers)
                    $unit->baseUnits()->attach($baseUnit['base_unit_id'], [
                        'multiplier' => $baseUnit['multiplier']
                    ]);
                }
            }
        }

        // Reload unit with all base units for response
        $unit->refresh();
        $unit->load('baseUnits');

        return response()->json([
            'success' => true,
            'id' => $unit->id,
            'unit' => $unit,
            'message' => 'Unit saved successfully with ' . $unit->baseUnits->count() . ' base unit(s)'
        ]);
    }

       public function update_units(Request $request, Unit $unit)
        {
            $request->validate([
                'name' => 'required|string|max:255',
                'short_name' => 'required|string|max:50',
                'allow_decimal' => 'required|boolean',
            ]);
            
            // Format name: trim and capitalize first letter of each word
            $name = trim($request->name);
            $name = ucwords(strtolower($name));
            
            // Check for duplicate name (case-insensitive, excluding current unit)
            $existingUnit = Unit::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])
                ->where('id', '!=', $unit->id)
                ->first();
            
            if ($existingUnit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit with this name already exists. Please use a different name.'
                ], 422);
            }
            
            // Format short_name: trim and uppercase
            $shortName = strtoupper(trim($request->short_name));
            
            // Check for duplicate short_name (case-insensitive, excluding current unit)
            $existingShortName = Unit::whereRaw('UPPER(TRIM(short_name)) = ?', [$shortName])
                ->where('id', '!=', $unit->id)
                ->first();
            
            if ($existingShortName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit with this short name already exists. Please use a different short name.'
                ], 422);
            }
            
            $unit->update([
                'name' => $name,
                'short_name' => $shortName,
                'allow_decimal' => $request->allow_decimal,
                'decimal_places' => $request->decimal_places ?? ($request->allow_decimal ? 2 : 0),
            ]);
            
            // Update multiple base units: allow same base_unit_id with different multipliers (1L, 2L, 3L, ...)
            if ($request->has('base_units') && is_array($request->base_units)) {
                $unit->baseUnits()->detach();
                foreach ($request->base_units as $baseUnit) {
                    if (!empty($baseUnit['base_unit_id'])) {
                        $multiplier = isset($baseUnit['multiplier']) && $baseUnit['multiplier'] !== '' && is_numeric($baseUnit['multiplier'])
                            ? (float) $baseUnit['multiplier'] : 1;
                        $unit->baseUnits()->attach($baseUnit['base_unit_id'], ['multiplier' => $multiplier]);
                    }
                }
            } else {
                $unit->baseUnits()->detach();
            }
            
            // Load base units for response
            $unit->load('baseUnits');
            
            return response()->json([
                'success' => true,
                'unit' => $unit,
                'message'=>"Unit update Successfully"
            ]);
        }

    public function show_unit(Unit $unit)
    {
        $unit->load('baseUnits');
        return response()->json([
            'success' => true,
            'unit' => $unit
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
        $unit->decimal_places = $request->decimal_places ?? ($request->allow_decimal ? 2 : 0);
        
        $unit->save();
        
        // Save multiple base units: allow same base_unit_id with different multipliers (e.g. 1L, 2L, 3L)
        if ($request->has('base_units') && is_array($request->base_units)) {
            foreach ($request->base_units as $baseUnit) {
                if (!empty($baseUnit['base_unit_id']) && isset($baseUnit['multiplier']) && $baseUnit['multiplier'] !== '') {
                    $multiplier = is_numeric($baseUnit['multiplier']) ? (float) $baseUnit['multiplier'] : 1;
                    $unit->baseUnits()->attach($baseUnit['base_unit_id'], ['multiplier' => $multiplier]);
                }
            }
        }
        
        return redirect()->back()->with('success','Unit Saved Successfully');
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
        $unit->decimal_places = $request->decimal_places ?? ($request->allow_decimal ? 2 : 0);
        
        $unit->update();
        
        // Update multiple base units: allow same base_unit_id with different multipliers (e.g. 1L, 2L, 3L)
        if ($request->has('base_units') && is_array($request->base_units)) {
            $unit->baseUnits()->detach();
            foreach ($request->base_units as $baseUnit) {
                if (!empty($baseUnit['base_unit_id']) && isset($baseUnit['multiplier']) && $baseUnit['multiplier'] !== '') {
                    $multiplier = is_numeric($baseUnit['multiplier']) ? (float) $baseUnit['multiplier'] : 1;
                    $unit->baseUnits()->attach($baseUnit['base_unit_id'], ['multiplier' => $multiplier]);
                }
            }
        } else {
            $unit->baseUnits()->detach();
        }
        
        return redirect()->back()->with('success', 'Unit updated successfully.');
    }


    public function deleteunits($id)
        {
            $units = Unit::findOrFail($id);
            $units->delete();
            return redirect()->back()->with('success', 'Unit deleted successfully.');

        }

    // Pricing Engine Methods
    public function pricing_engine()
    {
        $units = Unit::where('status', 'active')
            ->with(['baseUnits'])
            ->orderBy('name')
            ->get();
        return view('admin.unit.pricing_engine', compact('units'));
    }

    public function search_units(Request $request)
    {
        $search = $request->get('search', '');
        $units = Unit::where('status', 'active')
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('short_name', 'like', "%{$search}%");
            })
            ->with(['baseUnits'])
            ->orderBy('name')
            ->limit(50)
            ->get();

        // Format for split search view - if unit has multiple base units, create separate entries
        $formattedUnits = [];
        foreach ($units as $unit) {
            $baseUnits = $unit->baseUnits;
            if ($baseUnits->count() > 0) {
                // Create separate entry for each base unit conversion
                foreach ($baseUnits as $baseUnit) {
                    $multiplier = (float) $baseUnit->pivot->multiplier;
                    $displayMultiplier = $multiplier == (int) $multiplier
                        ? (string) (int) $multiplier
                        : rtrim(rtrim(sprintf('%.4f', $multiplier), '0'), '.');
                    $formattedUnits[] = [
                        'id' => $unit->id,
                        'name' => $unit->name,
                        'short_name' => $unit->short_name,
                        'display_text' => $unit->name . ' - ' . $displayMultiplier . ' ' . $baseUnit->name,
                        'base_unit_id' => $baseUnit->id,
                        'base_unit_name' => $baseUnit->name,
                        'base_unit_short_name' => $baseUnit->short_name,
                        'multiplier' => $baseUnit->pivot->multiplier,
                        'decimal_places' => $unit->decimal_places ?? 0,
                        'allow_decimal' => $unit->allow_decimal
                    ];
                }
            } else {
                // Unit without base units — show short form (e.g. Killo Gram (KG))
                $formattedUnits[] = [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'short_name' => $unit->short_name,
                    'display_text' => $unit->short_name ? $unit->name . ' (' . $unit->short_name . ')' : $unit->name,
                    'base_unit_id' => null,
                    'base_unit_name' => null,
                    'base_unit_short_name' => null,
                    'multiplier' => null,
                    'decimal_places' => $unit->decimal_places ?? 0,
                    'allow_decimal' => $unit->allow_decimal
                ];
            }
        }

        return response()->json($formattedUnits);
    }

    public function calculate_price(Request $request, $unitId)
    {
        $salePrice = floatval($request->get('sale_price', 0));
        $unit = Unit::with('baseUnits')->findOrFail($unitId);
        
        $results = [];
        
        if ($salePrice > 0) {
            $baseUnits = $unit->baseUnits;
            
            if ($baseUnits->count() > 0) {
                foreach ($baseUnits as $baseUnit) {
                    $multiplier = floatval($baseUnit->pivot->multiplier);
                    if ($multiplier > 0) {
                        $basePrice = $salePrice / $multiplier;
                        // Use base unit's decimal places setting (base unit is already loaded via relationship)
                        $decimalPlaces = $baseUnit->decimal_places ?? ($baseUnit->allow_decimal ? 2 : 0);
                        $results[] = [
                            'base_unit_name' => $baseUnit->name,
                            'base_unit_short_name' => $baseUnit->short_name,
                            'multiplier' => $multiplier,
                            'base_price' => round($basePrice, $decimalPlaces),
                            'formatted_price' => 'Rs. ' . number_format($basePrice, $decimalPlaces, '.', ',') . ' / ' . $baseUnit->name
                        ];
                    }
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name
            ],
            'sale_price' => $salePrice,
            'base_prices' => $results
        ]);
    }
}
