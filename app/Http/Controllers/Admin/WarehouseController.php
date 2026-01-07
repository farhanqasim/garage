<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\Branch;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    /**
     * Show form to create new warehouse
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get branches that don't have a warehouse yet
        if ($user->role === 'admin') {
            $branches = Branch::where('status', 'active')
                ->whereDoesntHave('warehouse')
                ->orderBy('branch_name', 'asc')
                ->get();
        } else {
            // Users can only create warehouse for their branch
            $branchId = session('selected_branch_id');
            $branches = Branch::where('id', $branchId)
                ->where('status', 'active')
                ->whereDoesntHave('warehouse')
                ->get();
        }

        return view('admin.warehouses.create', compact('branches'));
    }

    /**
     * Store new warehouse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'branch_id' => 'required|exists:branches,id|unique:warehouses,branch_id',
            'warehouse_name' => 'required|string|max:255',
            'warehouse_code' => 'nullable|string|max:255|unique:warehouses,warehouse_code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        // Check if branch already has warehouse
        $existingWarehouse = Warehouse::where('branch_id', $request->branch_id)->first();
        if ($existingWarehouse) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This branch already has a warehouse.');
        }

        // Check access for non-admin users
        if ($user->role !== 'admin' && $request->branch_id != session('selected_branch_id')) {
            abort(403, 'Unauthorized access.');
        }

        // Auto-generate warehouse code if not provided
        $warehouseCode = $request->warehouse_code;
        if (!$warehouseCode) {
            $warehouseCode = 'WH-' . strtoupper(Str::random(6));
            // Ensure uniqueness
            while (Warehouse::where('warehouse_code', $warehouseCode)->exists()) {
                $warehouseCode = 'WH-' . strtoupper(Str::random(6));
            }
        }

        $warehouse = Warehouse::create([
            'branch_id' => $request->branch_id,
            'warehouse_name' => $request->warehouse_name,
            'warehouse_code' => $warehouseCode,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country ?? 'Pakistan',
            'phone' => $request->phone,
            'email' => $request->email,
            'manager_name' => $request->manager_name,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('warehouses.show', $warehouse->id)
            ->with('success', 'Warehouse created successfully!');
    }

    /**
     * Get warehouse by branch ID
     */
    public function getByBranch($branchId)
    {
        $warehouse = Warehouse::where('branch_id', $branchId)->first();
        
        if (!$warehouse) {
            return response()->json(['error' => 'Warehouse not found'], 404);
        }
        
        $itemsCount = WarehouseItem::where('warehouse_id', $warehouse->id)->count();
        
        return response()->json([
            'id' => $warehouse->id,
            'warehouse_name' => $warehouse->warehouse_name,
            'warehouse_code' => $warehouse->warehouse_code,
            'items_count' => $itemsCount,
        ]);
    }

    /**
     * Display list of warehouses (branch-specific for users)
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            // Admin can see all warehouses
            $warehouses = Warehouse::with(['branch', 'items'])->paginate(20);
        } else {
            // User can only see their branch warehouse
            $branchId = session('selected_branch_id');
            if (!$branchId) {
                return redirect()->route('all.branches')
                    ->with('error', 'Please select a branch first.');
            }
            
            $warehouses = Warehouse::where('branch_id', $branchId)
                ->with(['branch', 'items'])
                ->paginate(20);
        }

        return view('admin.warehouses.index', compact('warehouses'));
    }

    /**
     * Show warehouse details and items
     */
    public function show($id)
    {
        $user = Auth::user();
        $warehouse = Warehouse::with(['branch', 'items.item'])->findOrFail($id);

        // Check access
        if ($user->role !== 'admin' && $warehouse->branch_id != session('selected_branch_id')) {
            abort(403, 'Unauthorized access to this warehouse.');
        }

        $items = $warehouse->items()
            ->with('item')
            ->orderBy('available_quantity', 'asc')
            ->paginate(50);

        return view('admin.warehouses.show', compact('warehouse', 'items'));
    }

    /**
     * Show form to add item to warehouse
     */
    public function addItem($id)
    {
        $user = Auth::user();
        $warehouse = Warehouse::with('branch')->findOrFail($id);

        // Check access
        if ($user->role !== 'admin' && $warehouse->branch_id != session('selected_branch_id')) {
            abort(403, 'Unauthorized access.');
        }

        $branchId = $warehouse->branch_id;
        
        $purchasedItemIds = \App\Models\PurchaseItem::whereHas('purchase', function($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })->distinct()->pluck('item_id');
        
        $existingWarehouseItemIds = WarehouseItem::where('warehouse_id', $id)->pluck('item_id');
        
        $itemIds = $purchasedItemIds->merge($existingWarehouseItemIds)->unique();
        
        $items = Item::with(['partnumber_item', 'category'])
            ->where('is_active', 1)
            ->whereIn('id', $itemIds)
            ->orderBy('short_disc', 'asc')
            ->get();

        return view('admin.warehouses.add-item', compact('warehouse', 'items'));
    }

    /**
     * Store item in warehouse
     */
    public function storeItem(Request $request, $id)
    {
        $user = Auth::user();
        $warehouse = Warehouse::findOrFail($id);

        // Check access
        if ($user->role !== 'admin' && $warehouse->branch_id != session('selected_branch_id')) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Check if item already exists in warehouse
        $warehouseItem = WarehouseItem::where('warehouse_id', $id)
            ->where('item_id', $request->item_id)
            ->first();

        if ($warehouseItem) {
            // Update existing item
            $warehouseItem->quantity += $request->quantity;
            $warehouseItem->min_stock_level = $request->min_stock_level ?? $warehouseItem->min_stock_level;
            $warehouseItem->max_stock_level = $request->max_stock_level ?? $warehouseItem->max_stock_level;
            $warehouseItem->location = $request->location ?? $warehouseItem->location;
            $warehouseItem->notes = $request->notes ?? $warehouseItem->notes;
            $warehouseItem->save();
        } else {
            // Create new warehouse item
            WarehouseItem::create([
                'warehouse_id' => $id,
                'item_id' => $request->item_id,
                'quantity' => $request->quantity,
                'reserved_quantity' => 0,
                'available_quantity' => $request->quantity,
                'min_stock_level' => $request->min_stock_level ?? 0,
                'max_stock_level' => $request->max_stock_level,
                'location' => $request->location,
                'notes' => $request->notes,
            ]);
        }

        return redirect()->route('warehouses.show', $id)
            ->with('success', 'Item added to warehouse successfully!');
    }

    /**
     * Update warehouse item stock
     */
    public function updateStock(Request $request, $warehouseId, $itemId)
    {
        $user = Auth::user();
        $warehouse = Warehouse::findOrFail($warehouseId);

        // Check access
        if ($user->role !== 'admin' && $warehouse->branch_id != session('selected_branch_id')) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $warehouseItem = WarehouseItem::where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->firstOrFail();

        $warehouseItem->quantity = $request->quantity;
        $warehouseItem->min_stock_level = $request->min_stock_level ?? 0;
        $warehouseItem->max_stock_level = $request->max_stock_level;
        $warehouseItem->location = $request->location;
        $warehouseItem->save();

        return redirect()->back()->with('success', 'Stock updated successfully!');
    }

    /**
     * Remove item from warehouse
     */
    public function removeItem($warehouseId, $itemId)
    {
        $user = Auth::user();
        $warehouse = Warehouse::findOrFail($warehouseId);

        // Check access
        if ($user->role !== 'admin' && $warehouse->branch_id != session('selected_branch_id')) {
            abort(403, 'Unauthorized access.');
        }

        WarehouseItem::where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->delete();

        return redirect()->back()->with('success', 'Item removed from warehouse!');
    }

    /**
     * Get low stock items
     */
    public function lowStock($id)
    {
        $user = Auth::user();
        $warehouse = Warehouse::with('branch')->findOrFail($id);

        // Check access
        if ($user->role !== 'admin' && $warehouse->branch_id != session('selected_branch_id')) {
            abort(403, 'Unauthorized access.');
        }

        $lowStockItems = WarehouseItem::where('warehouse_id', $id)
            ->whereRaw('available_quantity <= min_stock_level')
            ->with('item')
            ->orderBy('available_quantity', 'asc')
            ->get();

        return view('admin.warehouses.low-stock', compact('warehouse', 'lowStockItems'));
    }
}
