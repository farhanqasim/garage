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
use App\Models\CustomerCar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    public function all_sales()
    {
        $sales = Sale::with(['customer', 'user', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.sales.index', compact('sales'));
    }
    
    public function create_sale_new(){
        $customers = Customer::orderBy('created_at', 'desc')->get();
        $branches = \App\Models\Branch::where('status', 'active')->get();
        $units = \App\Models\Unit::all();
        return view('admin.sales.create-new', compact('customers', 'branches', 'units'));
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
     * Get next estimate number
     */
    public function getNextEstimateNumber()
    {
        $branchId = session('selected_branch_id');
        
        if (!$branchId) {
            return response()->json(['error' => 'Branch not selected'], 400);
        }
        
        // Get the last estimate number for this branch
        $lastEstimate = Sale::where('branch_id', $branchId)
            ->where('status', 'estimate')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 0; // Start from 00000
        if ($lastEstimate) {
            // Extract number from reference if it exists, otherwise use ID
            if ($lastEstimate->reference && preg_match('/EST\s*#?\s*(\d+)/i', $lastEstimate->reference, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                // Count estimates for this branch (start from 0, so count gives next number)
                $nextNumber = Sale::where('branch_id', $branchId)
                    ->where('status', 'estimate')
                    ->count();
            }
        }
        
        return response()->json([
            'number' => str_pad($nextNumber, 5, '0', STR_PAD_LEFT)
        ]);
    }

    /**
     * Get next sale order number
     */
    public function getNextSaleOrderNumber()
    {
        $branchId = session('selected_branch_id');
        
        if (!$branchId) {
            return response()->json(['error' => 'Branch not selected'], 400);
        }
        
        // Get the last sale order number for this branch
        $lastSaleOrder = Sale::where('branch_id', $branchId)
            ->where('status', 'sale_order')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 0; // Start from 00000
        if ($lastSaleOrder) {
            // Extract number from reference if it exists
            if ($lastSaleOrder->reference && preg_match('/SO\s*#?\s*(\d+)/i', $lastSaleOrder->reference, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                // Count sale orders for this branch (start from 0, so count gives next number)
                $nextNumber = Sale::where('branch_id', $branchId)
                    ->where('status', 'sale_order')
                    ->count();
            }
        }
        
        return response()->json([
            'number' => str_pad($nextNumber, 5, '0', STR_PAD_LEFT)
        ]);
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
     * Search items directly from items table - No warehouse or branch filtering
     * Includes stock quantity, price calculations, and sales prices
     */
    public function ajaxSearch(Request $request)
    {
        $search = $request->input('q', '');
        $results = [];
        
        // Load all relationships for efficient searching and display
        $query = Item::with([
            'partnumber_item',
            'vehical_item.manutacturer_vehical',
            'vehical_item.model_vehical',
            'category',
            'subcategory',
            'unit_item', // Load unit relationship to get unit name
            'product_item', // Product name
            'company_item', // Company
            'quality_item', // Quality
            'technology_item', // Technology
            'grade_item', // Grade
            'volt_item', // Volt
            'cca_item', // CCA
            'group_item', // Group
            'made_in_item', // Made In
            'level_item', // Level
            'plate_item', // Plate (for battery)
            'amphors_item', // Amperes (for battery)
            'vehical_item.engine_vehical',
            'vehical_item.country_vehical',
            'lineitems_item',
            'mileage_item',
            'minus_pool_item',
            'farmula_item',
            'services_item',
            'warrenty_item',
        ])->where('is_active', 1);

        // Multi-term search: space-separated words = AND filter (each term must match somewhere in item)
        // If search is empty, show all active items (YouTube style - show all when no query)
        $search = trim($request->input('q', ''));
        $terms = $search !== '' ? array_values(array_filter(preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY))) : [];
        
        // Only apply search filters if there are search terms
        if (!empty($terms)) {
        foreach ($terms as $term) {
            $query->where(function ($q) use ($term) {
                // ========== PRIMARY PRODUCT IDENTIFICATION ==========
                $q->where('bar_code', 'LIKE', "%{$term}%")
                  ->orWhere('pro_dis', 'LIKE', "%{$term}%")
                  ->orWhere('short_disc', 'LIKE', "%{$term}%")
                  ->orWhere('serial_number', 'LIKE', "%{$term}%")
                  ->orWhere('p_brochure', 'LIKE', "%{$term}%");
                // ========== CATEGORY / PART NUMBER ==========
                $q->orWhereHas('category', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('subcategory', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('partnumber_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                });
                // ========== VEHICLE RELATED ==========
                if (is_numeric($term)) {
                    $q->orWhere('vehical_id', $term);
                }
                $q->orWhereHas('vehical_item', function ($subQ) use ($term) {
                    $subQ->where('year_from', 'LIKE', "%{$term}%")
                      ->orWhere('year_to', 'LIKE', "%{$term}%")
                      ->orWhere('car_manufactured_country', 'LIKE', "%{$term}%")
                      ->orWhere('id', 'LIKE', "%{$term}%")
                      ->orWhere('v_part_number_id', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('vehical_item.engine_vehical', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%")->orWhere('id', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('vehical_item.country_vehical', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%")->orWhere('id', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('vehical_item.manutacturer_vehical', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%")->orWhere('id', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('vehical_item.model_vehical', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%")->orWhere('id', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('vehical_item.vehical_part_number', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%")->orWhere('id', 'LIKE', "%{$term}%");
                });
                // ========== PRODUCT / COMPANY / PLATE / AMPHORS / LINE / MILEAGE ==========
                $q->orWhere('type', 'LIKE', "%{$term}%")->orWhere('p_id', 'LIKE', "%{$term}%");
                $q->orWhereHas('product_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('company_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('plate_item', function ($subQ) use ($term) {
                    // Strip "pl", "PL", "pl ", "PL " from the end of the term for plates search
                    $plateTerm = preg_replace('/\s*(pl|PL)\s*$/i', '', $term);
                    $subQ->where('name', 'LIKE', "%{$plateTerm}%");
                })
                ->orWhereHas('amphors_item', function ($subQ) use ($term) {
                    // Strip "ah", "AH", "ah ", "AH " from the end of the term for amperes search
                    $amperesTerm = preg_replace('/\s*(ah|AH)\s*$/i', '', $term);
                    $subQ->where('name', 'LIKE', "%{$amperesTerm}%");
                })
                ->orWhereHas('lineitems_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('mileage_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                });
                // ========== BATTERY SPECS ==========
                $q->orWhereHas('volt_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('cca_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('minus_pool_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('technology_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('grade_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('farmula_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhere('battery_size', 'LIKE', "%{$term}%");
                // ========== LOCATION / QUALITY / STOCK / UNIT / PACKAGING ==========
                $q->orWhere('bussiness_location', 'LIKE', "%{$term}%");
                $q->orWhereHas('quality_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                });
                $q->orWhere('l_stock', 'LIKE', "%{$term}%")->orWhere('m_stock', 'LIKE', "%{$term}%");
                $q->orWhereHas('unit_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                });
                $q->orWhere('packing', 'LIKE', "%{$term}%")
                  ->orWhere('scale', 'LIKE', "%{$term}%")
                  ->orWhere('weight_unit', 'LIKE', "%{$term}%")
                  ->orWhere('filling', 'LIKE', "%{$term}%")
                  ->orWhere('weight_for_delivery', 'LIKE', "%{$term}%")
                  ->orWhere('packing_purchase_rate', 'LIKE', "%{$term}%")
                  ->orWhere('total_price', 'LIKE', "%{$term}%")
                  ->orWhere('price_per_unit', 'LIKE', "%{$term}%")
                  ->orWhere('sale_price', 'LIKE', "%{$term}%")
                  ->orWhere('on_hand', 'LIKE', "%{$term}%")
                  ->orWhere('rack', 'LIKE', "%{$term}%")
                  ->orWhere('supplier', 'LIKE', "%{$term}%");
                if (is_numeric($term)) {
                    $numericValue = (float)$term;
                    $q->orWhere('filling', $numericValue)
                      ->orWhere('weight_for_delivery', $numericValue)
                      ->orWhere('packing_purchase_rate', $numericValue)
                      ->orWhere('total_price', $numericValue)
                      ->orWhere('price_per_unit', $numericValue)
                      ->orWhere('sale_price', $numericValue)
                      ->orWhere('on_hand', (int)$numericValue);
                }
                if (strlen($term) >= 4) {
                    $q->orWhere('update_date', 'LIKE', "%{$term}%");
                }
                $q->orWhereHas('services_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('warrenty_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('group_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('made_in_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                })
                ->orWhereHas('level_item', function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%");
                });
            });
        }
        } // End of if (!empty($terms))

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

        // Order by: if search exists, relevance; otherwise by most recent
        if (empty($terms)) {
            $query->orderBy('created_at', 'desc');
        } else {
            // When searching, order by relevance (items with matches in important fields first)
            $query->orderBy('updated_at', 'desc');
        }
        
        // Limit results
        $limit = $request->input('limit', 50);
        $items = $query->limit($limit)->get();

        // Return items directly from items table (no warehouse filtering)
        foreach ($items as $item) {
            // Get packing size for carton/loose calculation
            $packingSize = floatval($item->packing ?? 1);
            $onHand = floatval($item->on_hand ?? 0);
            $cartons = floor($onHand / $packingSize);
            $loose = fmod($onHand, $packingSize);
            
            // Price calculations
            $salePrice = floatval($item->sale_price ?? 0);
            $packingPurchaseRate = floatval($item->packing_purchase_rate ?? 0);
            $totalPrice = floatval($item->total_price ?? 0);
            $pricePerUnit = floatval($item->price_per_unit ?? 0);
            
            // Calculate price per unit if total price is given
            if ($totalPrice > 0 && $onHand > 0) {
                $calculatedPricePerUnit = $totalPrice / $onHand;
            } elseif ($pricePerUnit > 0) {
                $calculatedPricePerUnit = $pricePerUnit;
            } elseif ($packingPurchaseRate > 0 && $packingSize > 0) {
                $calculatedPricePerUnit = $packingPurchaseRate / $packingSize;
            } else {
                $calculatedPricePerUnit = $salePrice > 0 ? $salePrice : 0;
            }
            
            // Calculate total cost based on on_hand quantity
            $totalCost = $calculatedPricePerUnit * $onHand;
            
            $results[] = [
                'type' => 'item',
                'id' => $item->id,
                'item' => $item,
                // Stock and Quantity Information (from items table)
                'warehouse_quantity' => $onHand,
                'available_quantity' => $onHand,
                'reserved_quantity' => 0,
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
                'bar_code' => $item->bar_code,
                'serial_number' => $item->serial_number,
                'unit' => ($item->unit_item && ($item->unit_item->name || $item->unit_item->short_name)) 
                    ? ($item->unit_item->name || $item->unit_item->short_name) 
                    : ($item->unit ?? 'Unit'),
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
                'vehicles' => 'nullable|array',
                'vehicles.*.customer_id' => 'required|exists:customers,id',
                'vehicles.*.plate_number' => 'required|string|max:255',
                'vehicles.*.make' => 'required|string|max:255',
                'vehicles.*.model' => 'required|string|max:255',
                'vehicles.*.year' => 'required|string|max:4',
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

            // Save vehicles to customer_cars table
            if ($request->filled('vehicles') && is_array($request->vehicles)) {
                foreach ($request->vehicles as $vehicleData) {
                    // Check if vehicle with same plate number already exists for this customer
                    $existingVehicle = CustomerCar::where('customer_id', $vehicleData['customer_id'])
                        ->where('plate_number', $vehicleData['plate_number'])
                        ->first();
                    
                    if ($existingVehicle) {
                        // Update existing vehicle
                        $existingVehicle->update([
                            'make' => $vehicleData['make'],
                            'model' => $vehicleData['model'],
                            'year' => $vehicleData['year'],
                        ]);
                    } else {
                        // Create new vehicle
                        CustomerCar::create([
                            'customer_id' => $vehicleData['customer_id'],
                            'plate_number' => $vehicleData['plate_number'],
                            'make' => $vehicleData['make'],
                            'model' => $vehicleData['model'],
                            'year' => $vehicleData['year'],
                        ]);
                    }
                }
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

    /**
     * Show sale details
     */
    public function show($id)
    {
        $sale = Sale::with(['customer', 'branch', 'user', 'saleItems.item.partnumber_item', 'saleItems.item.category', 'payments.paymentMethod', 'payments.bankAccount.bank'])
            ->findOrFail($id);
        
        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Get sale data for editing
     */
    public function edit($id)
    {
        $sale = Sale::with(['customer', 'branch', 'saleItems.item.partnumber_item', 'saleItems.item.category'])->findOrFail($id);
        $customers = Customer::orderBy('created_at', 'desc')->get();
        $branches = \App\Models\Branch::where('status', 'active')->get();
        
        return view('admin.sales.edit', compact('sale', 'customers', 'branches'));
    }

    /**
     * Update sale
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'branch_id' => 'required|exists:branches,id',
                'sale_date' => 'required|date',
                'reference' => 'nullable|string|max:255',
                'status' => 'nullable|string',
                'discount' => 'nullable|numeric|min:0',
                'order_tax' => 'nullable|numeric|min:0',
                'shipping' => 'nullable|numeric|min:0',
            ]);

            $sale->update($validated);
            
            // Recalculate grand total
            $itemsTotal = $sale->saleItems->sum('total');
            $grandTotal = $itemsTotal + ($request->order_tax ?? 0) - ($request->discount ?? 0) + ($request->shipping ?? 0);
            $sale->update(['grand_total' => $grandTotal]);

            return redirect()->route('all_sales')
                ->with('success', 'Sale updated successfully!');

        } catch (\Exception $e) {
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

    /**
     * Delete sale
     */
    public function destroy($id)
    {
        $sale = Sale::with('saleItems')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Restore stock
            $warehouse = Warehouse::where('branch_id', $sale->branch_id)->first();
            if ($warehouse) {
                foreach ($sale->saleItems as $saleItem) {
                    $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
                        ->where('item_id', $saleItem->item_id)
                        ->first();
                    
                    if ($warehouseItem) {
                        $warehouseItem->quantity += $saleItem->quantity;
                        $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                        $warehouseItem->save();
                    }
                    
                    // Restore item on_hand
                    $item = Item::find($saleItem->item_id);
                    if ($item) {
                        $item->on_hand = ($item->on_hand ?? 0) + $saleItem->quantity;
                        $item->save();
                    }
                }
            }
            
            // Delete payments
            SalePayment::where('sale_id', $sale->id)->delete();
            
            // Delete sale items
            $sale->saleItems()->delete();
            
            // Delete sale
            $sale->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download PDF
     */
    public function pdf($id)
    {
        $sale = Sale::with(['customer', 'branch', 'user', 'saleItems.item.partnumber_item', 'saleItems.item.category'])->findOrFail($id);
        
        // Logo handling
        $logoUrl = setting_value('logo') ?: asset('assets/img/logo.svg');
        $logoData = null;
        if ($logoPath = setting_value('logo')) {
            $fullPath = str_replace(url('/'), public_path(), $logoPath);
            if (file_exists($fullPath)) {
                $logoData = 'data:image/' . pathinfo($fullPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }
        
        $data = [
            'sale' => $sale,
            'logoData' => $logoData,
            'logoUrl' => $logoUrl,
            'companyName' => setting_value('logo_text', 'MUBARAK TRADERS'),
            'helpline' => setting_value('helpline', '+92-335-08-999-08'),
            'address' => setting_value('address', ''),
            'city' => setting_value('city', ''),
            'state' => setting_value('state', ''),
            'zip' => setting_value('zip', ''),
            'country' => setting_value('country', ''),
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.sales.pdf', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'DejaVu Sans',
                  ]);
        
        return $pdf->download('Sale-' . ($sale->reference ?? $sale->id) . '.pdf');
    }

    /**
     * Get payments for a sale
     */
    public function getPayments($id)
    {
        $sale = Sale::with(['payments.paymentMethod', 'payments.bankAccount.bank'])->findOrFail($id);
        $totalPaid = $sale->total_paid;
        $discount = $sale->discount ?? 0;
        // If discount is given and no payment, treat discount as payment
        if ($discount > 0 && $totalPaid == 0) {
            $due = max(0, $sale->grand_total - $discount);
        } else {
            $due = max(0, $sale->grand_total - $totalPaid);
        }
        $payments = $sale->payments;
        
        return view('admin.sales.payments', compact('sale', 'payments', 'totalPaid', 'due', 'discount'));
    }

    /**
     * Show create payment form
     */
    public function showCreatePayment($id)
    {
        $sale = Sale::findOrFail($id);
        $totalPaid = $sale->total_paid;
        $discount = $sale->discount ?? 0;
        // If discount is given and no payment, treat discount as payment
        if ($discount > 0 && $totalPaid == 0) {
            $remaining = max(0, $sale->grand_total - $discount);
        } else {
            $remaining = max(0, $sale->grand_total - $totalPaid);
        }
        
        return view('admin.sales.create-payment', compact('sale', 'totalPaid', 'remaining', 'discount'));
    }

    /**
     * Create payment for a sale
     */
    public function createPayment(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_transaction_id' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string',
        ]);
        
        $paymentAmount = floatval($request->payment_amount);
        $discount = $sale->discount ?? 0;
        // Calculate remaining considering discount as payment if no payment made
        if ($discount > 0 && $sale->total_paid == 0) {
            $remaining = max(0, $sale->grand_total - $discount);
        } else {
            $remaining = max(0, $sale->grand_total - $sale->total_paid);
        }
        
        if ($paymentAmount > $remaining) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Payment amount cannot exceed remaining amount (Rs ' . number_format($remaining, 2) . ')');
        }
        
        DB::beginTransaction();
        try {
            $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
            
            if ($paymentMethod->requires_bank_account && !$request->bank_account_id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Bank account is required for this payment method.');
            }
            
            $payment = Payment::create([
                'user_id' => auth()->id(),
                'customer_id' => $sale->customer_id,
                'payment_method_id' => $request->payment_method_id,
                'bank_account_id' => $request->bank_account_id ?? null,
                'amount' => $paymentAmount,
                'currency' => 'PKR',
                'direction' => 'in',
                'payment_date' => $request->payment_date,
                'transaction_id' => $request->payment_transaction_id ?? null,
                'status' => 'paid',
                'paid_at' => now(),
                'notes' => $request->payment_notes ?? "Payment for Sale #{$sale->id}",
            ]);
            
            SalePayment::create([
                'sale_id' => $sale->id,
                'payment_id' => $payment->id,
                'allocated_amount' => $paymentAmount,
            ]);
            
            DB::commit();
            
            return redirect()->route('sales.payments', $sale->id)
                ->with('success', 'Payment created successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating payment: ' . $e->getMessage());
        }
    }
}
