<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amphor;
use App\Models\BatterySize;
use App\Models\Brand;
use App\Models\CarCompany;
use App\Models\CarCountry;
use App\Models\CarManufacturer;
use App\Models\CarModel;
use App\Models\CarName;
use App\Models\Category;
use App\Models\Cca;
use App\Models\Company;
use App\Models\EngineCc;
use App\Models\Formula;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Item;
use App\Models\Level;
use App\Models\LineItem;
use App\Models\MadeIn;
use App\Models\Mileage;
use App\Models\Minuspool;
use App\Models\Packing;
use App\Models\PartNumber;
use App\Models\Platos;
use App\Models\PoolDirection;
use App\Models\Product;
use App\Models\Producttype;
use App\Models\Quality;
use App\Models\Scale;
use App\Models\Series;
use App\Models\Services;
use App\Models\Technology;
use App\Models\Unit;
use App\Models\VehicalType;
use App\Models\Volt;
use App\Models\Warrenty;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Milon\Barcode\DNS1D;

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

    /**
     * Check if an item is referenced in any transaction or stock ledger tables.
     * If referenced, deletion must be blocked to keep reports consistent.
     */
    protected function getItemUsageCounts(int $itemId): array
    {
        $tables = [
            // Core transactional lines
            'sale_items' => 'item_id',
            'purchase_items' => 'item_id',
            // Stock ledgers / current stock
            'warehouse_items' => 'item_id',
            'claim_warehouse_items' => 'item_id',

            // Optional tables (based on your policy spec). We only count them if they exist.
            'scrap_items' => 'item_id',
            'claim_items' => 'item_id',
            'stock_ledgers' => 'item_id',
            'stock_ledger' => 'item_id',
        ];

        $counts = [];
        foreach ($tables as $table => $col) {
            if (! Schema::hasTable($table)) {
                $counts[$table] = 0;

                continue;
            }
            if (! Schema::hasColumn($table, $col)) {
                $counts[$table] = 0;

                continue;
            }
            $counts[$table] = (int) DB::table($table)->where($col, $itemId)->count();
        }

        return $counts;
    }

    protected function isItemDeletableByUsage(Item $item): bool
    {
        $counts = $this->getItemUsageCounts((int) $item->id);
        foreach ($counts as $cnt) {
            if ($cnt > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * UI helper endpoint: return whether delete is allowed for this item.
     */
    public function canDeleteItem($id)
    {
        $item = Item::findOrFail($id);
        $this->authorize($this->getDeletePermissionForType($item->type));

        $counts = $this->getItemUsageCounts((int) $item->id);
        $usedIn = array_filter($counts, fn ($v) => (int) $v > 0);
        $canDelete = empty($usedIn);

        Log::info('Item canDelete check', [
            'item_id' => (int) $item->id,
            'item_bar_code' => $item->bar_code ?? null,
            'item_type' => $item->type ?? null,
            'counts' => $counts,
            'can_delete' => $canDelete,
        ]);

        return response()->json([
            'can_delete' => $canDelete,
            'message' => 'This item cannot be deleted because it is already used in transactions.',
            'used_in' => $usedIn,
        ]);
    }

    public function all_items(Request $request)
    {
        $viewPerms = ['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'];
        if (! collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view items.');
        }
        $perPage = $request->input('per_page', 50);

        $query = Item::with([
            'item_user',
            'item_user.branch',
            'product_item',
            'partnumber_item',
            'updated_by_user',
            'category',
            'company_item',
            'quality_item',
            'unit_item',
        ]);

        if ($request->input('type') === 'battery') {
            $query->with(['volt_item', 'plate_item', 'amphors_item', 'cca_item']);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('technology_id')) {
            $query->where('technology', $request->technology_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('short_disc', 'like', "%{$search}%")
                    ->orWhere('pro_dis', 'like', "%{$search}%")
                    ->orWhere('bar_code', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate($perPage);

        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'items' => $items->getCollection()->map(function ($item) {
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
                        if ($itemName && ! str_contains($itemName, $partNum)) {
                            $itemName .= ' - '.$partNum;
                        }
                    }

                    return [
                        'id' => $item->id,
                        'name' => $itemName,
                        'image' => $item->image ? ((str_starts_with($item->image, 'http://') || str_starts_with($item->image, 'https://')) ? $item->image : '/'.ltrim($item->image, '/')) : '/assets/img/media/default.png',
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
                        'volt_name' => $item->volt_item ? (str_ends_with((string) $item->volt_item->name, 'V') ? $item->volt_item->name : $item->volt_item->name.'V') : null,
                        'plate_name' => $item->plate_item ? (str_ends_with((string) $item->plate_item->name, 'PL') ? $item->plate_item->name : $item->plate_item->name.'PL') : null,
                        'amphors_name' => $item->amphors_item ? (str_ends_with((string) $item->amphors_item->name, 'AH') ? $item->amphors_item->name : $item->amphors_item->name.'AH') : null,
                        'cca_name' => $item->cca_item ? (str_contains((string) $item->cca_item->name, 'CCA') ? $item->cca_item->name : $item->cca_item->name.'CCA') : null,
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
                }),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                ],
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
        if (! collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view items.');
        }

        if ($request->filled('type') && $request->type === 'battery') {
            $batteryCategoryIds = Item::where('type', 'battery')
                ->whereNotNull('category_id')
                ->distinct()
                ->pluck('category_id');

            if ($batteryCategoryIds->isNotEmpty()) {
                $batteryCategories = Category::whereIn('id', $batteryCategoryIds)->get(['id', 'parent_id']);
                $topCategoryIds = $batteryCategories->map(function ($cat) {
                    return $cat->parent_id ?: $cat->id;
                })->unique()->values();

                $categories = Category::whereIn('id', $topCategoryIds)
                    ->orderBy('name')
                    ->get();
            } else {
                $categories = collect();
            }
        } else {
            $categories = Category::whereNull('parent_id')
                ->orderBy('name')
                ->get();
        }

        $query = Item::with(['category', 'unit_item', 'plate_item', 'amphors_item', 'volt_item', 'cca_item', 'company_item', 'product_item', 'partnumber_item', 'group_item', 'updated_by_user.branch', 'priceUpdatedBranch'])
            ->orderBy('category_id')
            ->orderBy('short_disc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('technology_id')) {
            $query->where('technology', $request->technology_id);
        }

        $items = $query->get();

        $currentBranchName = session('selected_branch_name');
        if (! $currentBranchName && auth()->user() && auth()->user()->branch_id) {
            $currentBranchName = \App\Models\Branch::where('id', auth()->user()->branch_id)->value('branch_name');
        }

        $plates = Platos::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $amphors = Amphor::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $groups = Group::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        if ($request->filled('type') && $request->type === 'battery') {
            // Base query for all battery items (for companies list)
            $batteryItemQuery = Item::where('type', 'battery');

            $batteryCompanyIds = (clone $batteryItemQuery)
                ->whereNotNull('company_id')
                ->distinct()
                ->pluck('company_id');

            // For technologies list we must respect selected company (if any)
            $batteryTechQuery = Item::where('type', 'battery');
            if ($request->filled('company_id')) {
                $batteryTechQuery->where('company_id', $request->company_id);
            }
            $batteryTechnologyIds = $batteryTechQuery
                ->whereNotNull('technology')
                ->distinct()
                ->pluck('technology');

            $companies = Company::where('status', 'active')
                ->whereIn('id', $batteryCompanyIds)
                ->orderBy('name')
                ->get(['id', 'name']);

            $technologies = Technology::where('status', 'active')
                ->whereIn('id', $batteryTechnologyIds)
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $companies = Company::where('status', 'active')->orderBy('name')->get(['id', 'name']);
            $technologies = Technology::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        }

        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $partnumbers = PartNumber::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $volts = Volt::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $ccas = Cca::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.item.price-list', compact('items', 'categories', 'currentBranchName', 'plates', 'amphors', 'groups', 'companies', 'products', 'partnumbers', 'volts', 'ccas', 'technologies'));
    }

    /**
     * Item Stock Report - detailed stock in/out with filters.
     */
    public function stockReport(Request $request)
    {
        $viewPerms = ['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'];
        if (! collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
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
            $key = $row->item_id.':'.($row->branch_id ?? 0);
            $rows[$key] = [
                'item_id' => $row->item_id,
                'branch_id' => $row->branch_id,
                'stock_in' => (float) $row->stock_in,
                'stock_out' => 0.0,
            ];
        }
        foreach ($saleAgg as $row) {
            $key = $row->item_id.':'.($row->branch_id ?? 0);
            if (! isset($rows[$key])) {
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
            if ($literPerCan === null && $it->filling !== null && $it->filling !== '' && ! is_nan((float) $it->filling)) {
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
                    return $row->item_id.':'.($row->branch_id ?? 0);
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
                            ? $primaryName.' ('.$supplier->company.')'
                            : $supplier->company;
                    }

                    return $primaryName ?: 'Supplier #'.$supplier->id;
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
                    return $row->item_id.':'.($row->branch_id ?? 0);
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
                            ? $primaryName.' ('.$customer->company.')'
                            : $customer->company;
                    }

                    return $primaryName ?: 'Customer #'.$customer->id;
                });
            }
        }

        $reportRows = collect($rows)->map(function ($row) use ($items, $branchNames, $purchaseDetailsByKey, $supplierNamesById, $saleDetailsByKey, $customerNamesById) {
            $item = $items->get($row['item_id']);
            if (! $item) {
                return null;
            }

            $key = $row['item_id'].':'.($row['branch_id'] ?? 0);

            $rawName = $item->short_disc ?? $item->pro_dis ?? '';
            $productName = trim(strip_tags((string) $rawName));
            if ($productName === '' && $item->partnumber_item) {
                $productName = $item->partnumber_item->name ?? $item->bar_code ?? 'Item #'.$item->id;
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
                $key = $row->item_id.':'.($row->branch_id ?? 0);
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
                $key = $row->item_id.':'.($row->branch_id ?? 0);
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
                if (! $item) {
                    continue;
                }

                $rawName = $item->short_disc ?? $item->pro_dis ?? '';
                $productName = trim(strip_tags((string) $rawName));
                if ($productName === '' && $item->partnumber_item) {
                    $productName = $item->partnumber_item->name ?? $item->bar_code ?? 'Item #'.$item->id;
                }
                if ($productName === '') {
                    $productName = $item->bar_code ?? 'Item #'.$item->id;
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
                        $partyName = $supplierNamesById[$entry['party_id']] ?? ('Supplier #'.$entry['party_id']);
                    } elseif ($entry['party_type'] === 'customer' && $entry['party_id']) {
                        $partyName = $customerNamesById[$entry['party_id']] ?? ('Customer #'.$entry['party_id']);
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
        $warehouseItems = $wiQuery->with([
            'item.category', 'item.company_item', 'item.partnumber_item',
            'item.group_item', 'item.plate_item', 'item.amphors_item', 'item.volt_item', 'item.cca_item', 'item.product_item',
            'item.quality_item', 'item.grade_item', 'item.level_item', 'item.unit_item',
        ])->limit($maxWarehouseItems)->get();

        $warehouseRows = [];
        foreach ($warehouseItems as $wi) {
            $item = $wi->item;
            if (! $item) {
                continue;
            }
            $rawName = $item->short_disc ?? $item->pro_dis ?? '';
            $productName = trim(strip_tags((string) $rawName));
            if ($productName === '' && $item->partnumber_item) {
                $productName = $item->partnumber_item->name ?? $item->bar_code ?? 'Item #'.$item->id;
            }
            if ($productName === '') {
                $productName = $item->bar_code ?? 'Item #'.$item->id;
            }
            $warehouse = $warehouses->firstWhere('id', $wi->warehouse_id);

            $battery_display = null;
            if (($item->type ?? null) === 'battery') {
                $v = $item->volt_item ? trim((string) ($item->volt_item->name ?? '')) : '';
                $voltDisplay = $v !== '' ? (preg_match('/\d*\s*V$/i', $v) ? $v : $v.'V') : '';
                $p = $item->plate_item ? trim((string) ($item->plate_item->name ?? '')) : '';
                $plateDisplay = $p !== '' ? (preg_match('/\d*\s*PL$/i', $p) ? $p : $p.'PL') : '';
                $a = $item->amphors_item ? trim((string) ($item->amphors_item->name ?? '')) : '';
                $ampDisplay = $a !== '' ? (preg_match('/\d*\s*AH$/i', $a) ? $a : $a.'AH') : '';
                $c = $item->cca_item ? trim((string) ($item->cca_item->name ?? '')) : '';
                $ccaDisplay = $c !== '' ? (preg_match('/\d*\s*CCA$/i', $c) ? $c : $c.'CCA') : '';
                $groupDisplay = $item->group_item ? trim((string) ($item->group_item->name ?? '')) : '';
                $companyDisplay = $item->company_item ? trim((string) ($item->company_item->name ?? '')) : '';
                $battery_display = [
                    'product_name' => $item->product_item ? trim((string) ($item->product_item->name ?? '')) : '',
                    'group' => $groupDisplay,
                    'plate' => $plateDisplay,
                    'amp' => $ampDisplay,
                    'company' => $companyDisplay,
                    'volt' => $voltDisplay,
                    'cca' => $ccaDisplay,
                    'bar_code' => $item->bar_code ?? '',
                ];
            }

            $oil_display = null;
            if (($item->type ?? null) === 'oil') {
                $qualityName = $item->quality_item ? trim((string) ($item->quality_item->name ?? '')) : '';
                $gradeName = $item->grade_item ? trim((string) ($item->grade_item->name ?? '')) : '';
                $levelName = $item->level_item ? trim((string) ($item->level_item->name ?? '')) : '';
                $companyName = $item->company_item ? trim((string) ($item->company_item->name ?? '')) : '';
                $unitName = $item->unit_item ? trim((string) ($item->unit_item->name ?? $item->unit_item->short_name ?? '')) : '';
                $unitVolume = '';
                if (preg_match('/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)\b/i', $unitName, $m)) {
                    $unitVolume = $m[1].' LITER';
                } elseif (preg_match('/\b(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i', $unitName, $m)) {
                    $unitVolume = $m[1].' LITER';
                }
                if ($unitVolume === '' && ! empty($oilConfigByItemId[$item->id]['liter_per_can'])) {
                    $unitVolume = $oilConfigByItemId[$item->id]['liter_per_can'].' LITER';
                }
                if ($unitVolume === '' && $item->filling !== null && $item->filling !== '' && ! is_nan((float) $item->filling) && (float) $item->filling > 0) {
                    $unitVolume = (float) $item->filling.' LITER';
                }
                if ($unitVolume === '' && $item->unit_option !== null && $item->unit_option !== '' && strpos($item->unit_option, '_') !== false) {
                    $parts = explode('_', $item->unit_option);
                    $last = end($parts);
                    if (is_numeric($last) && (float) $last > 0) {
                        $unitVolume = $last.' LITER';
                    }
                }
                $unitType = '';
                if (stripos($unitName, 'can') !== false) {
                    $unitType = 'CAN';
                } elseif ($item->unit_item && trim((string) ($item->unit_item->short_name ?? '')) !== '') {
                    $unitType = strtoupper(trim($item->unit_item->short_name));
                }
                $oil_display = [
                    'quality' => $qualityName,
                    'grade' => $gradeName,
                    'level' => $levelName,
                    'company' => $companyName,
                    'unit_volume' => $unitVolume,
                    'unit_type' => $unitType,
                    'bar_code' => $item->bar_code ?? '',
                ];
            }

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
                'item_id' => $item->id,
                'branch_id' => $warehouse && $warehouse->branch ? $warehouse->branch->id : null,
                'item_type' => $item->type ?: 'Item',
                'product_name' => $productName,
                'part_number' => optional($item->partnumber_item)->name ?: $item->bar_code,
                'battery_display' => $battery_display,
                'oil_display' => $oil_display ?? null,
                'category' => optional($item->category)->name,
                'company' => optional($item->company_item)->name,
                'branch' => $warehouse && $warehouse->branch ? $warehouse->branch->branch_name : '—',
                'warehouse' => $warehouse ? $warehouse->warehouse_name : 'Warehouse #'.$wi->warehouse_id,
                'warehouse_code' => $warehouse ? ($warehouse->warehouse_code ?? '') : '',
                'quantity' => (float) $wi->quantity,
                'available_quantity' => (float) ($wi->available_quantity ?? $wi->quantity),
                'min_stock_level' => $wi->min_stock_level !== null && $wi->min_stock_level !== '' ? (float) $wi->min_stock_level : null,
                'item_l_stock' => $item->l_stock !== null && $item->l_stock !== '' ? (float) $item->l_stock : null,
                'item_m_stock' => $item->m_stock !== null && $item->m_stock !== '' ? (float) $item->m_stock : null,
                'qty_can' => $qtyCan,
                'qty_liter' => $qtyLiter,
                'qty_ml' => $qtyMl,
            ];
        }

        // Summary: total stock grouped by Product (item_id) + Branch
        $summaryRows = [];
        $grouped = collect($warehouseRows)->groupBy(function ($r) {
            return ($r['item_id'] ?? 0).'_'.($r['branch_id'] ?? 0);
        });
        foreach ($grouped as $key => $rows) {
            $first = $rows->first();
            $totalQty = $rows->sum('quantity');
            $totalMinStock = $rows->sum(function ($r) {
                $whMin = isset($r['min_stock_level']) && $r['min_stock_level'] !== null && is_numeric($r['min_stock_level']) ? (float) $r['min_stock_level'] : null;
                $itemLow = isset($r['item_l_stock']) && $r['item_l_stock'] !== null && is_numeric($r['item_l_stock']) ? (float) $r['item_l_stock'] : null;
                $effective = $whMin !== null ? $whMin : $itemLow;

                return $effective !== null ? $effective : 0;
            });
            $itemLStock = null;
            $itemMStock = null;
            $firstRow = $rows->first();
            if ($firstRow) {
                if (isset($firstRow['item_l_stock']) && $firstRow['item_l_stock'] !== null) {
                    $itemLStock = (float) $firstRow['item_l_stock'];
                }
                if (isset($firstRow['item_m_stock']) && $firstRow['item_m_stock'] !== null) {
                    $itemMStock = (float) $firstRow['item_m_stock'];
                }
            }
            $requiredQty = null;
            if ($itemMStock !== null && (float) $itemMStock > 0) {
                $requiredQty = max(0, $itemMStock - $totalQty);
            } elseif ($totalMinStock > 0) {
                $requiredQty = max(0, $totalMinStock - $totalQty);
            } elseif ($itemLStock !== null && (float) $itemLStock > 0 && $totalQty < (float) $itemLStock) {
                $requiredQty = max(0, (float) $itemLStock - $totalQty);
            }
            $displayMinStock = $totalMinStock > 0 ? $totalMinStock : $itemLStock;
            $isVeryLowStock = $itemLStock !== null && (float) $itemLStock > 0 && $totalQty < (float) $itemLStock;
            $totalCan = $rows->sum(function ($r) {
                return isset($r['qty_can']) && is_numeric($r['qty_can']) ? $r['qty_can'] : 0;
            });
            $totalLiter = $rows->sum(function ($r) {
                return isset($r['qty_liter']) && is_numeric($r['qty_liter']) ? $r['qty_liter'] : 0;
            });
            $totalMl = $rows->sum(function ($r) {
                return isset($r['qty_ml']) && is_numeric($r['qty_ml']) ? $r['qty_ml'] : 0;
            });
            $summaryRows[] = [
                'item_id' => $first['item_id'],
                'branch_id' => $first['branch_id'],
                'product_name' => $first['product_name'],
                'battery_display' => $first['battery_display'],
                'oil_display' => $first['oil_display'] ?? null,
                'branch' => $first['branch'],
                'total_quantity' => $totalQty,
                'min_stock' => $displayMinStock !== null && (float) $displayMinStock > 0 ? (float) $displayMinStock : null,
                'required_quantity' => $requiredQty,
                'maintain_stock' => $itemMStock !== null && (float) $itemMStock > 0 ? (float) $itemMStock : null,
                'is_very_low_stock' => $isVeryLowStock,
                'total_can' => $totalCan,
                'total_liter' => $totalLiter,
                'total_ml' => $totalMl,
                'warehouse_count' => $rows->count(),
            ];
        }

        // Group warehouse rows under each summary for drill-down display
        $summaryWithDetails = [];
        foreach ($summaryRows as $sr) {
            $details = collect($warehouseRows)->filter(function ($wr) use ($sr) {
                return ($wr['item_id'] ?? null) == $sr['item_id'] && ($wr['branch_id'] ?? null) == $sr['branch_id'];
            })->values()->all();
            $summaryWithDetails[] = ['summary' => $sr, 'details' => $details];
        }

        return view('admin.item.stock-report', [
            'rows' => $reportRows,
            'transactions' => $transactions,
            'warehouseRows' => $warehouseRows,
            'summaryRows' => $summaryRows,
            'summaryWithDetails' => $summaryWithDetails,
            'branches' => $branches,
            'warehouses' => $warehouses,
            'users' => $users,
            'categories' => $categories,
            'print_mode' => $request->get('print') === '1',
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
     * A4 print-friendly stock report: item name, category, company, branch,
     * current qty, min stock, low-stock highlight, purchase qty needed,
     * canister details (liters per can / remaining), recommended vendors with rates.
     */
    public function stockReportA4(Request $request)
    {
        $viewPerms = ['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'];
        if (! collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view items.');
        }

        $branches = \App\Models\Branch::orderBy('branch_name')->get();
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::with('branch')->orderBy('warehouse_name')->get();

        $branchId = $request->branch_id ?: null;
        $warehouseIdFilter = $request->warehouse_id ?: null;
        $typeFilter = $request->type;
        $categoryId = $request->category_id;

        $wiQuery = \App\Models\WarehouseItem::query()
            ->select('warehouse_items.*')
            ->join('warehouses', 'warehouse_items.warehouse_id', '=', 'warehouses.id')
            ->join('items', 'warehouse_items.item_id', '=', 'items.id')
            ->when($branchId, fn ($q) => $q->where('warehouses.branch_id', $branchId))
            ->when($warehouseIdFilter, fn ($q) => $q->where('warehouse_items.warehouse_id', $warehouseIdFilter))
            ->when($typeFilter && $typeFilter !== 'all', fn ($q) => $q->where('items.type', $typeFilter))
            ->when($categoryId, fn ($q) => $q->where('items.category_id', $categoryId));

        $maxItems = (int) (config('app.max_warehouse_items_report', 10000) ?: 10000);
        $warehouseItems = $wiQuery->with([
            'item.category', 'item.company_item', 'item.partnumber_item', 'item.unit_item',
        ])->limit($maxItems)->get();

        $itemIds = $warehouseItems->pluck('item_id')->unique()->values();
        $items = Item::with(['category', 'company_item', 'partnumber_item', 'unit_item'])
            ->whereIn('id', $itemIds)->get()->keyBy('id');

        // Oil/canister: liters per can per item
        $oilConfigByItemId = [];
        foreach ($items as $it) {
            if (($it->type ?? null) !== 'oil') {
                continue;
            }
            $literPerCan = null;
            $unitName = $it->unit_item ? trim($it->unit_item->name ?? $it->unit_item->short_name ?? '') : '';
            $unitOption = $it->unit_option ? trim((string) $it->unit_option) : '';
            if ($unitOption !== '' && strpos($unitOption, '_') !== false) {
                $parts = explode('_', $unitOption);
                $lastPart = end($parts);
                if (is_numeric($lastPart) && (float) $lastPart > 0) {
                    $literPerCan = (float) $lastPart;
                }
            }
            if ($literPerCan === null && preg_match('/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)\b/i', $unitName, $m)) {
                $literPerCan = (float) $m[1];
            } elseif ($literPerCan === null && preg_match('/\b(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i', $unitName, $m)) {
                $literPerCan = (float) $m[1];
            }
            if ($literPerCan === null && $it->filling !== null && $it->filling !== '' && ! is_nan((float) $it->filling)) {
                $literPerCan = (float) $it->filling;
            }
            $oilConfigByItemId[$it->id] = ['liter_per_can' => $literPerCan && $literPerCan > 0 ? $literPerCan : null];
        }

        // Recommended vendors per item: latest purchase rate per supplier
        $vendorsByItemId = [];
        if ($itemIds->isNotEmpty()) {
            $purchaseItems = \App\Models\PurchaseItem::query()
                ->select('purchase_items.item_id', 'purchase_items.rate', 'purchases.supplier_id', 'purchases.purchase_date')
                ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->whereIn('purchase_items.item_id', $itemIds)
                ->whereNotNull('purchases.supplier_id')
                ->orderBy('purchases.purchase_date', 'desc')
                ->get();
            $seen = [];
            foreach ($purchaseItems as $pi) {
                $key = $pi->item_id.'_'.$pi->supplier_id;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $vendorsByItemId[$pi->item_id] = $vendorsByItemId[$pi->item_id] ?? [];
                $vendorsByItemId[$pi->item_id][] = [
                    'supplier_id' => $pi->supplier_id,
                    'rate' => (float) $pi->rate,
                ];
            }
            $supplierIds = collect($vendorsByItemId)->flatten(1)->pluck('supplier_id')->unique()->filter();
            $suppliers = \App\Models\Supplier::whereIn('id', $supplierIds)->get()->keyBy('id');
            foreach ($vendorsByItemId as $iid => $list) {
                foreach ($list as $idx => $v) {
                    $sup = $suppliers->get($v['supplier_id']);
                    $name = $sup ? (($sup->names[0] ?? null) ?: $sup->company ?: 'Supplier #'.$sup->id) : 'Supplier #'.$v['supplier_id'];
                    $vendorsByItemId[$iid][$idx]['name'] = $name;
                }
            }
        }

        $rows = [];
        foreach ($warehouseItems as $wi) {
            $item = $items->get($wi->item_id);
            if (! $item) {
                continue;
            }
            $rawName = $item->short_disc ?? $item->pro_dis ?? '';
            $productName = trim(strip_tags((string) $rawName));
            if ($productName === '' && $item->partnumber_item) {
                $productName = $item->partnumber_item->name ?? $item->bar_code ?? 'Item #'.$item->id;
            }
            $warehouse = $warehouses->firstWhere('id', $wi->warehouse_id);
            $branchName = $warehouse && $warehouse->branch ? $warehouse->branch->branch_name : '—';
            $warehouseName = $warehouse ? $warehouse->warehouse_name : 'Warehouse #'.$wi->warehouse_id;

            $minStock = $wi->min_stock_level !== null && $wi->min_stock_level !== ''
                ? (float) $wi->min_stock_level
                : (isset($item->min_qty) ? (float) $item->min_qty : null);
            $maxStock = $wi->max_stock_level !== null && $wi->max_stock_level !== ''
                ? (float) $wi->max_stock_level
                : (isset($item->max_qty) ? (float) $item->max_qty : null);
            $available = (float) $wi->available_quantity;
            $isLow = $minStock !== null && $available <= $minStock;
            $qtyToPurchase = null;
            if ($minStock !== null && $available < $minStock) {
                $qtyToPurchase = $minStock - $available;
            } elseif ($maxStock !== null && $available < $maxStock && $isLow) {
                $qtyToPurchase = $maxStock - $available;
            }

            $literPerCan = $oilConfigByItemId[$item->id]['liter_per_can'] ?? null;
            $canisterDetail = null;
            $qtyCan = $qtyLiter = $qtyMl = null;
            if (($item->type ?? null) === 'oil' && $literPerCan && $literPerCan > 0) {
                $totalLiters = (float) $wi->quantity;
                $fullCans = (int) floor($totalLiters / $literPerCan);
                $remainder = $totalLiters - ($fullCans * $literPerCan);
                $wholeLiters = (int) floor($remainder);
                $ml = (int) round(($remainder - $wholeLiters) * 1000);
                $qtyCan = $fullCans;
                $qtyLiter = $wholeLiters;
                $qtyMl = $ml;
                $canisterDetail = $literPerCan.' L/can';
                if ($fullCans > 0 || $wholeLiters > 0 || $ml > 0) {
                    $canisterDetail .= ' — '.$fullCans.' can(s), '.$wholeLiters.' L, '.$ml.' ml remaining';
                }
            }

            $rows[] = [
                'product_name' => $productName,
                'part_number' => optional($item->partnumber_item)->name ?: $item->bar_code,
                'category' => optional($item->category)->name,
                'company' => optional($item->company_item)->name,
                'branch' => $branchName,
                'warehouse' => $warehouseName,
                'current_qty' => $available,
                'min_stock' => $minStock,
                'max_stock' => $maxStock,
                'is_low_stock' => $isLow,
                'qty_to_purchase' => $qtyToPurchase,
                'liter_per_can' => $literPerCan,
                'canister_detail' => $canisterDetail,
                'qty_can' => $qtyCan,
                'qty_liter' => $qtyLiter,
                'qty_ml' => $qtyMl,
                'vendors' => $vendorsByItemId[$item->id] ?? [],
            ];
        }

        $filters = [
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseIdFilter,
            'type' => $typeFilter ?: 'all',
            'category_id' => $categoryId,
        ];

        return view('admin.item.stock-report-a4', [
            'rows' => $rows,
            'branches' => $branches,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'filters' => $filters,
            'print_mode' => $request->get('print') === '1',
        ]);
    }

    public function scrapReport(Request $request)
    {
        $viewPerms = ['view_items', 'view_scrap'];
        if (! collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to view scrap report.');
        }

        $query = Item::with(['product_item', 'category'])
            ->where('type', 'scrap');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->orderBy('created_at', 'desc')->limit(1000)->get();

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
        if (! collect($updatePerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to update item prices.');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.total_price' => 'nullable|numeric|min:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
            'items.*.retail_price' => 'nullable|numeric|min:0',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.r_tax_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.amount_adjustment_pct' => 'nullable|numeric|min:-99|max:99',
            'items.*.plat_id' => 'nullable',
            'items.*.amphors' => 'nullable',
            'items.*.company_id' => 'nullable',
            'items.*.gorup' => 'nullable',
            'items.*.p_id' => 'nullable',
            'items.*.part_number_id' => 'nullable',
            'items.*.volt' => 'nullable',
            'items.*.cca' => 'nullable',
            'items.*.gorup_name' => 'nullable|string|max:255',
            'items.*.company_name' => 'nullable|string|max:255',
            'items.*.p_name' => 'nullable|string|max:255',
            'items.*.volt_name' => 'nullable|string|max:255',
            'items.*.cca_name' => 'nullable|string|max:255',
            'items.*.plat_name' => 'nullable|string|max:255',
            'items.*.amphors_name' => 'nullable|string|max:255',
        ]);

        $updated = 0;
        $changed = false;
        foreach ($request->items as $row) {
            $item = Item::find($row['id']);
            if (! $item) {
                continue;
            }
            $changed = false;
            if (isset($row['total_price']) && $row['total_price'] !== '') {
                $item->total_price = $row['total_price'];
                $changed = true;
            }
            if (isset($row['sale_price']) && $row['sale_price'] !== '') {
                $item->sale_price = $row['sale_price'];
                $changed = true;
            }
            if (array_key_exists('retail_price', $row)) {
                $item->retail_price = $row['retail_price'] !== '' && $row['retail_price'] !== null ? $row['retail_price'] : null;
                $changed = true;
            }
            if (array_key_exists('tax_percentage', $row)) {
                $item->tax_percentage = $row['tax_percentage'] !== '' && $row['tax_percentage'] !== null ? $row['tax_percentage'] : 0;
                $changed = true;
            }
            if (array_key_exists('r_tax_percentage', $row)) {
                $item->r_tax_percentage = $row['r_tax_percentage'] !== '' && $row['r_tax_percentage'] !== null ? $row['r_tax_percentage'] : 0.05;
                $changed = true;
            }
            if (array_key_exists('amount_adjustment_pct', $row)) {
                $item->amount_adjustment_pct = $row['amount_adjustment_pct'] !== '' && $row['amount_adjustment_pct'] !== null ? $row['amount_adjustment_pct'] : null;
                $changed = true;
            }
            if (array_key_exists('plat_id', $row)) {
                $item->plat_id = $row['plat_id'] !== '' && $row['plat_id'] !== null ? $row['plat_id'] : null;
                $changed = true;
            }
            if (array_key_exists('amphors', $row)) {
                $item->amphors = $row['amphors'] !== '' && $row['amphors'] !== null ? $row['amphors'] : null;
                $changed = true;
            }
            if (array_key_exists('company_id', $row)) {
                $item->company_id = $row['company_id'] !== '' && $row['company_id'] !== null ? $row['company_id'] : null;
                $changed = true;
            }
            if (array_key_exists('gorup', $row)) {
                $item->gorup = $row['gorup'] !== '' && $row['gorup'] !== null ? $row['gorup'] : null;
                $changed = true;
            }
            if (array_key_exists('p_id', $row)) {
                $item->p_id = $row['p_id'] !== '' && $row['p_id'] !== null ? $row['p_id'] : null;
                $changed = true;
            }
            if (array_key_exists('part_number_id', $row)) {
                $item->part_number_id = $row['part_number_id'] !== '' && $row['part_number_id'] !== null ? $row['part_number_id'] : null;
                $changed = true;
            }
            if (array_key_exists('volt', $row)) {
                $item->volt = $row['volt'] !== '' && $row['volt'] !== null ? $row['volt'] : null;
                $changed = true;
            }
            if (array_key_exists('cca', $row)) {
                $item->cca = $row['cca'] !== '' && $row['cca'] !== null ? $row['cca'] : null;
                $changed = true;
            }
            if (array_key_exists('gorup_name', $row)) {
                $name = trim((string) ($row['gorup_name'] ?? ''));
                if ($name !== '') {
                    $group = Group::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if (! $group) {
                        $group = Group::create(['name' => $name, 'status' => 'active']);
                    }
                    $item->gorup = $group->id;
                } else {
                    $item->gorup = null;
                }
                $changed = true;
            }
            if (array_key_exists('company_name', $row)) {
                $name = trim((string) ($row['company_name'] ?? ''));
                if ($name !== '') {
                    $company = Company::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if (! $company) {
                        $company = Company::create(['name' => $name, 'status' => 'active']);
                    }
                    $item->company_id = $company->id;
                } else {
                    $item->company_id = null;
                }
                $changed = true;
            }
            if (array_key_exists('p_name', $row)) {
                $name = trim((string) ($row['p_name'] ?? ''));
                if ($name !== '') {
                    $product = Product::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if (! $product) {
                        $product = Product::create(['name' => $name, 'status' => 'active']);
                    }
                    $item->p_id = $product->id;
                } else {
                    $item->p_id = null;
                }
                $changed = true;
            }
            if (array_key_exists('volt_name', $row)) {
                $name = trim((string) ($row['volt_name'] ?? ''));
                if ($name !== '') {
                    $volt = Volt::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if (! $volt) {
                        $volt = Volt::create(['name' => $name, 'status' => 'active']);
                    }
                    $item->volt = $volt->id;
                } else {
                    $item->volt = null;
                }
                $changed = true;
            }
            if (array_key_exists('cca_name', $row)) {
                $name = trim((string) ($row['cca_name'] ?? ''));
                if ($name !== '') {
                    $cca = Cca::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if (! $cca) {
                        $cca = Cca::create(['name' => $name, 'status' => 'active']);
                    }
                    $item->cca = $cca->id;
                } else {
                    $item->cca = null;
                }
                $changed = true;
            }
            if (array_key_exists('plat_name', $row)) {
                $name = trim((string) ($row['plat_name'] ?? ''));
                if ($name !== '') {
                    $plat = Platos::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if (! $plat) {
                        $plat = Platos::create(['name' => $name, 'status' => 'active']);
                    }
                    $item->plat_id = $plat->id;
                } else {
                    $item->plat_id = null;
                }
                $changed = true;
            }
            if (array_key_exists('amphors_name', $row)) {
                $name = trim((string) ($row['amphors_name'] ?? ''));
                if ($name !== '') {
                    $amphor = Amphor::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                    if (! $amphor) {
                        $amphor = Amphor::create(['name' => $name, 'status' => 'active']);
                    }
                    $item->amphors = $amphor->id;
                } else {
                    $item->amphors = null;
                }
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
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }
        $addPerms = ['add_items', 'add_parts', 'add_filters', 'add_break_pad', 'add_oil', 'add_battery', 'add_scrap', 'add_services'];
        if (! collect($addPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            abort(403, 'You do not have permission to create items.');
        }

        try {
            // Only load what's needed at page load — rest loads via AJAX /api/dropdown
            $Categories = Category::whereNull('parent_id')
                ->where('status', 'active')
                ->with('children')
                ->orderBy('name')
                ->get();

            $item_types = Producttype::where('status', 'active')->get(['id', 'name']);
            try {
                $units = Unit::with('baseUnits')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: units load failed', ['message' => $e->getMessage()]);
                $units = collect([]);
            }
            $items = collect([]);

            try {
                $latestItems = Item::with([
                    'item_user', 'item_user.branch', 'product_item:id,name',
                    'category:id,name', 'partnumber_item:id,name', 'company_item:id,name',
                    'quality_item:id,name',
                ])->latest()->take(5)->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: latestItems load failed', ['message' => $e->getMessage()]);
                $latestItems = collect([]);
            }

            $typePermMap = ['parts' => 'add_parts', 'filters' => 'add_filters', 'breakpad' => 'add_break_pad', 'oil' => 'add_oil', 'battery' => 'add_battery', 'scrap' => 'add_scrap', 'services' => 'add_services'];
            $allowedItemTypes = collect($typePermMap)->filter(fn ($perm) => auth()->user()->can($perm) || auth()->user()->can('add_items'))->keys()->values()->all();

            // Load dropdown data from DB (like category) so list persists after save/refresh
            try {
                $Companies = Company::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: Companies load failed', ['message' => $e->getMessage()]);
                $Companies = collect();
            }
            try {
                $product = Product::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: product load failed', ['message' => $e->getMessage()]);
                $product = collect();
            }
            try {
                $groups = Group::orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: groups load failed', ['message' => $e->getMessage()]);
                $groups = collect();
            }
            try {
                $technologies = Technology::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: technologies load failed', ['message' => $e->getMessage()]);
                $technologies = collect();
            }
            try {
                $series = Series::orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: series load failed', ['message' => $e->getMessage()]);
                $series = collect();
            }
            try {
                $qualities = Quality::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: qualities load failed', ['message' => $e->getMessage()]);
                $qualities = collect();
            }
            try {
                $platos = Platos::where('status', 'active')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: platos load failed', ['message' => $e->getMessage()]);
                $platos = collect();
            }
            try {
                $amphors = Amphor::where('status', 'active')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: amphors load failed', ['message' => $e->getMessage()]);
                $amphors = collect();
            }
            try {
                $volts = Volt::where('status', 'active')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: volts load failed', ['message' => $e->getMessage()]);
                $volts = collect();
            }
            try {
                $ccas = Cca::where('status', 'active')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: ccas load failed', ['message' => $e->getMessage()]);
                $ccas = collect();
            }
            try {
                $minspols = Minuspool::where('status', 'active')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: minspols load failed', ['message' => $e->getMessage()]);
                $minspols = collect();
            }

            // Battery sizes for Scrap -> Battery Size dropdown
            try {
                $batterySizes = BatterySize::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: batterySizes load failed', ['message' => $e->getMessage()]);
                $batterySizes = collect();
            }

            $poolDirections = collect();
            $warrenties = collect();
            $grades = collect();
            $brands = collect();
            $milleages = collect();
            $levels = collect();
            $made_ins = collect();
            $formulas = collect();
            $services = collect();
            try {
                $grades = Grade::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: grades load failed', ['message' => $e->getMessage()]);
            }
            try {
                $milleages = Mileage::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: milleages load failed', ['message' => $e->getMessage()]);
            }
            try {
                $levels = Level::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: levels load failed', ['message' => $e->getMessage()]);
            }
            try {
                $brands = Brand::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: brands load failed', ['message' => $e->getMessage()]);
            }
            try {
                $poolDirections = PoolDirection::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: poolDirections load failed', ['message' => $e->getMessage()]);
            }
            try {
                $warrenties = Warrenty::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: warrenties load failed', ['message' => $e->getMessage()]);
            }
            try {
                $made_ins = MadeIn::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: made_ins load failed', ['message' => $e->getMessage()]);
            }
            try {
                $formulas = Formula::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: formulas load failed', ['message' => $e->getMessage()]);
            }
            try {
                $services = Services::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: services load failed', ['message' => $e->getMessage()]);
            }
            try {
                $Vehicals = VehicalType::with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number'])->where('status', 'active')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: Vehicals load failed', ['message' => $e->getMessage()]);
                $Vehicals = collect();
            }
            try {
                $carManufacturers = CarManufacturer::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: carManufacturers load failed', ['message' => $e->getMessage()]);
                $carManufacturers = collect();
            }
            try {
                $carNames = CarName::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: carNames load failed', ['message' => $e->getMessage()]);
                $carNames = collect();
            }
            try {
                $carModels = CarModel::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: carModels load failed', ['message' => $e->getMessage()]);
                $carModels = collect();
            }
            try {
                $carCountries = CarCountry::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: carCountries load failed', ['message' => $e->getMessage()]);
                $carCountries = collect();
            }
            try {
                $engineccs = EngineCc::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: engineccs load failed', ['message' => $e->getMessage()]);
                $engineccs = collect();
            }
            try {
                $Vehis = VehicalType::with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number'])
                    ->where('status', 'active')
                    ->orderBy('id', 'desc')
                    ->take(5)
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: Vehis load failed', ['message' => $e->getMessage()]);
                $Vehis = collect();
            }
            try {
                $partnumbers = PartNumber::where('status', 'active')->orderBy('name')->get();
            } catch (\Throwable $e) {
                Log::warning('items_create: partnumbers load failed', ['message' => $e->getMessage()]);
                $partnumbers = collect();
            }

            // Clear any previous error from failed load so toast does not show again
            session()->forget('error');

            return view('admin.item.create', compact(
                'hideVehicleTable', 'Categories', 'item_types', 'units', 'items', 'latestItems', 'allowedItemTypes',
                'poolDirections', 'warrenties', 'grades', 'brands', 'milleages', 'levels', 'made_ins', 'formulas', 'services',
                'Vehicals', 'carManufacturers', 'carNames', 'carModels', 'carCountries', 'engineccs', 'Vehis', 'partnumbers',
                'Companies', 'product', 'groups', 'technologies', 'qualities', 'platos', 'amphors', 'volts', 'ccas', 'minspols', 'batterySizes', 'series'
            ));
        } catch (\Throwable $e) {
            Log::error('items_create failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Failed to load create page. Please try again.',
                ], 500);
            }

            return redirect()->route('home')->with('error', config('app.debug') ? $e->getMessage() : 'Failed to load create page. Please try again.');
        }
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

        // Normalize empty part_number_id and p_id so validation (nullable|exists) and DB don't fail on ""
        $pn = $request->input('part_number_id');
        $pid = $request->input('p_id');
        if ($pn === '' || (is_string($pn) && trim($pn) === '')) {
            $request->merge(['part_number_id' => null]);
        }
        if ($pid === '' || (is_string($pid) && trim($pid) === '')) {
            $request->merge(['p_id' => null]);
        }
        // Normalize user_id so validation and DB don't fail (items.user_id is required)
        $uid = $request->input('user_id');
        if ($uid === '' || $uid === null || (is_string($uid) && trim($uid) === '')) {
            $request->merge(['user_id' => auth()->id()]);
        }

        // return $request->all();
        // Validate fields first (before transaction)
        $categoriesEmpty = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->count() <= 0;
        $categoryId = $request->input('category_id');
        $categoryName = '';
        if (! empty($categoryId)) {
            $categoryName = (string) (Category::where('id', $categoryId)->value('name') ?? '');
        }
        $isPetrolEngineOilCategory = strtoupper(trim(preg_replace('/\s+/', ' ', $categoryName))) === 'PETROL ENGINE OIL';
        $seriesRequired = ($type === 'battery' || $categoriesEmpty) && ! $isPetrolEngineOilCategory;
        $seriesRule = ($seriesRequired ? 'required' : 'nullable').'|string';

        $validated = $request->validate([
            'bar_code' => 'required|unique:items,bar_code',
            'user_id' => 'nullable|exists:users,id',
            'p_id' => 'nullable|exists:products,id',
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
            'series_id' => $seriesRule,
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
            'scrap_dim_width' => 'nullable|numeric|min:0',
            'scrap_dim_height' => 'nullable|numeric|min:0',
            'scrap_dim_length' => 'nullable|numeric|min:0',
            'scrap_dim_depth' => 'nullable|numeric|min:0',
            'scrap_dim_unit' => 'nullable|string|in:cm,inch',
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
            'part_number_id' => 'nullable|exists:part_numbers,id',
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
            $hasPart = ! empty($partNumberId) && trim((string) $partNumberId) !== '';
            $hasProduct = ! empty($pId) && trim((string) $pId) !== '';
            if (! $hasPart && ! $hasProduct) {
                $err = ['part_number_id' => ['Please select at least one: Part Number or Product Name.'], 'p_id' => ['Please select at least one: Part Number or Product Name.']];
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $err], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors($err);
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
                    $err = ['duplicate' => ['This combination of Category, Quality, Part Number and Company already exists for this type. Please change one value.']];
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $err], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors($err);
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
                    $err = ['scrap_quantity' => ['Quantity is required for count-based scrap items.']];
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $err], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors($err);
                }
            } else {
                $scrapWeight = $request->input('scrap_weight_kg');
                if (empty($scrapWeight) || (is_numeric($scrapWeight) && (float) $scrapWeight <= 0)) {
                    $err = ['scrap_weight_kg' => ['Weight (KG) is required for weight-based scrap items.']];
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $err], 422);
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors($err);
                }
            }
        }

        try {
            DB::beginTransaction();

            $data = $validated;

            // Ensure quality_id and technology are in $data if present in request
            // This handles cases where fields might not be in validated array
            if ($request->has('quality_id') && ! isset($data['quality_id'])) {
                $data['quality_id'] = $request->input('quality_id');
            }
            if ($request->has('technology') && ! isset($data['technology'])) {
                $data['technology'] = $request->input('technology');
            }
            // Ensure short_disc and pro_dis (descriptions) are always passed from request
            $data['short_disc'] = $request->input('short_disc', $data['short_disc'] ?? null);
            $data['pro_dis'] = $request->input('pro_dis', $data['pro_dis'] ?? null);
            // Ensure part_number_id and p_id are saved to DB (same as category_id) so data persists after save
            if ($request->filled('part_number_id')) {
                $data['part_number_id'] = (int) $request->input('part_number_id');
            } else {
                $data['part_number_id'] = null;
            }
            if ($request->filled('p_id')) {
                $data['p_id'] = (int) $request->input('p_id');
            } else {
                $data['p_id'] = null;
            }

            // Required by items table: user_id (name is a virtual accessor from short_disc/pro_dis/partnumber, not a DB column)
            $data['user_id'] = $data['user_id'] ?? auth()->id();
            // Ensure short_disc or pro_dis has a fallback for display; do NOT set $data['name'] - items table has no name column
            if (empty($data['short_disc']) && empty($data['pro_dis'])) {
                if (! empty($data['part_number_id'])) {
                    $partNum = PartNumber::find($data['part_number_id']);
                    $data['short_disc'] = $partNum ? $partNum->name : ($request->input('bar_code') ?? null);
                } elseif (! empty($data['p_id'])) {
                    $product = Product::find($data['p_id']);
                    $data['short_disc'] = $product ? $product->name : ($request->input('bar_code') ?? null);
                } else {
                    $data['short_disc'] = $request->input('bar_code') ?? null;
                }
            }
            unset($data['name']);

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
                    if (isset($data['unit']) && $data['unit'] !== '' && ! is_numeric($data['unit'])) {
                        $data['unit_option'] = null;
                    }
                }
            }

            /* ============================
            ✅ Barcode Generation
            ============================ */
            if ($request->bar_code) {
                $barcode = new DNS1D;
                $barcode->setStorPath(public_path('items/barcodes/'));
                $barcodeImage = $barcode->getBarcodePNG($request->bar_code, 'C128', 2, 70);

                $barcodePath = 'items/barcodes/'.uniqid().'.png';

                if (! file_exists(public_path('items/barcodes'))) {
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
            if (! empty($data['battery_size_id'])) {
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
            } elseif (isset($data['vehical_id']) && ! empty($data['vehical_id']) && is_numeric($data['vehical_id'])) {
                $vehicleIds = [(int) $data['vehical_id']];
            }

            unset($data['vehical_id']);
            $data['vehical_ids'] = $vehicleIds;
            $data['vehical_id'] = $vehicleIds[0] ?? null;

            // items table has no 'name' column (name is a model accessor); prevent insert error
            unset($data['name']);

            /* Create ONE item */
            $item = Item::create($data);

            Log::info('Item created successfully', ['item_id' => $item->id, 'vehicle_ids' => $vehicleIds]);

            DB::commit();

            $vehicleCount = count($vehicleIds);
            $successMessage = $vehicleCount > 0
                ? 'Item created successfully with '.$vehicleCount.' vehicle(s)!'
                : 'Item created successfully!';

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                if ($request->action === 'save_new') {
                    return response()->json([
                        'success' => true,
                        'message' => $successMessage,
                        'items_count' => 1,
                        'redirect' => route('all.items.create.new'),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'items_count' => 1,
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
                    'errors' => $e->errors(),
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
                'data' => $request->except(['image', 'images']),
            ]);

            // Return JSON response for errors in AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create item: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create item: '.$e->getMessage())
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

            'unit_item',
        ])->findOrFail($id);
        $updatePerm = $this->getUpdatePermissionForType($item->type);
        if (! auth()->check() || ! (auth()->user()->can($updatePerm) || auth()->user()->can('update_items'))) {
            abort(403, 'You do not have permission to edit this item.');
        }
        // return $item;
        // All the collections you already had
        $platos = Platos::where('status', 'active')->get();
        $amphors = Amphor::where('status', 'active')->get();
        $lineitems = LineItem::where('status', 'active')->get();
        $Companies = Company::where('status', 'active')->get();
        $Categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('children')
            ->get();
        $packings = Packing::where('status', 'active')->get();
        $scales = Scale::where('status', 'active')->get();
        $Vehicals = VehicalType::with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number'])->where('status', 'active')->get();

        $milleages = Mileage::where('status', 'active')->get();
        $item_types = Producttype::where('status', 'active')->get();
        $units = Unit::where('status', 'active')->get();
        // Optional – car-related dropdowns (only if they exist)
        $carCompanies = CarCompany::where('status', 'active')->get();
        $carNames = CarName::where('status', 'active')->get();
        $carModels = CarModel::where('status', 'active')->get();
        $carCountries = CarCountry::where('status', 'active')->get();
        $carManufacturers = CarManufacturer::where('status', 'active')->get();
        // return $carManufacturers;
        $volts = Volt::where('status', 'active')->get();
        $ccas = Cca::where('status', 'active')->get();
        $minspols = Minuspool::where('status', 'active')->get();
        $technologies = Technology::where('status', 'active')->get();
        $series = Series::orderBy('name')->get();
        $grades = Grade::where('status', 'active')->get();
        $brands = Brand::where('status', 'active')->get();
        $formulas = Formula::where('status', 'active')->get();
        $product = Product::where('status', 'active')->get();
        $qualities = Quality::where('status', 'active')->get();
        $partnumbers = PartNumber::with('part_number_vehical')->where('status', 'active')->get();
        $engineccs = EngineCc::where('status', 'active')->get();
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
            'cca_item',
        ])->latest()->take(5)->get();
        $services = Services::where('status', 'active')->get();
        $groups = Group::where('status', 'active')->get();
        $warrenties = Warrenty::where('status', 'active')->get();
        $made_ins = MadeIn::where('status', 'active')->get();
        $levels = Level::where('status', 'active')->get();
        $batterySizes = BatterySize::where('status', 'active')->orderBy('name')->get();
        // Get latest 5 vehicles - each record already has all year ranges in years JSON column
        $Vehis = VehicalType::with([
            'manutacturer_vehical',
            'model_vehical',
            'engine_vehical',
            'country_vehical',
            'vehical_part_number',
        ])
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->take(5) // Limit to 5 latest vehicles
            ->get()
            ->map(function ($vehicle) {
                // Format year ranges from JSON column and sort them by 'from' year
                $yearRanges = collect($vehicle->years ?? [])
                    ->map(function ($range) {
                        if (isset($range['from']) && isset($range['to'])) {
                            return [
                                'from' => (int) $range['from'],
                                'to' => (int) $range['to'],
                                'display' => $range['from'] == $range['to']
                                    ? (string) $range['from']
                                    : $range['from'].'-'.$range['to'],
                            ];
                        }

                        return null;
                    })
                    ->filter()
                    ->sortBy('from') // Sort by 'from' year in ascending order
                    ->values()
                    ->map(function ($range) {
                        return $range['display'];
                    });

                $vehicle->year_ranges = $yearRanges;
                $vehicle->years = $yearRanges->implode(', ');

                return $vehicle;
            });
        // Full unit option value for dropdown (e.g. "12_8" for CAN 2 Liter) so exact option is selected
        $itemUnitOptionForSelect = null;
        if (! empty($item->unit_option) && is_string($item->unit_option)) {
            $itemUnitOptionForSelect = trim($item->unit_option);
        }
        // Fallback: resolve unit id for edit form (when no unit_option saved)
        $itemUnitIdForSelect = null;
        if (! $itemUnitOptionForSelect && isset($item->unit) && $item->unit !== null && $item->unit !== '') {
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
        } elseif (! $itemUnitOptionForSelect && $item->relationLoaded('unit_item') && $item->unit_item && isset($item->unit_item->id)) {
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
            'series',
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
        $updatePerm = $this->getUpdatePermissionForType($type);
        if (! auth()->check() || ! (auth()->user()->can($updatePerm) || auth()->user()->can('update_items'))) {
            abort(403, 'You do not have permission to update this item.');
        }
        // return $request->all();
        // Validate ONLY fields that exist in $fillable
        $categoryId = $request->input('category_id');
        $categoryName = '';
        if (! empty($categoryId)) {
            $categoryName = (string) (Category::where('id', $categoryId)->value('name') ?? '');
        }
        $isPetrolEngineOilCategory = strtoupper(trim(preg_replace('/\s+/', ' ', $categoryName))) === 'PETROL ENGINE OIL';
        $seriesRule = ((($type === 'battery') && ! $isPetrolEngineOilCategory) ? 'required' : 'nullable').'|string';
        $validated = $request->validate([
            'bar_code' => 'required|unique:items,bar_code,'.$item->id,
            'user_id' => 'nullable|exists:users,id',
            'p_id' => 'nullable|exists:products,id',
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
            'series_id' => $seriesRule,
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
            'scrap_dim_width' => 'nullable|numeric|min:0',
            'scrap_dim_height' => 'nullable|numeric|min:0',
            'scrap_dim_length' => 'nullable|numeric|min:0',
            'scrap_dim_depth' => 'nullable|numeric|min:0',
            'scrap_dim_unit' => 'nullable|string|in:cm,inch',
            'packing_purchase_rate' => 'nullable|numeric|min:0',
            'update_date' => 'nullable|date',
            'rack' => 'nullable|string',
            'supplier' => 'nullable|string',
            'pro_dis' => 'nullable|string',
            'part_number_id' => 'nullable|exists:part_numbers,id',
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
            // Ensure part_number_id and p_id are saved (same as category_id) so data persists after save
            if ($request->has('part_number_id')) {
                $data['part_number_id'] = $request->filled('part_number_id') ? $request->input('part_number_id') : null;
            }
            if ($request->has('p_id')) {
                $data['p_id'] = $request->filled('p_id') ? $request->input('p_id') : null;
            }
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
                    if ($data['unit'] !== '' && ! is_numeric($data['unit'])) {
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
                'request_has_unit' => $request->has('unit'),
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
            if (! empty($data['battery_size_id'])) {
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
                'unit_was_saved' => isset($data['unit']) ? 'YES' : 'NO',
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
                'data' => $request->except(['image', 'images']),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update item: '.$e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update item: '.$e->getMessage())
                ->withInput();
        }
    }

    public function item_show($id)
    {
        $item = Item::with([
            'vehical_item' => function ($query) {
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
            'updated_by_user',
        ])->find($id);
        // return $item;
        if (! $item) {
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
                'vehical_item' => function ($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                },
                'product_item',
                'partnumber_item',
            ])->find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found',
                ], 404);
            }

            if (! $item->vehical_item) {
                return response()->json([
                    'success' => false,
                    'message' => 'No vehicle details found for this item',
                ], 404);
            }

            $vehicle = $item->vehical_item;
            $yearRanges = [];
            if ($vehicle->year_from && $vehicle->year_to) {
                if ($vehicle->year_from == $vehicle->year_to) {
                    $yearRanges[] = $vehicle->year_from;
                } else {
                    $yearRanges[] = $vehicle->year_from.'-'.$vehicle->year_to;
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
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle details', [
                'item_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching vehicle details: '.$e->getMessage(),
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
                'vehical_item' => function ($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                },
                'product_item',
                'partnumber_item',
                'company_item',
                'quality_item',
            ])->find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found',
                ], 404);
            }

            if (! $item->vehical_item) {
                return response()->json([
                    'success' => false,
                    'message' => 'No vehicle details found for this item',
                ], 404);
            }

            $vehicle = $item->vehical_item;

            // Build vehicle information string for AI prompt
            $vehicleInfo = [];
            if ($vehicle->manutacturer_vehical) {
                $vehicleInfo[] = 'Manufacturer: '.$vehicle->manutacturer_vehical->name;
            }
            if ($vehicle->model_vehical) {
                $vehicleInfo[] = 'Model: '.$vehicle->model_vehical->name;
            }
            if ($vehicle->engine_vehical) {
                $vehicleInfo[] = 'Engine: '.$vehicle->engine_vehical->name.' CC';
            }
            if ($vehicle->country_vehical) {
                $vehicleInfo[] = 'Country: '.$vehicle->country_vehical->name;
            }
            if ($vehicle->year_from && $vehicle->year_to) {
                $vehicleInfo[] = 'Year Range: '.$vehicle->year_from.'-'.$vehicle->year_to;
            }
            if ($vehicle->vehical_part_number) {
                $vehicleInfo[] = 'Part Number: '.$vehicle->vehical_part_number->name;
            }

            $itemInfo = [];
            if ($item->product_item) {
                $itemInfo[] = 'Product: '.$item->product_item->name;
            }
            if ($item->partnumber_item) {
                $itemInfo[] = 'Part Number: '.$item->partnumber_item->name;
            }
            if ($item->company_item) {
                $itemInfo[] = 'Company: '.$item->company_item->name;
            }
            if ($item->quality_item) {
                $itemInfo[] = 'Quality: '.$item->quality_item->name;
            }
            if ($item->type) {
                $itemInfo[] = 'Type: '.ucfirst($item->type);
            }

            $vehicleInfoStr = implode(', ', $vehicleInfo);
            $itemInfoStr = implode(', ', $itemInfo);

            // Build AI prompt
            $prompt = "As an automotive service history expert, provide a detailed service history tracker and maintenance recommendations for the following vehicle and part information:\n\n";
            $prompt .= 'Vehicle Details: '.$vehicleInfoStr."\n";
            $prompt .= 'Part Details: '.$itemInfoStr."\n\n";
            $prompt .= "Please provide:\n";
            $prompt .= "1. Recommended service intervals\n";
            $prompt .= "2. Common maintenance tasks\n";
            $prompt .= "3. Potential issues to watch for\n";
            $prompt .= "4. Replacement recommendations\n";
            $prompt .= "5. Service history checklist\n\n";
            $prompt .= 'Format the response in a clear, professional manner with sections and bullet points. Keep it concise but informative.';

            // Call Google Gemini API
            $geminiApiKey = env('GOOGLE_GEMINI_API_KEY');
            if (! $geminiApiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Gemini API key is not configured. Please add GOOGLE_GEMINI_API_KEY to your .env file.',
                ], 500);
            }

            $client = new Client;
            $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key='.$geminiApiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $serviceHistory = $responseData['candidates'][0]['content']['parts'][0]['text'];

                return response()->json([
                    'success' => true,
                    'service_history' => $serviceHistory,
                ]);
            } else {
                Log::error('Unexpected Gemini API response format', [
                    'response' => $responseData,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected response format from AI service',
                ], 500);
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Error calling Gemini API', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error calling AI service: '.$e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error generating service history', [
                'item_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating service history: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getItemsByType($type, Request $request)
    {
        try {
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
                'cca_item',
            ])
                ->where('type', $type)
                ->latest();

            $maxItems = (int) (config('app.max_items_per_request', 1000) ?: 1000);
            if ($request->has('all') && $request->get('all') == 'true') {
                $items = $query->limit($maxItems)->get();
            } else {
                $items = $query->take(5)->get();
            }

            $totalItemsCount = Item::where('type', $type)->count();

            $itemsArray = $items->map(function ($item) {
                try {
                    $branchName = '-';
                    if ($item->item_user) {
                        $branchName = $item->item_user->branch?->branch_name
                            ?? $item->item_user->assignedBranches?->first()?->branch_name
                            ?? '-';
                    }
                    $voltItem = $item->volt_item;
                    $plateItem = $item->plate_item;
                    $amphorsItem = $item->amphors_item;
                    $ccaItem = $item->cca_item;

                    return [
                        'id' => $item->id,
                        'image' => $item->image ? ((str_starts_with((string) $item->image, 'http://') || str_starts_with((string) $item->image, 'https://')) ? $item->image : '/'.ltrim($item->image, '/')) : '/assets/img/media/default.png',
                        'bar_code' => $item->bar_code,
                        'barcode_image' => $item->barcode_image,
                        'user_name' => $item->item_user?->name ?? '-',
                        'branch_name' => $branchName,
                        'product_name' => $item->product_item?->name ?? '-',
                        'type' => $item->type,
                        'is_active' => $item->is_active,
                        'category_name' => $item->category?->name ?? '-',
                        'part_number' => $item->partnumber_item?->name ?? '-',
                        'company_name' => $item->company_item?->name ?? '-',
                        'quality_name' => $item->quality_item?->name ?? '-',
                        'volt_name' => $voltItem ? (str_ends_with((string) $voltItem->name, 'V') ? $voltItem->name : $voltItem->name.'V') : null,
                        'plate_name' => $plateItem ? (str_ends_with((string) $plateItem->name, 'PL') ? $plateItem->name : $plateItem->name.'PL') : null,
                        'amphors_name' => $amphorsItem ? (str_ends_with((string) $amphorsItem->name, 'AH') ? $amphorsItem->name : $amphorsItem->name.'AH') : null,
                        'cca_name' => $ccaItem ? (str_contains((string) $ccaItem->name, 'CCA') ? $ccaItem->name : $ccaItem->name.'CCA') : null,
                        'updated_by_user' => $item->updated_by_user ? ['name' => $item->updated_by_user->name] : null,
                        'last_updated_at' => $item->last_updated_at?->format('d M Y, h:i A'),
                        'updated_at' => $item->updated_at?->format('d M Y, h:i A'),
                        'show_url' => route('item.show', $item->id),
                        'edit_url' => route('item.edit', $item->id),
                        'delete_url' => route('item.delete', $item->id),
                        'duplicate_url' => route('item.duplicate', $item->id),
                    ];
                } catch (\Throwable $e) {
                    Log::warning('getItemsByType: failed to map item', ['item_id' => $item->id ?? null, 'error' => $e->getMessage()]);

                    return [
                        'id' => $item->id,
                        'image' => '/assets/img/media/default.png',
                        'bar_code' => $item->bar_code ?? '',
                        'barcode_image' => $item->barcode_image ?? null,
                        'user_name' => '-',
                        'branch_name' => '-',
                        'product_name' => '-',
                        'type' => $item->type ?? '',
                        'is_active' => true,
                        'category_name' => '-',
                        'part_number' => '-',
                        'company_name' => '-',
                        'quality_name' => '-',
                        'volt_name' => null,
                        'plate_name' => null,
                        'amphors_name' => null,
                        'cca_name' => null,
                        'updated_by_user' => null,
                        'last_updated_at' => null,
                        'updated_at' => null,
                        'show_url' => $item->id ? route('item.show', $item->id) : '#',
                        'edit_url' => $item->id ? route('item.edit', $item->id) : '#',
                        'delete_url' => $item->id ? route('item.delete', $item->id) : '#',
                        'duplicate_url' => $item->id ? route('item.duplicate', $item->id) : '#',
                    ];
                }
            });

            return response()->json([
                'success' => true,
                'items' => $itemsArray,
                'total_count' => $totalItemsCount,
            ]);
        } catch (\Throwable $e) {
            Log::error('getItemsByType failed', ['type' => $type, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load items.',
                'items' => [],
                'total_count' => 0,
            ], 500);
        }
    }

    public function deleteSingleImage($id)
    {
        $item = Item::findOrFail($id);

        if ($item->image) {

            // Remove domain if stored as full URL
            $imagePath = str_replace(url('/').'/', '', $item->image);

            if (file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }
        }

        $item->image = null;
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    public function deleteSingleFromArray(Request $request)
    {
        $item = Item::findOrFail($request->item_id);

        if (! $item->images) {
            return response()->json(['status' => false, 'message' => 'No images found']);
        }

        $images = $item->images;

        // Remove the image from array
        $images = array_values(array_filter($images, function ($img) use ($request) {
            return $img !== $request->image;
        }));

        // Delete the file from folder
        $imagePath = str_replace(url('/').'/', '', $request->image); // convert full URL to relative path
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
        $ids = array_values($request->input('ids', []));
        $isJson = $request->ajax() || $request->wantsJson();

        if (count($ids) === 0) {
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected.',
                    'deactivated' => 0,
                    'blocked_used' => 0,
                    'permission_skipped' => 0,
                    'blocked_items' => [],
                ], 422);
            }

            return back()->with('error', 'No items selected.');
        }

        $deactivated = 0;
        $skippedUsed = 0;
        $permissionSkipped = 0;
        $blockedItems = [];

        foreach ($ids as $id) {
            $item = Item::find($id);
            if (! $item) {
                continue;
            }

            if (! auth()->user()->can($this->getDeletePermissionForType($item->type))) {
                $permissionSkipped++;

                continue;
            }

            // Block deletion if item is used in any transaction/stock ledger.
            if (! $this->isItemDeletableByUsage($item)) {
                $skippedUsed++;

                $counts = $this->getItemUsageCounts((int) $item->id);
                $usedIn = array_filter($counts, fn ($v) => (int) $v > 0);

                $blockedItems[] = [
                    'id' => (int) $item->id,
                    'message' => 'This item cannot be deleted because it is already used in transactions.',
                    'used_in' => $usedIn,
                ];

                continue;
            }

            // Soft delete recommendation: keep history intact by deactivating item.
            $item->is_active = false;
            $item->auto_deactive = true;
            $item->save();
            $deactivated++;
        }

        $message = null;
        if ($deactivated > 0 && ($skippedUsed > 0 || $permissionSkipped > 0)) {
            $message = "{$deactivated} item(s) deactivated successfully. {$skippedUsed} item(s) could not be deleted because they are used in transactions.";
        } elseif ($deactivated > 0) {
            $message = "{$deactivated} item(s) deactivated successfully.";
        } elseif ($skippedUsed > 0) {
            $message = 'No items could be deleted because they are used in transactions.';
        } else {
            $message = $permissionSkipped > 0 ? 'No items could be deleted (permission denied).' : 'No items could be deleted.';
        }

        if ($isJson) {
            return response()->json([
                'success' => $deactivated > 0 && $skippedUsed === 0,
                'message' => $message,
                'deactivated' => $deactivated,
                'blocked_used' => $skippedUsed,
                'permission_skipped' => $permissionSkipped,
                'blocked_items' => array_slice($blockedItems, 0, 20),
            ]);
        }

        if ($deactivated > 0 && $skippedUsed > 0) {
            return back()->with('error', "{$deactivated} item(s) deactivated successfully. {$skippedUsed} item(s) could not be deleted because they are used in transactions.");
        }
        if ($deactivated > 0) {
            return back()->with('success', "{$deactivated} item(s) deactivated successfully.");
        }

        return back()->with('error', $skippedUsed > 0 ? 'No items could be deleted because they are used in transactions.' : 'No items could be deleted (permission denied).');
    }

    /**
     * Bulk update selected items (retail price, cost, sale price, category, is_active).
     * Only fields sent in request are updated.
     */
    public function bulkUpdate(Request $request)
    {
        $updatePerms = ['update_items', 'update_parts', 'update_filters', 'update_break_pad', 'update_oil', 'update_battery', 'update_scrap', 'update_services'];
        if (! collect($updatePerms)->contains(fn ($p) => auth()->user()->can($p))) {
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
            if (! $item || ! auth()->user()->can($this->getUpdatePermissionForType($item->type))) {
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

    /**
     * Toggle item is_active (separate from soft delete / recycle bin).
     */
    public function toggleItemActive(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $this->authorize($this->getUpdatePermissionForType($item->type));

        $newActive = ! ((bool) $item->is_active);
        $item->is_active = $newActive;
        if ($newActive) {
            $item->auto_deactive = false;
        }
        $item->save();

        $wantsJson = $request->expectsJson()
            || $request->ajax()
            || str_contains((string) $request->header('Accept', ''), 'application/json');

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $item->is_active,
                'message' => $item->is_active ? 'Item activated successfully.' : 'Item deactivated successfully.',
            ]);
        }

        return redirect()->back()->with(
            'success',
            $item->is_active ? 'Item activated successfully.' : 'Item deactivated successfully.'
        );
    }

    public function item_delete(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $this->authorize($this->getDeletePermissionForType($item->type));

        $wantsJson = $request->expectsJson()
            || $request->ajax()
            || str_contains((string) $request->header('Accept', ''), 'application/json');

        // Block deletion if item is used anywhere in transactions/stock ledgers.
        if (! $this->isItemDeletableByUsage($item)) {
            $msg = 'This item cannot be deleted because it is already used in transactions.';
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        // Recommended soft-delete behavior: deactivate item (keep history intact).
        $item->is_active = false;
        $item->auto_deactive = true;
        $item->save();

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Item deactivated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Item deactivated successfully.');
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

        // Even permanent delete must be blocked if referenced.
        if (! $this->isItemDeletableByUsage($item)) {
            return redirect()->back()->with('error', 'This item cannot be permanently deleted because it is already used in transactions.');
        }

        $item->forceDelete();

        return redirect()->back()->with('success', 'Item permanently deleted!');
    }

    public function itemduplicate($id)
    {
        $item = Item::findOrFail($id);
        $newItem = $item->replicate();
        $newItem->bar_code = $item->bar_code.'-COPY';
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
        $platos = Platos::where('status', 'active')->get();
        $amphors = Amphor::where('status', 'active')->get();
        $lineitems = LineItem::where('status', 'active')->get();
        $Companies = Company::where('status', 'active')->get();

        // Parent categories with subcategories eager loaded
        $Categories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->with('children')
            ->get();

        $packings = Packing::where('status', 'active')->get();
        $scales = Scale::where('status', 'active')->get();
        $Vehicals = VehicalType::where('status', 'active')->get();
        $milleages = Mileage::where('status', 'active')->get();
        $item_types = Producttype::where('status', 'active')->get();
        $units = Unit::where('status', 'active')->get();

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
            'name' => $company->name,
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
            'name' => $name->name,
        ]);
    }

    public function storeModel(Request $request)
    {
        $rawName = $request->input('name');
        if (! is_string($rawName) || trim($rawName) === '') {
            return response()->json(['success' => false, 'message' => 'Model name is required.'], 422);
        }
        if (mb_strlen($rawName) > 255) {
            return response()->json(['success' => false, 'message' => 'Model name is too long.'], 422);
        }
        if ($request->filled('car_manufacturer_id')) {
            $mid = (int) $request->input('car_manufacturer_id');
            if ($mid > 0 && ! CarManufacturer::query()->whereKey($mid)->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid make.'], 422);
            }
        }

        $normalized = $this->normalizeVehicleMasterName($rawName);
        if ($normalized === '') {
            return response()->json(['success' => false, 'message' => 'Model name is required.'], 422);
        }

        $dup = $this->findCarModelByNormalizedName($normalized);
        if ($dup) {
            return response()->json([
                'success' => false,
                'message' => 'A model with this name already exists (same spelling when spaces and case are ignored).',
                'existing_id' => $dup->id,
                'name' => $dup->name,
            ], 422);
        }

        $model = CarModel::create([
            'name' => $normalized,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'id' => $model->id,
            'name' => $model->name,
        ]);
    }

    public function show_car_model($id)
    {
        return response()->json(CarModel::findOrFail($id));
    }

    public function update_car_model(Request $request, $id)
    {
        $rawName = $request->input('name');
        if (! is_string($rawName) || trim($rawName) === '') {
            return response()->json(['success' => false, 'message' => 'Model name is required.'], 422);
        }
        if (mb_strlen($rawName) > 255) {
            return response()->json(['success' => false, 'message' => 'Model name is too long.'], 422);
        }
        $carmodel = CarModel::findOrFail($id);
        $normalized = $this->normalizeVehicleMasterName($rawName);
        if ($normalized === '') {
            return response()->json(['success' => false, 'message' => 'Model name is required.'], 422);
        }
        $dup = $this->findCarModelByNormalizedName($normalized);
        if ($dup && (int) $dup->id !== (int) $carmodel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Another model already uses this name.',
                'existing_id' => $dup->id,
            ], 422);
        }
        $carmodel->update(['name' => $normalized]);

        return response()->json([
            'success' => true,
            'id' => $carmodel->id,
            'name' => $carmodel->name,
            'message' => 'Car model Update Successfully',
        ]);
    }

    public function destory_car_model($id)
    {
        CarModel::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Car model deleted Successfully',
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
            'name' => $country->name,
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
            'message' => 'Car Country Update Successfully',
        ]);
    }

    public function destory_car_country($id)
    {
        CarCountry::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Car Country deleted Successfully',
        ]);
    }

    public function storeManufacturer(Request $request)
    {
        $rawName = $request->input('name');
        if (! is_string($rawName) || trim($rawName) === '') {
            return response()->json(['success' => false, 'message' => 'Make name is required.'], 422);
        }
        if (mb_strlen($rawName) > 255) {
            return response()->json(['success' => false, 'message' => 'Make name is too long.'], 422);
        }

        $normalized = $this->normalizeVehicleMasterName($rawName);
        if ($normalized === '') {
            return response()->json(['success' => false, 'message' => 'Make name is required.'], 422);
        }

        $dup = $this->findCarManufacturerByNormalizedName($normalized);
        if ($dup) {
            return response()->json([
                'success' => false,
                'message' => 'This make already exists.',
                'existing_id' => $dup->id,
                'name' => $dup->name,
            ], 422);
        }

        $manufacture = CarManufacturer::create([
            'name' => $normalized,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'id' => $manufacture->id,
            'name' => $manufacture->name,
        ]);
    }

    /**
     * JSON: active makes for sales vehicle modal + item fitment (shared master).
     */
    public function vehicleMasterMakes()
    {
        $makes = CarManufacturer::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'makes' => $makes]);
    }

    /**
     * JSON: models for a make — prefers models linked via item fitment (vehical_types), else all active models.
     * context=sale: skip fitment filter so customer vehicles can use any active model (same master table).
     */
    public function vehicleMasterModels(Request $request)
    {
        $query = CarModel::query()
            ->where('status', 'active')
            ->orderBy('name');

        $forSale = $request->query('context') === 'sale';
        $manufacturerId = $request->query('manufacturer_id');
        if (! $forSale && $manufacturerId !== null && $manufacturerId !== '') {
            $mid = (int) $manufacturerId;
            $modelIds = VehicalType::query()
                ->where('car_manufacturer', $mid)
                ->distinct()
                ->pluck('car_model_name')
                ->filter(function ($id) {
                    return $id !== null && $id !== '';
                })
                ->values();
            if ($modelIds->isNotEmpty()) {
                $query->whereIn('id', $modelIds);
            }
        }

        $models = $query->get(['id', 'name']);

        $includeRaw = $request->query('include_model_ids', '');
        if (is_string($includeRaw) && $includeRaw !== '') {
            $extraIds = collect(explode(',', $includeRaw))
                ->map(fn ($v) => (int) trim($v))
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();
            if ($extraIds->isNotEmpty()) {
                $extras = CarModel::query()
                    ->where('status', 'active')
                    ->whereIn('id', $extraIds)
                    ->get(['id', 'name']);
                $models = $models->concat($extras)->unique('id')->sortBy('name')->values();
            }
        }

        return response()->json(['success' => true, 'models' => $models]);
    }

    /** Trim and collapse internal whitespace for vehicle master names. */
    protected function normalizeVehicleMasterName(?string $name): string
    {
        $s = trim((string) $name);

        return preg_replace('/\s+/u', ' ', $s) ?? $s;
    }

    protected function vehicleMasterNameKey(string $normalizedName): string
    {
        return mb_strtolower($normalizedName, 'UTF-8');
    }

    protected function findCarManufacturerByNormalizedName(string $normalizedName): ?CarManufacturer
    {
        $key = $this->vehicleMasterNameKey($normalizedName);

        return CarManufacturer::query()
            ->get()
            ->first(function (CarManufacturer $row) use ($key) {
                return $this->vehicleMasterNameKey($this->normalizeVehicleMasterName($row->name)) === $key;
            });
    }

    protected function findCarModelByNormalizedName(string $normalizedName): ?CarModel
    {
        $key = $this->vehicleMasterNameKey($normalizedName);

        return CarModel::query()
            ->get()
            ->first(function (CarModel $row) use ($key) {
                return $this->vehicleMasterNameKey($this->normalizeVehicleMasterName($row->name)) === $key;
            });
    }

    public function show_car_manufacturer($id)
    {
        return response()->json(CarManufacturer::findOrFail($id));
    }

    public function update_car_manufacturer(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Name is required.',
        ]);

        try {
            $manufacture = CarManufacturer::findOrFail($id);
            $normalized = $this->normalizeVehicleMasterName($request->name);
            if ($normalized === '') {
                return response()->json(['success' => false, 'message' => 'Name is required.'], 422);
            }
            $dup = $this->findCarManufacturerByNormalizedName($normalized);
            if ($dup && (int) $dup->id !== (int) $manufacture->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another make already uses this name.',
                    'existing_id' => $dup->id,
                ], 422);
            }
            $manufacture->update(['name' => $normalized]);

            return response()->json([
                'success' => true,
                'id' => $manufacture->id,
                'name' => $manufacture->name,
                'message' => 'Car Manufacturer Update Successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('update_car_manufacturer: '.$e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Could not update make. '.(config('app.debug') ? $e->getMessage() : 'Please try again.'),
            ], 500);
        }
    }

    public function destory_car_manufacturer($id)
    {
        CarManufacturer::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Car Manufacturer deleted Successfully',
        ]);
    }

    public function getItemsCountByPartNumber($partNumberId)
    {
        $partNumber = PartNumber::find($partNumberId);
        if (! $partNumber) {
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

        // Group by quality_id so counts match exact DB slices (same as product name stats).
        $grouped = [];
        $items->each(function ($item) use (&$grouped) {
            $key = $item->quality_id !== null ? 'id:'.$item->quality_id : 'null';
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'quality_id' => $item->quality_id,
                    'name' => $item->quality_item->name ?? ($item->grade ?: 'Standard'),
                    'count' => 0,
                ];
            }
            $grouped[$key]['count']++;
        });

        $details = [];
        $qualities = [];
        foreach ($grouped as $row) {
            $details[] = $row['count'].' '.$row['name'];
            $qualities[] = [
                'quality_id' => $row['quality_id'],
                'name' => $row['name'],
                'count' => $row['count'],
            ];
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
        if (! $product) {
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

        // Group by quality_id so counts match exact DB slices (and badges open the correct rows).
        $grouped = [];
        $items->each(function ($item) use (&$grouped) {
            $key = $item->quality_id !== null ? 'id:'.$item->quality_id : 'null';
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'quality_id' => $item->quality_id,
                    'name' => $item->quality_item->name ?? ($item->grade ?: 'Standard'),
                    'count' => 0,
                ];
            }
            $grouped[$key]['count']++;
        });

        $details = [];
        $qualities = [];
        foreach ($grouped as $row) {
            $details[] = $row['count'].' '.$row['name'];
            $qualities[] = [
                'quality_id' => $row['quality_id'],
                'name' => $row['name'],
                'count' => $row['count'],
            ];
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

    /**
     * Items for a product (p_id) and a single quality_id (or null quality rows when quality_id is null in query).
     */
    public function getItemsByProductAndQuality(Request $request)
    {
        $productId = (int) $request->query('product_id', 0);
        if ($productId < 1) {
            return response()->json(['success' => false, 'message' => 'Invalid product.'], 422);
        }

        $product = Product::find($productId);
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $rawQuality = $request->query('quality_id');
        $useNullQuality = $rawQuality === null || $rawQuality === '' || strtolower((string) $rawQuality) === 'null';

        $query = Item::with([
            'item_user',
            'product_item',
            'category',
            'subcategory',
            'partnumber_item',
            'company_item',
            'quality_item',
        ])->where('p_id', $productId);

        if ($useNullQuality) {
            $query->whereNull('quality_id');
        } else {
            $qualityId = (int) $rawQuality;
            if ($qualityId < 1) {
                return response()->json(['success' => false, 'message' => 'Invalid quality.'], 422);
            }
            $query->where('quality_id', $qualityId);
        }

        $items = $query->latest()->get();

        $qualityLabel = trim((string) $request->query('quality_label', ''));
        if ($qualityLabel === '') {
            $qualityLabel = $useNullQuality ? 'No quality set' : ($items->first()?->quality_item?->name ?? '—');
        }

        return response()->json([
            'success' => true,
            'product_name' => $product->name,
            'quality_name' => $qualityLabel,
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image' => $item->image ? ((str_starts_with($item->image, 'http://') || str_starts_with($item->image, 'https://')) ? $item->image : '/'.ltrim($item->image, '/')) : '/assets/img/media/default.png',
                    'user_name' => $item->item_user->name ?? '-',
                    'product_name' => $item->product_item->name ?? '-',
                    'type' => $item->type,
                    'bar_code' => $item->bar_code ?? '',
                    'is_active' => $item->is_active,
                    'category_name' => $item->category->name ?? 'N/A',
                    'subcategory_name' => $item->subcategory->name ?? '',
                    'part_number_name' => $item->partnumber_item->name ?? 'N/A',
                    'company_name' => $item->company_item->name ?? 'N/A',
                    'quality_name' => $item->quality_item->name ?? ($item->grade ?? 'N/A'),
                    'show_url' => route('item.show', $item->id),
                    'edit_url' => route('item.edit', $item->id),
                    'delete_url' => route('item.delete', $item->id),
                ];
            }),
            'total' => $items->count(),
        ]);
    }

    /**
     * Items for a part number and a single quality_id (or null quality rows).
     */
    public function getItemsByPartNumberAndQuality(Request $request)
    {
        $partNumberId = (int) $request->query('part_number_id', 0);
        if ($partNumberId < 1) {
            return response()->json(['success' => false, 'message' => 'Invalid part number.'], 422);
        }

        $partNumber = PartNumber::find($partNumberId);
        if (! $partNumber) {
            return response()->json(['success' => false, 'message' => 'Part number not found.'], 404);
        }

        $rawQuality = $request->query('quality_id');
        $useNullQuality = $rawQuality === null || $rawQuality === '' || strtolower((string) $rawQuality) === 'null';

        $query = Item::with([
            'item_user',
            'product_item',
            'category',
            'subcategory',
            'partnumber_item',
            'company_item',
            'quality_item',
        ])->where('part_number_id', $partNumberId);

        if ($useNullQuality) {
            $query->whereNull('quality_id');
        } else {
            $qualityId = (int) $rawQuality;
            if ($qualityId < 1) {
                return response()->json(['success' => false, 'message' => 'Invalid quality.'], 422);
            }
            $query->where('quality_id', $qualityId);
        }

        $items = $query->latest()->get();

        $qualityLabel = trim((string) $request->query('quality_label', ''));
        if ($qualityLabel === '') {
            $qualityLabel = $useNullQuality ? 'No quality set' : ($items->first()?->quality_item?->name ?? '—');
        }

        return response()->json([
            'success' => true,
            'part_number_name' => $partNumber->name,
            'quality_name' => $qualityLabel,
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image' => $item->image ? ((str_starts_with($item->image, 'http://') || str_starts_with($item->image, 'https://')) ? $item->image : '/'.ltrim($item->image, '/')) : '/assets/img/media/default.png',
                    'user_name' => $item->item_user->name ?? '-',
                    'product_name' => $item->product_item->name ?? '-',
                    'type' => $item->type,
                    'bar_code' => $item->bar_code ?? '',
                    'is_active' => $item->is_active,
                    'category_name' => $item->category->name ?? 'N/A',
                    'subcategory_name' => $item->subcategory->name ?? '',
                    'part_number_name' => $item->partnumber_item->name ?? 'N/A',
                    'company_name' => $item->company_item->name ?? 'N/A',
                    'quality_name' => $item->quality_item->name ?? ($item->grade ?? 'N/A'),
                    'show_url' => route('item.show', $item->id),
                    'edit_url' => route('item.edit', $item->id),
                    'delete_url' => route('item.delete', $item->id),
                ];
            }),
            'total' => $items->count(),
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
            'quality_item',
        ])
            ->where('part_number_id', $partNumberId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image' => $item->image ? ((str_starts_with($item->image, 'http://') || str_starts_with($item->image, 'https://')) ? $item->image : '/'.ltrim($item->image, '/')) : '/assets/img/media/default.png',
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
            'total' => $items->count(),
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
                    'message' => 'No items selected.',
                ], 400);
            }

            if (! $phoneNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number is required.',
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
                'vehical_item' => function ($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                },
            ])
                ->whereIn('id', $itemIds)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found.',
                ], 404);
            }

            // Generate PDF
            $pdf = Pdf::loadView('admin.item.whatsapp-pdf', compact('items', 'phoneNumber'));
            $pdf->setPaper('a4', 'portrait');

            // Save PDF temporarily
            $filename = 'items_'.time().'_'.rand(1000, 9999).'.pdf';
            $pdfPath = public_path('temp_pdfs/'.$filename);

            // Create directory if it doesn't exist
            if (! file_exists(public_path('temp_pdfs'))) {
                mkdir(public_path('temp_pdfs'), 0755, true);
            }

            $pdf->save($pdfPath);

            // Generate PDF URL
            $pdfUrl = url('temp_pdfs/'.$filename);

            // Create WhatsApp message
            $message = "📦 *Product Details*\n\n";

            // Add item names
            foreach ($items as $index => $item) {
                $itemName = $item->product_item->name ?? 'Item #'.($index + 1);
                $message .= '• '.$itemName."\n";
            }

            $message .= "\n📄 *Full Product Specification PDF:*\n";
            $message .= $pdfUrl;
            $message .= "\n\n_This PDF contains complete product details, specifications, and pricing information._";

            return response()->json([
                'success' => true,
                'message' => $message,
                'pdf_url' => $pdfUrl,
                'pdf_path' => $pdfPath,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp PDF Generation Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate Price List PDF for WhatsApp share (same filters as price list page).
     */
    public function generatePriceListWhatsAppPdf(Request $request)
    {
        $viewPerms = ['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'];
        if (! collect($viewPerms)->contains(fn ($p) => auth()->user()->can($p))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $phoneNumber = $request->input('phone_number');
        if (! $phoneNumber) {
            return response()->json(['success' => false, 'message' => 'Phone number is required.'], 400);
        }

        try {
            $query = Item::with([
                'category', 'unit_item', 'plate_item', 'amphors_item', 'volt_item', 'cca_item',
                'company_item', 'product_item', 'partnumber_item', 'group_item',
                'updated_by_user.branch', 'priceUpdatedBranch',
            ])
                ->orderBy('category_id')
                ->orderBy('short_disc');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }
            if ($request->filled('technology_id')) {
                $query->where('technology', $request->technology_id);
            }

            $items = $query->get();

            $currentBranchName = session('selected_branch_name');
            if (! $currentBranchName && auth()->user() && auth()->user()->branch_id) {
                $currentBranchName = \App\Models\Branch::where('id', auth()->user()->branch_id)->value('branch_name');
            }

            $pdf = Pdf::loadView('admin.item.price-list-pdf', compact('items', 'currentBranchName'));
            $pdf->setPaper('a4', 'landscape');

            $filename = 'price_list_'.time().'_'.rand(1000, 9999).'.pdf';
            $pdfPath = public_path('temp_pdfs/'.$filename);
            if (! file_exists(public_path('temp_pdfs'))) {
                mkdir(public_path('temp_pdfs'), 0755, true);
            }
            $pdf->save($pdfPath);

            $pdfUrl = url('temp_pdfs/'.$filename);
            $message = "📋 *Price List*\n\n";
            $message .= '📄 PDF: '.$pdfUrl."\n\n";
            $message .= '_Download the PDF and attach it in this chat, then press Send._';

            return response()->json([
                'success' => true,
                'message' => $message,
                'pdf_url' => $pdfUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Price List WhatsApp PDF Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: '.$e->getMessage(),
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
                'vehical_item' => function ($query) {
                    $query->with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number']);
                },
            ])
                ->findOrFail($id);

            // Generate PDF
            $pdf = Pdf::loadView('admin.item.product-specification-pdf', compact('item'));
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download('product-specification-'.($item->bar_code ?? $item->id).'-'.time().'.pdf');

        } catch (\Exception $e) {
            Log::error('Product Specification PDF Generation Error: '.$e->getMessage());

            return back()->with('error', 'Failed to generate PDF: '.$e->getMessage());
        }
    }

    public function checkBarcode(Request $request)
    {
        $barCode = $request->input('bar_code');

        if (! $barCode) {
            return response()->json([
                'exists' => false,
                'message' => 'Barcode is required',
            ], 400);
        }

        $exists = Item::where('bar_code', $barCode)->exists();

        return response()->json([
            'exists' => $exists,
            'bar_code' => $barCode,
        ]);
    }
}
