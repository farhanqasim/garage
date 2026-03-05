<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cca;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Volt;
use App\Models\Brand;
use App\Models\Grade;
use App\Models\Scale;
use App\Models\Amphor;
use App\Models\BatterySize;
use App\Models\Platos;
use App\Models\CarName;
use App\Models\Company;
use App\Models\Formula;
use App\Models\Mileage;
use App\Models\Packing;
use App\Models\Product;
use App\Models\Quality;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\EngineCc;
use App\Models\LineItem;
use App\Models\Minuspool;
use App\Models\PoleThickness;
use App\Models\PoolDirection;
use App\Models\CarCompany;
use App\Models\CarCountry;
use App\Models\PartNumber;
use App\Models\Technology;
use App\Models\Producttype;
use App\Models\VehicalType;
use Illuminate\Http\Request;
use App\Models\CarManufacturer;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Level;
use App\Models\MadeIn;
use App\Models\Services;
use App\Models\Warrenty;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;


class ItemController extends Controller
{
    /** Map item type to add permission name */
    protected function getAddPermissionForType(?string $type): string
    {
        $map = ['parts' => 'add_parts', 'filters' => 'add_filters', 'breakpad' => 'add_break_pad', 'oil' => 'add_oil', 'battery' => 'add_battery', 'scrap' => 'add_scrap', 'services' => 'add_services'];
        return $map[$type ?? ''] ?? 'add_items';
    }

    /** Map item type to update permission name */
    protected function getUpdatePermissionForType(?string $type): string
    {
        $map = ['parts' => 'update_parts', 'filters' => 'update_filters', 'breakpad' => 'update_break_pad', 'oil' => 'update_oil', 'battery' => 'update_battery', 'scrap' => 'update_scrap', 'services' => 'update_services'];
        return $map[$type ?? ''] ?? 'update_items';
    }

    /** Map item type to view permission name */
    protected function getViewPermissionForType(?string $type): string
    {
        $map = ['parts' => 'view_parts', 'filters' => 'view_filters', 'breakpad' => 'view_break_pad', 'oil' => 'view_oil', 'battery' => 'view_battery', 'scrap' => 'view_scrap', 'services' => 'view_services'];
        return $map[$type ?? ''] ?? 'view_items';
    }

    /** Map item type to delete permission name */
    protected function getDeletePermissionForType(?string $type): string
    {
        $map = ['parts' => 'delete_parts', 'filters' => 'delete_filters', 'breakpad' => 'delete_break_pad', 'oil' => 'delete_oil', 'battery' => 'delete_battery', 'scrap' => 'delete_scrap', 'services' => 'delete_services'];
        return $map[$type ?? ''] ?? 'delete_items';
    }



    public function all_items(Request $request)
    {
        $viewPerms = ['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'];
        if (!collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view items.');
        }
        $items = Item::with([
            'item_user', 
            'item_user.branch',
            'product_item', 
            'partnumber_item', 
            'updated_by_user', 
            'category',
            'company_item',
            'quality_item',
            'unit_item',
            'vehical_item',
            'volt_item',
            'plate_item',
            'amphors_item',
            'cca_item'
        ])->latest()->get();
        
        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'items' => $items->map(function($item) {
                    // Build item name for sales
                    $itemName = $item->short_disc ?? $item->pro_dis ?? '';
                    if (empty($itemName) && $item->product_item) {
                        $itemName = $item->product_item->name ?? '';
                    }
                    if (empty($itemName) && $item->partnumber_item) {
                        $itemName = $item->partnumber_item->name ?? '';
                    }
                    if (empty($itemName)) {
                        $itemName = $item->bar_code ?? 'N/A';
                    }
                    
                    // Add part number to name if available
                    if ($item->partnumber_item && $item->partnumber_item->name) {
                        $partNum = $item->partnumber_item->name;
                        if ($itemName && !str_contains($itemName, $partNum)) {
                            $itemName .= ' - ' . $partNum;
                        }
                    }
                    
                    return [
                        'id' => $item->id,
                        'name' => $itemName,
                        'image' => $item->image ? ((str_starts_with($item->image, 'http://') || str_starts_with($item->image, 'https://')) ? $item->image : '/' . ltrim($item->image, '/')) : '/assets/img/media/default.png',
                        'bar_code' => $item->bar_code ?? '',
                        'barcode_image' => $item->barcode_image,
                        'type' => $item->type ?? '',
                        'is_active' => $item->is_active ?? true,
                        'user_name' => $item->item_user->name ?? '',
                        'product_name' => $item->product_item->name ?? '',
                        'part_number' => $item->partnumber_item->name ?? '',
                        'category_name' => $item->category ? $item->category->name : 'N/A',
                        'company_name' => $item->company_item->name ?? '',
                        'quality_name' => $item->quality_item->name ?? null,
                        'volt_name' => $item->volt_item ? (str_ends_with((string)$item->volt_item->name, 'V') ? $item->volt_item->name : $item->volt_item->name . 'V') : null,
                        'plate_name' => $item->plate_item ? (str_ends_with((string)$item->plate_item->name, 'PL') ? $item->plate_item->name : $item->plate_item->name . 'PL') : null,
                        'amphors_name' => $item->amphors_item ? (str_ends_with((string)$item->amphors_item->name, 'AH') ? $item->amphors_item->name : $item->amphors_item->name . 'AH') : null,
                        'cca_name' => $item->cca_item ? (str_contains((string)$item->cca_item->name, 'CCA') ? $item->cca_item->name : $item->cca_item->name . 'CCA') : null,
                        'branch_name' => $item->item_user && $item->item_user->branch ? $item->item_user->branch->branch_name : '',
                        'sale_price' => floatval($item->sale_price ?? 0),
                        'on_hand' => floatval($item->on_hand ?? 0),
                        'stock' => floatval($item->on_hand ?? 0),
                        'price' => floatval($item->sale_price ?? $item->price_per_unit ?? 0),
                        'unit' => $item->unit_item->name ?? ($item->unit ?? 'Unit'),
                        'updated_by_user' => $item->updated_by_user ? [
                            'name' => $item->updated_by_user->name,
                        ] : null,
                        'last_updated_at' => $item->last_updated_at ? $item->last_updated_at->format('d M Y, h:i A') : null,
                        'updated_at' => $item->updated_at ? $item->updated_at->format('d M Y, h:i A') : null,
                        'show_url' => route('item.show', $item->id),
                        'edit_url' => route('item.edit', $item->id),
                        'delete_url' => route('item.delete', $item->id),
                        'duplicate_url' => route('item.duplicate', $item->id),
                        'has_vehicle' => $item->vehical_item ? true : false,
                    ];
                })
            ]);
        }
        
        // Regular page load - categories for bulk edit modal
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        return view('admin.item.index', compact('items', 'categories'));
    }

    /**
     * Item Price List - all items with category-wise filter.
     */
    public function priceList(Request $request)
    {
        $viewPerms = ['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'];
        if (!collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view items.');
        }

        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $query = Item::with(['category', 'unit_item', 'plate_item', 'amphors_item', 'volt_item', 'cca_item', 'company_item', 'product_item', 'partnumber_item', 'updated_by_user.branch', 'priceUpdatedBranch'])
            ->orderBy('category_id')
            ->orderBy('short_disc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $items = $query->get();

        $currentBranchName = session('selected_branch_name');
        if (!$currentBranchName && auth()->user() && auth()->user()->branch_id) {
            $currentBranchName = \App\Models\Branch::where('id', auth()->user()->branch_id)->value('branch_name');
        }

        return view('admin.item.price-list', compact('items', 'categories', 'currentBranchName'));
    }

    /**
     * Item Stock Report - detailed stock in/out with filters.
     */
    public function stockReport(Request $request)
    {
        $viewPerms = ['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'];
        if (!collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view items.');
        }

        $branches = \App\Models\Branch::orderBy('branch_name')->get();
        $users = \App\Models\User::orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();

        $from = $request->filled('from')
            ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->from)->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? \Carbon\Carbon::createFromFormat('Y-m-d', $request->to)->endOfDay()
            : now()->endOfDay();

        $branchId = $request->branch_id ?: null;
        $userId = $request->user_id ?: null;
        $typeFilter = $request->type;
        $categoryId = $request->category_id;

        // Aggregate stock-in from purchases
        $purchaseAgg = \App\Models\PurchaseItem::query()
            ->selectRaw('purchase_items.item_id, purchases.branch_id, SUM(purchase_items.quantity) as stock_in')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->whereBetween('purchases.purchase_date', [$from->toDateString(), $to->toDateString()])
            ->when($branchId, fn ($q) => $q->where('purchases.branch_id', $branchId))
            ->groupBy('purchase_items.item_id', 'purchases.branch_id')
            ->get();

        // Aggregate stock-out from sales
        $saleAgg = \App\Models\SaleItem::query()
            ->selectRaw('sale_items.item_id, sales.branch_id, SUM(sale_items.quantity) as stock_out')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$from->toDateString(), $to->toDateString()])
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->when($userId, fn ($q) => $q->where('sales.user_id', $userId))
            ->groupBy('sale_items.item_id', 'sales.branch_id')
            ->get();

        // Merge aggregates keyed by item + branch
        $rows = [];
        foreach ($purchaseAgg as $row) {
            $key = $row->item_id . ':' . ($row->branch_id ?? 0);
            $rows[$key] = [
                'item_id' => $row->item_id,
                'branch_id' => $row->branch_id,
                'stock_in' => (float) $row->stock_in,
                'stock_out' => 0.0,
            ];
        }
        foreach ($saleAgg as $row) {
            $key = $row->item_id . ':' . ($row->branch_id ?? 0);
            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'item_id' => $row->item_id,
                    'branch_id' => $row->branch_id,
                    'stock_in' => 0.0,
                    'stock_out' => 0.0,
                ];
            }
            $rows[$key]['stock_out'] += (float) $row->stock_out;
        }

        $itemIds = collect($rows)->pluck('item_id')->unique()->values();
        $itemsQuery = Item::with(['category', 'company_item', 'partnumber_item'])
            ->whereIn('id', $itemIds);

        if ($typeFilter && $typeFilter !== 'all') {
            $itemsQuery->where('type', $typeFilter);
        }
        if ($categoryId) {
            $itemsQuery->where('category_id', $categoryId);
        }

        $items = $itemsQuery->get()->keyBy('id');
        $branchNames = $branches->keyBy('id')->map->branch_name;

        // Pre-compute oil configuration (liter-per-can) for items
        $oilConfigByItemId = [];
        foreach ($items as $it) {
            if (($it->type ?? null) !== 'oil') {
                continue;
            }
            $literPerCan = null;
            $unitName = $it->unit_item ? trim($it->unit_item->name ?? $it->unit_item->short_name ?? '') : '';
            $unitOption = $it->unit_option ? trim((string) $it->unit_option) : '';

            // 0) From unit_option (e.g. "12_8_4" => 4 Liter)
            if ($unitOption !== '' && strpos($unitOption, '_') !== false) {
                $parts = explode('_', $unitOption);
                $lastPart = end($parts);
                if (is_numeric($lastPart) && (float) $lastPart > 0) {
                    $literPerCan = (float) $lastPart;
                }
            }

            // 1) From unit name, like "Can - 4 Liter" or "Can 4L"
            if ($literPerCan === null && preg_match('/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)\b/i', $unitName, $m)) {
                $literPerCan = (float) $m[1];
            } elseif ($literPerCan === null && preg_match('/\b(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i', $unitName, $m)) {
                $literPerCan = (float) $m[1];
            }

            // 2) Fallback to filling (per-can liters)
            if ($literPerCan === null && $it->filling !== null && $it->filling !== '' && !is_nan((float) $it->filling)) {
                $literPerCan = (float) $it->filling;
            }

            $oilConfigByItemId[$it->id] = [
                'liter_per_can' => $literPerCan && $literPerCan > 0 ? $literPerCan : null,
            ];
        }

        // Latest purchase (supplier & datetime) per item+branch within filters
        $purchaseDetailsByKey = collect();
        $supplierNamesById = collect();
        $purchaseDetailsRaw = collect();
        if ($itemIds->isNotEmpty()) {
            $purchaseDetailsRaw = \App\Models\PurchaseItem::query()
                ->selectRaw('purchase_items.item_id, purchase_items.quantity as qty, purchases.branch_id, purchases.supplier_id, purchases.purchase_date, purchases.created_at')
                ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->whereBetween('purchases.purchase_date', [$from->toDateString(), $to->toDateString()])
                ->when($branchId, fn ($q) => $q->where('purchases.branch_id', $branchId))
                ->whereIn('purchase_items.item_id', $itemIds)
                ->orderBy('purchases.purchase_date', 'desc')
                ->orderBy('purchases.created_at', 'desc')
                ->get();

            $purchaseDetailsByKey = $purchaseDetailsRaw
                ->groupBy(function ($row) {
                    return $row->item_id . ':' . ($row->branch_id ?? 0);
                })
                ->map(function ($group) {
                    return $group->first();
                });

            $supplierIds = $purchaseDetailsRaw->pluck('supplier_id')->filter()->unique();
            if ($supplierIds->isNotEmpty()) {
                $suppliers = \App\Models\Supplier::whereIn('id', $supplierIds)->get()->keyBy('id');
                $supplierNamesById = $suppliers->map(function ($supplier) {
                    $primaryName = $supplier->names[0] ?? null;
                    if ($supplier->company) {
                        return $primaryName
                            ? $primaryName . ' (' . $supplier->company . ')'
                            : $supplier->company;
                    }
                    return $primaryName ?: 'Supplier #' . $supplier->id;
                });
            }
        }

        // Latest sale (customer & datetime) per item+branch within filters
        $saleDetailsByKey = collect();
        $customerNamesById = collect();
        $saleDetailsRaw = collect();
        if ($itemIds->isNotEmpty()) {
            $saleDetailsRaw = \App\Models\SaleItem::query()
                ->selectRaw('sale_items.item_id, sale_items.quantity as qty, sales.branch_id, sales.customer_id, sales.sale_date, sales.created_at')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.sale_date', [$from->toDateString(), $to->toDateString()])
                ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
                ->when($userId, fn ($q) => $q->where('sales.user_id', $userId))
                ->whereIn('sale_items.item_id', $itemIds)
                ->orderBy('sales.sale_date', 'desc')
                ->orderBy('sales.created_at', 'desc')
                ->get();

            $saleDetailsByKey = $saleDetailsRaw
                ->groupBy(function ($row) {
                    return $row->item_id . ':' . ($row->branch_id ?? 0);
                })
                ->map(function ($group) {
                    return $group->first();
                });

            $customerIds = $saleDetailsRaw->pluck('customer_id')->filter()->unique();
            if ($customerIds->isNotEmpty()) {
                $customers = \App\Models\Customer::whereIn('id', $customerIds)->get()->keyBy('id');
                $customerNamesById = $customers->map(function ($customer) {
                    $primaryName = $customer->names[0] ?? null;
                    if ($customer->company) {
                        return $primaryName
                            ? $primaryName . ' (' . $customer->company . ')'
                            : $customer->company;
                    }
                    return $primaryName ?: 'Customer #' . $customer->id;
                });
            }
        }

        $reportRows = collect($rows)->map(function ($row) use ($items, $branchNames, $purchaseDetailsByKey, $supplierNamesById, $saleDetailsByKey, $customerNamesById) {
            $item = $items->get($row['item_id']);
            if (!$item) {
                return null;
            }

            $key = $row['item_id'] . ':' . ($row['branch_id'] ?? 0);

            $rawName = $item->short_disc ?? $item->pro_dis ?? '';
            $productName = trim(strip_tags((string) $rawName));
            if ($productName === '' && $item->partnumber_item) {
                $productName = $item->partnumber_item->name ?? $item->bar_code ?? 'Item #' . $item->id;
            }

            $purchaseMeta = $purchaseDetailsByKey->get($key);
            $purchaseFrom = $purchaseMeta && $purchaseMeta->supplier_id
                ? ($supplierNamesById[$purchaseMeta->supplier_id] ?? null)
                : null;
            $purchaseAt = $purchaseMeta && $purchaseMeta->created_at
                ? \Carbon\Carbon::parse($purchaseMeta->created_at)
                : null;

            $saleMeta = $saleDetailsByKey->get($key);
            $saleTo = $saleMeta && $saleMeta->customer_id
                ? ($customerNamesById[$saleMeta->customer_id] ?? null)
                : null;
            $saleAt = $saleMeta && $saleMeta->created_at
                ? \Carbon\Carbon::parse($saleMeta->created_at)
                : null;

            return [
                'item' => $item,
                'item_type' => $item->type ?: 'Item',
                'product_name' => $productName,
                'part_number' => optional($item->partnumber_item)->name ?: $item->bar_code,
                'category' => optional($item->category)->name,
                'company' => optional($item->company_item)->name,
                'branch' => $row['branch_id'] ? ($branchNames[$row['branch_id']] ?? 'Unknown') : 'All',
                'stock_in' => $row['stock_in'],
                'stock_out' => $row['stock_out'],
                'net_movement' => $row['stock_in'] - $row['stock_out'],
                'last_purchase_from' => $purchaseFrom,
                'last_purchase_at' => $purchaseAt,
                'last_sale_to' => $saleTo,
                'last_sale_at' => $saleAt,
            ];
        })->filter()->values();

        // Transaction-wise movements with running balance (within selected period)
        $transactions = collect();
        if ($itemIds->isNotEmpty()) {
            $transactionsByKey = [];

            foreach ($purchaseDetailsRaw as $row) {
                $key = $row->item_id . ':' . ($row->branch_id ?? 0);
                $occurredAt = $row->created_at ?: $row->purchase_date;
                $transactionsByKey[$key][] = [
                    'item_id' => $row->item_id,
                    'branch_id' => $row->branch_id,
                    'direction' => 'in',
                    'qty' => (float) $row->qty,
                    'party_type' => 'supplier',
                    'party_id' => $row->supplier_id,
                    'occurred_at' => \Carbon\Carbon::parse($occurredAt),
                ];
            }

            foreach ($saleDetailsRaw as $row) {
                $key = $row->item_id . ':' . ($row->branch_id ?? 0);
                $occurredAt = $row->created_at ?: $row->sale_date;
                $transactionsByKey[$key][] = [
                    'item_id' => $row->item_id,
                    'branch_id' => $row->branch_id,
                    'direction' => 'out',
                    'qty' => (float) $row->qty,
                    'party_type' => 'customer',
                    'party_id' => $row->customer_id,
                    'occurred_at' => \Carbon\Carbon::parse($occurredAt),
                ];
            }

            $txRows = [];
            foreach ($transactionsByKey as $key => $list) {
                usort($list, function ($a, $b) {
                    return $a['occurred_at']->timestamp <=> $b['occurred_at']->timestamp;
                });

                $parts = explode(':', $key);
                $itemId = (int) $parts[0];
                $branchIdForKey = (int) ($parts[1] ?? 0);
                $item = $items->get($itemId);
                if (!$item) {
                    continue;
                }

                $rawName = $item->short_disc ?? $item->pro_dis ?? '';
                $productName = trim(strip_tags((string) $rawName));
                if ($productName === '' && $item->partnumber_item) {
                    $productName = $item->partnumber_item->name ?? $item->bar_code ?? 'Item #' . $item->id;
                }

                $branchName = $branchIdForKey ? ($branchNames[$branchIdForKey] ?? 'Unknown') : 'All';

                $isOil = ($item->type ?? null) === 'oil';
                $literPerCanForItem = $oilConfigByItemId[$item->id]['liter_per_can'] ?? null;

                $balance = 0.0;
                foreach ($list as $entry) {
                    if ($entry['direction'] === 'in') {
                        $balance += $entry['qty'];
                    } else {
                        $balance -= $entry['qty'];
                    }

                    $partyName = null;
                    if ($entry['party_type'] === 'supplier' && $entry['party_id']) {
                        $partyName = $supplierNamesById[$entry['party_id']] ?? ('Supplier #' . $entry['party_id']);
                    } elseif ($entry['party_type'] === 'customer' && $entry['party_id']) {
                        $partyName = $customerNamesById[$entry['party_id']] ?? ('Customer #' . $entry['party_id']);
                    }

                    // Oil running balance breakdown (can / liter / ml)
                    $balCan = null;
                    $balLiter = null;
                    $balMl = null;
                    if ($isOil && $literPerCanForItem && $literPerCanForItem > 0) {
                        $totalLiters = $balance;
                        $fullCans = (int) floor($totalLiters / $literPerCanForItem);
                        $remainder = $totalLiters - ($fullCans * $literPerCanForItem);
                        $wholeLiters = (int) floor($remainder);
                        $ml = (int) round(($remainder - $wholeLiters) * 1000);

                        $balCan = $fullCans;
                        $balLiter = $wholeLiters;
                        $balMl = $ml;
                    }

                    $txRows[] = [
                        'item' => $item,
                        'item_type' => $item->type ?: 'Item',
                        'product_name' => $productName,
                        'part_number' => optional($item->partnumber_item)->name ?: $item->bar_code,
                        'category' => optional($item->category)->name,
                        'company' => optional($item->company_item)->name,
                        'branch' => $branchName,
                        'type' => $entry['direction'] === 'in' ? 'Purchase' : 'Sale',
                        'party' => $partyName,
                        'occurred_at' => $entry['occurred_at'],
                        'qty_in' => $entry['direction'] === 'in' ? $entry['qty'] : 0.0,
                        'qty_out' => $entry['direction'] === 'out' ? $entry['qty'] : 0.0,
                        'balance_after' => $balance,
                        'balance_can' => $balCan,
                        'balance_liter' => $balLiter,
                        'balance_ml' => $balMl,
                    ];
                }
            }

            $transactions = collect($txRows)->sortBy('occurred_at')->values();
        }

        // Warehouse-wise current stock (from warehouse_items)
        $warehouses = \App\Models\Warehouse::with('branch')->orderBy('warehouse_name')->get();
        $warehouseIdFilter = $request->warehouse_id ?: null;

        $wiQuery = \App\Models\WarehouseItem::query()
            ->select('warehouse_items.*')
            ->join('warehouses', 'warehouse_items.warehouse_id', '=', 'warehouses.id')
            ->join('items', 'warehouse_items.item_id', '=', 'items.id')
            ->where('warehouse_items.quantity', '>', 0)
            ->when($branchId, fn ($q) => $q->where('warehouses.branch_id', $branchId))
            ->when($warehouseIdFilter, fn ($q) => $q->where('warehouse_items.warehouse_id', $warehouseIdFilter))
            ->when($typeFilter && $typeFilter !== 'all', fn ($q) => $q->where('items.type', $typeFilter))
            ->when($categoryId, fn ($q) => $q->where('items.category_id', $categoryId));

        $maxWarehouseItems = (int) (config('app.max_warehouse_items_report', 10000) ?: 10000);
        $warehouseItems = $wiQuery->with(['item.category', 'item.company_item', 'item.partnumber_item'])->limit($maxWarehouseItems)->get();

        $warehouseRows = [];
        foreach ($warehouseItems as $wi) {
            $item = $wi->item;
            if (!$item) {
                continue;
            }
            $rawName = $item->short_disc ?? $item->pro_dis ?? '';
            $productName = trim(strip_tags((string) $rawName));
            if ($productName === '' && $item->partnumber_item) {
                $productName = $item->partnumber_item->name ?? $item->bar_code ?? 'Item #' . $item->id;
            }
            $warehouse = $warehouses->firstWhere('id', $wi->warehouse_id);

            $isOil = ($item->type ?? null) === 'oil';
            $literPerCanForItem = $oilConfigByItemId[$item->id]['liter_per_can'] ?? null;
            $qtyCan = null;
            $qtyLiter = null;
            $qtyMl = null;
            if ($isOil && $literPerCanForItem && $literPerCanForItem > 0) {
                $totalLiters = (float) $wi->quantity;
                $fullCans = (int) floor($totalLiters / $literPerCanForItem);
                $remainder = $totalLiters - ($fullCans * $literPerCanForItem);
                $wholeLiters = (int) floor($remainder);
                $ml = (int) round(($remainder - $wholeLiters) * 1000);

                $qtyCan = $fullCans;
                $qtyLiter = $wholeLiters;
                $qtyMl = $ml;
            }

            $warehouseRows[] = [
                'item_type' => $item->type ?: 'Item',
                'product_name' => $productName,
                'part_number' => optional($item->partnumber_item)->name ?: $item->bar_code,
                'category' => optional($item->category)->name,
                'company' => optional($item->company_item)->name,
                'branch' => $warehouse && $warehouse->branch ? $warehouse->branch->branch_name : '—',
                'warehouse' => $warehouse ? $warehouse->warehouse_name : 'Warehouse #' . $wi->warehouse_id,
                'warehouse_code' => $warehouse ? ($warehouse->warehouse_code ?? '') : '',
                'quantity' => (float) $wi->quantity,
                'qty_can' => $qtyCan,
                'qty_liter' => $qtyLiter,
                'qty_ml' => $qtyMl,
            ];
        }

        return view('admin.item.stock-report', [
            'rows' => $reportRows,
            'transactions' => $transactions,
            'warehouseRows' => $warehouseRows,
            'branches' => $branches,
            'warehouses' => $warehouses,
            'users' => $users,
            'categories' => $categories,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseIdFilter,
                'user_id' => $userId,
                'type' => $typeFilter ?: 'all',
                'category_id' => $categoryId,
            ],
        ]);
    }

    /**
     * Scrap Report: list scrap items with Total Weight and Total Scrap Value.
     */
    public function scrapReport(Request $request)
    {
        $viewPerms = ['view_items', 'view_scrap'];
        if (!collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view scrap report.');
        }

        $query = Item::with(['product_item', 'category'])
            ->where('type', 'scrap');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        $totalWeight = $items->sum(fn ($item) => (float) ($item->weight_for_delivery ?? 0));
        $totalScrapValue = $items->sum(fn ($item) => (float) ($item->total_price ?? 0));

        $categories = \App\Models\Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.item.scrap-report', [
            'items' => $items,
            'totalWeight' => $totalWeight,
            'totalScrapValue' => $totalScrapValue,
            'categories' => $categories,
            'filters' => [
                'category_id' => $request->category_id,
            ],
        ]);
    }

    /**
     * Bulk update item prices (Cost, Sale Price, Retail) from Price List page.
     */
    public function bulkPriceUpdate(Request $request)
    {
        $updatePerms = ['update_items', 'update_parts', 'update_filters', 'update_break_pad', 'update_oil', 'update_battery', 'update_scrap', 'update_services'];
        if (!collect($updatePerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to update item prices.');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.total_price' => 'nullable|numeric|min:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
            'items.*.retail_price' => 'nullable|numeric|min:0',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $updated = 0;
        $changed = false;
        foreach ($request->items as $row) {
            $item = Item::find($row['id']);
            if (!$item) continue;
            $changed = false;
            if (isset($row['total_price']) && $row['total_price'] !== '') { $item->total_price = $row['total_price']; $changed = true; }
            if (isset($row['sale_price']) && $row['sale_price'] !== '') { $item->sale_price = $row['sale_price']; $changed = true; }
            if (array_key_exists('retail_price', $row)) {
                $item->retail_price = $row['retail_price'] !== '' && $row['retail_price'] !== null ? $row['retail_price'] : null;
                $changed = true;
            }
            if (array_key_exists('tax_percentage', $row)) {
                $item->tax_percentage = $row['tax_percentage'] !== '' && $row['tax_percentage'] !== null ? $row['tax_percentage'] : 0;
                $changed = true;
            }
            if ($changed) {
                $item->updated_by = auth()->id();
                $item->last_updated_at = now();
                $item->price_updated_branch_id = session('selected_branch_id') ?: (auth()->user()->branch_id ?? null);
                $updated++;
            }
            $item->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            $updatedItems = [];
            $responseBranchFallback = session('selected_branch_name') ?: (auth()->user() && auth()->user()->branch_id ? \App\Models\Branch::where('id', auth()->user()->branch_id)->value('branch_name') : null);
            foreach ($request->items as $row) {
                $item = Item::with(['updated_by_user.branch', 'priceUpdatedBranch'])->find($row['id']);
                if ($item) {
                    $branchName = $item->priceUpdatedBranch ? $item->priceUpdatedBranch->branch_name : ($item->updated_by_user && $item->updated_by_user->branch ? $item->updated_by_user->branch->branch_name : $responseBranchFallback);
                    $updatedItems[] = [
                        'id' => $item->id,
                        'last_updated_at' => $item->last_updated_at ? $item->last_updated_at->format('d/m/Y H:i') : '-',
                        'branch_name' => $branchName ?: '-',
                        'user_name' => $item->updated_by_user ? $item->updated_by_user->name : '-',
                    ];
                }
            }
            return response()->json(['success' => true, 'message' => "{$updated} item(s) updated.", 'updated' => $updated, 'updated_items' => $updatedItems]);
        }
        return redirect()->route('items.price.list', $request->only(['type', 'category_id']))->with('success', "{$updated} item(s) updated.");
    }

    public function items_create($hideVehicleTable = false)
    {
        $addPerms = ['add_items', 'add_parts', 'add_filters', 'add_break_pad', 'add_oil', 'add_battery', 'add_scrap', 'add_services'];
        if (!auth()->check()) {
            return redirect('/')->with('error', 'Please login to continue.');
        }
        if (!collect($addPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to create items.');
        }
        $platos      = Platos::where('status', 'active')->get();
        $amphors     = Amphor::where('status', 'active')->get();
        $lineitems   = LineItem::where('status', 'active')->get();
        $Companies   = Company::where('status', 'active')->get();
        // Parent categories
        $Categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('children') // Eager load subcategories
            ->get();

        $packings    = Packing::where('status', 'active')->get();
        $scales      = Scale::where('status', 'active')->get();
        // Optimize: Limit vehicle query - only load if needed, use chunking for large datasets
        $Vehicals    = VehicalType::select('id', 'v_part_number_id', 'car_manufacturer', 'car_model_name', 'engine_cc', 'car_manufactured_country', 'year_from', 'year_to')
            ->where('status', 'active')
            ->limit(1000) // Limit to prevent timeout
            ->get();

        $milleages   = Mileage::where('status', 'active')->get();
        $item_types  = Producttype::where('status', 'active')->get();
        // return $Vehicals;
        // Optimize: Don't load all items - empty collection to prevent timeout
        // Items can be loaded via AJAX if needed for autocomplete/search
        $items = collect([]);
        $units = Unit::with('baseUnits')->orderBy('name')->get();

        // return $units;
        $carCompanies     = CarCompany::orderBy('name')->get();
        $carNames         = CarName::orderBy('name')->get();
        $carModels        = CarModel::orderBy('name')->get();
        $carCountries     = CarCountry::orderBy('name')->get();
        $carManufacturers = CarManufacturer::orderBy('name')->get();
        // return $carModels;
        $volts      = Volt::where('status', 'active')->get();
        $ccas      = Cca::where('status', 'active')->get();
        $minspols      = Minuspool::where('status', 'active')->get();
        $poleThicknesses = PoleThickness::where('status', 'active')->get();
        $poolDirections = PoolDirection::where('status', 'active')->get();
        $technologies      = Technology::where('status', 'active')->get();
        $grades      = Grade::where('status', 'active')->get();
        $brands      = Brand::where('status', 'active')->get();
        $formulas      = Formula::where('status', 'active')->get();
        $product      = Product::where('status', 'active')->get();
        $qualities      = Quality::where('status', 'active')->get();
        // Optimize: Limit part numbers query to avoid timeout - only select needed columns
        $partnumbers      = PartNumber::select('id', 'name', 'type')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        // return $partnumbers;
        $engineccs      = EngineCc::where('status', 'active')->get();
        // Optimize: Limit to prevent timeout - load only 5 latest items with relationships
        // Don't use select() to avoid column name issues - just limit the query
        $latestItems = Item::with([
            'item_user',
            'item_user.branch',
            'item_user.assignedBranches',
            'product_item:id,name',
            'category:id,name',
            'partnumber_item:id,name',
            'company_item:id,name',
            'quality_item:id,name',
            'volt_item:id,name',
            'plate_item:id,name',
            'amphors_item:id,name',
            'cca_item:id,name'
        ])
            ->latest()
            ->take(5)
            ->get();
        // Get all vehicles and group by configuration (part, manufacturer, model, engine, country)
        // Multiple records exist per vehicle configuration with different year ranges
        // Optimize: Remove eager loading to prevent timeout - select only needed columns and limit results
        $Vehis = VehicalType::where('status', 'active')
            ->with(['vehical_part_number'])
            ->select('id', 'v_part_number_id', 'car_manufacturer', 'car_model_name', 'engine_cc', 'car_manufactured_country', 'year_from', 'year_to')
            ->orderBy('id', 'desc') // Order by latest first
            ->limit(2000) // Limit to prevent timeout
            ->get()
            ->groupBy(function($vehicle) {
                // Group by configuration fields
                return implode('|', [
                    $vehicle->v_part_number_id,
                    $vehicle->car_manufacturer,
                    $vehicle->car_model_name,
                    $vehicle->engine_cc,
                    $vehicle->car_manufactured_country
                ]);
            })
            ->map(function($vehicles) {
                // Get the first vehicle as representative (all have same config except years)
                $first = $vehicles->first();
                
                // Collect all year ranges for this configuration
                $yearRanges = $vehicles
                    ->map(function($v) {
                        $from = (int)$v->year_from;
                        $to = (int)$v->year_to;
                        if ($from && $to) {
                            return [
                                'from' => $from,
                                'to' => $to,
                                'display' => $from == $to ? (string)$from : $from . '-' . $to
                            ];
                        }
                        return null;
                    })
                    ->filter()
                    ->sortBy('from') // Sort by 'from' year in ascending order
                    ->values()
                    ->map(function($range) {
                        return $range['display'];
                    });
                
                // Add year_ranges to the first vehicle object
                $first->year_ranges = $yearRanges;
                $first->years = $yearRanges->implode(', ');
                return $first;
            })
            ->values() // Reset keys
            ->take(5); // Limit to 5 latest vehicles

        $services      = Services::where('status', 'active')->get();
        $warrenties      = Warrenty::where('status', 'active')->get();
        $groups      = Group::where('status', 'active')->get();

        $made_ins      = MadeIn::where('status', 'active')->get();
        $levels      = Level::where('status', 'active')->get();
        $batterySizes = BatterySize::where('status', 'active')->orderBy('name')->get();

        // Permission-based allowed item types (jo permission active ho)
        $typePermMap = ['parts' => 'add_parts', 'filters' => 'add_filters', 'breakpad' => 'add_break_pad', 'oil' => 'add_oil', 'battery' => 'add_battery', 'scrap' => 'add_scrap', 'services' => 'add_services'];
        $allowedItemTypes = collect($typePermMap)->filter(fn ($perm) => auth()->user()->can($perm) || auth()->user()->can('add_items'))->keys()->values()->all();

        return view('admin.item.create', compact(
            'hideVehicleTable',
            'platos',
            'amphors',
            'lineitems',
            'Companies',
            'Categories',
            'packings',
            'scales',
            'Vehicals',
            'milleages',
            'item_types',
            'items',
            'carCompanies',
            'carNames',
            'carModels',
            'carCountries',
            'carManufacturers',
            'volts',
            'ccas',
            'minspols',
            'poleThicknesses',
            'poolDirections',
            'technologies',
            'grades',
            'brands',
            'formulas',
            'product',
            'qualities',
            'partnumbers',
            'engineccs',
            'latestItems',
            'Vehis',
            'units',
            'services',
            'warrenties',
            'groups',
            'made_ins',
            'levels',
            'batterySizes',
            'allowedItemTypes'
        ));
    }

    /**
     * Clone of items_create - same create form at /all/items/create/new
     */
    public function items_create_new()
    {
        return $this->items_create(true);
    }

    public function getSubcategories($id)
    {
        $subcategories = Category::where('parent_id', $id)
            ->where('status', 'active')
            ->select('id', 'name')
            ->get();

        return response()->json($subcategories);
    }


    public function items_store(Request $request)
    {
        $type = $request->input('type');
        $perm = $this->getAddPermissionForType($type);
        $this->authorize($perm);

        // return $request->all(); 
        // Validate fields first (before transaction)
        $validated = $request->validate([
            'bar_code' => 'required|unique:items,bar_code',
            'user_id' => 'nullable|exists:users,id',
            'p_id' => 'nullable|string|max:255',
            'vehical_id' => 'nullable',
            'vehical_id.*' => 'nullable|integer|exists:vehical_types,id',
            'total_price' => 'nullable',
            'price_per_unit' => 'nullable',
            'on_hand' => 'nullable',
            'sale_price' => 'nullable',
            'total_sale_price' => 'nullable',
            'sale_price_per_base' => 'nullable',
            'retail_price' => 'nullable|numeric|min:0',
            'mileage' => 'nullable|numeric|min:0',
            'type' => 'nullable|string',
            'plat_id' => 'nullable|string',
            'amphors' => 'nullable|string',
            'lineitems' => 'nullable|string',
            'company_id' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'p_brochure' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'volt' => 'nullable|string',
            'cca' => 'nullable|string',
            'minus_pole_direction' => 'nullable|string',
            'minus_pool_direction' => 'nullable|string', // Keep both for backward compatibility
            'pole_thickness_id' => 'nullable|exists:pole_thicknesses,id',
            'pool_direction_id' => 'nullable|exists:pool_directions,id',
            'technology' => 'nullable|string',
            'grade' => 'nullable|string',
            'services' => 'nullable|string',
            'formulas' => 'nullable|string',
            'farmula' => 'nullable|string', // Keep both for backward compatibility
            'serial_number' => 'nullable|string',
            'battery_size' => 'nullable|string',
            'battery_size_id' => 'nullable|exists:battery_sizes,id',
            'business_location' => 'nullable|string',
            'bussiness_location' => 'nullable|string', // Keep both for backward compatibility
            'quality_id' => 'nullable|string',
            'l_stock' => 'nullable|string',
            'm_stock' => 'nullable|string',
            'unit' => 'nullable|string',
            'packing' => 'nullable|string',
            'scale' => 'nullable|string',
            'filling' => 'nullable|numeric|min:0',
            'weight_for_delivery' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:20',
            'scrap_weight_kg' => 'nullable|numeric|min:0',
            'scrap_rate_per_kg' => 'nullable|numeric|min:0',
            'scrap_total_price' => 'nullable|numeric|min:0',
            'scrap_quantity' => 'nullable|numeric|min:0',
            'scrap_rate_count' => 'nullable|numeric|min:0',
            'scrap_total_count_hidden' => 'nullable|numeric|min:0',
            'scrap_measurement' => 'nullable|string|in:weight,count',
            'packing_purchase_rate' => 'nullable|numeric|min:0',
            'update_date' => 'nullable|date',
            'rack' => 'nullable|string',
            'supplier' => 'nullable|string',
            'warrenty' => 'nullable|string',
            'group' => 'nullable|string',
            'gorup' => 'nullable|string', // Keep both for backward compatibility
            'made_in' => 'nullable|string',
            'pro_dis' => 'nullable|string',
            'short_disc' => 'nullable|string',
            'part_number_id' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'auto_deactive' => 'sometimes|boolean',
            'is_dead' => 'sometimes|boolean',
            'level' => 'nullable',
        ]);

        // Require at least one of Part Number or Product Name when type is parts, filters, breakpad, or battery
        $type = $request->input('type');
        if (in_array($type, ['parts', 'filters', 'breakpad', 'battery'])) {
            $partNumberId = $request->input('part_number_id');
            $pId = $request->input('p_id');
            $hasPart = !empty($partNumberId) && trim((string) $partNumberId) !== '';
            $hasProduct = !empty($pId) && trim((string) $pId) !== '';
            if (!$hasPart && !$hasProduct) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'part_number_id' => 'Please select at least one: Part Number or Product Name.',
                        'p_id' => 'Please select at least one: Part Number or Product Name.',
                    ]);
            }
        }

        if (in_array($type, ['parts', 'filters', 'breakpad'])) {
            // Only check if required fields are present
            if ($request->has('category_id') && $request->has('quality_id') && 
                $request->has('company_id') && $request->has('part_number_id')) {
                
                $query = Item::where('category_id', $request->category_id)
                    ->where('quality_id', $request->quality_id)
                    ->where('company_id', $request->company_id)
                    ->where('part_number_id', $request->part_number_id)
                    ->where('type', $type);
                
                $exists = $query->exists();
                if ($exists) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors([
                            'duplicate' => 'This combination of Category, Quality, Part Number and Company already exists for this type. Please change one value.'
                        ]);
                }
            }
        }

        // Scrap: require Weight (KG) or Quantity depending on scrap_measurement
        $type = $request->input('type');
        if ($type === 'scrap') {
            $scrapMeas = strtolower((string) ($request->input('scrap_measurement') ?? 'weight'));
            if ($scrapMeas === 'count') {
                $qty = $request->input('scrap_quantity');
                if ($qty === null || $qty === '' || (is_numeric($qty) && (float) $qty < 0)) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['scrap_quantity' => 'Quantity is required for count-based scrap items.']);
                }
            } else {
                $scrapWeight = $request->input('scrap_weight_kg');
                if (empty($scrapWeight) || (is_numeric($scrapWeight) && (float) $scrapWeight <= 0)) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['scrap_weight_kg' => 'Weight (KG) is required for weight-based scrap items.']);
                }
            }
        }

        try {
            DB::beginTransaction();

            $data = $validated;
            
            // Ensure quality_id and technology are in $data if present in request
            // This handles cases where fields might not be in validated array
            if ($request->has('quality_id') && !isset($data['quality_id'])) {
                $data['quality_id'] = $request->input('quality_id');
            }
            if ($request->has('technology') && !isset($data['technology'])) {
                $data['technology'] = $request->input('technology');
            }
            // Ensure short_disc and pro_dis (descriptions) are always passed from request
            $data['short_disc'] = $request->input('short_disc', $data['short_disc'] ?? null);
            $data['pro_dis'] = $request->input('pro_dis', $data['pro_dis'] ?? null);

            /* ============================
            ✅ Unit / unit_option (e.g. Can - 1 Liter vs Can - 4 Liter)
            ============================ */
            if ($request->filled('unit')) {
                $rawUnit = is_array($request->input('unit')) ? (string) ($request->input('unit')[0] ?? '') : (string) $request->input('unit');
                $rawUnit = trim($rawUnit);
                if ($rawUnit !== '') {
                    if (strpos($rawUnit, '_') !== false) {
                        $parts = explode('_', $rawUnit, 2);
                        $data['unit'] = $parts[0];
                        $data['unit_option'] = $rawUnit;
                    } else {
                        $data['unit'] = $rawUnit;
                        $data['unit_option'] = null;
                    }
                    if (isset($data['unit']) && $data['unit'] !== '' && !is_numeric($data['unit'])) {
                        $data['unit_option'] = null;
                    }
                }
            }

            /* ============================
            ✅ Barcode Generation
            ============================ */
            if ($request->bar_code) {
                $barcode = new DNS1D();
                $barcode->setStorPath(public_path('items/barcodes/'));
                $barcodeImage = $barcode->getBarcodePNG($request->bar_code, 'C128', 2, 70);

                $barcodePath = 'items/barcodes/' . uniqid() . '.png';

                if (!file_exists(public_path('items/barcodes'))) {
                    mkdir(public_path('items/barcodes'), 0777, true);
                }

                file_put_contents(public_path($barcodePath), base64_decode($barcodeImage));
                $data['barcode_image'] = $barcodePath;
            }

            /* ============================
            ✅ Image Uploads
            ============================ */
            if ($request->hasFile('image')) {
                $data['image'] = saveSingleFile($request->file('image'), 'items');
            }

            if ($request->hasFile('images')) {
                $data['images'] = saveMultipleFiles($request->file('images'), 'items');
            }

            /* ============================
            ✅ Boolean Defaults
            ============================ */
            $data['is_active'] = $data['is_active'] ?? true;
            $data['auto_deactive'] = $data['auto_deactive'] ?? false;
            $data['is_dead'] = $data['is_dead'] ?? false;
            
            /* ============================
            ✅ Serial Number - Only use if provided
            ============================ */
            // Don't auto-generate serial number - only use if explicitly provided

            /* ============================
            ✅ Field Name Mapping (Form → Database)
            ============================ */
            // Map form field names to database column names
            if (isset($data['minus_pole_direction'])) {
                $data['minus_pool_direction'] = $data['minus_pole_direction'];
                unset($data['minus_pole_direction']);
            }
            // Technology field is already in correct format, no mapping needed
            if (isset($data['group'])) {
                $data['gorup'] = $data['group'];
                unset($data['group']);
            }
            if (isset($data['business_location'])) {
                $data['bussiness_location'] = $data['business_location'];
                unset($data['business_location']);
            }
            if (isset($data['formulas'])) {
                $data['farmula'] = $data['formulas'];
                unset($data['formulas']);
            }
            if (!empty($data['battery_size_id'])) {
                $data['battery_size'] = BatterySize::find($data['battery_size_id'])->name;
                unset($data['battery_size_id']);
            }

            // Scrap items: map scrap fields to weight_for_delivery, price_per_unit, total_price (or on_hand for count-based)
            if (($request->input('type') ?? '') === 'scrap') {
                $scrapMeas = strtolower((string) ($request->input('scrap_measurement') ?? 'weight'));
                if ($scrapMeas === 'count') {
                    if ($request->filled('scrap_quantity')) {
                        $data['on_hand'] = $request->input('scrap_quantity');
                    }
                    if ($request->filled('scrap_rate_count')) {
                        $data['price_per_unit'] = $request->input('scrap_rate_count');
                    }
                    if ($request->filled('scrap_total_count_hidden')) {
                        $data['total_price'] = $request->input('scrap_total_count_hidden');
                    } elseif ($request->filled('scrap_quantity') && $request->filled('scrap_rate_count')) {
                        $data['total_price'] = (float) $request->input('scrap_quantity') * (float) $request->input('scrap_rate_count');
                    }
                } else {
                    if ($request->filled('scrap_weight_kg')) {
                        $data['weight_for_delivery'] = $request->input('scrap_weight_kg');
                    }
                    if ($request->filled('scrap_rate_per_kg')) {
                        $data['price_per_unit'] = $request->input('scrap_rate_per_kg');
                    }
                    if ($request->filled('scrap_total_price')) {
                        $data['total_price'] = $request->input('scrap_total_price');
                    } elseif ($request->filled('scrap_weight_kg') && $request->filled('scrap_rate_per_kg')) {
                        $data['total_price'] = (float) $request->input('scrap_weight_kg') * (float) $request->input('scrap_rate_per_kg');
                    }
                }
            }

            /* ============================
            ✅ Handle Vehicle IDs Array - One Item, Multiple Vehicles (in items.vehical_ids)
            ============================ */
            $vehicleIds = [];
            if (isset($data['vehical_id']) && is_array($data['vehical_id'])) {
                $vehicleIds = array_values(array_filter(array_map('intval', $data['vehical_id'])));
            } elseif (isset($data['vehical_id']) && !empty($data['vehical_id']) && is_numeric($data['vehical_id'])) {
                $vehicleIds = [(int) $data['vehical_id']];
            }
            
            unset($data['vehical_id']);
            $data['vehical_ids'] = $vehicleIds;
            $data['vehical_id'] = $vehicleIds[0] ?? null;
            
            /* Create ONE item */
            $item = Item::create($data);
            
            Log::info('Item created successfully', ['item_id' => $item->id, 'vehicle_ids' => $vehicleIds]);

            DB::commit();

            $vehicleCount = count($vehicleIds);
            $successMessage = $vehicleCount > 0 
                ? 'Item created successfully with ' . $vehicleCount . ' vehicle(s)!' 
                : 'Item created successfully!';
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                if ($request->action === 'save_new') {
                    return response()->json([
                        'success' => true,
                        'message' => $successMessage,
                        'items_count' => 1,
                        'redirect' => route('all.items.create.new')
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'items_count' => 1
                ]);
            }
            
            // Regular redirect for non-AJAX requests
            if ($request->action === 'save_new') {
                Log::info('Item created (Save & New)', ['item_id' => $item->id]);
                return redirect()->route('all.items.create.new')
                    ->with('success', 'Item created successfully!');
            }

            Log::info('Item created (Save)', ['item_id' => $item->id]);

            return redirect()->back()
                ->withInput()
                ->with('success', 'Item created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            // Return JSON response for validation errors in AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Item creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['image', 'images'])
            ]);
            
            // Return JSON response for errors in AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create item: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function item_edit($id)
    {
        $item = Item::with([
            'vehical_item',
            'category',
            'subcategory',
            'item_user',
            'product_item',
            'mileage_item',
            'plate_item',
            'unit_item',
            'amphors_item',
            'lineitems_item',
            'company_item',
            'volt_item',
            'cca_item',
            'minus_pool_item',
            'technology_item',
            'grade_item',
            'farmula_item',
            'quality_item',
            'services_item',
            'warrenty_item',
            'level_item',
            'group_item',
            'made_in_item',
           
            'unit_item'
        ])->findOrFail($id);
        $this->authorize($this->getUpdatePermissionForType($item->type));
        // return $item;
        // All the collections you already had
        $platos     = Platos::where('status', 'active')->get();
        $amphors    = Amphor::where('status', 'active')->get();
        $lineitems  = LineItem::where('status', 'active')->get();
        $Companies  = Company::where('status', 'active')->get();
        $Categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('children')
            ->get();
        $packings   = Packing::where('status', 'active')->get();
        $scales     = Scale::where('status', 'active')->get();
        $Vehicals    = VehicalType::with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number'])->where('status', 'active')->get();

        $milleages  = Mileage::where('status', 'active')->get();
        $item_types = Producttype::where('status', 'active')->get();
        $units      = Unit::where('status', 'active')->get();
        // Optional – car-related dropdowns (only if they exist)
        $carCompanies     = CarCompany::where('status', 'active')->get();
        $carNames         = CarName::where('status', 'active')->get();
        $carModels        = CarModel::where('status', 'active')->get();
        $carCountries     = CarCountry::where('status', 'active')->get();
        $carManufacturers = CarManufacturer::where('status', 'active')->get();
        // return $carManufacturers;
        $volts      = Volt::where('status', 'active')->get();
        $ccas      = Cca::where('status', 'active')->get();
        $minspols      = Minuspool::where('status', 'active')->get();
        $technologies      = Technology::where('status', 'active')->get();
        $grades      = Grade::where('status', 'active')->get();
        $brands      = Brand::where('status', 'active')->get();
        $formulas      = Formula::where('status', 'active')->get();
        $product      = Product::where('status', 'active')->get();
        $qualities      = Quality::where('status', 'active')->get();
        $partnumbers      = PartNumber::with('part_number_vehical')->where('status', 'active')->get();
        $engineccs      = EngineCc::where('status', 'active')->get();
        $latestItems = Item::with([
            'item_user',
            'item_user.branch',
            'item_user.assignedBranches',
            'product_item',
            'category',
            'partnumber_item',
            'company_item',
            'quality_item',
            'volt_item',
            'plate_item',
            'amphors_item',
            'cca_item'
        ])->latest()->take(5)->get();
        $services      = Services::where('status', 'active')->get();
        $groups      = Group::where('status', 'active')->get();
        $warrenties      = Warrenty::where('status', 'active')->get();
        $made_ins      = MadeIn::where('status', 'active')->get();
        $levels      = Level::where('status', 'active')->get();
        $batterySizes = BatterySize::where('status', 'active')->orderBy('name')->get();
        // Get latest 5 vehicles - each record already has all year ranges in years JSON column
        $Vehis = VehicalType::with([
            'manutacturer_vehical',
            'model_vehical',
            'engine_vehical',
            'country_vehical',
            'vehical_part_number'
        ])
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->take(5) // Limit to 5 latest vehicles
            ->get()
            ->map(function($vehicle) {
                // Format year ranges from JSON column and sort them by 'from' year
                $yearRanges = collect($vehicle->years ?? [])
                    ->map(function($range) {
                        if (isset($range['from']) && isset($range['to'])) {
                            return [
                                'from' => (int)$range['from'],
                                'to' => (int)$range['to'],
                                'display' => $range['from'] == $range['to'] 
                                    ? (string)$range['from'] 
                                    : $range['from'] . '-' . $range['to']
                            ];
                        }
                        return null;
                    })
                    ->filter()
                    ->sortBy('from') // Sort by 'from' year in ascending order
                    ->values()
                    ->map(function($range) {
                        return $range['display'];
                    });
                
                $vehicle->year_ranges = $yearRanges;
                $vehicle->years = $yearRanges->implode(', ');
                return $vehicle;
            });
        // Full unit option value for dropdown (e.g. "12_8" for CAN 2 Liter) so exact option is selected
        $itemUnitOptionForSelect = null;
        if (!empty($item->unit_option) && is_string($item->unit_option)) {
            $itemUnitOptionForSelect = trim($item->unit_option);
        }
        // Fallback: resolve unit id for edit form (when no unit_option saved)
        $itemUnitIdForSelect = null;
        if (!$itemUnitOptionForSelect && isset($item->unit) && $item->unit !== null && $item->unit !== '') {
            $raw = trim((string) $item->unit);
            if (is_numeric($raw)) {
                $itemUnitIdForSelect = (int) $raw;
            } elseif ($item->relationLoaded('unit_item') && $item->unit_item && isset($item->unit_item->id)) {
                $itemUnitIdForSelect = (int) $item->unit_item->id;
            } else {
                $unitByName = Unit::where('name', $raw)->orWhere('short_name', $raw)->first();
                if ($unitByName) {
                    $itemUnitIdForSelect = (int) $unitByName->id;
                }
            }
        } elseif (!$itemUnitOptionForSelect && $item->relationLoaded('unit_item') && $item->unit_item && isset($item->unit_item->id)) {
            $itemUnitIdForSelect = (int) $item->unit_item->id;
        }

        // Resolve level (CLASS) for edit form: support both level id and level name (e.g. "G")
        $levelIdForForm = null;
        if (isset($item->level) && $item->level !== '' && $item->level !== null) {
            if (is_numeric($item->level)) {
                $levelIdForForm = (string) $item->level;
            } elseif ($item->relationLoaded('level_item') && $item->level_item && isset($item->level_item->id)) {
                $levelIdForForm = (string) $item->level_item->id;
            } else {
                $levelByName = Level::where('name', trim((string) $item->level))->first();
                if ($levelByName) {
                    $levelIdForForm = (string) $levelByName->id;
                }
            }
        }

        return view('admin.item.edit', compact(
            'item',
            'itemUnitIdForSelect',
            'itemUnitOptionForSelect',
            'levelIdForForm',
            'platos',
            'amphors',
            'batterySizes',
            'lineitems',
            'Companies',
            'Categories',
            'packings',
            'scales',
            'Vehicals',
            'milleages',
            'item_types',
            'units',
            'carCompanies',
            'carNames',
            'carModels',
            'carCountries',
            'carManufacturers',
            'volts',
            'ccas',
            'minspols',
            'technologies',
            'grades',
            'brands',
            'qualities',
            'partnumbers',
            'engineccs',
            'services',
            'formulas',
            'latestItems',
            'Vehis',
            'groups',
            'made_ins',
            'levels',
            'warrenties',
            'product'
        ));
    }


    public function item_update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $type = $request->input('type', $item->type);
        $this->authorize($this->getUpdatePermissionForType($type));
        // return $request->all();
        // Validate ONLY fields that exist in $fillable
        $validated = $request->validate([
            'bar_code' => 'required|unique:items,bar_code,' . $item->id,
            'user_id' => 'nullable|exists:users,id',
            'p_id' => 'nullable|string|max:255',
            'vehical_id' => 'nullable',
            'vehical_id.*' => 'nullable|integer|exists:vehical_types,id',
            'total_price' => 'nullable',
            'price_per_unit' => 'nullable',
            'on_hand' => 'nullable',
            'sale_price' => 'nullable',
            'total_sale_price' => 'nullable',
            'sale_price_per_base' => 'nullable',
            'retail_price' => 'nullable|numeric|min:0',
            'mileage' => 'nullable|numeric|min:0',
            'type' => 'nullable|string',
            'plat_id' => 'nullable|string',
            'amphors' => 'nullable|string',
            'lineitems' => 'nullable|string',
            'company_id' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'p_brochure' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'volt' => 'nullable|string',
            'cca' => 'nullable|string',
            'minus_pole_direction' => 'nullable|string',
            'minus_pool_direction' => 'nullable|string', // Keep both for backward compatibility
            'technology' => 'nullable|string',
            'grade' => 'nullable|string',
            'farmula' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'battery_size' => 'nullable|string',
            'business_location' => 'nullable|string',
            'bussiness_location' => 'nullable|string', // Keep both for backward compatibility
            'quality_id' => 'nullable|string',
            'l_stock' => 'nullable|string',
            'm_stock' => 'nullable|string',
            'unit' => 'nullable|string',
            'packing' => 'nullable|string',
            'scale' => 'nullable|string',
            'filling' => 'nullable|numeric|min:0',
            'weight_for_delivery' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|max:20',
            'scrap_weight_kg' => 'nullable|numeric|min:0',
            'scrap_rate_per_kg' => 'nullable|numeric|min:0',
            'scrap_total_price' => 'nullable|numeric|min:0',
            'scrap_quantity' => 'nullable|numeric|min:0',
            'scrap_rate_count' => 'nullable|numeric|min:0',
            'scrap_total_count_hidden' => 'nullable|numeric|min:0',
            'scrap_measurement' => 'nullable|string|in:weight,count',
            'packing_purchase_rate' => 'nullable|numeric|min:0',
            'update_date' => 'nullable|date',
            'rack' => 'nullable|string',
            'supplier' => 'nullable|string',
            'pro_dis' => 'nullable|string',
            'part_number_id' => 'nullable|string',
            'short_disc' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'auto_deactive' => 'sometimes|boolean',
            'services' => 'nullable|string',
            'warrenty' => 'nullable|string',
            'group' => 'nullable|string',
            'gorup' => 'nullable|string', // Keep both for backward compatibility
            'made_in' => 'nullable|string',
            'is_dead' => 'sometimes|boolean',
            'unit_option' => 'nullable|string|max:64',
            'level' => 'nullable',
        ]);

        // Duplicate combination check - Only for parts, filters, and breakpad types
        $type = $request->input('type', $item->type);
        if (in_array($type, ['parts', 'filters', 'breakpad'])) {
            // Only check if required fields are present
            $categoryId = $request->has('category_id') ? $request->category_id : $item->category_id;
            $qualityId = $request->has('quality_id') ? $request->quality_id : $item->quality_id;
            $companyId = $request->has('company_id') ? $request->company_id : $item->company_id;
            $partNumberId = $request->has('part_number_id') ? $request->part_number_id : $item->part_number_id;
            
            if ($categoryId && $qualityId && $companyId && $partNumberId) {
                $query = Item::where('category_id', $categoryId)
                    ->where('quality_id', $qualityId)
                    ->where('company_id', $companyId)
                    ->where('part_number_id', $partNumberId)
                    ->where('type', $type)
                    ->where('id', '!=', $id); // Exclude current item
                
                $exists = $query->exists();
                if ($exists) {
                    $msg = 'This combination of Category, Quality, Part Number and Company already exists for this type. Please change one value.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['duplicate' => $msg]);
                }
            }
        }

        try {
            DB::beginTransaction();

            $data = $validated;
            
            // Ensure short_disc and pro_dis are always passed from request
            $data['short_disc'] = $request->input('short_disc');
            $data['pro_dis'] = $request->input('pro_dis');
            // Ensure level (CLASS) is saved when present
            if ($request->has('level')) {
                $data['level'] = $request->input('level') ?: null;
            }
            
            // Save unit: full option value (e.g. "12_8" for CAN 2 Liter) in unit_option; numeric id in unit for relation
            if ($request->filled('unit') || $request->filled('unit_option')) {
                $rawUnit = $request->filled('unit')
                    ? (is_array($request->input('unit')) ? (string) ($request->input('unit')[0] ?? '') : (string) $request->input('unit'))
                    : (string) $request->input('unit_option', '');
                $rawUnit = trim($rawUnit);
                if ($rawUnit !== '') {
                    if (strpos($rawUnit, '_') !== false) {
                        $parts = explode('_', $rawUnit, 2);
                        $data['unit'] = $parts[0];
                        $data['unit_option'] = $rawUnit;
                    } else {
                        $data['unit'] = $rawUnit;
                        $data['unit_option'] = $request->filled('unit_option') ? trim((string) $request->input('unit_option')) : null;
                    }
                    if ($data['unit'] !== '' && !is_numeric($data['unit'])) {
                        $data['unit_option'] = null;
                    }
                }
            }
            // Debug: Log unit value before update
            Log::info('Item Update - Unit Value Check', [
                'item_id' => $id,
                'unit_from_request' => $request->input('unit'),
                'unit_from_validated' => $validated['unit'] ?? 'NOT IN VALIDATED',
                'unit_in_data' => $data['unit'] ?? 'NOT IN DATA',
                'request_has_unit' => $request->has('unit')
            ]);

            // === Handle Thumbnail (Single Image) ===
            if ($request->hasFile('image')) {
                // Optional: Delete old image
                if ($item->image && file_exists(public_path($item->image))) {
                    @unlink(public_path($item->image));
                }
                $data['image'] = saveSingleFile($request->file('image'), 'items');
            }

            // === Handle Gallery Images (Multiple) ===
            if ($request->hasFile('images')) {
                $newPaths = saveMultipleFiles($request->file('images'), 'items');

                // Merge with existing images (from DB, already array of relative paths)
                $existing = is_array($item->images) ? $item->images : [];
                $data['images'] = array_merge($existing, $newPaths);
            }

            // === Preserve existing booleans if not sent ===
            $data['is_active'] = $request->has('is_active') ? (bool) $data['is_active'] : $item->is_active;
            $data['auto_deactive'] = $request->has('auto_deactive') ? (bool) $data['auto_deactive'] : $item->auto_deactive;
            $data['is_dead'] = $request->has('is_dead') ? (bool) $data['is_dead'] : $item->is_dead;

            /* ============================
            ✅ Field Name Mapping (Form → Database)
            ============================ */
            // Map form field names to database column names
            if (isset($data['minus_pole_direction'])) {
                $data['minus_pool_direction'] = $data['minus_pole_direction'];
                unset($data['minus_pole_direction']);
            }
            // Technology field is already in correct format, no mapping needed
            if (isset($data['group'])) {
                $data['gorup'] = $data['group'];
                unset($data['group']);
            }
            if (isset($data['business_location'])) {
                $data['bussiness_location'] = $data['business_location'];
                unset($data['business_location']);
            }
            if (isset($data['formulas'])) {
                $data['farmula'] = $data['formulas'];
                unset($data['formulas']);
            }
            if (!empty($data['battery_size_id'])) {
                $data['battery_size'] = BatterySize::find($data['battery_size_id'])->name;
                unset($data['battery_size_id']);
            }

            // Scrap items: map scrap fields to weight_for_delivery, price_per_unit, total_price (or on_hand for count-based)
            if (($request->input('type') ?? '') === 'scrap') {
                $scrapMeas = strtolower((string) ($request->input('scrap_measurement') ?? 'weight'));
                if ($scrapMeas === 'count') {
                    if ($request->filled('scrap_quantity')) {
                        $data['on_hand'] = $request->input('scrap_quantity');
                    }
                    if ($request->filled('scrap_rate_count')) {
                        $data['price_per_unit'] = $request->input('scrap_rate_count');
                    }
                    if ($request->filled('scrap_total_count_hidden')) {
                        $data['total_price'] = $request->input('scrap_total_count_hidden');
                    } elseif ($request->filled('scrap_quantity') && $request->filled('scrap_rate_count')) {
                        $data['total_price'] = (float) $request->input('scrap_quantity') * (float) $request->input('scrap_rate_count');
                    }
                } else {
                    if ($request->filled('scrap_weight_kg')) {
                        $data['weight_for_delivery'] = $request->input('scrap_weight_kg');
                    }
                    if ($request->filled('scrap_rate_per_kg')) {
                        $data['price_per_unit'] = $request->input('scrap_rate_per_kg');
                    }
                    if ($request->filled('scrap_total_price')) {
                        $data['total_price'] = $request->input('scrap_total_price');
                    } elseif ($request->filled('scrap_weight_kg') && $request->filled('scrap_rate_per_kg')) {
                        $data['total_price'] = (float) $request->input('scrap_weight_kg') * (float) $request->input('scrap_rate_per_kg');
                    }
                }
            }

            /* ============================
            ✅ Handle Multiple Vehicles (save to items.vehical_ids)
            ============================ */
            $vehicleIds = [];
            if ($request->has('vehical_id')) {
                $raw = $request->input('vehical_id');
                if (is_array($raw)) {
                    $vehicleIds = array_values(array_filter(array_map('intval', $raw)));
                } elseif (is_numeric($raw) && $raw) {
                    $vehicleIds = [(int) $raw];
                }
            }
            unset($data['vehical_id']);
            $data['vehical_ids'] = $vehicleIds;
            $data['vehical_id'] = $vehicleIds[0] ?? null;

            // === Track who updated and when ===
            // Only set if columns exist (migration has been run)
            if (auth()->check()) {
                $data['updated_by'] = auth()->id();
                $data['last_updated_at'] = now();
            }

            // === Update using mass assignment (safe via $fillable) ===
            $item->update($data);
            
            // Debug: Log unit value after update
            $item->refresh();
            Log::info('Item Update - After Save', [
                'item_id' => $item->id,
                'unit_in_database' => $item->unit,
                'unit_was_saved' => isset($data['unit']) ? 'YES' : 'NO'
            ]);

            DB::commit();

            Log::info('Item updated successfully', ['item_id' => $item->id]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Item updated successfully!']);
            }
            return redirect()->back()->with('success', 'Item updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Item update failed', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['image', 'images'])
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update item: ' . $e->getMessage()], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to update item: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function item_show($id)
    {
        $item = Item::with([
            'vehical_item' => function($query) {
                $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
            },
            'category',
            'subcategory',
            'item_user',
            'product_item',
            'unit_item',
            'partnumber_item',
            'company_item',
            'quality_item',
            'technology_item',
            'group_item',
            'plate_item',
            'amphors_item',
            'volt_item',
            'cca_item',
            'minus_pool_item',
            'grade_item',
            'warrenty_item',
            'mileage_item',
            'level_item',
            'made_in_item',
            'services_item',
            'updated_by_user'
        ])->find($id);
        // return $item;
        if (!$item) {
            abort(404, 'Item not found');
        }
        $this->authorize($this->getViewPermissionForType($item->type));
        return view('admin.item.show', compact('item'));
    }

    /**
     * Get vehicle details for an item
     */
    public function getVehicleDetails($id)
    {
        try {
            $item = Item::with([
                'vehical_item' => function($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                },
                'product_item',
                'partnumber_item'
            ])->find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            if (!$item->vehical_item) {
                return response()->json([
                    'success' => false,
                    'message' => 'No vehicle details found for this item'
                ], 404);
            }

            $vehicle = $item->vehical_item;
            $yearRanges = [];
            if ($vehicle->year_from && $vehicle->year_to) {
                if ($vehicle->year_from == $vehicle->year_to) {
                    $yearRanges[] = $vehicle->year_from;
                } else {
                    $yearRanges[] = $vehicle->year_from . '-' . $vehicle->year_to;
                }
            }

            return response()->json([
                'success' => true,
                'vehicle' => [
                    'manufacturer' => $vehicle->manutacturer_vehical->name ?? null,
                    'model' => $vehicle->model_vehical->name ?? null,
                    'engine' => $vehicle->engine_vehical->name ?? null,
                    'country' => $vehicle->country_vehical->name ?? null,
                    'part_number' => $vehicle->vehical_part_number->name ?? ($item->partnumber_item->name ?? null),
                    'year_ranges' => $yearRanges,
                    'year_from' => $vehicle->year_from ?? null,
                    'year_to' => $vehicle->year_to ?? null,
                ],
                'item' => [
                    'id' => $item->id,
                    'name' => $item->product_item->name ?? ($item->partnumber_item->name ?? 'N/A'),
                    'type' => $item->type ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle details', [
                'item_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching vehicle details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate service history using Google AI (Gemini)
     */
    public function generateServiceHistoryAI(Request $request, $id)
    {
        try {
            $item = Item::with([
                'vehical_item' => function($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                },
                'product_item',
                'partnumber_item',
                'company_item',
                'quality_item'
            ])->find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            if (!$item->vehical_item) {
                return response()->json([
                    'success' => false,
                    'message' => 'No vehicle details found for this item'
                ], 404);
            }

            $vehicle = $item->vehical_item;
            
            // Build vehicle information string for AI prompt
            $vehicleInfo = [];
            if ($vehicle->manutacturer_vehical) {
                $vehicleInfo[] = "Manufacturer: " . $vehicle->manutacturer_vehical->name;
            }
            if ($vehicle->model_vehical) {
                $vehicleInfo[] = "Model: " . $vehicle->model_vehical->name;
            }
            if ($vehicle->engine_vehical) {
                $vehicleInfo[] = "Engine: " . $vehicle->engine_vehical->name . " CC";
            }
            if ($vehicle->country_vehical) {
                $vehicleInfo[] = "Country: " . $vehicle->country_vehical->name;
            }
            if ($vehicle->year_from && $vehicle->year_to) {
                $vehicleInfo[] = "Year Range: " . $vehicle->year_from . "-" . $vehicle->year_to;
            }
            if ($vehicle->vehical_part_number) {
                $vehicleInfo[] = "Part Number: " . $vehicle->vehical_part_number->name;
            }
            
            $itemInfo = [];
            if ($item->product_item) {
                $itemInfo[] = "Product: " . $item->product_item->name;
            }
            if ($item->partnumber_item) {
                $itemInfo[] = "Part Number: " . $item->partnumber_item->name;
            }
            if ($item->company_item) {
                $itemInfo[] = "Company: " . $item->company_item->name;
            }
            if ($item->quality_item) {
                $itemInfo[] = "Quality: " . $item->quality_item->name;
            }
            if ($item->type) {
                $itemInfo[] = "Type: " . ucfirst($item->type);
            }

            $vehicleInfoStr = implode(", ", $vehicleInfo);
            $itemInfoStr = implode(", ", $itemInfo);

            // Build AI prompt
            $prompt = "As an automotive service history expert, provide a detailed service history tracker and maintenance recommendations for the following vehicle and part information:\n\n";
            $prompt .= "Vehicle Details: " . $vehicleInfoStr . "\n";
            $prompt .= "Part Details: " . $itemInfoStr . "\n\n";
            $prompt .= "Please provide:\n";
            $prompt .= "1. Recommended service intervals\n";
            $prompt .= "2. Common maintenance tasks\n";
            $prompt .= "3. Potential issues to watch for\n";
            $prompt .= "4. Replacement recommendations\n";
            $prompt .= "5. Service history checklist\n\n";
            $prompt .= "Format the response in a clear, professional manner with sections and bullet points. Keep it concise but informative.";

            // Call Google Gemini API
            $geminiApiKey = env('GOOGLE_GEMINI_API_KEY');
            if (!$geminiApiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Gemini API key is not configured. Please add GOOGLE_GEMINI_API_KEY to your .env file.'
                ], 500);
            }

            $client = new Client();
            $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $geminiApiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 30
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $serviceHistory = $responseData['candidates'][0]['content']['parts'][0]['text'];
                
                return response()->json([
                    'success' => true,
                    'service_history' => $serviceHistory
                ]);
            } else {
                Log::error('Unexpected Gemini API response format', [
                    'response' => $responseData
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected response format from AI service'
                ], 500);
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Error calling Gemini API', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error calling AI service: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error generating service history', [
                'item_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating service history: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getItemsByType($type, Request $request)
    {
        $query = Item::with([
            'item_user',
            'item_user.branch',
            'item_user.assignedBranches',
            'product_item', 
            'category',
            'partnumber_item',
            'company_item',
            'quality_item',
            'updated_by_user',
            'volt_item',
            'plate_item',
            'amphors_item',
            'cca_item'
        ])
            ->where('type', $type)
            ->latest();
        
        // Check if 'all' parameter is passed to get all items (capped to avoid OOM)
        $maxItems = (int) (config('app.max_items_per_request', 1000) ?: 1000);
        if ($request->has('all') && $request->get('all') == 'true') {
            $items = $query->limit($maxItems)->get();
        } else {
            $items = $query->take(5)->get();
        }

        $totalItemsCount = Item::where('type', $type)->count(); // Get total count for the type

        return response()->json([
            'success' => true,
            'items' => $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'image' => $item->image ? ((str_starts_with($item->image, 'http://') || str_starts_with($item->image, 'https://')) ? $item->image : '/' . ltrim($item->image, '/')) : '/assets/img/media/default.png',
                    'bar_code' => $item->bar_code,
                    'barcode_image' => $item->barcode_image,
                    'user_name' => $item->item_user->name ?? '-',
                    'branch_name' => $item->item_user ? ($item->item_user->branch?->branch_name ?? $item->item_user->assignedBranches->first()?->branch_name ?? '-') : '-',
                    'product_name' => $item->product_item->name ?? '-',
                    'type' => $item->type,
                    'is_active' => $item->is_active,
                    'category_name' => $item->category ? $item->category->name : 'N/A',
                    'part_number' => $item->partnumber_item ? $item->partnumber_item->name : '-',
                    'company_name' => $item->company_item ? $item->company_item->name : '-',
                    'quality_name' => $item->quality_item ? $item->quality_item->name : '-',
                    'volt_name' => $item->volt_item ? (str_ends_with((string)$item->volt_item->name, 'V') ? $item->volt_item->name : $item->volt_item->name . 'V') : null,
                    'plate_name' => $item->plate_item ? (str_ends_with((string)$item->plate_item->name, 'PL') ? $item->plate_item->name : $item->plate_item->name . 'PL') : null,
                    'amphors_name' => $item->amphors_item ? (str_ends_with((string)$item->amphors_item->name, 'AH') ? $item->amphors_item->name : $item->amphors_item->name . 'AH') : null,
                    'cca_name' => $item->cca_item ? (str_contains((string)$item->cca_item->name, 'CCA') ? $item->cca_item->name : $item->cca_item->name . 'CCA') : null,
                    'updated_by_user' => $item->updated_by_user ? [
                        'name' => $item->updated_by_user->name,
                    ] : null,
                    'last_updated_at' => $item->last_updated_at ? $item->last_updated_at->format('d M Y, h:i A') : null,
                    'updated_at' => $item->updated_at ? $item->updated_at->format('d M Y, h:i A') : null,
                    'show_url' => route('item.show', $item->id),
                    'edit_url' => route('item.edit', $item->id),
                    'delete_url' => route('item.delete', $item->id),
                    'duplicate_url' => route('item.duplicate', $item->id),
                ];
            }),
            'total_count' => $totalItemsCount
        ]);
    }

    public function deleteSingleImage($id)
    {
        $item = Item::findOrFail($id);

        if ($item->image) {

            // Remove domain if stored as full URL
            $imagePath = str_replace(url('/') . '/', '', $item->image);

            if (file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }
        }

        $item->image = null;
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    public function deleteSingleFromArray(Request $request)
    {
        $item = Item::findOrFail($request->item_id);

        if (!$item->images) {
            return response()->json(['status' => false, 'message' => 'No images found']);
        }

        $images = $item->images;

        // Remove the image from array
        $images = array_values(array_filter($images, function ($img) use ($request) {
            return $img !== $request->image;
        }));

        // Delete the file from folder
        $imagePath = str_replace(url('/') . '/', '', $request->image); // convert full URL to relative path
        if (file_exists(public_path($imagePath))) {
            unlink(public_path($imagePath));
        }

        // Save updated array
        $item->images = $images;
        $item->save();

        return response()->json(['status' => true, 'message' => 'Image deleted successfully']);
    }







    public function itembulkDelete(Request $request)
    {
        $ids = $request->ids ?? [];
        if (count($ids) === 0) {
            return back()->with('error', 'No items selected.');
        }
        $deleted = 0;
        foreach ($ids as $id) {
            $item = Item::find($id);
            if ($item && auth()->user()->can($this->getDeletePermissionForType($item->type))) {
                $item->delete();
                $deleted++;
            }
        }
        return back()->with('success', $deleted > 0 ? "{$deleted} item(s) deleted successfully." : 'No items could be deleted (permission denied).');
    }

    /**
     * Bulk update selected items (retail price, cost, sale price, category, is_active).
     * Only fields sent in request are updated.
     */
    public function bulkUpdate(Request $request)
    {
        $updatePerms = ['update_items', 'update_parts', 'update_filters', 'update_break_pad', 'update_oil', 'update_battery', 'update_scrap', 'update_services'];
        if (!collect($updatePerms)->contains(fn ($p) => auth()->user()->can($p))) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to update items.'], 403);
            }
            abort(403, 'You do not have permission to update items.');
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:items,id',
            'retail_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'nullable|boolean',
        ]);

        $ids = $request->ids;
        $updated = 0;
        foreach ($ids as $id) {
            $item = Item::find($id);
            if (!$item || !auth()->user()->can($this->getUpdatePermissionForType($item->type))) {
                continue;
            }
            $changed = false;
            if ($request->has('retail_price')) {
                $item->retail_price = $request->retail_price !== '' && $request->retail_price !== null ? $request->retail_price : null;
                $changed = true;
            }
            if ($request->has('total_price')) {
                $item->total_price = $request->total_price !== '' && $request->total_price !== null ? $request->total_price : null;
                $changed = true;
            }
            if ($request->has('sale_price')) {
                $item->sale_price = $request->sale_price !== '' && $request->sale_price !== null ? $request->sale_price : null;
                $changed = true;
            }
            if ($request->has('category_id')) {
                $item->category_id = $request->category_id ?: null;
                $changed = true;
            }
            if ($request->has('is_active') && $request->is_active !== '') {
                $item->is_active = (bool) $request->is_active;
                $changed = true;
            }
            if ($changed) {
                $item->save();
                $updated++;
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $updated > 0 ? "{$updated} item(s) updated successfully." : 'No changes applied.',
                'updated' => $updated,
            ]);
        }
        return redirect()->route('all.items')->with('success', $updated > 0 ? "{$updated} item(s) updated successfully." : 'No changes applied.');
    }

    public function item_delete($id)
    {
        $item = Item::findOrFail($id);
        $this->authorize($this->getDeletePermissionForType($item->type));
        $item->delete();
        return redirect()->back()->with('success', 'Item deleted successfully.');
    }

    public function recycleBin()
    {
        $maxItems = (int) (config('app.max_items_per_request', 1000) ?: 1000);
        $items = Item::onlyTrashed()->limit($maxItems)->orderByDesc('deleted_at')->get();

        return view('admin.item.recycle-bin', compact('items'));
    }

    public function restore($id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $item->restore();

        return redirect()->back()->with('success', 'Item restored successfully!');
    }

    public function forceDelete($id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $item->forceDelete();

        return redirect()->back()->with('success', 'Item permanently deleted!');
    }




    public function itemduplicate($id)
    {
        $item = Item::findOrFail($id);
        $newItem = $item->replicate();
        $newItem->bar_code = $item->bar_code . '-COPY';
        $newItem->save();

        return response()->json(['success' => true]);
    }

    public function duplicate($id)
    {
        $original = Item::findOrFail($id);
        $this->authorize($this->getAddPermissionForType($original->type));
        $item = $original->replicate();

        // Give a unique barcode and mark as copy
        $item->bar_code = strtoupper(\Illuminate\Support\Str::random(10));
        $item->name .= ' (Copy)';

        // === FETCH ALL DROPDOWN DATA (Same as Create) ===
        $platos      = Platos::where('status', 'active')->get();
        $amphors     = Amphor::where('status', 'active')->get();
        $lineitems   = LineItem::where('status', 'active')->get();
        $Companies   = Company::where('status', 'active')->get();

        // Parent categories with subcategories eager loaded
        $Categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('children')
            ->get();

        $packings    = Packing::where('status', 'active')->get();
        $scales      = Scale::where('status', 'active')->get();
        $Vehicals    = VehicalType::where('status', 'active')->get();
        $milleages   = Mileage::where('status', 'active')->get();
        $item_types  = Producttype::where('status', 'active')->get();
        $units       = Unit::where('status', 'active')->get();

        return view('admin.items.dublicate', compact(
            'item',
            'platos',
            'amphors',
            'lineitems',
            'Companies',
            'Categories',
            'packings',
            'scales',
            'Vehicals',
            'milleages',
            'item_types',
            'units'
        ));
    }


    public function storeCompany(Request $request)
    {

        $company = CarCompany::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $company->id,
            'name' => $company->name
        ]);
    }

    public function storeName(Request $request)
    {
        $name = CarName::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $name->id,
            'name' => $name->name
        ]);
    }

    public function storeModel(Request $request)
    {
        $model = CarModel::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $model->id,
            'name' => $model->name
        ]);
    }

    public function show_car_model($id)
    {
        return response()->json(CarModel::findOrFail($id));
    }

    public function update_car_model(Request $request, $id)
    {
        $carmodel = CarModel::findOrFail($id);
        $carmodel->update(['name' => $request->name]);
        return response()->json([
            'success' => true,
            'id' => $carmodel->id,
            'name' => $carmodel->name,
            'message' => "Car model Update Successfully"
        ]);
    }

    public function destory_car_model($id)
    {
        CarModel::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Car model deleted Successfully"
        ]);
    }



    public function storeCountry(Request $request)
    {
        $country = CarCountry::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $country->id,
            'name' => $country->name
        ]);
    }

        public function show_car_country($id)
    {
        return response()->json(CarCountry::findOrFail($id));
    }

    public function update_car_country(Request $request, $id)
    {
        $carcountry = CarCountry::findOrFail($id);
        $carcountry->update(['name' => $request->name]);
        return response()->json([
            'success' => true,
            'id' => $carcountry->id,
            'name' => $carcountry->name,
            'message' => "Car Country Update Successfully"
        ]);
    }

    public function destory_car_country($id)
    {
        CarCountry::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Car Country deleted Successfully"
        ]);
    }

    public function storeManufacturer(Request $request)
    {
        $manufacture = CarManufacturer::create(
            ['name' => $request->name]
        );
        return response()->json([
            'success' => true,
            'id' => $manufacture->id,
            'name' => $manufacture->name
        ]);
    }


    public function show_car_manufacturer($id)
    {
        return response()->json(CarManufacturer::findOrFail($id));
    }

    public function update_car_manufacturer(Request $request, $id)
    {
        $manufacture = CarManufacturer::findOrFail($id);
        $manufacture->update(['name' => $request->name]);
        return response()->json([
            'success' => true,
            'id' => $manufacture->id,
            'name' => $manufacture->name,
            'message' => "Car Manufacturer Update Successfully"
        ]);
    }

    public function destory_car_manufacturer($id)
    {
        CarManufacturer::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Car Manufacturer deleted Successfully"
        ]);
    }

    public function getItemsCountByPartNumber($partNumberId)
    {
        $partNumber = PartNumber::find($partNumberId);
        if (!$partNumber) {
            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'Part number database میں موجود نہیں ہے۔',
                'count' => 0,
                'total' => 0,
                'details' => [],
                'qualities' => [],
                'part_number_name' => null,
            ]);
        }

        $items = Item::with('quality_item')
            ->where('part_number_id', $partNumberId)
            ->get();
        $count = $items->count();

        // Group by quality: name => count
        $grouped = [];
        $items->each(function ($item) use (&$grouped) {
            $qualityName = $item->quality_item->name ?? ($item->grade ?? 'Standard');
            if (!isset($grouped[$qualityName])) {
                $grouped[$qualityName] = 0;
            }
            $grouped[$qualityName]++;
        });

        $details = [];
        $qualities = [];
        foreach ($grouped as $qualityName => $qualityCount) {
            $details[] = $qualityCount . ' ' . $qualityName;
            $qualities[] = ['name' => $qualityName, 'count' => $qualityCount];
        }

        return response()->json([
            'success' => true,
            'exists' => true,
            'part_number_name' => $partNumber->name,
            'count' => $count,
            'total' => $count,
            'details' => $details,
            'qualities' => $qualities,
        ]);
    }

    /**
     * Check if product exists and return quality counts for items with this product (p_id).
     * Same structure as getItemsCountByPartNumber for Product Name dropdown.
     */
    public function getItemsCountByProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'Product is not found in the database.',
                'count' => 0,
                'total' => 0,
                'details' => [],
                'qualities' => [],
                'product_name' => null,
            ]);
        }

        $items = Item::with('quality_item')
            ->where('p_id', $productId)
            ->get();
        $count = $items->count();

        $grouped = [];
        $items->each(function ($item) use (&$grouped) {
            $qualityName = $item->quality_item->name ?? ($item->grade ?? 'Standard');
            if (!isset($grouped[$qualityName])) {
                $grouped[$qualityName] = 0;
            }
            $grouped[$qualityName]++;
        });

        $details = [];
        $qualities = [];
        foreach ($grouped as $qualityName => $qualityCount) {
            $details[] = $qualityCount . ' ' . $qualityName;
            $qualities[] = ['name' => $qualityName, 'count' => $qualityCount];
        }

        return response()->json([
            'success' => true,
            'exists' => true,
            'product_name' => $product->name,
            'count' => $count,
            'total' => $count,
            'details' => $details,
            'qualities' => $qualities,
        ]);
    }

    public function getItemsByPartNumber($partNumberId)
    {
        $items = Item::with([
            'item_user',
            'product_item',
            'category',
            'partnumber_item',
            'company_item',
            'quality_item'
        ])
            ->where('part_number_id', $partNumberId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'image' => $item->image ? ((str_starts_with($item->image, 'http://') || str_starts_with($item->image, 'https://')) ? $item->image : '/' . ltrim($item->image, '/')) : '/assets/img/media/default.png',
                    'user_name' => $item->item_user->name ?? '-',
                    'product_name' => $item->product_item->name ?? '-',
                    'type' => $item->type,
                    'bar_code' => $item->bar_code,
                    'is_active' => $item->is_active,
                    'category_name' => $item->category->name ?? 'N/A',
                    'part_number_name' => $item->partnumber_item->name ?? 'N/A',
                    'company_name' => $item->company_item->name ?? 'N/A',
                    'quality_name' => $item->quality_item->name ?? 'N/A',
                    'show_url' => route('item.show', $item->id),
                    'edit_url' => route('item.edit', $item->id),
                    'delete_url' => route('item.delete', $item->id),
                    'duplicate_url' => route('item.duplicate', $item->id),
                ];
            }),
            'total' => $items->count()
        ]);
    }

    /**
     * Lookup existing weight+unit combinations for a given weight value (from items table).
     * Used by combined weight/unit input: if matches exist show Edit, else show Add with unit dropdown.
     */
    public function weightUnitLookup(Request $request)
    {
        $weight = $request->query('weight');
        if ($weight === null || $weight === '') {
            return response()->json(['success' => true, 'matches' => []]);
        }
        $weightVal = is_numeric($weight) ? (float) $weight : null;
        if ($weightVal === null) {
            return response()->json(['success' => true, 'matches' => []]);
        }
        $rows = Item::whereNotNull('weight_for_delivery')
            ->whereNotNull('weight_unit')
            ->where('weight_for_delivery', $weightVal)
            ->selectRaw('DISTINCT weight_for_delivery as weight, weight_unit as unit')
            ->get();
        $matches = $rows->map(function ($r) {
            return ['weight' => (float) $r->weight, 'unit' => $r->unit];
        })->values()->toArray();
        return response()->json(['success' => true, 'matches' => $matches]);
    }

    public function generateWhatsAppPdf(Request $request)
    {
        try {
            $itemIds = $request->input('item_ids', []);
            $phoneNumber = $request->input('phone_number');
            
            if (empty($itemIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected.'
                ], 400);
            }
            
            if (!$phoneNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number is required.'
                ], 400);
            }
            
            // Fetch items with all relationships
            $items = Item::with([
                'item_user',
                'product_item',
                'category',
                'subcategory',
                'partnumber_item',
                'company_item',
                'quality_item',
                'technology_item',
                'group_item',
                'plate_item',
                'amphors_item',
                'volt_item',
                'cca_item',
                'minus_pool_item',
                'grade_item',
                'warrenty_item',
                'mileage_item',
                'level_item',
                'made_in_item',
                'services_item',
                'unit_item',
                'vehical_item' => function($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                }
            ])
            ->whereIn('id', $itemIds)
            ->get();
            
            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found.'
                ], 404);
            }
            
            // Generate PDF
            $pdf = Pdf::loadView('admin.item.whatsapp-pdf', compact('items', 'phoneNumber'));
            $pdf->setPaper('a4', 'portrait');
            
            // Save PDF temporarily
            $filename = 'items_' . time() . '_' . rand(1000, 9999) . '.pdf';
            $pdfPath = public_path('temp_pdfs/' . $filename);
            
            // Create directory if it doesn't exist
            if (!file_exists(public_path('temp_pdfs'))) {
                mkdir(public_path('temp_pdfs'), 0755, true);
            }
            
            $pdf->save($pdfPath);
            
            // Generate PDF URL
            $pdfUrl = url('temp_pdfs/' . $filename);
            
            // Create WhatsApp message
            $message = "📦 *Product Details*\n\n";
            
            // Add item names
            foreach ($items as $index => $item) {
                $itemName = $item->product_item->name ?? 'Item #' . ($index + 1);
                $message .= "• " . $itemName . "\n";
            }
            
            $message .= "\n📄 *Full Product Specification PDF:*\n";
            $message .= $pdfUrl;
            $message .= "\n\n_This PDF contains complete product details, specifications, and pricing information._";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'pdf_url' => $pdfUrl,
                'pdf_path' => $pdfPath
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp PDF Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Product Specification PDF (Single Item)
     */
    public function generateProductSpecificationPdf($id)
    {
        try {
            $item = Item::with([
                'item_user',
                'product_item',
                'category',
                'subcategory',
                'partnumber_item',
                'company_item',
                'quality_item',
                'technology_item',
                'group_item',
                'plate_item',
                'amphors_item',
                'volt_item',
                'cca_item',
                'minus_pool_item',
                'grade_item',
                'warrenty_item',
                'mileage_item',
                'level_item',
                'made_in_item',
                'services_item',
                'unit_item',
                'updated_by_user',
                'vehical_item' => function($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                }
            ])
            ->findOrFail($id);

            // Generate PDF
            $pdf = Pdf::loadView('admin.item.product-specification-pdf', compact('item'));
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download('product-specification-' . ($item->bar_code ?? $item->id) . '-' . time() . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Product Specification PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function checkBarcode(Request $request)
    {
        $barCode = $request->input('bar_code');
        
        if (!$barCode) {
            return response()->json([
                'exists' => false,
                'message' => 'Barcode is required'
            ], 400);
        }
        
        $exists = Item::where('bar_code', $barCode)->exists();
        
        return response()->json([
            'exists' => $exists,
            'bar_code' => $barCode
        ]);
    }
}
