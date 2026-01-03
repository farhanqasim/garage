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

    public function post_units(Request $request)
    {
        $unit = Unit::create([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'allow_decimal' => $request->allow_decimal,
            'define_base_unit' => $request->define_base_unit ? 1 : 0,
            'base_unit_multiplier' => $request->base_unit_multiplier,
            'base_unit_id' => $request->base_unit_id,
        ]);

        return response()->json([
            'success' => true,
            'id' => $unit->id,
            'unit' => $unit
        ]);
    }

       public function update_units(Request $request, Unit $unit)
        {
            $request->validate([
                'name' => 'required|string|max:255',
                'short_name' => 'required|string|max:50',
                'allow_decimal' => 'required|boolean',
            ]);
            $unit->update([
                'name' => $request->name,
                'short_name' => $request->short_name,
                'allow_decimal' => $request->allow_decimal,
                'define_base_unit' => $request->define_base_unit ? 1 : 0,
                'base_unit_multiplier' => $request->base_unit_multiplier,
                'base_unit_id' => $request->base_unit_id,
            ]);
            return response()->json([
                'success' => true,
                'unit' => $unit,
                'message'=>"Unit update Successfully"
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
        $unit->define_base_unit = $request->define_base_unit ? 1 : 0;
        
        // Keep old fields for backward compatibility
        $unit->base_unit_multiplier = $request->base_unit_multiplier;
        $unit->base_unit_id = $request->base_unit_id;
        
        $unit->save();
        
        // Save multiple base units if provided
        if ($request->define_base_unit && $request->has('base_units')) {
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
        $unit->define_base_unit = $request->define_base_unit ? 1 : 0;
        
        // Keep old fields for backward compatibility
        $unit->base_unit_multiplier = $request->base_unit_multiplier;
        $unit->base_unit_id = $request->base_unit_id;
        
        $unit->update();
        
        // Update multiple base units if provided
        if ($request->define_base_unit && $request->has('base_units')) {
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
        
        return redirect()->back()->with('success', 'Unit updated successfully.');
    }


    public function deleteunits($id)
        {
            $units = Unit::findOrFail($id);
            $units->delete();
            return redirect()->back()->with('success', 'Unit deleted successfully.');

        }
}
