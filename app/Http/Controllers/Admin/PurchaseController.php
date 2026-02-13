<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseCart;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Category;
use App\Models\CarManufacturer;
use App\Models\PartNumber;
use App\Models\Technology;
use App\Models\Grade;
use App\Models\Volt;
use App\Models\Cca;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Models\PurchasePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    public function all_purchases()
    {
        $purchases = Purchase::with(['supplier', 'items.item'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();
        $branches = \App\Models\Branch::where('status', 'active')->get();
        $units = \App\Models\Unit::where('status', 'active')->orderBy('name')->get();
        return view('admin.purchases.create', compact('suppliers', 'branches', 'units'));
    }

    /**
     * Get users list with CLAIM RETURN access (claim_return_enabled).
     */
    public function getClaimReturnAccessList(Request $request)
    {
        $users = User::with('roles')->orderBy('name')->get(['id', 'name', 'email', 'claim_return_enabled']);
        $list = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?? '',
                'roles' => $user->roles->pluck('name')->toArray(),
                'has_access' => (bool) ($user->claim_return_enabled ?? false),
            ];
        });
        return response()->json($list);
    }

    /**
     * Toggle CLAIM RETURN access for a user. Only Super Admin, Admin, Manager can toggle.
     */
    public function toggleClaimReturnAccess(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Manager'])) {
            return response()->json(['success' => false], 403);
        }
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'enabled' => 'required|in:0,1,true,false',
        ]);
        $user = User::findOrFail($request->user_id);
        $user->claim_return_enabled = filter_var($request->enabled, FILTER_VALIDATE_BOOLEAN);
        $user->save();
        return response()->json(['success' => true, 'enabled' => (bool) $user->claim_return_enabled]);
    }

    /**
     * Get purchase cart from database (purchase_cart table). Cart survives refresh.
     */
    public function getPurchaseCart(Request $request)
    {
        $userId = auth()->id();
        $rows = PurchaseCart::with('item')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();

        $branchId = null;
        $supplierId = null;
        $items = [];
        foreach ($rows as $row) {
            if ($row->branch_id !== null) {
                $branchId = $row->branch_id;
            }
            if ($row->supplier_id !== null) {
                $supplierId = $row->supplier_id;
            }
            $itemName = $row->item ? ($row->item->short_disc ?? $row->item->pro_dis ?? $row->item->bar_code ?? 'Item #' . $row->item_id) : 'Item #' . $row->item_id;
            $items[] = [
                'item_id' => $row->item_id,
                'name' => $itemName,
                'quantity' => (float) $row->quantity,
                'unit' => $row->unit,
                'rate' => (float) $row->rate,
                'discount' => (float) $row->discount,
                'tax_percentage' => (float) $row->tax_percentage,
                'tax_amount' => (float) $row->tax_amount,
                'total' => (float) $row->total,
            ];
        }
        // Use first row's branch/supplier if not set
        if ($rows->isNotEmpty()) {
            $first = $rows->first();
            if ($branchId === null) {
                $branchId = $first->branch_id;
            }
            if ($supplierId === null) {
                $supplierId = $first->supplier_id;
            }
        }
        $cart = [
            'branch_id' => $branchId,
            'supplier_id' => $supplierId,
            'items' => $items,
        ];
        return response()->json($cart);
    }

    /**
     * Update purchase cart in database (purchase_cart table). Replaces all cart items for this user.
     */
    public function updatePurchaseCart(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'items' => 'nullable|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.name' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
        ]);

        $userId = auth()->id();
        $branchId = $request->input('branch_id');
        $supplierId = $request->input('supplier_id');
        $itemsInput = $request->input('items', []);

        PurchaseCart::where('user_id', $userId)->delete();

        foreach ($itemsInput as $it) {
            PurchaseCart::create([
                'user_id' => $userId,
                'branch_id' => $branchId,
                'supplier_id' => $supplierId,
                'item_id' => $it['item_id'],
                'quantity' => $it['quantity'],
                'unit' => $it['unit'] ?? null,
                'rate' => $it['rate'],
                'discount' => $it['discount'] ?? 0,
                'tax_percentage' => $it['tax_percentage'] ?? 0,
                'tax_amount' => $it['tax_amount'] ?? 0,
                'total' => $it['total'] ?? 0,
            ]);
        }

        $cart = [
            'branch_id' => $branchId,
            'supplier_id' => $supplierId,
            'items' => $itemsInput,
        ];
        return response()->json(['success' => true, 'cart' => $cart]);
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
        try {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'purchase_date' => 'required|date',
                'reference' => 'nullable|string|max:255',
                'is_purchase_order' => 'nullable|boolean',
                'status' => 'required|in:received,pending,ordered',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.unit' => 'nullable|string',
                'items.*.discount' => 'nullable|numeric|min:0',
                'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors($e->errors());
        }

        $warehouse = Warehouse::where('branch_id', $request->branch_id)->first();
        if (!$warehouse) {
            $errorMessage = 'Warehouse not found for selected branch.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }

        DB::beginTransaction();
        try {
            $isPurchaseOrder = $request->boolean('is_purchase_order', false);
            if ($isPurchaseOrder) {
                $poCount = Purchase::where('invoice_no', 'like', 'PO-%')->count();
                $invoiceNo = 'PO-' . str_pad($poCount, 5, '0', STR_PAD_LEFT);
            } else {
                $invoiceNo = 'PUR-' . date('Y') . '-' . str_pad((Purchase::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
            }

            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemModel = Item::find($item['item_id']);
                if (!$itemModel) {
                    throw new \Exception("Item not found: {$item['item_id']}");
                }

                $quantity = floatval($item['quantity']);
                $rate = floatval($item['rate']);
                $discount = floatval($item['discount'] ?? 0);
                $taxPercentage = floatval($item['tax_percentage'] ?? 0);
                
                $itemSubtotal = ($quantity * $rate) - $discount;
                $taxAmount = ($itemSubtotal * $taxPercentage) / 100;
                $itemTotal = $itemSubtotal + $taxAmount;
                
                $subtotal += $itemTotal;
            }

            $orderTax = floatval($request->order_tax ?? 0);
            $discount = floatval($request->discount ?? 0);
            $shipping = floatval($request->shipping ?? 0);
            $grandTotal = $subtotal + $orderTax - $discount + $shipping;

            try {
                $purchaseDate = Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
            } catch (\Exception $e) {
                $purchaseDate = Carbon::parse($request->purchase_date)->format('Y-m-d');
            }

            $purchase = Purchase::create([
                'invoice_no' => $invoiceNo,
                'is_purchase_order' => $isPurchaseOrder,
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

            foreach ($request->items as $item) {
                $itemModel = Item::findOrFail($item['item_id']);
                $quantity = floatval($item['quantity']);
                $rate = floatval($item['rate']);
                $discount = floatval($item['discount'] ?? 0);
                $taxPercentage = floatval($item['tax_percentage'] ?? 0);
                
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

                $warehouseItem = WarehouseItem::lockForUpdate()
                    ->where('warehouse_id', $warehouse->id)
                    ->where('item_id', $item['item_id'])
                    ->first();

                if ($warehouseItem) {
                    $warehouseItem->quantity += $quantity;
                    $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                    $warehouseItem->save();
                } else {
                    WarehouseItem::create([
                        'warehouse_id' => $warehouse->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                        'available_quantity' => $quantity,
                    ]);
                }

                $itemModel->on_hand = ($itemModel->on_hand ?? 0) + $quantity;
                $itemModel->save();
            }

            // Create payment if provided
            if ($request->filled('payment_method_id') && $request->payment_amount > 0) {
                $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
                $paymentAmount = floatval($request->payment_amount);
                
                // Validate payment amount doesn't exceed grand total
                if ($paymentAmount > $grandTotal) {
                    throw new \Exception("Payment amount (Rs " . number_format($paymentAmount, 2) . ") cannot exceed grand total (Rs " . number_format($grandTotal, 2) . ").");
                }
                
                // Validate bank account if required
                if ($paymentMethod->requires_bank_account && !$request->bank_account_id) {
                    throw new \Exception('Bank account is required for this payment method.');
                }
                
                // Validate bank account exists if provided
                if ($request->bank_account_id) {
                    $bankAccount = BankAccount::find($request->bank_account_id);
                    if (!$bankAccount || !$bankAccount->status) {
                        throw new \Exception('Selected bank account is not available.');
                    }
                }
                
                $payment = Payment::create([
                    'user_id' => auth()->id(),
                    'supplier_id' => $request->supplier_id,
                    'payment_method_id' => $request->payment_method_id,
                    'bank_account_id' => $request->bank_account_id ?? null,
                    'amount' => $paymentAmount,
                    'currency' => 'PKR',
                    'direction' => 'out', // Outgoing payment for purchase
                    'payment_date' => $request->payment_date ?? $purchaseDate,
                    'transaction_id' => $request->payment_transaction_id ?? null,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'notes' => $request->payment_notes ?? "Payment for Purchase #{$purchase->invoice_no}",
                ]);
                
                // Link payment to purchase
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'payment_id' => $payment->id,
                    'allocated_amount' => $paymentAmount,
                ]);
                
                // Create bank transaction if bank account is used
                if ($request->bank_account_id && $paymentMethod->requires_bank_account) {
                    \App\Models\BankTransaction::create([
                        'bank_account_id' => $request->bank_account_id,
                        'transaction_date' => $request->payment_date ?? $purchaseDate,
                        'description' => "Purchase Payment - Invoice #{$purchase->invoice_no}" . ($request->payment_notes ? " - {$request->payment_notes}" : ''),
                        'amount' => $paymentAmount,
                        'type' => 'debit', // Debit for outgoing payment
                        'statement_reference' => $request->payment_transaction_id ?? $purchase->invoice_no,
                        'matched_payment_id' => $payment->id,
                        'reconciled' => false,
                    ]);
                }
            }

            DB::commit();

            // Clear purchase_cart table for this user (items have moved to purchase_items)
            PurchaseCart::where('user_id', auth()->id())->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase created successfully',
                    'purchase_id' => $purchase->id,
                    'invoice_no' => $purchase->invoice_no
                ]);
            }
            
            return redirect()->route('all_purchases')->with('success', 'Purchase created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Failed to create purchase: ' . $e->getMessage();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'branch', 'items.item.partnumber_item', 'items.item.category', 'items.item.vehical_item'])->findOrFail($id);
        
        $data = [
            'purchase' => $purchase,
            'companyName' => setting_value('logo_text', 'MUBARAK TRADERS'),
            'helpline' => setting_value('helpline', '+92-335-08-999-08'),
        ];
        
        return view('admin.purchases.show', $data);
    }

    public function convertToSale($id)
    {
        $purchase = Purchase::with(['items.item', 'branch'])->findOrFail($id);
        
        // Store purchase data in session to pre-fill sales form
        session([
            'purchase_to_sale' => [
                'purchase_id' => $purchase->id,
                'branch_id' => $purchase->branch_id,
                'items' => $purchase->items->map(function($item) {
                    return [
                        'item_id' => $item->item_id,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'rate' => $item->rate,
                        'discount' => $item->discount ?? 0,
                        'tax_percentage' => $item->tax_percentage ?? 0,
                        'tax_amount' => $item->tax_amount ?? 0,
                        'total' => $item->total_cost,
                    ];
                })->toArray(),
                'subtotal' => $purchase->subtotal,
                'order_tax' => $purchase->order_tax,
                'discount' => $purchase->discount,
                'shipping' => $purchase->shipping,
                'grand_total' => $purchase->grand_total,
                'reference' => $purchase->reference,
            ]
        ]);
        
        return redirect()->route('create_sale')->with('success', 'Purchase items loaded. Please select a customer to create sale.');
    }

    public function pdf($id)
    {
        $purchase = Purchase::with(['items.item', 'supplier', 'branch'])->findOrFail($id);
    
        // Logo handling
        $logoUrl = setting_value('logo') ?: asset('assets/img/logo.svg');
        $logoData = null;
        if ($logoPath = setting_value('logo')) {
            $fullPath = str_replace(url('/'), public_path(), $logoPath);
            if (file_exists($fullPath)) {
                $logoData = 'data:image/' . pathinfo($fullPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }
    
        // Signature handling
        $signatureData = null;
        if ($signatureUrl = setting_value('signature')) {
            $signaturePath = str_replace(url('/'), public_path(), $signatureUrl);
            if (!file_exists($signaturePath)) {
                $signaturePath = public_path(str_replace(url('/') . '/', '', $signatureUrl));
            }
            if (file_exists($signaturePath)) {
                $ext = strtolower(pathinfo($signaturePath, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'png' : ($ext === 'jpg' || $ext === 'jpeg' ? 'jpeg' : 'svg+xml');
                $signatureData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($signaturePath));
            }
        }
    
        $data = [
            'purchase'     => $purchase,
            'logoData'     => $logoData,
            'logoUrl'      => $logoUrl,
            'companyName'  => setting_value('logo_text', 'MUBARAK TRADERS'),
            'helpline'     => setting_value('helpline', '+92-335-08-999-08'),
            'address'      => setting_value('address', ''),
            'city'         => setting_value('city', ''),
            'state'        => setting_value('state', ''),
            'zip'          => setting_value('zip', ''),
            'country'      => setting_value('country', ''),
            'signatureData'=> $signatureData,
        ];
    
        $pdf = Pdf::loadView('admin.purchases.pdf', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled'      => true,
                      'defaultFont'          => 'DejaVu Sans',
                  ]);
    
        return $pdf->download('Invoice-' . $purchase->invoice_no . '.pdf');
    }

    public function edit($id)
    {
        $purchase = Purchase::with(['supplier', 'items.item', 'branch'])->findOrFail($id);
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();
        $branches = \App\Models\Branch::where('status', 'active')->get();
        
        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);
        
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

        $warehouse = Warehouse::where('branch_id', $request->branch_id)->first();
        if (!$warehouse) {
            return redirect()->back()->withInput()->with('error', 'Warehouse not found for selected branch.');
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemModel = Item::find($item['item_id']);
                if (!$itemModel) {
                    throw new \Exception("Item not found: {$item['item_id']}");
                }

                $quantity = floatval($item['quantity']);
                $rate = floatval($item['rate']);
                $discount = floatval($item['discount'] ?? 0);
                $taxPercentage = floatval($item['tax_percentage'] ?? 0);
                
                $itemSubtotal = ($quantity * $rate) - $discount;
                $taxAmount = ($itemSubtotal * $taxPercentage) / 100;
                $itemTotal = $itemSubtotal + $taxAmount;
                
                $subtotal += $itemTotal;
            }

            $orderTax = floatval($request->order_tax ?? 0);
            $discount = floatval($request->discount ?? 0);
            $shipping = floatval($request->shipping ?? 0);
            $grandTotal = $subtotal + $orderTax - $discount + $shipping;

            try {
                $purchaseDate = Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
            } catch (\Exception $e) {
                $purchaseDate = Carbon::parse($request->purchase_date)->format('Y-m-d');
            }

            $oldBranchId = $purchase->branch_id;
            $oldWarehouse = Warehouse::where('branch_id', $oldBranchId)->first();

            if ($oldWarehouse) {
                foreach ($purchase->items as $oldItem) {
                    $warehouseItem = WarehouseItem::lockForUpdate()
                        ->where('warehouse_id', $oldWarehouse->id)
                        ->where('item_id', $oldItem->item_id)
                        ->first();

                    if ($warehouseItem) {
                        $warehouseItem->quantity = max(0, $warehouseItem->quantity - floatval($oldItem->quantity));
                        $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                        $warehouseItem->save();
                    }

                    $itemModel = Item::find($oldItem->item_id);
                    if ($itemModel) {
                        $itemModel->on_hand = max(0, ($itemModel->on_hand ?? 0) - floatval($oldItem->quantity));
                        $itemModel->save();
                    }
                }
            }

            $purchase->update([
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

            $purchase->items()->delete();

            foreach ($request->items as $item) {
                $itemModel = Item::findOrFail($item['item_id']);
                $quantity = floatval($item['quantity']);
                $rate = floatval($item['rate']);
                $discount = floatval($item['discount'] ?? 0);
                $taxPercentage = floatval($item['tax_percentage'] ?? 0);
                
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

                $warehouseItem = WarehouseItem::lockForUpdate()
                    ->where('warehouse_id', $warehouse->id)
                    ->where('item_id', $item['item_id'])
                    ->first();

                if ($warehouseItem) {
                    $warehouseItem->quantity += $quantity;
                    $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                    $warehouseItem->save();
                } else {
                    WarehouseItem::create([
                        'warehouse_id' => $warehouse->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $quantity,
                        'reserved_quantity' => 0,
                        'available_quantity' => $quantity,
                    ]);
                }

                $itemModel->on_hand = ($itemModel->on_hand ?? 0) + $quantity;
                $itemModel->save();
            }

            DB::commit();
            return redirect()->route('all_purchases')->with('success', 'Purchase updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update purchase: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            $warehouse = Warehouse::where('branch_id', $purchase->branch_id)->first();
            
            if ($warehouse) {
                foreach ($purchase->items as $purchaseItem) {
                    $warehouseItem = WarehouseItem::lockForUpdate()
                        ->where('warehouse_id', $warehouse->id)
                        ->where('item_id', $purchaseItem->item_id)
                        ->first();

                    if ($warehouseItem) {
                        $warehouseItem->quantity = max(0, $warehouseItem->quantity - floatval($purchaseItem->quantity));
                        $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                        $warehouseItem->save();
                    }

                    $itemModel = Item::find($purchaseItem->item_id);
                    if ($itemModel) {
                        $itemModel->on_hand = max(0, ($itemModel->on_hand ?? 0) - floatval($purchaseItem->quantity));
                        $itemModel->save();
                    }
                }
            }

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();
            return redirect()->route('all_purchases')->with('success', 'Purchase deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
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
        $item = Item::with(['partnumber_item', 'category', 'vehical_item.manutacturer_vehical', 'vehical_item.model_vehical', 'unit_item', 'warrenty_item'])->findOrFail($id);
        
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
        
        // Get unit name from relationship (unit column stores unit ID)
        $unitName = 'Unit'; // default
        if ($item->unit_item) {
            $unitName = $item->unit_item->name ?? $item->unit_item->short_name ?? 'Unit';
        }
        
        // Get warranty info
        $warrantyName = null;
        $warrantyValue = null;
        $warrantyUnit = null;
        if ($item->warrenty_item) {
            $warrantyName = $item->warrenty_item->name ?? null;
            if ($warrantyName) {
                // Parse warranty name (e.g., "1 Year" -> value: "1", unit: "Years")
                $warrantyNameLower = strtolower(trim($warrantyName));
                // Match patterns like "1 year", "2 years", "6 months", etc.
                if (preg_match('/^(\d+)\s*(year|years|month|months|week|weeks|day|days)$/i', $warrantyNameLower, $matches)) {
                    $warrantyValue = $matches[1];
                    $unit = strtolower($matches[2]);
                    // Normalize unit names
                    if (in_array($unit, ['year', 'years'])) {
                        $warrantyUnit = 'Years';
                    } elseif (in_array($unit, ['month', 'months'])) {
                        $warrantyUnit = 'Months';
                    } elseif (in_array($unit, ['week', 'weeks'])) {
                        $warrantyUnit = 'Weeks';
                    } elseif (in_array($unit, ['day', 'days'])) {
                        $warrantyUnit = 'Days';
                    }
                }
            }
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
            'unit' => $unitName,
            'unit_id' => $item->unit, // Also return unit ID for reference
            'image' => $item->image, // Include item image URL
            'stock' => $item->on_hand ?? 0,
            'warehouse_stock' => $item->on_hand ?? 0,
            'shop_stock' => 0,
            'bar_code' => $item->bar_code,
            'serial_number' => $item->serial_number,
            'packing' => $item->packing ?? 1, // Packing size for cartons calculation
            'warehouse_id' => $warehouseId,
            'warranty_name' => $warrantyName,
            'warranty_value' => $warrantyValue,
            'warranty_unit' => $warrantyUnit,
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
     * Get purchase history for an item (all past purchases of this item)
     */
    public function getItemPurchaseHistory($id)
    {
        $item = Item::findOrFail($id);
        
        // Get all purchase items for this item, ordered by newest first
        $purchaseHistory = PurchaseItem::with(['purchase.supplier'])
            ->where('item_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(20) // Limit to last 20 purchases
            ->get();
        
        $history = [];
        foreach ($purchaseHistory as $purchaseItem) {
            $purchase = $purchaseItem->purchase;
            $supplier = $purchase ? $purchase->supplier : null;
            
            // Get supplier name
            $supplierName = 'Unknown Supplier';
            if ($supplier) {
                $names = is_array($supplier->names) ? $supplier->names : json_decode($supplier->names, true) ?? [];
                $supplierName = $names[0] ?? $supplier->company ?? 'Unknown Supplier';
            }
            
            $history[] = [
                'id' => $purchaseItem->id,
                'purchase_id' => $purchaseItem->purchase_id,
                'invoice_no' => $purchase ? $purchase->invoice_no : 'N/A',
                'supplier_id' => $supplier ? $supplier->id : null,
                'supplier_name' => $supplierName,
                'quantity' => (float) $purchaseItem->quantity,
                'unit' => $purchaseItem->unit ?? 'Unit',
                'rate' => (float) $purchaseItem->rate,
                'discount' => (float) $purchaseItem->discount,
                'tax_percentage' => (float) $purchaseItem->tax_percentage,
                'total_cost' => (float) $purchaseItem->total_cost,
                'purchase_date' => $purchase ? $purchase->purchase_date->format('d/m/Y') : 'N/A',
                'days_ago' => $purchase ? $purchase->purchase_date->diffInDays(now()) : null,
                'created_at' => $purchaseItem->created_at->format('d/m/Y H:i'),
            ];
        }
        
        // Calculate statistics
        $totalPurchases = count($history);
        $lastPurchase = $history[0] ?? null;
        $avgRate = $totalPurchases > 0 ? collect($history)->avg('rate') : 0;
        $minRate = $totalPurchases > 0 ? collect($history)->min('rate') : 0;
        $maxRate = $totalPurchases > 0 ? collect($history)->max('rate') : 0;
        $totalQuantity = collect($history)->sum('quantity');
        
        return response()->json([
            'item_id' => $item->id,
            'item_name' => $item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'Item #' . $item->id,
            'total_purchases' => $totalPurchases,
            'total_quantity' => $totalQuantity,
            'avg_rate' => round($avgRate, 2),
            'min_rate' => round($minRate, 2),
            'max_rate' => round($maxRate, 2),
            'last_purchase' => $lastPurchase,
            'history' => $history,
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
        
        // 3. Search items - Show ALL items for purchase (no warehouse filter)
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
        ])->where('is_active', 1);

        // Multi-term search: space-separated words = AND filter (each term must match somewhere in item)
        $search = trim($request->input('q', ''));
        $terms = $search !== '' ? array_values(array_filter(preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY))) : [];
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

        // Filter by model
        if ($request->has('model_id') && $request->model_id) {
            $query->whereHas('vehical_item.model_vehical', function ($q) use ($request) {
                $q->where('id', $request->model_id);
            });
        }

        // Filter by country
        if ($request->has('country_id') && $request->country_id) {
            $query->whereHas('vehical_item.country_vehical', function ($q) use ($request) {
                $q->where('id', $request->country_id);
            });
        }

        // Filter by engine
        if ($request->has('engine_id') && $request->engine_id) {
            $query->whereHas('vehical_item.engine_vehical', function ($q) use ($request) {
                $q->where('id', $request->engine_id);
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by year
        if ($request->has('year') && $request->year) {
            $yearFilter = $request->year;
            // Check if it's a range (e.g., "2020-2025") or single year
            if (strpos($yearFilter, '-') !== false) {
                $years = explode('-', $yearFilter);
                $yearFrom = trim($years[0]);
                $yearTo = trim($years[1] ?? $years[0]);
                $query->whereHas('vehical_item', function ($q) use ($yearFrom, $yearTo) {
                    $q->where(function ($subQ) use ($yearFrom, $yearTo) {
                        $subQ->where(function ($yq) use ($yearFrom, $yearTo) {
                            // Year range overlaps with search range
                            $yq->where('year_from', '<=', $yearTo)
                               ->where('year_to', '>=', $yearFrom);
                        });
                    });
                });
            } else {
                // Single year search
                $year = trim($yearFilter);
                $query->whereHas('vehical_item', function ($q) use ($year) {
                    $q->where(function ($subQ) use ($year) {
                        $subQ->where('year_from', '<=', $year)
                             ->where('year_to', '>=', $year)
                             ->orWhere('year_from', 'LIKE', "%{$year}%")
                             ->orWhere('year_to', 'LIKE', "%{$year}%");
                    });
                });
            }
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

    /**
     * Get supplier balance (outstanding amount to pay)
     */
    public function getSupplierBalance(Request $request, $supplierId)
    {
        try {
            $supplier = Supplier::findOrFail($supplierId);
            
            // Get opening balance
            $openingBalance = $supplier->opening_balance ?? 0;
            $balanceType = $supplier->balance_type ?? 'pay'; // 'pay' means we owe supplier
            
            // Get all purchases from this supplier
            $purchases = Purchase::where('supplier_id', $supplier->id)->get();
            
            // Calculate total purchases
            $totalPurchases = $purchases->sum('grand_total');
            
            // Get all payments made to this supplier (sum of allocated amounts from purchase_payments)
            $totalPayments = PurchasePayment::whereHas('purchase', function($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId);
            })->sum('allocated_amount');
            
            // Calculate balance
            // If balance_type is 'pay', we owe: opening_balance + purchases - payments
            // If balance_type is 'receive', supplier owes us: opening_balance - purchases + payments
            if ($balanceType == 'pay') {
                $balance = $openingBalance + $totalPurchases - $totalPayments;
            } else {
                $balance = $openingBalance - $totalPurchases + $totalPayments;
            }
            
            return response()->json([
                'success' => true,
                'balance' => round($balance, 2),
                'opening_balance' => round($openingBalance, 2),
                'total_purchases' => round($totalPurchases, 2),
                'total_payments' => round($totalPayments, 2),
                'balance_type' => $balanceType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'balance' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
