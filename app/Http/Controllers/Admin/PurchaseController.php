<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Category;
use App\Models\CarManufacturer;
use App\Models\PartNumber;
use App\Models\Technology;
use App\Models\Grade;
use App\Models\Volt;
use App\Models\Cca;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function all_purchases()
    {
        $purchases = Purchase::with(['supplier', 'items.item'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();
        $branches = \App\Models\Branch::where('status', 'active')->get();
        return view('admin.purchases.create', compact('suppliers', 'branches'));
    }
    
    /**
     * Search suppliers by phone number
     */
    public function searchSuppliersByPhone(Request $request)
    {
        $phone = $request->input('phone', '');
        
        if (empty($phone)) {
            return response()->json([]);
        }
        
        $suppliers = Supplier::where(function($q) use ($phone) {
            $q->whereJsonContains('phones', $phone)
              ->orWhereJsonContains('phones', '%' . $phone . '%');
        })
        ->orWhere(function($q) use ($phone) {
            $q->where('phones', 'LIKE', "%{$phone}%");
        })
        ->limit(10)
        ->get();
        
        $results = [];
        foreach ($suppliers as $supplier) {
            $phones = is_array($supplier->phones) ? $supplier->phones : json_decode($supplier->phones, true) ?? [];
            $names = is_array($supplier->names) ? $supplier->names : json_decode($supplier->names, true) ?? [];
            
            // Find matching phone
            $matchingPhone = '';
            foreach ($phones as $p) {
                if (stripos($p, $phone) !== false) {
                    $matchingPhone = $p;
                    break;
                }
            }
            
            $results[] = [
                'id' => $supplier->id,
                'name' => $names[0] ?? 'N/A',
                'phone' => $matchingPhone ?: ($phones[0] ?? ''),
                'company' => $supplier->company ?? '',
                'address' => $supplier->address ?? '',
                'area' => $supplier->area ?? '',
            ];
        }
        
        return response()->json($results);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'status' => 'required|in:received,pending,ordered',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Generate invoice number
        $invoiceNo = 'PUR-' . date('Y') . '-' . str_pad(Purchase::max('id') + 1, 5, '0', STR_PAD_LEFT);

        // Calculate totals
        $subtotal = 0;
        foreach ($request->items as $item) {
            $quantity = $item['quantity'];
            $rate = $item['rate'];
            $discount = $item['discount'] ?? 0;
            $taxPercentage = $item['tax_percentage'] ?? 0;
            
            $itemSubtotal = ($quantity * $rate) - $discount;
            $taxAmount = ($itemSubtotal * $taxPercentage) / 100;
            $itemTotal = $itemSubtotal + $taxAmount;
            
            $subtotal += $itemTotal;
        }

        $orderTax = $request->order_tax ?? 0;
        $discount = $request->discount ?? 0;
        $shipping = $request->shipping ?? 0;
        $grandTotal = $subtotal + $orderTax - $discount + $shipping;

        // Convert date format (handle both d/m/Y and Y-m-d)
        try {
            $purchaseDate = Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
        } catch (\Exception $e) {
            // If already in Y-m-d format or other format, try to parse directly
            $purchaseDate = Carbon::parse($request->purchase_date)->format('Y-m-d');
        }

        $purchase = Purchase::create([
            'invoice_no' => $invoiceNo,
            'branch_id' => $request->branch_id,
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $purchaseDate,
            'reference' => $request->reference,
            'status' => $request->status,
            'subtotal' => $subtotal,
            'order_tax' => $orderTax,
            'discount' => $discount,
            'shipping' => $shipping,
            'grand_total' => $grandTotal,
            'description' => $request->description,
        ]);

        // Create purchase items
        foreach ($request->items as $item) {
            $quantity = $item['quantity'];
            $rate = $item['rate'];
            $discount = $item['discount'] ?? 0;
            $taxPercentage = $item['tax_percentage'] ?? 0;
            
            $itemSubtotal = ($quantity * $rate) - $discount;
            $taxAmount = ($itemSubtotal * $taxPercentage) / 100;
            $unitCost = $itemSubtotal / $quantity;
            $totalCost = $itemSubtotal + $taxAmount;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_id' => $item['item_id'],
                'quantity' => $quantity,
                'unit' => $item['unit'] ?? null,
                'rate' => $rate,
                'discount' => $discount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
            ]);

            // Update item stock if status is 'received'
            if ($request->status === 'received') {
                $itemModel = Item::find($item['item_id']);
                if ($itemModel) {
                    $itemModel->on_hand = ($itemModel->on_hand ?? 0) + $quantity;
                    $itemModel->save();
                }
            }
        }

        return redirect()->route('all_purchases')->with('success', 'Purchase created successfully');
    }

    public function searchItems(Request $request)
    {
        $search = $request->input('search', '');
        
        $items = Item::where('is_active', 1)
            ->where(function($query) use ($search) {
                $query->where('bar_code', 'like', '%' . $search . '%')
                      ->orWhere('short_disc', 'like', '%' . $search . '%')
                      ->orWhere('pro_dis', 'like', '%' . $search . '%');
            })
            ->select('id', 'bar_code', 'short_disc', 'pro_dis', 'on_hand', 'packing_purchase_rate', 'product_unit', 'image')
            ->limit(20)
            ->get();

        // Add computed name field to each item for frontend display
        $items = $items->map(function($item) {
            $item->name = $item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A';
            return $item;
        });

        return response()->json($items);
    }

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
        
        // Get warehouse for this item from selected branch
        $warehouseId = null;
        $branchId = session('selected_branch_id');
        if ($branchId) {
            $warehouse = \App\Models\Warehouse::where('branch_id', $branchId)->first();
            if ($warehouse) {
                $warehouseItem = \App\Models\WarehouseItem::where('warehouse_id', $warehouse->id)
                    ->where('item_id', $item->id)
                    ->first();
                if ($warehouseItem) {
                    $warehouseId = $warehouse->id;
                }
            }
        }
        
        return response()->json([
            'id' => $item->id,
            'name' => $itemName,
            'rate' => $item->packing_purchase_rate ?? 0,
            'total_price' => $item->total_price ?? 0,
            'price_per_unit' => $item->price_per_unit ?? 0,
            'unit' => $item->unit ?? 'Unit',
            'stock' => $item->on_hand ?? 0,
            'warehouse_stock' => $item->on_hand ?? 0,
            'shop_stock' => 0,
            'bar_code' => $item->bar_code,
            'serial_number' => $item->serial_number,
            'packing' => $item->packing ?? 1, // Packing size for cartons calculation
            'warehouse_id' => $warehouseId,
        ]);
    }
    
    /**
     * Get stock status for an item across all branches and warehouses
     */
    public function getItemStockStatus($id)
    {
        $item = Item::findOrFail($id);
        $packingSize = $item->packing ?? 1; // Default packing size
        
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
     * Advanced search with multiple filters (YouTube-style)
     * Shows branches first, then warehouses, then items
     */
    public function ajaxSearch(Request $request)
    {
        $search = $request->input('q', '');
        $results = [];
        
        // 1. Search branches first (if search term provided)
        if ($search) {
            $matchingBranches = \App\Models\Branch::where('status', 'active')
                ->where(function($q) use ($search) {
                    $q->where('branch_name', 'LIKE', "%{$search}%")
                      ->orWhere('branch_code', 'LIKE', "%{$search}%");
                })
                ->limit(5)
                ->get();
            
            foreach ($matchingBranches as $branch) {
                $results[] = [
                    'type' => 'branch',
                    'id' => $branch->id,
                    'name' => $branch->branch_name,
                    'code' => $branch->branch_code,
                    'display' => $branch->branch_name . ($branch->branch_code ? ' (' . $branch->branch_code . ')' : '')
                ];
            }
        }
        
        // 2. Search warehouses (if search term provided)
        if ($search) {
            $matchingWarehouses = \App\Models\Warehouse::with('branch')
                ->where(function($q) use ($search) {
                    $q->where('warehouse_name', 'LIKE', "%{$search}%")
                      ->orWhere('warehouse_code', 'LIKE', "%{$search}%");
                })
                ->limit(10)
                ->get();
            
            foreach ($matchingWarehouses as $warehouse) {
                $results[] = [
                    'type' => 'warehouse',
                    'id' => $warehouse->id,
                    'name' => $warehouse->warehouse_name,
                    'code' => $warehouse->warehouse_code,
                    'branch_id' => $warehouse->branch_id,
                    'branch_name' => $warehouse->branch ? $warehouse->branch->branch_name : '',
                    'display' => $warehouse->warehouse_name . ($warehouse->warehouse_code ? ' (' . $warehouse->warehouse_code . ')' : '') . ($warehouse->branch ? ' - ' . $warehouse->branch->branch_name : '')
                ];
            }
        }
        
        // 3. Search items (filtered by selected branch if provided, or show all)
        $branchId = $request->input('branch_id') ?? session('selected_branch_id');
        
        $query = Item::with([
            'partnumber_item',
            'vehical_item.manutacturer_vehical',
            'vehical_item.model_vehical',
            'category',
            'subcategory',
        ]);
        
        // If branch is selected, filter by that branch's warehouse items
        if ($branchId) {
            $warehouse = \App\Models\Warehouse::where('branch_id', $branchId)->first();
            if ($warehouse) {
                $warehouseItemIds = \App\Models\WarehouseItem::where('warehouse_id', $warehouse->id)
                    ->pluck('item_id')
                    ->toArray();
                
                if (!empty($warehouseItemIds)) {
                    $query->whereIn('id', $warehouseItemIds);
                } else {
                    // No items in warehouse, return only branches/warehouses
                    return response()->json($results);
                }
            }
        }

        // Comprehensive text search - Search ALL fields based on actual Item model relationships
        // Only use columns that exist in the database migration and search through relationships
        $search = trim($request->input('q', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // ========== PRIMARY PRODUCT IDENTIFICATION ==========
                // Product Name Fields (Most Important) - These columns exist in DB
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
                // Direct columns that exist in DB
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
                
                // ========== CAR COMPANY FIELDS (direct columns from migration) ==========
                // These exist in migration: car_company, car_name, car_model_name, car_manufactur_country
                $q->orWhere('car_company', 'LIKE', "%{$search}%")
                  ->orWhere('car_name', 'LIKE', "%{$search}%")
                  ->orWhere('car_model_name', 'LIKE', "%{$search}%")
                  ->orWhere('car_manufactur_country', 'LIKE', "%{$search}%");
                
                // ========== BATTERY/PRODUCT SPECIFICATIONS ==========
                // Direct columns
                $q->orWhere('battery_size', 'LIKE', "%{$search}%");
                
                // Via relationships - These are foreign keys, search through related tables
                $q->orWhereHas('volt_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                $q->orWhereHas('cca_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                $q->orWhereHas('minus_pool_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Technology - Note: column is 'tecnology' in DB but relationship uses 'technology'
                $q->orWhereHas('technology_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                $q->orWhereHas('grade_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                $q->orWhereHas('farmula_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // ========== LOCATION AND BUSINESS FIELDS ==========
                $q->orWhere('bussiness_location', 'LIKE', "%{$search}%");
                
                $q->orWhereHas('quality_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Stock Levels
                $q->orWhere('l_stock', 'LIKE', "%{$search}%")
                  ->orWhere('m_stock', 'LIKE', "%{$search}%");
                
                // ========== UNIT AND PACKAGING ==========
                $q->orWhereHas('unit_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Direct packaging fields
                $q->orWhere('packing', 'LIKE', "%{$search}%")
                  ->orWhere('scale', 'LIKE', "%{$search}%");
                
                // Numeric fields (convert to string for search)
                if (is_numeric($search)) {
                    $q->orWhere('filling', 'LIKE', "%{$search}%")
                      ->orWhere('weight_for_delivery', 'LIKE', "%{$search}%")
                      ->orWhere('packing_purchase_rate', 'LIKE', "%{$search}%");
                }
                
                // ========== STORAGE AND SUPPLIER ==========
                $q->orWhere('rack', 'LIKE', "%{$search}%")
                  ->orWhere('supplier', 'LIKE', "%{$search}%");
                
                // ========== OTHER RELATIONSHIPS ==========
                $q->orWhereHas('services_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('warrenty_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('group_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('made_in_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('level_item', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
                
                // Mileage - also search as direct column if it's stored as string
                $q->orWhere('mileage', 'LIKE', "%{$search}%");
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

        // Price range filter (for purchase rate)
        if ($request->has('min_price') && $request->min_price) {
            $query->where('packing_purchase_rate', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('packing_purchase_rate', '<=', $request->max_price);
        }

        // Limit results
        $limit = $request->input('limit', 50);
        $items = $query->limit($limit)->get();

        // Group items by warehouse
        $warehouseItems = [];
        foreach ($items as $item) {
            // Get warehouse for this item (through warehouse_items)
            $warehouseItem = \App\Models\WarehouseItem::where('item_id', $item->id)->first();
            if ($warehouseItem) {
                $warehouse = $warehouseItem->warehouse;
                if ($warehouse) {
                    $warehouseId = $warehouse->id;
                    if (!isset($warehouseItems[$warehouseId])) {
                        $warehouseItems[$warehouseId] = [
                            'warehouse' => $warehouse,
                            'branch' => $warehouse->branch,
                            'items' => []
                        ];
                    }
                    $warehouseItems[$warehouseId]['items'][] = $item;
                }
            }
        }
        
        // Add warehouses with their items (warehouses appear before items)
        foreach ($warehouseItems as $warehouseId => $data) {
            $warehouse = $data['warehouse'];
            $branch = $data['branch'];
            
            // Add warehouse header
            $results[] = [
                'type' => 'warehouse',
                'id' => $warehouse->id,
                'name' => $warehouse->warehouse_name,
                'code' => $warehouse->warehouse_code,
                'branch_id' => $warehouse->branch_id,
                'branch_name' => $branch ? $branch->branch_name : '',
                'display' => $warehouse->warehouse_name . ($warehouse->warehouse_code ? ' (' . $warehouse->warehouse_code . ')' : '') . ($branch ? ' - ' . $branch->branch_name : '')
            ];
            
            // Add items under this warehouse
            foreach ($data['items'] as $item) {
                $results[] = [
                    'type' => 'item',
                    'id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->warehouse_name,
                    'item' => $item
                ];
            }
        }
        
        // If no warehouse grouping, just return items
        if (empty($warehouseItems) && !empty($items)) {
            foreach ($items as $item) {
                $results[] = [
                    'type' => 'item',
                    'id' => $item->id,
                    'item' => $item
                ];
            }
        }

        return response()->json($results);
    }
}
