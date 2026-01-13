<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartNumber;
use App\Models\Item;
use App\Models\VehicalType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartNumberController extends Controller
{
    /**
     * Display a listing of part numbers
     */
    public function index(Request $request)
    {
        $query = PartNumber::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $partNumbers = $query->withCount(['item_part_number', 'part_number_vehical'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.part-numbers.index', compact('partNumbers'));
    }

    /**
     * Store a newly created part number
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:part_numbers,name',
            'type' => 'nullable|string|in:parts,filters,breakpad,oil,battery',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Clean the name
        $name = $this->cleanPartNumberName($request->name);

        // Check for duplicates after cleaning
        $existing = PartNumber::where('name', $name)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Part number already exists (after cleaning)'
            ], 422);
        }

        $partNumber = PartNumber::create([
            'name' => $name,
            'type' => $request->type,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'id' => $partNumber->id,
            'name' => $partNumber->name,
            'message' => 'Part number created successfully'
        ]);
    }

    /**
     * Display the specified part number
     */
    public function show($id)
    {
        $partNumber = PartNumber::with(['item_part_number', 'part_number_vehical'])
            ->findOrFail($id);
        
        return response()->json($partNumber);
    }

    /**
     * Update the specified part number
     */
    public function update(Request $request, $id)
    {
        $partNumber = PartNumber::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:part_numbers,name,' . $id,
            'type' => 'nullable|string|in:parts,filters,breakpad,oil,battery',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Clean the name
        $name = $this->cleanPartNumberName($request->name);

        // Check for duplicates after cleaning (excluding current)
        $existing = PartNumber::where('name', $name)
            ->where('id', '!=', $id)
            ->first();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Part number already exists (after cleaning)'
            ], 422);
        }

        $partNumber->update([
            'name' => $name,
            'type' => $request->type ?? $partNumber->type,
            'status' => $request->status ?? $partNumber->status,
        ]);

        return response()->json([
            'success' => true,
            'id' => $partNumber->id,
            'name' => $partNumber->name,
            'message' => 'Part number updated successfully'
        ]);
    }

    /**
     * Remove the specified part number
     */
    public function destroy($id)
    {
        $partNumber = PartNumber::findOrFail($id);

        // Check if part number is being used
        $itemsCount = Item::where('part_number_id', $id)->count();
        $vehiclesCount = VehicalType::where('v_part_number_id', $id)->count();

        if ($itemsCount > 0 || $vehiclesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete. Part number is used in {$itemsCount} item(s) and {$vehiclesCount} vehicle(s)."
            ], 422);
        }

        $partNumber->delete();

        return response()->json([
            'success' => true,
            'message' => 'Part number deleted successfully'
        ]);
    }

    /**
     * Clean up duplicate and invalid part numbers
     */
    public function cleanup()
    {
        DB::beginTransaction();
        try {
            $cleaned = 0;
            $merged = 0;
            $deleted = 0;

            // Get all part numbers
            $partNumbers = PartNumber::all();

            foreach ($partNumbers as $partNumber) {
                // Clean the name
                $cleanedName = $this->cleanPartNumberName($partNumber->name);

                // If name changed, check for duplicates
                if ($cleanedName !== $partNumber->name) {
                    $existing = PartNumber::where('name', $cleanedName)
                        ->where('id', '!=', $partNumber->id)
                        ->first();

                    if ($existing) {
                        // Merge: Move all references to the existing one
                        Item::where('part_number_id', $partNumber->id)
                            ->update(['part_number_id' => $existing->id]);
                        
                        VehicalType::where('v_part_number_id', $partNumber->id)
                            ->update(['v_part_number_id' => $existing->id]);

                        $partNumber->delete();
                        $merged++;
                    } else {
                        // Just update the name
                        $partNumber->update(['name' => $cleanedName]);
                        $cleaned++;
                    }
                }

                // Delete invalid entries (empty or just whitespace)
                if (trim($partNumber->name) === '' || strlen(trim($partNumber->name)) < 2) {
                    // Only delete if not used
                    $itemsCount = Item::where('part_number_id', $partNumber->id)->count();
                    $vehiclesCount = VehicalType::where('v_part_number_id', $partNumber->id)->count();

                    if ($itemsCount === 0 && $vehiclesCount === 0) {
                        $partNumber->delete();
                        $deleted++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cleanup completed successfully',
                'stats' => [
                    'cleaned' => $cleaned,
                    'merged' => $merged,
                    'deleted' => $deleted,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean part number name
     */
    private function cleanPartNumberName($name)
    {
        // Trim whitespace
        $name = trim($name);
        
        // Remove extra spaces
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Remove special characters that shouldn't be in part numbers (keep alphanumeric, spaces, hyphens, underscores)
        $name = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $name);
        
        // Trim again
        $name = trim($name);
        
        return $name;
    }

    /**
     * Get part numbers for select dropdown (AJAX)
     */
    public function getForSelect(Request $request)
    {
        $query = PartNumber::where('status', 'active');

        // Filter by type if provided
        if ($request->has('type') && $request->type) {
            $query->where(function($q) use ($request) {
                $q->where('type', $request->type)
                  ->orWhereNull('type');
            });
        }

        // Search if provided
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $partNumbers = $query->orderBy('name')
            ->limit(100)
            ->get(['id', 'name', 'type']);

        return response()->json($partNumbers);
    }
}
