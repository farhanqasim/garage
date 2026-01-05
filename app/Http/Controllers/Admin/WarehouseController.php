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

        $items = Item::where('is_active', 1)->orderBy('short_disc', 'asc')->get();

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
