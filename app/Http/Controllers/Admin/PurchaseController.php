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
        return view('admin.purchases.create', compact('suppliers'));
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
        $item = Item::findOrFail($id);
        
        return response()->json([
            'id' => $item->id,
            'name' => $item->short_disc ?? $item->pro_dis ?? $item->bar_code,
            'rate' => $item->packing_purchase_rate ?? 0,
            'unit' => $item->product_unit ?? 'Unit',
            'stock' => $item->on_hand ?? 0,
            'warehouse_stock' => $item->on_hand ?? 0, // Can be modified later for warehouse/shop separation
            'shop_stock' => 0, // Can be modified later for warehouse/shop separation
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
     * Advanced search with multiple filters (YouTube-style)
     * Filtered by selected branch's warehouse items
     */
    public function ajaxSearch(Request $request)
    {
        // Get branch_id from request or session
        $branchId = $request->input('branch_id') ?? session('selected_branch_id');
        
        if (!$branchId) {
            return response()->json([
                'error' => 'Please select a branch first'
            ], 400);
        }

        // Get warehouse for this branch
        $warehouse = \App\Models\Warehouse::where('branch_id', $branchId)->first();
        
        if (!$warehouse) {
            return response()->json([
                'error' => 'No warehouse found for selected branch'
            ], 400);
        }

        // Get item IDs from warehouse_items for this warehouse
        $warehouseItemIds = \App\Models\WarehouseItem::where('warehouse_id', $warehouse->id)
            ->pluck('item_id')
            ->toArray();

        // If no items in warehouse, return empty
        if (empty($warehouseItemIds)) {
            return response()->json([]);
        }

        $query = Item::with([
            'partnumber_item',
            'vehical_item.manutacturer_vehical',
            'vehical_item.model_vehical',
            'category',
            'subcategory',
        ])->whereIn('id', $warehouseItemIds); // Filter by warehouse items only

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

        return response()->json($items);
    }
}
