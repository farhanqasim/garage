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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{
    public function all_sales()
    {
        return view('admin.sales.index');
    }
    
    public function create_sale(){
        $customers = Customer::select('id', 'names', 'phones', 'company')->get();
        $invoiceNumber = Sale::generateInvoiceNumber();
        return view('admin.sales.create', compact('customers', 'invoiceNumber'));
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
     * Store a new sale
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'sale_date' => 'required|date',
            'status' => 'required|in:completed,pending,cancelled',
            'order_tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:paid,partial,unpaid',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $totalTaxAmount = 0;

            foreach ($request->items as $item) {
                $itemSubtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $itemTaxAmount = ($itemSubtotal * ($item['tax_percent'] ?? 0)) / 100;
                $subtotal += $itemSubtotal;
                $totalTaxAmount += $itemTaxAmount;
            }

            $orderTaxAmount = ($subtotal * ($request->order_tax ?? 0)) / 100;
            $grandTotal = $subtotal + $totalTaxAmount + $orderTaxAmount - ($request->discount ?? 0) + ($request->shipping ?? 0);

            // Create sale
            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'sale_date' => $request->sale_date,
                'status' => $request->status,
                'subtotal' => $subtotal,
                'order_tax' => $request->order_tax ?? 0,
                'order_tax_amount' => $orderTaxAmount,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'grand_total' => $grandTotal,
                'payment_status' => $request->payment_status,
                'paid_amount' => $request->paid_amount ?? $grandTotal,
                'due_amount' => $grandTotal - ($request->paid_amount ?? $grandTotal),
                'notes' => $request->notes,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                
                // Calculate item totals
                $itemSubtotal = ($itemData['unit_price'] * $itemData['quantity']) - ($itemData['discount'] ?? 0);
                $itemTaxAmount = ($itemSubtotal * ($itemData['tax_percent'] ?? 0)) / 100;
                $unitCost = $itemSubtotal / $itemData['quantity'];
                $totalCost = $itemSubtotal + $itemTaxAmount;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_id' => $item->id,
                    'item_name' => $item->partnumber_item->name ?? $item->bar_code ?? 'Item #' . $item->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount' => $itemData['discount'] ?? 0,
                    'tax_percent' => $itemData['tax_percent'] ?? 0,
                    'tax_amount' => $itemTaxAmount,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                // Deduct stock
                $newStock = max(0, ($item->on_hand ?? 0) - $itemData['quantity']);
                $item->update(['on_hand' => $newStock]);
            }

            DB::commit();

            Log::info('Sale created successfully', ['sale_id' => $sale->id, 'invoice_number' => $sale->invoice_number]);

            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully!',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'redirect' => route('all_sales')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['items'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale: ' . $e->getMessage()
            ], 500);
        }
    }
}
