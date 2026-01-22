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
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    public function all_sales()
    {
        return view('admin.sales.index');
    }
    
    public function create_sale(){
        $customers = Customer::orderBy('created_at', 'desc')->get();
        $purchaseData = session('purchase_to_sale', null);
        
        // Set branch if coming from purchase
        if ($purchaseData && isset($purchaseData['branch_id'])) {
            $branch = \App\Models\Branch::find($purchaseData['branch_id']);
            if ($branch) {
                session([
                    'selected_branch_id' => $branch->id,
                    'selected_branch_name' => $branch->branch_name,
                    'selected_branch_code' => $branch->branch_code ?? '',
                ]);
            }
        }
        
        return view('admin.sales.create', compact('customers', 'purchaseData'));
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
     * Search items in warehouse - Only returns items that are in the selected branch's warehouse
     * Includes stock quantity, price calculations, and sales prices
     */
    public function ajaxSearch(Request $request)
    {
        $search = $request->input('q', '');
        $results = [];
        
        // Get selected branch ID (required)
        $branchId = $request->input('branch_id') ?? session('selected_branch_id');
        
        if (!$branchId) {
            return response()->json([
                'error' => 'Please select a branch first'
            ], 400);
        }
        
        // Get warehouse for the selected branch
        $warehouse = \App\Models\Warehouse::where('branch_id', $branchId)->first();
        
        if (!$warehouse) {
            return response()->json([
                'error' => 'No warehouse found for selected branch'
            ], 404);
        }
        
        // Get all item IDs that are in this warehouse
        $warehouseItemIds = \App\Models\WarehouseItem::where('warehouse_id', $warehouse->id)
            ->pluck('item_id')
            ->toArray();
        
        if (empty($warehouseItemIds)) {
            return response()->json([]);
        }
        
        // Load all relationships for efficient searching and display
        $query = Item::with([
            'partnumber_item',
            'category',
            'subcategory',
            'product_item',
            'company_item',
            'vehical_item.manutacturer_vehical',
            'vehical_item.model_vehical',
            'vehical_item.engine_vehical',
            'vehical_item.country_vehical',
            'plate_item',
            'amphors_item',
            'lineitems_item',
            'mileage_item',
            'volt_item',
            'cca_item',
            'minus_pool_item',
            'technology_item',
            'grade_item',
            'farmula_item',
            'quality_item',
            'unit_item',
            'services_item',
            'warrenty_item',
            'group_item',
            'made_in_item',
            'level_item',
        ])->whereIn('id', $warehouseItemIds); // Only items in warehouse

        // Comprehensive text search - Search ALL fields based on actual Item model relationships
        $search = trim($request->input('q', ''));
        // If search query provided, filter items; otherwise show all items in warehouse
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // ========== PRIMARY PRODUCT IDENTIFICATION ==========
                // Product Name Fields (Most Important)
                $q->where('bar_code', 'LIKE', "%{$search}%")
                  ->orWhere('pro_dis', 'LIKE', "%{$search}%")
                  ->orWhere('short_disc', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('p_brochure', 'LIKE', "%{$search}%");
                
                // ========== CATEGORY SEARCH ==========
                $q->orWhereHas('category', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('subcategory', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // ========== PART NUMBER SEARCH ==========
                $q->orWhereHas('partnumber_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // ========== VEHICLE RELATED SEARCH ==========
                $q->orWhereHas('vehical_item', function ($subQ) use ($search) {
                    $subQ->where('year_from', 'LIKE', "%{$search}%")
                  ->orWhere('year_to', 'LIKE', "%{$search}%")
                  ->orWhere('car_manufactured_country', 'LIKE', "%{$search}%");
            })
                ->orWhereHas('vehical_item.engine_vehical', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('vehical_item.country_vehical', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('vehical_item.manutacturer_vehical', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('vehical_item.model_vehical', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // ========== PRODUCT TYPE AND IDENTIFICATION ==========
                $q->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhere('p_id', 'LIKE', "%{$search}%");
                
                // ========== RELATIONSHIP BASED SEARCHES (Using actual relationships) ==========
                // Product
                $q->orWhereHas('product_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Company
                $q->orWhereHas('company_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Plate/Platos
                $q->orWhereHas('plate_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Amphors
                $q->orWhereHas('amphors_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Line Items
                $q->orWhereHas('lineitems_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Mileage
                $q->orWhereHas('mileage_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // ========== BATTERY/PRODUCT SPECIFICATIONS ==========
                // Volt (via relationship)
                $q->orWhereHas('volt_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // CCA (via relationship)
                $q->orWhereHas('cca_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Minus Pool Direction (via relationship - only minus_pool_direction exists in DB)
                $q->orWhereHas('minus_pool_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Technology (via relationship)
                $q->orWhereHas('technology_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Grade (via relationship)
                $q->orWhereHas('grade_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Formula/Farmula (via relationship)
                $q->orWhereHas('farmula_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Battery Size (direct field)
                $q->orWhere('battery_size', 'LIKE', "%{$search}%");
                
                // ========== LOCATION AND BUSINESS FIELDS ==========
                // Business Location (only bussiness_location exists in DB, not business_location)
                $q->orWhere('bussiness_location', 'LIKE', "%{$search}%");
                
                // Quality (via relationship)
                $q->orWhereHas('quality_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Stock Levels
                $q->orWhere('l_stock', 'LIKE', "%{$search}%")
                  ->orWhere('m_stock', 'LIKE', "%{$search}%");
                
                // ========== UNIT AND PACKAGING ==========
                // Unit (via relationship)
                $q->orWhereHas('unit_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Direct packaging fields
                $q->orWhere('packing', 'LIKE', "%{$search}%")
                  ->orWhere('scale', 'LIKE', "%{$search}%")
                  ->orWhere('weight_unit', 'LIKE', "%{$search}%");
                
                // Numeric fields (convert to string for search)
                if (is_numeric($search)) {
                    $q->orWhere('filling', 'LIKE', "%{$search}%")
                      ->orWhere('weight_for_delivery', 'LIKE', "%{$search}%")
                      ->orWhere('packing_purchase_rate', 'LIKE', "%{$search}%");
                }
                
                // ========== STORAGE AND SUPPLIER ==========
                $q->orWhere('rack', 'LIKE', "%{$search}%")
                  ->orWhere('supplier', 'LIKE', "%{$search}%");
                
                // Date field (search as string)
                $q->orWhere('update_date', 'LIKE', "%{$search}%");
                
                // ========== ADDITIONAL PRODUCT INFORMATION ==========
                // Services (via relationship)
                $q->orWhereHas('services_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Warranty (via relationship)
                $q->orWhereHas('warrenty_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Group (only gorup exists in DB, not group)
                $q->orWhereHas('group_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Made In (via relationship)
                $q->orWhereHas('made_in_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Level (via relationship)
                $q->orWhereHas('level_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
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

        // Price range filter (for sale price)
        if ($request->has('min_price') && $request->min_price) {
            $query->where('sale_price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        // Limit results
        $limit = $request->input('limit', 50);
        $items = $query->limit($limit)->get();

        // Build results with warehouse stock, quantity, and price calculations
        foreach ($items as $item) {
            // Get warehouse item details for this item
            $warehouseItem = \App\Models\WarehouseItem::where('warehouse_id', $warehouse->id)
                ->where('item_id', $item->id)
                ->first();
            
            if (!$warehouseItem) {
                continue; // Skip if not in warehouse
            }
            
            // Calculate quantities
            $warehouseQuantity = floatval($warehouseItem->quantity ?? 0);
            $availableQuantity = floatval($warehouseItem->available_quantity ?? 0);
            $reservedQuantity = floatval($warehouseItem->reserved_quantity ?? 0);
            
            // Get packing size for carton/loose calculation
            $packingSize = floatval($item->packing ?? 1);
            $cartons = floor($warehouseQuantity / $packingSize);
            $loose = fmod($warehouseQuantity, $packingSize);
            
            // Price calculations
            $salePrice = floatval($item->sale_price ?? 0);
            $packingPurchaseRate = floatval($item->packing_purchase_rate ?? 0);
            $totalPrice = floatval($item->total_price ?? 0);
            $pricePerUnit = floatval($item->price_per_unit ?? 0);
            
            // Calculate price per unit if total price is given
            if ($totalPrice > 0 && $warehouseQuantity > 0) {
                $calculatedPricePerUnit = $totalPrice / $warehouseQuantity;
            } elseif ($pricePerUnit > 0) {
                $calculatedPricePerUnit = $pricePerUnit;
            } elseif ($packingPurchaseRate > 0 && $packingSize > 0) {
                $calculatedPricePerUnit = $packingPurchaseRate / $packingSize;
            } else {
                $calculatedPricePerUnit = $salePrice > 0 ? $salePrice : 0;
            }
            
            // Calculate total cost based on warehouse quantity
            $totalCost = $calculatedPricePerUnit * $warehouseQuantity;
            
            // Build item name
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
            
                $results[] = [
                    'type' => 'item',
                    'id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->warehouse_name,
                'warehouse_code' => $warehouse->warehouse_code,
                'item' => $item,
                // Stock and Quantity Information
                'warehouse_quantity' => $warehouseQuantity,
                'available_quantity' => $availableQuantity,
                'reserved_quantity' => $reservedQuantity,
                'cartons' => $cartons,
                'loose' => $loose,
                'packing_size' => $packingSize,
                // Price Information
                'sale_price' => $salePrice,
                'packing_purchase_rate' => $packingPurchaseRate,
                'total_price' => $totalPrice,
                'price_per_unit' => $pricePerUnit,
                'calculated_price_per_unit' => round($calculatedPricePerUnit, 2),
                'total_cost' => round($totalCost, 2),
                // Item Details
                'item_name' => $itemName,
                'bar_code' => $item->bar_code,
                'serial_number' => $item->serial_number,
                'unit' => $item->unit ?? 'Unit',
                'category_name' => $item->category ? $item->category->name : null,
                'part_number' => $item->partnumber_item ? $item->partnumber_item->name : null,
            ];
        }
        
        return response()->json($results);
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
        
        // Calculate price - prioritize sale_price, then packing_purchase_rate, then total_price/on_hand
        $salePrice = floatval($item->sale_price ?? 0);
        $packingPurchaseRate = floatval($item->packing_purchase_rate ?? 0);
        $totalPrice = floatval($item->total_price ?? 0);
        $onHand = floatval($item->on_hand ?? 0);
        
        // If sale_price exists, use it
        if ($salePrice > 0) {
            $rate = $salePrice;
        } 
        // If total_price and on_hand exist, calculate per unit
        elseif ($totalPrice > 0 && $onHand > 0) {
            $rate = $totalPrice / $onHand;
        }
        // Otherwise use packing_purchase_rate
        else {
            $rate = $packingPurchaseRate > 0 ? $packingPurchaseRate : 0;
        }
        
        return response()->json([
            'id' => $item->id,
            'name' => $itemName,
            'rate' => round($rate, 2),
            'sale_price' => $salePrice,
            'packing_purchase_rate' => $packingPurchaseRate,
            'total_price' => $totalPrice,
            'unit' => $item->unit ?? 'Unit',
            'stock' => $onHand,
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
    
    /**
     * Store a new sale
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'branch_id' => 'required|exists:branches,id',
                'sale_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.unit' => 'nullable|string',
                'items.*.discount' => 'nullable|numeric|min:0',
                'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
                'items.*.tax_amount' => 'nullable|numeric|min:0',
                'items.*.total' => 'required|numeric|min:0',
                'order_tax' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'shipping' => 'nullable|numeric|min:0',
                'reference' => 'nullable|string|max:255',
                'status' => 'nullable|string',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'payment_amount' => 'nullable|numeric|min:0',
                'payment_date' => 'nullable|date',
                'payment_transaction_id' => 'nullable|string|max:255',
                'payment_notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $warehouse = Warehouse::where('branch_id', $request->branch_id)->first();
        if (!$warehouse) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warehouse not found for selected branch.'
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Warehouse not found for selected branch.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $saleQuantity = floatval($itemData['quantity']);

                $warehouseItem = WarehouseItem::lockForUpdate()
                    ->where('warehouse_id', $warehouse->id)
                    ->where('item_id', $itemData['item_id'])
                    ->first();

                if (!$warehouseItem) {
                    throw new \Exception("Item '{$item->bar_code}' not found in warehouse stock.");
                }

                $availableQuantity = floatval($warehouseItem->available_quantity ?? 0);
                if ($availableQuantity < $saleQuantity) {
                    throw new \Exception("Insufficient stock for item '{$item->bar_code}'. Available: {$availableQuantity}, Required: {$saleQuantity}");
                }
            }

            $itemsTotal = 0;
            foreach ($request->items as $item) {
                $itemsTotal += floatval($item['total']);
            }

            $orderTax = floatval($request->order_tax ?? 0);
            $discount = floatval($request->discount ?? 0);
            $shipping = floatval($request->shipping ?? 0);
            $grandTotal = $itemsTotal + $orderTax - $discount + $shipping;

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'branch_id' => $request->branch_id,
                'sale_date' => $request->sale_date,
                'reference' => $request->reference,
                'status' => $request->status ?? 'pending',
                'subtotal' => $itemsTotal,
                'order_tax' => $orderTax,
                'discount' => $discount,
                'shipping' => $shipping,
                'grand_total' => $grandTotal,
                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'] ?? 'Unit',
                    'rate' => $itemData['rate'],
                    'discount' => $itemData['discount'] ?? 0,
                    'tax_percentage' => $itemData['tax_percentage'] ?? 0,
                    'tax_amount' => $itemData['tax_amount'] ?? 0,
                    'total' => $itemData['total'],
                    'warranty' => $itemData['warranty'] ?? null,
                ]);

                $saleQuantity = floatval($itemData['quantity']);
                $warehouseItem = WarehouseItem::lockForUpdate()
                    ->where('warehouse_id', $warehouse->id)
                    ->where('item_id', $itemData['item_id'])
                    ->firstOrFail();

                $warehouseItem->quantity -= $saleQuantity;
                if ($warehouseItem->quantity < 0) {
                    $warehouseItem->quantity = 0;
                }
                $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                $warehouseItem->save();

                $item = Item::find($itemData['item_id']);
                if ($item) {
                    $item->on_hand = max(0, ($item->on_hand ?? 0) - $saleQuantity);
                    $item->save();
                }
            }

            // Create payment if provided
            if ($request->filled('payment_method_id') && $request->payment_amount > 0) {
                $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
                $paymentAmount = floatval($request->payment_amount);
                
                // Validate bank account if required
                if ($paymentMethod->requires_bank_account && !$request->bank_account_id) {
                    throw new \Exception('Bank account is required for this payment method.');
                }
                
                $payment = Payment::create([
                    'user_id' => auth()->id(),
                    'customer_id' => $request->customer_id,
                    'payment_method_id' => $request->payment_method_id,
                    'bank_account_id' => $request->bank_account_id ?? null,
                    'amount' => $paymentAmount,
                    'currency' => 'PKR',
                    'direction' => 'in', // Incoming payment for sale
                    'payment_date' => $request->payment_date ?? $request->sale_date,
                    'transaction_id' => $request->payment_transaction_id ?? null,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'notes' => $request->payment_notes ?? "Payment for Sale #{$sale->id}",
                ]);
                
                // Link payment to sale
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_id' => $payment->id,
                    'allocated_amount' => $paymentAmount,
                ]);
            }

            DB::commit();

            // Clear purchase_to_sale session data after successful sale creation
            if (session()->has('purchase_to_sale')) {
                session()->forget('purchase_to_sale');
            }

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale created successfully!',
                    'sale_id' => $sale->id
                ]);
            }

            return redirect()->route('all_sales')
                ->with('success', 'Sale created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale creation error: ' . $e->getMessage());
            Log::error('Sale creation stack trace: ' . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
