<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Item;
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
}
