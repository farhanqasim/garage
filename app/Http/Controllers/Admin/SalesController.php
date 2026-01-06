<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\CarManufacturer;
use App\Models\PartNumber;
use App\Models\Technology;
use App\Models\Grade;
use App\Models\Volt;
use App\Models\Cca;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function all_sales()
    {
        return view('admin.sales.index');
    }
    
    public function create_sale(){
        return view('admin.sales.create');
    }

    /**
     * Get filter options for the search filter
     */
    public function getFilterOptions()
    {
        $categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        $manufacturers = CarManufacturer::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        $partNumbers = PartNumber::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(50)
            ->get();
            
        $technologies = Technology::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        $grades = Grade::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        $volts = Volt::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        $ccas = Cca::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get unique suppliers and racks from items
        $suppliers = Item::whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->distinct()
            ->pluck('supplier')
            ->filter()
            ->sort()
            ->values();
            
        $racks = Item::whereNotNull('rack')
            ->where('rack', '!=', '')
            ->distinct()
            ->pluck('rack')
            ->filter()
            ->sort()
            ->values();

        return response()->json([
            'categories' => $categories,
            'manufacturers' => $manufacturers,
            'part_numbers' => $partNumbers,
            'technologies' => $technologies,
            'grades' => $grades,
            'volts' => $volts,
            'ccas' => $ccas,
            'suppliers' => $suppliers,
            'racks' => $racks,
        ]);
    }

    /**
     * Advanced search with multiple filters
     */
    public function ajaxSearch(Request $request)
    {
        $query = Item::with([
            'partnumber_item',
            'vehical_item.manutacturer_vehical',
            'vehical_item.model_vehical',
            'category',
            'subcategory',
        ]);

        // Text search
        $search = $request->input('q', '');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('bar_code', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('pro_dis', 'LIKE', "%{$search}%")
                  ->orWhere('short_disc', 'LIKE', "%{$search}%")
                  ->orWhere('battery_size', 'LIKE', "%{$search}%")
                  ->orWhere('p_id', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhere('volt', 'LIKE', "%{$search}%")
                  ->orWhere('cca', 'LIKE', "%{$search}%")
                  ->orWhere('technology', 'LIKE', "%{$search}%")
                  ->orWhere('grade', 'LIKE', "%{$search}%")
                  ->orWhere('farmula', 'LIKE', "%{$search}%")
                  ->orWhere('rack', 'LIKE', "%{$search}%")
                  ->orWhere('supplier', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('partnumber_item', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('vehical_item', function ($q) use ($search) {
                $q->where('year_from', 'LIKE', "%{$search}%")
                  ->orWhere('year_to', 'LIKE', "%{$search}%")
                  ->orWhere('car_manufactured_country', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('vehical_item.engine_vehical', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('vehical_item.country_vehical', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('vehical_item.manutacturer_vehical', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('vehical_item.model_vehical', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by subcategory
        if ($request->has('subcategory_id') && $request->subcategory_id) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Filter by manufacturer
        if ($request->has('manufacturer_id') && $request->manufacturer_id) {
            $query->whereHas('vehical_item.manutacturer_vehical', function ($q) use ($request) {
                $q->where('id', $request->manufacturer_id);
            });
        }

        // Filter by part number
        if ($request->has('part_number_id') && $request->part_number_id) {
            $query->where('part_number_id', $request->part_number_id);
        }

        // Filter by technology
        if ($request->has('technology_id') && $request->technology_id) {
            $query->where('technology', $request->technology_id);
        }

        // Filter by grade
        if ($request->has('grade_id') && $request->grade_id) {
            $query->where('grade', $request->grade_id);
        }

        // Filter by volt
        if ($request->has('volt_id') && $request->volt_id) {
            $query->where('volt', $request->volt_id);
        }

        // Filter by CCA
        if ($request->has('cca_id') && $request->cca_id) {
            $query->where('cca', $request->cca_id);
        }

        // Filter by supplier
        if ($request->has('supplier') && $request->supplier) {
            $query->where('supplier', 'LIKE', "%{$request->supplier}%");
        }

        // Filter by rack
        if ($request->has('rack') && $request->rack) {
            $query->where('rack', 'LIKE', "%{$request->rack}%");
        }

        // Filter by stock availability
        if ($request->has('in_stock')) {
            if ($request->in_stock == 'yes') {
                $query->where('on_hand', '>', 0);
            } elseif ($request->in_stock == 'no') {
                $query->where('on_hand', '<=', 0);
            }
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active == '1' ? 1 : 0);
        }

        // Price range filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('sale_price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        // Limit results
        $limit = $request->input('limit', 50);
        $items = $query->limit($limit)->get();

        return response()->json($items);
    }
    
    /**
     * Get item details for sales
     */
    public function getItemDetails($id)
    {
        $item = Item::with(['partnumber_item', 'category', 'vehical_item.manutacturer_vehical', 'vehical_item.model_vehical'])->findOrFail($id);
        
        // Build item name from available data
        $itemName = $item->short_disc ?? $item->pro_dis ?? '';
        if (empty($itemName) && $item->partnumber_item) {
            $itemName = $item->partnumber_item->name ?? '';
        }
        if (empty($itemName)) {
            $itemName = $item->bar_code;
        }
        
        // Add manufacturer and model if available
        if ($item->vehical_item && $item->vehical_item->manutacturer_vehical) {
            $itemName .= ' - ' . $item->vehical_item->manutacturer_vehical->name;
        }
        if ($item->vehical_item && $item->vehical_item->model_vehical) {
            $itemName .= ' ' . $item->vehical_item->model_vehical->name;
        }
        
        return response()->json([
            'id' => $item->id,
            'name' => $itemName,
            'rate' => $item->sale_price ?? $item->packing_purchase_rate ?? 0,
            'unit' => $item->unit ?? 'Unit',
            'stock' => $item->on_hand ?? 0,
            'bar_code' => $item->bar_code,
            'serial_number' => $item->serial_number,
            'packing' => $item->packing ?? 1,
        ]);
    }
    
    /**
     * Get stock status for an item across all branches and warehouses (Sales)
     */
    public function getItemStockStatus($id)
    {
        $item = Item::findOrFail($id);
        $packingSize = $item->packing ?? 1;
        
        // Get all warehouse items for this item
        $warehouseItems = \App\Models\WarehouseItem::with(['warehouse.branch'])
            ->where('item_id', $id)
            ->get();
        
        $stockStatus = [];
        
        // Group by branch first
        $branchStocks = [];
        foreach ($warehouseItems as $warehouseItem) {
            $warehouse = $warehouseItem->warehouse;
            $branch = $warehouse ? $warehouse->branch : null;
            $branchId = $branch ? $branch->id : 0;
            $branchName = $branch ? $branch->branch_name : 'No Branch';
            $branchCode = $branch ? $branch->branch_code : '';
            
            $quantity = floatval($warehouseItem->quantity ?? 0);
            $cartons = floor($quantity / $packingSize);
            $loose = $quantity % $packingSize;
            
            if (!isset($branchStocks[$branchId])) {
                $branchStocks[$branchId] = [
                    'branch_id' => $branchId,
                    'branch_name' => $branchName,
                    'branch_code' => $branchCode,
                    'display' => $branchName . ($branchCode ? ' (' . $branchCode . ')' : ''),
                    'total_cartons' => 0,
                    'total_loose' => 0,
                    'warehouses' => []
                ];
            }
            
            $warehouseData = [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->warehouse_name,
                'warehouse_code' => $warehouse->warehouse_code,
                'quantity' => $quantity,
                'cartons' => $cartons,
                'loose' => $loose,
                'display' => $warehouse->warehouse_name . ($warehouse->warehouse_code ? ' (' . $warehouse->warehouse_code . ')' : '')
            ];
            
            $branchStocks[$branchId]['warehouses'][] = $warehouseData;
            $branchStocks[$branchId]['total_cartons'] += $cartons;
            $branchStocks[$branchId]['total_loose'] += $loose;
        }
        
        // Convert to array format
        foreach ($branchStocks as $branchStock) {
            // Add branch total
            $stockStatus[] = [
                'type' => 'branch',
                'id' => $branchStock['branch_id'],
                'name' => $branchStock['branch_name'],
                'code' => $branchStock['branch_code'],
                'display' => $branchStock['display'],
                'cartons' => $branchStock['total_cartons'],
                'loose' => $branchStock['total_loose'],
            ];
            
            // Add warehouses under branch
            foreach ($branchStock['warehouses'] as $warehouse) {
                $stockStatus[] = [
                    'type' => 'warehouse',
                    'id' => $warehouse['warehouse_id'],
                    'name' => $warehouse['warehouse_name'],
                    'code' => $warehouse['warehouse_code'],
                    'display' => $warehouse['display'],
                    'cartons' => $warehouse['cartons'],
                    'loose' => $warehouse['loose'],
                    'quantity' => $warehouse['quantity'],
                    'branch_id' => $branchStock['branch_id'],
                ];
            }
        }
        
        return response()->json($stockStatus);
    }
}
