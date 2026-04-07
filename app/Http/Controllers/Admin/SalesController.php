<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\CarManufacturer;
use App\Models\CarModel;
use App\Models\PartNumber;
use App\Models\Technology;
use App\Models\Grade;
use App\Models\Volt;
use App\Models\Cca;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemWarrantyProof;
use App\Models\SaleItemWarrantyCode;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\ClaimWarehouseItem;
use App\Models\ClaimSendReversal;
use App\Models\PurchaseItem;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Models\SalePayment;
use App\Models\CustomerCar;
use App\Models\Mileage;
use App\Models\TemporaryItemNameSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Support\ItemProductTypeLabel;
use App\Support\VehicleYearSearch;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    private function isRetailCustomerId(int $customerId): bool
    {
        $customer = Customer::find($customerId);
        return ($customer && ($customer->customer_type ?? 'retail') === 'retail');
    }

    /**
     * Parse oil interval KM from mileage label (e.g. "5000 KM", "5,000", "5000").
     */
    private function parseOilIntervalKmFromMileageLabel(?string $label): ?float
    {
        $name = $label !== null ? trim($label) : '';
        if ($name === '') {
            return null;
        }
        if (preg_match('/([\d,\.]+)\s*(?:km|kilometer|kilometre|kms?)\b/i', $name, $m)) {
            $n = (float) str_replace(',', '', $m[1]);

            return $n > 0 ? $n : null;
        }
        if (preg_match('/^[\d,\.\s]+$/', $name) && preg_match('/\d/', $name)) {
            $n = (float) str_replace([',', ' '], '', $name);

            return $n > 0 ? $n : null;
        }

        return null;
    }

    /**
     * First oil interval KM from cart lines: request mileage_name / mileage_id, then Item::mileage_item.
     */
    private function firstOilMileageIntervalKmFromItems(array $itemRows): ?float
    {
        if ($itemRows === []) {
            return null;
        }
        $ids = array_values(array_unique(array_filter(array_map(static function ($row) {
            return isset($row['item_id']) ? (int) $row['item_id'] : 0;
        }, $itemRows))));
        $itemsKeyed = $ids === [] ? collect() : Item::with('mileage_item')->whereIn('id', $ids)->get()->keyBy('id');

        $mileageIds = array_values(array_unique(array_filter(array_map(static function ($row) {
            if (! isset($row['mileage_id']) || $row['mileage_id'] === '' || $row['mileage_id'] === null) {
                return 0;
            }

            return (int) $row['mileage_id'];
        }, $itemRows))));
        $mileagesKeyed = $mileageIds === [] ? collect() : Mileage::whereIn('id', $mileageIds)->get()->keyBy('id');

        foreach ($itemRows as $row) {
            $mileageNameRow = isset($row['mileage_name']) ? trim((string) $row['mileage_name']) : '';
            if ($mileageNameRow !== '') {
                $fromName = $this->parseOilIntervalKmFromMileageLabel($mileageNameRow);
                if ($fromName !== null) {
                    return $fromName;
                }
            }
            $mid = isset($row['mileage_id']) && $row['mileage_id'] !== '' && $row['mileage_id'] !== null
                ? (int) $row['mileage_id'] : 0;
            if ($mid > 0) {
                $mRow = $mileagesKeyed->get($mid);
                if ($mRow) {
                    $fromDb = $this->parseOilIntervalKmFromMileageLabel((string) ($mRow->name ?? ''));
                    if ($fromDb !== null) {
                        return $fromDb;
                    }
                }
            }

            $iid = isset($row['item_id']) ? (int) $row['item_id'] : 0;
            if ($iid <= 0) {
                continue;
            }
            $item = $itemsKeyed->get($iid);
            if (! $item || ! $item->mileage_item) {
                continue;
            }
            $fromItem = $this->parseOilIntervalKmFromMileageLabel((string) ($item->mileage_item->name ?? ''));
            if ($fromItem !== null) {
                return $fromItem;
            }
        }

        return null;
    }

    private function normalizeWarrantyCode(?string $code): ?string
    {
        $c = $code !== null ? trim($code) : '';
        if ($c === '') return null;
        return mb_strtolower($c);
    }

    private function isCodeLikeWarrantyValue(string $code): bool
    {
        $c = strtoupper(trim($code));
        if ($c === '') return false;
        // digits only (2..12)
        if (preg_match('/^[0-9]{2,12}$/', $c)) return true;
        // alphanumeric (4..15) but not letters-only
        if (preg_match('/^[A-Z0-9]{4,15}$/', $c) && !preg_match('/^[A-Z]+$/', $c)) return true;
        // hyphen/underscore codes (4..20) must contain separator
        if (preg_match('/^[A-Z0-9\-_]{4,20}$/', $c) && (str_contains($c, '-') || str_contains($c, '_'))) return true;
        return false;
    }

    private function extractWarrantyCodesFromProofPayload(array $p): array
    {
        $out = [];
        $push = function (?string $val, bool $isFinal, string $source) use (&$out) {
            $v = $val !== null ? trim((string) $val) : '';
            if ($v === '') return;
            if (!$this->isCodeLikeWarrantyValue($v)) return;
            $out[] = ['code' => $v, 'is_final' => $isFinal, 'source' => $source];
        };

        $push($p['final_code'] ?? null, true, 'final');
        // legacy field used by frontend submit as preferred value
        $push($p['code'] ?? null, false, 'legacy');
        $push($p['scanned_code'] ?? null, false, 'scanned');

        $cands = $p['extracted_codes'] ?? null;
        if (is_array($cands)) {
            foreach ($cands as $c) {
                $push(is_scalar($c) ? (string) $c : null, false, 'ocr');
            }
        }
        return $out;
    }

    private function saveWarrantyProofImageDataUrl(string $dataUrl): string
    {
        if (!str_starts_with($dataUrl, 'data:image/')) {
            throw new \InvalidArgumentException('Invalid image data.');
        }
        $parts = explode(',', $dataUrl, 2);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Invalid image data.');
        }
        $meta = $parts[0];
        $b64 = $parts[1];

        $ext = 'jpg';
        if (str_contains($meta, 'image/png')) $ext = 'png';
        elseif (str_contains($meta, 'image/webp')) $ext = 'webp';
        elseif (str_contains($meta, 'image/jpeg') || str_contains($meta, 'image/jpg')) $ext = 'jpg';

        $binary = base64_decode($b64, true);
        if ($binary === false) {
            throw new \InvalidArgumentException('Invalid base64 image.');
        }

        $dir = public_path('Warranty_proofs');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $binary);
        return 'Warranty_proofs/' . $filename;
    }

    public function all_sales()
    {
        $sales = Sale::with([
            'customer',
            'user',
            'payments.paymentMethod',
            'saleItems.item.partnumber_item',
            'saleItems.item.category',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.sales.index', compact('sales'));
    }
    
    public function create_sale_new(){
        $customers = Customer::with('customerCars', 'branch')->orderBy('created_at', 'desc')->get();
        $branches = \App\Models\Branch::where('status', 'active')->orderBy('branch_name', 'asc')->get();
        $units = \App\Models\Unit::all();
        $suppliers = \App\Models\Supplier::orderBy('created_at', 'desc')->get();
        $mileages = Mileage::orderBy('name')->get();
        $temporaryItemId = Item::where('bar_code', '__SALE_TEMPORARY__')->value('id');

        return view('admin.sales.create-new', compact('customers', 'branches', 'units', 'suppliers', 'mileages', 'temporaryItemId'));
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
            return response()->json(['number' => '00001']);
        }
        
        // Get the last estimate number for this branch (separate EST series)
        $lastEstimate = Sale::where('branch_id', $branchId)
            ->where('status', 'estimate')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 1; // First estimate = 00001
        if ($lastEstimate && $lastEstimate->reference && preg_match('/EST\s*#?\s*(\d+)/i', $lastEstimate->reference, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } elseif ($lastEstimate) {
            $nextNumber = Sale::where('branch_id', $branchId)->where('status', 'estimate')->count() + 1;
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
            return response()->json(['number' => '00001']);
        }
        
        // Get the last sale order number for this branch (separate SO series)
        $lastSaleOrder = Sale::where('branch_id', $branchId)
            ->where('status', 'sale_order')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 1; // First sale order = 00001
        if ($lastSaleOrder && $lastSaleOrder->reference && preg_match('/SO\s*#?\s*(\d+)/i', $lastSaleOrder->reference, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } elseif ($lastSaleOrder) {
            $nextNumber = Sale::where('branch_id', $branchId)->where('status', 'sale_order')->count() + 1;
        }
        
        return response()->json([
            'number' => str_pad($nextNumber, 5, '0', STR_PAD_LEFT)
        ]);
    }

    /**
     * Get next invoice (sale) number for this branch (separate INV series)
     */
    public function getNextInvoiceNumber()
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['number' => '00001', 'message' => 'Please select a branch for accurate numbering']);
        }
        $next = $this->getNextReferenceNumberForBranchAndStatus($branchId, 'pending', 'INV');
        return response()->json(['number' => $next]);
    }

    /**
     * Get next reference number for a branch and status (INV / EST / SO) - each has its own series.
     */
    private function getNextReferenceNumberForBranchAndStatus(int $branchId, string $status, string $prefix): string
    {
        $last = Sale::where('branch_id', $branchId)
            ->where('status', $status)
            ->orderBy('id', 'desc')
            ->first();
        $nextNumber = 1;
        $pattern = '/(' . preg_quote($prefix, '/') . '\s*#?\s*(\d+))/i';
        if ($last && $last->reference && preg_match($pattern, $last->reference, $matches)) {
            $nextNumber = (int) $matches[2] + 1;
        } elseif ($last) {
            $nextNumber = Sale::where('branch_id', $branchId)->where('status', $status)->count() + 1;
        }
        return str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
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

        // Warranty-code traceability search (used heavily in Claim In / barcode search).
        $searchTrim = trim((string) $search);
        if ($searchTrim !== '') {
            $norm = $this->normalizeWarrantyCode($searchTrim);
            if ($norm) {
                $wq = SaleItemWarrantyCode::query()
                    ->with([
                        'sale:id,reference,sale_date,customer_id,branch_id',
                        'sale.branch:id,branch_name',
                        'customer:id,name',
                        'item:id,pro_dis,bar_code,type',
                        'warehouse:id,warehouse_name',
                        'proof:id,proof_image,proof_code',
                    ])
                    ->where('code_norm', $norm)
                    ->orderByDesc('is_final')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get();

                foreach ($wq as $row) {
                    $sale = $row->sale;
                    $cust = $row->customer;
                    $item = $row->item;
                    $branchName = optional($sale?->branch)->branch_name;
                    $warehouseName = optional($row->warehouse)->warehouse_name;
                    $hasProof = (bool) optional($row->proof)->proof_image || (bool) optional($row->proof)->proof_code;
                    $results[] = [
                        'type' => 'warranty_code',
                        'matched_code' => $row->code,
                        'matched_by' => $row->is_final ? 'final' : ($row->source ?: 'code'),
                        'sale_id' => $row->sale_id,
                        'sale_item_id' => $row->sale_item_id,
                        'reference' => $sale?->reference,
                        'sale_date' => $sale?->sale_date,
                        'customer_name' => $cust?->name,
                        'branch_name' => $branchName,
                        'warehouse_name' => $warehouseName,
                        'item_id' => $row->item_id,
                        'item_name' => $item?->pro_dis,
                        'bar_code' => $item?->bar_code,
                        'has_proof' => $hasProof,
                        'display' => ($item?->pro_dis ?: 'Item') . ' (Matched by warranty code: ' . $row->code . ')',
                    ];
                }
            }
        }
        
        // Load all relationships for efficient searching and display
        $query = Item::with([
            'partnumber_item',
            'vehical_item.manutacturer_vehical',
            'vehical_item.model_vehical',
            'category',
            'subcategory',
            'unit_item', // Load unit relationship to get unit name
            'unit_item.baseUnits',
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
        ])->where('is_active', 1)
            ->where(function ($q) {
                $q->where('is_temporary', false)->orWhereNull('is_temporary');
            });

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
                    $subQ->where(function ($vq) use ($term) {
                        $vq->where('year_from', 'LIKE', "%{$term}%")
                            ->orWhere('year_to', 'LIKE', "%{$term}%");
                        if (VehicleYearSearch::isPlausibleYearTerm($term)) {
                            $vq->orWhere(function ($yq) use ($term) {
                                VehicleYearSearch::whereVehicleRowContainsYear($yq, (int) $term);
                            });
                        }
                        if (preg_match('/^(\d{4})\s*-\s*(\d{4})$/', trim($term), $m)) {
                            $low = min((int) $m[1], (int) $m[2]);
                            $high = max((int) $m[1], (int) $m[2]);
                            $vq->orWhere(function ($yq) use ($low, $high) {
                                VehicleYearSearch::whereVehicleRowOverlapsYearRange($yq, $low, $high);
                            });
                        }
                    })
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

        // Sale context: only items that have a sale price / are meant for sale
        if ($request->boolean('for_sale')) {
            $query->where(function ($q) {
                $q->where('sale_price', '>', 0)
                  ->orWhere('price_per_unit', '>', 0)
                  ->orWhere('total_price', '>', 0);
            });
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

        $entryType = $request->get('entry_type'); // e.g. sale, claim, scrap
        if (is_array($entryType)) {
            $entryType = $entryType[0] ?? null;
        }
        $entryType = $entryType ? strtolower(trim((string) $entryType)) : null;
        $branchId = $request->get('branch_id');
        if (is_array($branchId)) {
            $branchId = $branchId[0] ?? null;
        }
        $branchId = ($branchId !== null && $branchId !== '') ? (string) $branchId : null;

        // Preload warehouses for branch so we can compute branch-scoped stock
        // (used for search badge consistency with stock-status list).
        $warehouseIdsForBranch = [];
        if ($branchId !== null) {
            $warehouseIdsForBranch = Warehouse::where('branch_id', $branchId)->pluck('id')->all();
        }

        // Entry-type based item filtering for search results:
        // - Scrap modes: only show items where items.type === 'scrap'
        // - Sale/Return modes: hide scrap + claim-type items
        // - Claim mode: allow all (claim stock comes from ClaimWarehouseItem separately)
        $isScrapMode = in_array($entryType, ['scrap', 'scrap_in', 'scrap_sale'], true);
        $isSaleLikeMode = in_array($entryType, ['sale', 'return'], true);

        if ($isScrapMode) {
            $items = $items->where('type', 'scrap')->values();
        } elseif ($isSaleLikeMode) {
            $blockedTypes = ['scrap', 'claim', 'claim_in', 'claim_send'];
            $items = $items->reject(function ($item) use ($blockedTypes) {
                $t = strtolower(trim((string) ($item->type ?? '')));
                return in_array($t, $blockedTypes, true);
            })->values();

            // Additional rule:
            // Hide "claim items" from normal sale search when the item already has claim_stock
            // in ClaimWarehouseItem for the selected branch (or globally if no branch).
            // This prevents claim-stock-only items from appearing in "ADD SALE ITEM".
            $itemIds = $items->pluck('id')->values()->all();
            if (!empty($itemIds)) {
                $claimQtyByItem = ClaimWarehouseItem::select('item_id', DB::raw('SUM(quantity) as claim_qty'))
                    ->whereIn('item_id', $itemIds)
                    ->when($branchId !== null && $branchId !== '', function ($q) use ($branchId) {
                        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
                        if ($warehouseIds->isNotEmpty()) {
                            $q->whereIn('warehouse_id', $warehouseIds);
                        }
                    })
                    ->groupBy('item_id')
                    ->pluck('claim_qty', 'item_id');

                $items = $items->reject(function ($item) use ($claimQtyByItem) {
                    // Only hide those items that have claim stock but NO normal on-hand.
                    // This way "new/normal items" (on_hand > 0) remain visible in ADD SALE ITEM.
                    $claimQty = (float) ($claimQtyByItem[$item->id] ?? 0);
                    $onHand = (float) ($item->on_hand ?? 0);
                    return abs($claimQty) > 0.000001 && $onHand <= 0.000001;
                })->values();
            }
        }

        // Return items directly from items table (no warehouse filtering)
        foreach ($items as $item) {
            // Get packing size for carton/loose calculation
            $packingSize = floatval($item->packing ?? 1);
            // Default stock from items table (global).
            $onHand = floatval($item->on_hand ?? 0);
            // For claim search, show claim stock (branch-scoped if branch_id given; else total)
            if ($entryType === 'claim') {
                $claimQuery = ClaimWarehouseItem::where('item_id', $item->id);
                if ($branchId !== null && $branchId !== '') {
                    $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
                    if ($warehouseIds->isNotEmpty()) {
                        $claimQuery->whereIn('warehouse_id', $warehouseIds);
                    }
                }
                $onHand = (float) $claimQuery->sum('quantity');
                Log::info('Claim search readback', [
                    'item_id' => $item->id,
                    'branch_id' => $branchId,
                    'claim_stock_sum' => $onHand,
                ]);
            } else {
                // For normal/scrap search: use warehouse_items sum inside selected branch
                // so badge matches #stock-status-list dropdown availability.
                if (!empty($warehouseIdsForBranch)) {
                    $onHand = (float) WarehouseItem::where('item_id', $item->id)
                        ->whereIn('warehouse_id', $warehouseIdsForBranch)
                        ->sum('quantity');
                }
            }
            $cartons = floor($onHand / $packingSize);
            $loose = fmod($onHand, $packingSize);
            
            // Price calculations
            $salePrice = floatval($item->sale_price ?? 0);
            $packingPurchaseRate = floatval($item->packing_purchase_rate ?? 0);
            $totalPrice = floatval($item->total_price ?? 0);
            $pricePerUnit = floatval($item->price_per_unit ?? 0);
            
            // Unit display and liter-per-can for oil/can (same as purchase search)
            $unitInfo = $this->getItemUnitDisplayForSearch($item);
            
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
                'unit_display' => $unitInfo['unit_display'],
                'liter_per_can' => $unitInfo['liter_per_can'],
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
     * For sale search: get unit display string and liter-per-can for item (oil/can display in first line).
     * Same logic as PurchaseController::getItemUnitDisplayForSearch.
     */
    private function getItemUnitDisplayForSearch(Item $item): array
    {
        $unitName = $item->unit_item ? trim($item->unit_item->name ?? $item->unit_item->short_name ?? '') : '';
        $literPerCan = null;
        // 0) From item's unit_option (selected conversion e.g. "12_8_4" => 4 Liter)
        $unitOption = $item->unit_option ? trim((string) $item->unit_option) : '';
        if ($unitOption !== '' && strpos($unitOption, '_') !== false) {
            $parts = explode('_', $unitOption);
            $lastPart = end($parts);
            if (is_numeric($lastPart) && (float) $lastPart > 0) {
                $literPerCan = (float) $lastPart;
            }
        }
        // 1) From unit name e.g. "Can - 4 Liter" or "Can 4L"
        if ($literPerCan === null && preg_match('/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)\b/i', $unitName, $m)) {
            $literPerCan = (float) $m[1];
        } elseif ($literPerCan === null && preg_match('/\b(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i', $unitName, $m)) {
            $literPerCan = (float) $m[1];
        }
        // 2) From item filling (per-can liters)
        if ($literPerCan === null && $item->filling !== null && $item->filling !== '' && !is_nan((float) $item->filling)) {
            $literPerCan = (float) $item->filling;
        }
        // 3) From unit's base unit (e.g. Can has base unit Liter with multiplier 4)
        if ($literPerCan === null && $item->unit_item && $item->unit_item->relationLoaded('baseUnits')) {
            foreach ($item->unit_item->baseUnits as $base) {
                $baseName = trim($base->name ?? $base->short_name ?? '');
                if (stripos($baseName, 'liter') !== false || stripos($baseName, 'ltr') !== false || $baseName === 'L') {
                    $mult = $base->pivot->multiplier ?? $base->pivot->getAttribute('multiplier') ?? null;
                    if ($mult !== null && $mult !== '' && (float) $mult > 0) {
                        $literPerCan = (float) $mult;
                        break;
                    }
                }
            }
        }
        $unitDisplay = $unitName;
        if ($literPerCan > 0) {
            $literal = (floor($literPerCan) == $literPerCan) ? (int) $literPerCan : number_format($literPerCan, 1, '.', '');
            $canLiteral = 'Can - ' . $literal . ' Liter';
            if ($unitDisplay === '' || $unitDisplay === 'Unit' || stripos($unitDisplay, 'liter') === false) {
                $unitDisplay = $canLiteral;
            }
        }
        return ['unit_display' => $unitDisplay ?: '', 'liter_per_can' => $literPerCan];
    }
    
    /**
     * Get item details for sales
     */
    public function getItemDetails($id)
    {
        $item = Item::with(['partnumber_item', 'category', 'subcategory', 'product_item', 'vehical_item.manutacturer_vehical', 'vehical_item.model_vehical', 'unit_item', 'unit_item.baseUnits', 'mileage_item', 'warrenty_item', 'grade_item', 'technology_item', 'company_item', 'quality_item'])->findOrFail($id);
        $rawEntry = request()->get('entry_type');
        $entryType = is_array($rawEntry) ? (string) ($rawEntry[0] ?? '') : (string) $rawEntry;
        $entryType = strtolower(trim($entryType)) ?: null;

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
        if ($entryType === 'claim') {
            $onHand = (float) ClaimWarehouseItem::where('item_id', $item->id)->sum('quantity');
            Log::info('Claim getItemDetails readback', ['item_id' => $item->id, 'claim_stock_sum' => $onHand]);
        }
        
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
        
        $unitInfo = $this->getItemUnitDisplayForSearch($item);
        
        $mileageId = $item->mileage_item ? $item->mileage_item->id : null;
        $mileageName = $item->mileage_item ? trim($item->mileage_item->name ?? '') : '';

        // Get warranty info (same parsing as PurchaseController)
        $warrantyName = null;
        $warrantyValue = null;
        $warrantyUnit = null;
        if ($item->warrenty_item) {
            $warrantyName = $item->warrenty_item->name ?? null;
            if ($warrantyName) {
                $warrantyNameLower = strtolower(trim($warrantyName));
                if (preg_match('/^(\d+)\s*(year|years|month|months|week|weeks|day|days)$/i', $warrantyNameLower, $matches)) {
                    $warrantyValue = $matches[1];
                    $unit = strtolower($matches[2]);
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

        $imageUrl = null;
        if ($item->image) {
            $imageUrl = str_starts_with($item->image, 'http') ? $item->image : asset($item->image);
        }

        $technologyName = $item->technology_item ? trim((string) ($item->technology_item->name ?? '')) : null;
        $gradeName = $item->grade_item ? trim((string) ($item->grade_item->name ?? '')) : null;
        $companyName = $item->company_item ? trim((string) ($item->company_item->name ?? '')) : null;
        $qualityName = $item->quality_item ? trim((string) ($item->quality_item->name ?? '')) : '';
        $categoryNameSales = $item->category ? trim((string) ($item->category->name ?? '')) : '';
        $subcategoryNameSales = $item->subcategory ? trim((string) ($item->subcategory->name ?? '')) : '';
        $itemTypeSales = strtolower(trim((string) ($item->type ?? '')));
        $productTypeLabelSales = ItemProductTypeLabel::resolve($categoryNameSales, $itemTypeSales, $subcategoryNameSales !== '' ? $subcategoryNameSales : null);
        $partNumberSales = $item->partnumber_item ? trim((string) ($item->partnumber_item->name ?? '')) : '';
        $productTitleSales = '';
        if ($item->product_item && trim((string) ($item->product_item->name ?? '')) !== '') {
            $productTitleSales = trim(strip_tags((string) $item->product_item->name));
        }

        $salePricePerBase = isset($item->sale_price_per_base) && $item->sale_price_per_base !== null && $item->sale_price_per_base !== ''
            ? (float) $item->sale_price_per_base
            : 0.0;
        $retailPrice = $item->retail_price !== null && $item->retail_price !== '' && (float) $item->retail_price > 0
            ? (float) $item->retail_price
            : null;
        $taxPct = isset($item->tax_percentage) && $item->tax_percentage !== '' && (float) $item->tax_percentage > 0
            ? (float) $item->tax_percentage
            : 18.0;
        $rTaxPct = isset($item->r_tax_percentage) && $item->r_tax_percentage !== '' && (float) $item->r_tax_percentage >= 0
            ? (float) $item->r_tax_percentage
            : 0.05;

        return response()->json([
            'id' => $item->id,
            'name' => $itemName,
            'rate' => round($rate, 2),
            'sale_price' => $salePrice,
            'sale_price_per_base' => $salePricePerBase > 0 ? round($salePricePerBase, 4) : null,
            'retail_price' => $retailPrice,
            'tax_percentage' => $taxPct,
            'r_tax_percentage' => $rTaxPct,
            'packing_purchase_rate' => $packingPurchaseRate,
            'total_price' => $totalPrice,
            'unit'           => ($item->unit_item && ($item->unit_item->name ?? $item->unit_item->short_name)) ? ($item->unit_item->name ?? $item->unit_item->short_name) : ($item->unit ?? 'Unit'),
            'liter_per_can'  => $unitInfo['liter_per_can'] > 0 ? $unitInfo['liter_per_can'] : null,
            'stock'          => $onHand,
            'bar_code'       => $item->bar_code,
            'serial_number'  => $item->serial_number,
            'packing'        => $item->packing ?? 1,
            'type'           => $item->type ?? null,
            'category_name'  => $categoryNameSales !== '' ? $categoryNameSales : null,
            'part_number'    => $partNumberSales !== '' ? $partNumberSales : null,
            'product_type_label' => $productTypeLabelSales,
            'product_title' => $productTitleSales !== '' ? $productTitleSales : null,
            'technology_name' => $technologyName !== '' ? $technologyName : null,
            'grade_name'      => $gradeName !== '' ? $gradeName : null,
            'company_name'    => $companyName !== '' ? $companyName : null,
            'quality_name'    => $qualityName !== '' ? $qualityName : null,
            'image'          => $imageUrl,
            'mileage_id'     => $mileageId,
            'mileage_name'   => $mileageName,
            'warranty_name'  => $warrantyName,
            'warranty_value' => $warrantyValue,
            'warranty_unit'  => $warrantyUnit,
        ]);
    }
    
    /**
     * Get claim stock summary for a branch (for display next to CLAIM IN button).
     */
    public function getClaimStockSummary(Request $request)
    {
        $branchId = $request->query('branch_id');
        if (!$branchId) {
            return response()->json(['total_quantity' => 0, 'display' => '0 Piece']);
        }
        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
        if ($warehouseIds->isEmpty()) {
            return response()->json(['total_quantity' => 0, 'display' => '0 Piece']);
        }
        $total = (float) ClaimWarehouseItem::whereIn('warehouse_id', $warehouseIds)->sum('quantity');
        $display = $total == (int) $total ? (int) $total . ' Piece' : number_format($total, 2) . ' Piece';
        return response()->json(['total_quantity' => $total, 'display' => $display]);
    }

    /**
     * Get claim SEND total quantity for a branch.
     * Used for "Claim Send" badge (outgoing movements only).
     */
    public function getClaimSendStockSummary(Request $request)
    {
        $branchId = $request->query('branch_id');
        if (!$branchId) {
            return response()->json(['total_quantity' => 0, 'display' => '0 Piece']);
        }

        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
        if ($warehouseIds->isEmpty()) {
            return response()->json(['total_quantity' => 0, 'display' => '0 Piece']);
        }

        // Claim Send movements are stored on purchase_items with entry_type = claim_send.
        $total = (float) PurchaseItem::whereIn('warehouse_id', $warehouseIds)
            ->whereHas('purchase', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where('entry_type', 'claim_send')
            ->sum('quantity');

        $display = $total == (int) $total ? (int) $total . ' Piece' : number_format($total, 2) . ' Piece';
        return response()->json(['total_quantity' => $total, 'display' => $display]);
    }

    /**
     * Public image URLs for claim detail rows (primary + gallery).
     *
     * @return list<string>
     */
    protected function itemGalleryPublicUrlsForClaimDetail(?Item $item): array
    {
        if (! $item) {
            return [];
        }
        $seen = [];
        $out = [];
        $paths = [];
        try {
            $main = $item->getRawOriginal('image');
        } catch (\Throwable $e) {
            $main = null;
        }
        if ($main !== null && $main !== '') {
            $paths[] = (string) $main;
        }
        $rawImages = null;
        try {
            $rawImages = $item->getRawOriginal('images');
        } catch (\Throwable $e) {
            $rawImages = null;
        }
        if ($rawImages !== null && $rawImages !== '') {
            $decoded = is_array($rawImages) ? $rawImages : json_decode((string) $rawImages, true);
            if (is_array($decoded)) {
                foreach ($decoded as $p) {
                    if ($p !== null && $p !== '') {
                        $paths[] = (string) $p;
                    }
                }
            }
        }
        foreach ($paths as $p) {
            $u = preg_match('#^https?://#i', $p) ? $p : asset(ltrim($p, '/'));
            if ($u !== '' && ! isset($seen[$u])) {
                $seen[$u] = true;
                $out[] = $u;
            }
        }

        return $out;
    }

    /**
     * @return array{image: ?string, image_path: ?string, images: array<int, string>}
     */
    protected function claimDetailItemImageFields(?Item $item): array
    {
        if (! $item) {
            return ['image' => null, 'image_path' => null, 'images' => []];
        }
        try {
            $raw = $item->getRawOriginal('image');
        } catch (\Throwable $e) {
            $raw = null;
        }
        $rawStr = ($raw !== null && $raw !== '') ? (string) $raw : null;
        $urls = $this->itemGalleryPublicUrlsForClaimDetail($item);
        $primary = $urls[0] ?? null;

        return [
            'image' => $primary,
            'image_path' => $rawStr,
            'images' => $urls,
        ];
    }

    /**
     * Get detailed claim stock history for a branch (+ optional item).
     * Used when clicking the "Claim stock: X Piece" badge.
     */
    public function getClaimStockDetail(Request $request)
    {
        $branchId = (int) $request->query('branch_id');
        $itemId = $request->query('item_id') ? (int) $request->query('item_id') : null;

        if ($branchId <= 0) {
            return response()->json([
                'records' => [],
                'totals' => [
                    'total_claim_in' => 0,
                    'total_claim_sent' => 0,
                    'current_claim_stock' => 0,
                ],
            ]);
        }

        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
        if ($warehouseIds->isEmpty()) {
            return response()->json([
                'records' => [],
                'totals' => [
                    'total_claim_in' => 0,
                    'total_claim_sent' => 0,
                    'current_claim_stock' => 0,
                ],
            ]);
        }

        $records = [];
        $claimAvailabilityMap = ClaimWarehouseItem::whereIn('warehouse_id', $warehouseIds)
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->get(['warehouse_id', 'item_id', 'quantity'])
            ->mapWithKeys(function ($row) {
                $key = ((int) $row->warehouse_id) . ':' . ((int) $row->item_id);
                return [$key => (float) ($row->quantity ?? 0)];
            });

        // 1) Claim In from Sales (customer claims coming in)
        $saleItems = SaleItem::with([
            'sale.customer',
            'sale.branch',
            'warehouse',
            'item.product_item',
            'item.company_item',
            'item.plate_item',
            'item.amphors_item',
            'item.volt_item',
            'item.cca_item',
        ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereHas('sale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'claim')
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        foreach ($saleItems as $line) {
            $sale = $line->sale;
            $customer = $sale ? $sale->customer : null;
            $branch = $sale ? $sale->branch : null;
            $warehouse = $line->warehouse;
            $item = $line->item;

            $createdAt = $sale && $sale->created_at ? $sale->created_at : $line->created_at;
            $dt = $createdAt ? \Carbon\Carbon::parse($createdAt) : now();
            
            $itemTitle = $item ? trim((string) (optional($item->product_item)->name ?? '')) : '';
            if ($itemTitle === '' && $item) {
                $itemTitle = trim(strip_tags((string) ($item->short_disc ?? $item->pro_dis ?? '')));
            }
            if ($itemTitle === '' && $item) {
                $itemTitle = trim((string) ($item->bar_code ?? ''));
            }
            $batteryLine = null;
            $batteryMeta = null;
            if ($item && strtolower(trim((string) ($item->type ?? ''))) === 'battery') {
                $parts = [];
                $plate = trim((string) (optional($item->plate_item)->name ?? ''));
                if ($plate !== '') $parts[] = str_contains(strtoupper($plate), 'PL') ? $plate : $plate . 'PL';
                $ah = trim((string) (optional($item->amphors_item)->name ?? ''));
                if ($ah !== '') $parts[] = str_contains(strtoupper($ah), 'AH') ? $ah : $ah . 'AH';
                $company = trim((string) (optional($item->company_item)->name ?? ''));
                if ($company !== '') $parts[] = $company;
                if ($parts !== []) $batteryLine = implode(' • ', $parts);
                $metaParts = [];
                $volt = trim((string) (optional($item->volt_item)->name ?? ''));
                if ($volt !== '') $metaParts[] = str_contains(strtoupper($volt), 'V') ? $volt : $volt . 'V';
                $cca = trim((string) (optional($item->cca_item)->name ?? ''));
                if ($cca !== '') $metaParts[] = str_contains(strtoupper($cca), 'CCA') ? $cca : $cca . 'CCA';
                if ($metaParts !== []) $batteryMeta = implode(' • ', $metaParts);
            }

            $records[] = array_merge([
                'date' => $dt->format('d/m/Y'),
                'time' => $dt->format('h:i A'),
                'datetime_sort' => $dt->timestamp,
                'customer_name' => $customer ? ($customer->name ?? ($customer->names[0] ?? $customer->names ?? 'Walk-in')) : 'Walk-in',
                'invoice_no' => $sale ? ($sale->invoice_no ?? $sale->id) : null,
                'sale_id' => $sale ? (int) $sale->id : null,
                'item_name' => $itemTitle,
                'item_id' => (int) ($line->item_id ?? ($item ? $item->id : 0)),
                'item_line' => $batteryLine,
                'item_meta' => $batteryMeta,
                'item_code' => $item ? ($item->bar_code ?? $item->sku ?? null) : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'warehouse_name' => $warehouse ? $warehouse->warehouse_name : null,
                'warehouse_id' => (int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0)),
                'quantity' => (float) $line->quantity,
                'available_claim_qty' => (float) ($claimAvailabilityMap[((int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0))) . ':' . ((int) ($line->item_id ?? ($item ? $item->id : 0)))] ?? 0),
                'entry_type' => 'claim_in',
                'entry_type_label' => 'Claim In',
                'reference_no' => $sale ? ($sale->reference ?? null) : null,
                'remarks' => $sale ? ($sale->description ?? $sale->notes ?? null) : null,
            ], $this->claimDetailItemImageFields($item));
        }

        // 2) Claim stock movements from Purchases (claim in / claim send with suppliers)
        $purchaseItems = PurchaseItem::with([
            'purchase.supplier',
            'purchase.branch',
            'warehouse',
            'item.product_item',
            'item.company_item',
            'item.plate_item',
            'item.amphors_item',
            'item.volt_item',
            'item.cca_item',
            'item.vehical_item.manutacturer_vehical',
            'item.vehical_item.model_vehical',
        ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereHas('purchase', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            // Claim In history must show ONLY claim_in rows (so fetch ONLY `entry_type = claim`)
            ->where('entry_type', 'claim')
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        foreach ($purchaseItems as $line) {
            $purchase = $line->purchase;
            $supplier = $purchase ? $purchase->supplier : null;
            $branch = $purchase ? $purchase->branch : null;
            $warehouse = $line->warehouse;
            $item = $line->item;

            // NOTE: purchase_date is commonly stored as DATE (no time) so it renders as 12:00 AM.
            // Use the actual event time from purchase_item.created_at (fallback: purchase.created_at).
            $eventDt = $line->created_at
                ? \Carbon\Carbon::parse($line->created_at)
                : (($purchase && $purchase->created_at) ? \Carbon\Carbon::parse($purchase->created_at) : now());
            // Keep the business date from purchase_date when present (but DO NOT use it for time).
            $purchaseDate = $purchase && $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date) : null;
            $displayDate = $purchaseDate ? $purchaseDate->format('d/m/Y') : $eventDt->format('d/m/Y');

            $baseQty = (float) $line->quantity;
            $qtyDelta = $baseQty; // claim in = positive quantity
            
            $itemTitle = $item ? trim((string) (optional($item->product_item)->name ?? '')) : '';
            if ($itemTitle === '' && $item) {
                $itemTitle = trim(strip_tags((string) ($item->short_disc ?? $item->pro_dis ?? '')));
            }
            if ($itemTitle === '' && $item) {
                $itemTitle = trim((string) ($item->bar_code ?? ''));
            }
            $batteryLine = null;
            $batteryMeta = null;
            if ($item && strtolower(trim((string) ($item->type ?? ''))) === 'battery') {
                // Line: Plate • Amperes • Company
                $parts = [];
                $plate = trim((string) (optional($item->plate_item)->name ?? ''));
                if ($plate !== '') $parts[] = str_contains(strtoupper($plate), 'PL') ? $plate : $plate . 'PL';
                $ah = trim((string) (optional($item->amphors_item)->name ?? ''));
                if ($ah !== '') $parts[] = str_contains(strtoupper($ah), 'AH') ? $ah : $ah . 'AH';
                $company = trim((string) (optional($item->company_item)->name ?? ''));
                if ($company !== '') $parts[] = $company;
                if ($parts !== []) $batteryLine = implode(' • ', $parts);
                
                // Meta: Volt • CCA
                $metaParts = [];
                $volt = trim((string) (optional($item->volt_item)->name ?? ''));
                if ($volt !== '') $metaParts[] = str_contains(strtoupper($volt), 'V') ? $volt : $volt . 'V';
                $cca = trim((string) (optional($item->cca_item)->name ?? ''));
                if ($cca !== '') $metaParts[] = str_contains(strtoupper($cca), 'CCA') ? $cca : $cca . 'CCA';
                if ($metaParts !== []) $batteryMeta = implode(' • ', $metaParts);
            }

            $records[] = array_merge([
                'date' => $displayDate,
                'time' => $eventDt->format('h:i A'),
                'datetime_sort' => $eventDt->timestamp,
                'customer_name' => $supplier ? ($supplier->name ?? $supplier->company_name ?? 'Supplier') : 'Supplier',
                'invoice_no' => $purchase ? ($purchase->invoice_no ?? $purchase->id) : null,
                'purchase_id' => $purchase ? (int) $purchase->id : null,
                'item_name' => $itemTitle,
                'item_id' => (int) ($line->item_id ?? ($item ? $item->id : 0)),
                'item_line' => $batteryLine,
                'item_meta' => $batteryMeta,
                'item_code' => $item ? ($item->bar_code ?? $item->sku ?? null) : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'warehouse_name' => $warehouse ? $warehouse->warehouse_name : null,
                'warehouse_id' => (int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0)),
                'quantity' => $qtyDelta,
                'available_claim_qty' => (float) ($claimAvailabilityMap[((int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0))) . ':' . ((int) ($line->item_id ?? ($item ? $item->id : 0)))] ?? 0),
                'entry_type' => 'claim_in',
                'entry_type_label' => 'Claim In',
                'reference_no' => $purchase ? ($purchase->reference ?? null) : null,
                'remarks' => $purchase ? ($purchase->description ?? null) : null,
            ], $this->claimDetailItemImageFields($item));
        }

        // Sort oldest first by datetime
        usort($records, function ($a, $b) {
            return $a['datetime_sort'] <=> $b['datetime_sort'];
        });

        // Claim In tab must reflect *current* claim stock, not historical receipts.
        // Filter out fully-sent rows (no remaining claim stock for that item/warehouse).
        $records = array_values(array_filter($records, function ($rec) {
            $avail = (float) ($rec['available_claim_qty'] ?? 0);
            $qty = (float) ($rec['quantity'] ?? 0);
            return $qty > 0 && $avail > 0;
        }));

        // Totals must remain separate even when the table shows ONLY Claim In rows.
        $totalClaimSent = abs((float) PurchaseItem::whereIn('warehouse_id', $warehouseIds)
            ->whereHas('purchase', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'claim_send')
            ->sum('quantity'));

        // Derive current claim stock from ClaimWarehouseItem to stay exact
        $currentClaimStock = (float) ClaimWarehouseItem::whereIn('warehouse_id', $warehouseIds)
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->sum('quantity');
        $totalClaimIn = $currentClaimStock;

        return response()->json([
            'records' => $records,
            'totals' => [
                'total_claim_in' => $totalClaimIn,
                'total_claim_sent' => $totalClaimSent,
                'current_claim_stock' => $currentClaimStock,
            ],
        ]);
    }

    /**
     * Get detailed claim SEND stock history for a branch (+ optional item).
     * Used when viewing "Claim Send" history so table never mixes in/out rows.
     */
    public function getClaimSendStockDetail(Request $request)
    {
        $branchId = (int) $request->query('branch_id');
        $itemId = $request->query('item_id') ? (int) $request->query('item_id') : null;

        if ($branchId <= 0) {
            return response()->json([
                'records' => [],
                'totals' => [
                    'total_claim_in' => 0,
                    'total_claim_sent' => 0,
                    'current_claim_stock' => 0,
                ],
            ]);
        }

        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
        if ($warehouseIds->isEmpty()) {
            return response()->json([
                'records' => [],
                'totals' => [
                    'total_claim_in' => 0,
                    'total_claim_sent' => 0,
                    'current_claim_stock' => 0,
                ],
            ]);
        }

        $records = [];
        $claimAvailabilityMap = ClaimWarehouseItem::whereIn('warehouse_id', $warehouseIds)
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->get(['warehouse_id', 'item_id', 'quantity'])
            ->mapWithKeys(function ($row) {
                $key = ((int) $row->warehouse_id) . ':' . ((int) $row->item_id);
                return [$key => (float) ($row->quantity ?? 0)];
            });

        // Totals (full scope, not limited to 500 records)
        $saleClaimInSum = (float) SaleItem::whereIn('warehouse_id', $warehouseIds)
            ->whereHas('sale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'claim')
            ->sum('quantity');

        $purchaseClaimInSum = (float) PurchaseItem::whereIn('warehouse_id', $warehouseIds)
            ->whereHas('purchase', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'claim')
            ->sum('quantity');

        $totalClaimIn = $saleClaimInSum + $purchaseClaimInSum;

        $totalClaimSent = abs((float) PurchaseItem::whereIn('warehouse_id', $warehouseIds)
            ->whereHas('purchase', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'claim_send')
            ->sum('quantity'));

        // 1) Claim Send history from Purchases only (outgoing movements)
        $purchaseItems = PurchaseItem::with([
            'purchase.supplier',
            'purchase.branch',
            'warehouse',
            'item.product_item',
            'item.company_item',
            'item.plate_item',
            'item.amphors_item',
            'item.volt_item',
            'item.cca_item',
            'item.vehical_item.manutacturer_vehical',
            'item.vehical_item.model_vehical',
        ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereHas('purchase', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'claim_send')
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        $reversedByPurchaseItemId = [];
        if (Schema::hasTable('claim_send_reversals')) {
            $reversedByPurchaseItemId = ClaimSendReversal::whereIn('purchase_item_id', $purchaseItems->pluck('id'))
                ->selectRaw('purchase_item_id, SUM(quantity) as reversed_qty')
                ->groupBy('purchase_item_id')
                ->pluck('reversed_qty', 'purchase_item_id')
                ->map(fn ($v) => (float) $v)
                ->all();
        }

        foreach ($purchaseItems as $line) {
            $purchase = $line->purchase;
            $supplier = $purchase ? $purchase->supplier : null;
            $branch = $purchase ? $purchase->branch : null;
            $warehouse = $line->warehouse;
            $item = $line->item;

            $eventDt = $line->created_at
                ? \Carbon\Carbon::parse($line->created_at)
                : (($purchase && $purchase->created_at) ? \Carbon\Carbon::parse($purchase->created_at) : now());

            $purchaseDate = $purchase && $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date) : null;
            $displayDate = $purchaseDate ? $purchaseDate->format('d/m/Y') : $eventDt->format('d/m/Y');

            $baseQty = abs((float) $line->quantity);
            $reversedQty = (float) ($reversedByPurchaseItemId[$line->id] ?? 0);
            $remainingQty = max(0.0, $baseQty - $reversedQty);
            if ($remainingQty <= 0) {
                continue;
            }
            $qtyDelta = -$baseQty; // claim send = outgoing

            $itemTitle = $item ? trim((string) (optional($item->product_item)->name ?? '')) : '';
            if ($itemTitle === '' && $item) {
                $itemTitle = trim(strip_tags((string) ($item->short_disc ?? $item->pro_dis ?? '')));
            }
            if ($itemTitle === '' && $item) {
                $itemTitle = trim((string) ($item->bar_code ?? ''));
            }

            $batteryLine = null;
            $batteryMeta = null;
            if ($item && strtolower(trim((string) ($item->type ?? ''))) === 'battery') {
                $parts = [];
                $plate = trim((string) (optional($item->plate_item)->name ?? ''));
                if ($plate !== '') $parts[] = str_contains(strtoupper($plate), 'PL') ? $plate : $plate . 'PL';
                $ah = trim((string) (optional($item->amphors_item)->name ?? ''));
                if ($ah !== '') $parts[] = str_contains(strtoupper($ah), 'AH') ? $ah : $ah . 'AH';
                $company = trim((string) (optional($item->company_item)->name ?? ''));
                if ($company !== '') $parts[] = $company;
                if ($parts !== []) $batteryLine = implode(' • ', $parts);

                $metaParts = [];
                $volt = trim((string) (optional($item->volt_item)->name ?? ''));
                if ($volt !== '') $metaParts[] = str_contains(strtoupper($volt), 'V') ? $volt : $volt . 'V';
                $cca = trim((string) (optional($item->cca_item)->name ?? ''));
                if ($cca !== '') $metaParts[] = str_contains(strtoupper($cca), 'CCA') ? $cca : $cca . 'CCA';
                if ($metaParts !== []) $batteryMeta = implode(' • ', $metaParts);
            }

            $invNo = $purchase ? trim((string) ($purchase->invoice_no ?? '')) : '';
            $records[] = array_merge([
                'date' => $displayDate,
                'time' => $eventDt->format('h:i A'),
                'datetime_sort' => $eventDt->timestamp,
                'customer_name' => $supplier ? ($supplier->name ?? $supplier->company_name ?? 'Supplier') : 'Supplier',
                'invoice_no' => $purchase ? ($purchase->invoice_no ?? $purchase->id) : null,
                'claim_id' => $invNo !== '' ? ('INV ' . $invNo) : ($purchase ? ('P#' . (int) $purchase->id) : null),
                'display_quantity' => $remainingQty,
                'purchase_id' => $purchase ? (int) $purchase->id : null,
                'purchase_item_id' => (int) $line->id,
                'item_name' => $itemTitle,
                'item_id' => (int) ($line->item_id ?? ($item ? $item->id : 0)),
                'item_line' => $batteryLine,
                'item_meta' => $batteryMeta,
                'item_code' => $item ? ($item->bar_code ?? $item->sku ?? null) : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'warehouse_name' => $warehouse ? $warehouse->warehouse_name : null,
                'warehouse_id' => (int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0)),
                'quantity' => -$remainingQty,
                'reversed_quantity' => $reversedQty,
                'remaining_quantity' => $remainingQty,
                'available_claim_qty' => (float) ($claimAvailabilityMap[((int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0))) . ':' . ((int) ($line->item_id ?? ($item ? $item->id : 0)))] ?? 0),
                'entry_type' => 'claim_send',
                'entry_type_label' => 'Claim Send',
                'reference_no' => $purchase ? ($purchase->reference ?? null) : null,
                'remarks' => $purchase ? ($purchase->description ?? null) : null,
            ], $this->claimDetailItemImageFields($item));
        }

        // Sort oldest first by datetime
        usort($records, function ($a, $b) {
            return $a['datetime_sort'] <=> $b['datetime_sort'];
        });

        $currentClaimStock = (float) ClaimWarehouseItem::whereIn('warehouse_id', $warehouseIds)
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->sum('quantity');

        return response()->json([
            'records' => $records,
            'totals' => [
                'total_claim_in' => $totalClaimIn,
                'total_claim_sent' => $totalClaimSent,
                'current_claim_stock' => $currentClaimStock,
            ],
        ]);
    }

    /**
     * Claim Reverse history (items reversed back from claim_send).
     * This is treated as a separate tab from Claim In.
     */
    public function getClaimReverseStockDetail(Request $request)
    {
        $branchId = (int) $request->query('branch_id');
        $itemId = $request->query('item_id') ? (int) $request->query('item_id') : null;

        if ($branchId <= 0) {
            return response()->json(['records' => [], 'totals' => ['total_claim_reversed' => 0]]);
        }
        if (!Schema::hasTable('claim_send_reversals')) {
            return response()->json(['records' => [], 'totals' => ['total_claim_reversed' => 0]]);
        }

        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
        if ($warehouseIds->isEmpty()) {
            return response()->json(['records' => [], 'totals' => ['total_claim_reversed' => 0]]);
        }

        $claimAvailabilityMap = ClaimWarehouseItem::whereIn('warehouse_id', $warehouseIds)
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->get(['warehouse_id', 'item_id', 'quantity'])
            ->mapWithKeys(function ($row) {
                $key = ((int) $row->warehouse_id) . ':' . ((int) $row->item_id);
                return [$key => (float) ($row->quantity ?? 0)];
            });

        $reversals = ClaimSendReversal::with([
            'purchaseItem.purchase.supplier',
            'purchaseItem.purchase.branch',
            'purchaseItem.warehouse',
            'purchaseItem.item.product_item',
            'purchaseItem.item.company_item',
            'purchaseItem.item.plate_item',
            'purchaseItem.item.amphors_item',
            'purchaseItem.item.volt_item',
            'purchaseItem.item.cca_item',
            'purchaseItem.item.vehical_item.manutacturer_vehical',
            'purchaseItem.item.vehical_item.model_vehical',
        ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        $records = [];
        $total = 0.0;

        foreach ($reversals as $rev) {
            $line = $rev->purchaseItem;
            if (! $line) continue;
            $purchase = $line->purchase;
            $supplier = $purchase ? $purchase->supplier : null;
            $branch = $purchase ? $purchase->branch : null;
            $warehouse = $line->warehouse;
            $item = $line->item;

            $eventDt = $rev->created_at ? \Carbon\Carbon::parse($rev->created_at) : now();
            $displayDate = $eventDt->format('d/m/Y');

            $itemTitle = $item ? trim((string) (optional($item->product_item)->name ?? '')) : '';
            if ($itemTitle === '' && $item) {
                $itemTitle = trim(strip_tags((string) ($item->short_disc ?? $item->pro_dis ?? '')));
            }
            if ($itemTitle === '' && $item) {
                $itemTitle = trim((string) ($item->bar_code ?? ''));
            }

            $batteryLine = null;
            $batteryMeta = null;
            if ($item && strtolower(trim((string) ($item->type ?? ''))) === 'battery') {
                $parts = [];
                $plate = trim((string) (optional($item->plate_item)->name ?? ''));
                if ($plate !== '') $parts[] = str_contains(strtoupper($plate), 'PL') ? $plate : $plate . 'PL';
                $ah = trim((string) (optional($item->amphors_item)->name ?? ''));
                if ($ah !== '') $parts[] = str_contains(strtoupper($ah), 'AH') ? $ah : $ah . 'AH';
                $company = trim((string) (optional($item->company_item)->name ?? ''));
                if ($company !== '') $parts[] = $company;
                if ($parts !== []) $batteryLine = implode(' • ', $parts);

                $metaParts = [];
                $volt = trim((string) (optional($item->volt_item)->name ?? ''));
                if ($volt !== '') $metaParts[] = str_contains(strtoupper($volt), 'V') ? $volt : $volt . 'V';
                $cca = trim((string) (optional($item->cca_item)->name ?? ''));
                if ($cca !== '') $metaParts[] = str_contains(strtoupper($cca), 'CCA') ? $cca : $cca . 'CCA';
                if ($metaParts !== []) $batteryMeta = implode(' • ', $metaParts);
            }

            $qty = abs((float) $rev->quantity);
            $total += $qty;
            $invNo = $purchase ? trim((string) ($purchase->invoice_no ?? '')) : '';
            $claimRef = $invNo !== '' ? ('INV ' . $invNo) : ($purchase ? ('P#' . (int) $purchase->id) : null);

            $records[] = array_merge([
                'date' => $displayDate,
                'time' => $eventDt->format('h:i A'),
                'datetime_sort' => $eventDt->timestamp,
                'reversal_id' => (int) $rev->id,
                'purchase_item_id' => (int) $line->id,
                'purchase_id' => $purchase ? (int) $purchase->id : null,
                'customer_name' => $supplier ? ($supplier->name ?? $supplier->company_name ?? 'Supplier') : 'Supplier',
                'claim_id' => $claimRef,
                'display_quantity' => $qty,
                'item_name' => $itemTitle,
                'item_id' => (int) ($line->item_id ?? ($item ? $item->id : 0)),
                'item_line' => $batteryLine,
                'item_meta' => $batteryMeta,
                'item_code' => $item ? ($item->bar_code ?? $item->sku ?? null) : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'warehouse_name' => $warehouse ? $warehouse->warehouse_name : null,
                'warehouse_id' => (int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0)),
                'quantity' => $qty,
                'available_claim_qty' => (float) ($claimAvailabilityMap[((int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0))) . ':' . ((int) ($line->item_id ?? ($item ? $item->id : 0)))] ?? 0),
                'entry_type' => 'claim_reverse',
                'entry_type_label' => 'Claim Reverse',
                'reason' => $rev->reason,
                'remarks' => $purchase ? ($purchase->description ?? null) : null,
            ], $this->claimDetailItemImageFields($item));
        }

        usort($records, function ($a, $b) {
            return ($b['datetime_sort'] ?? 0) <=> ($a['datetime_sort'] ?? 0);
        });

        return response()->json([
            'records' => $records,
            'totals' => ['total_claim_reversed' => $total],
        ]);
    }

    /**
     * Reverse (reject) a claim-send movement back into claim stock (atomic).
     */
    public function reverseClaimSend(Request $request)
    {
        if (!Schema::hasTable('claim_send_reversals')) {
            return response()->json([
                'success' => false,
                'message' => "Reverse feature is not installed yet (missing table claim_send_reversals). Run: php artisan migrate",
            ], 400);
        }

        $v = Validator::make($request->all(), [
            'purchase_item_id' => 'required|integer|exists:purchase_items,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:2000',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => $v->errors()->first()], 422);
        }

        $purchaseItemId = (int) $request->input('purchase_item_id');
        $qty = abs((float) $request->input('quantity'));
        $reason = $request->input('reason');

        try {
            $res = DB::transaction(function () use ($purchaseItemId, $qty, $reason) {
                /** @var PurchaseItem $line */
                $line = PurchaseItem::lockForUpdate()->with(['purchase'])->findOrFail($purchaseItemId);
                if (($line->entry_type ?? '') !== 'claim_send') {
                    throw new \Exception('Only claim-send rows can be reversed.');
                }
                $warehouseId = $line->warehouse_id ? (int) $line->warehouse_id : null;
                $itemId = (int) $line->item_id;
                $sentQty = abs((float) $line->quantity);

                $alreadyReversed = (float) ClaimSendReversal::where('purchase_item_id', $line->id)->sum('quantity');
                $remaining = max(0.0, $sentQty - $alreadyReversed);
                if ($remaining + 0.000001 < $qty) {
                    throw new \Exception("Reverse qty exceeds remaining sent qty. Remaining: {$remaining}");
                }

                if (! $warehouseId) {
                    throw new \Exception('Warehouse is required to reverse claim send.');
                }

                $claimItem = ClaimWarehouseItem::lockForUpdate()
                    ->where('warehouse_id', $warehouseId)
                    ->where('item_id', $itemId)
                    ->first();
                if (! $claimItem) {
                    $claimItem = ClaimWarehouseItem::create([
                        'warehouse_id' => $warehouseId,
                        'item_id' => $itemId,
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'available_quantity' => 0,
                    ]);
                    $claimItem = ClaimWarehouseItem::lockForUpdate()->find($claimItem->id);
                }

                $claimItem->quantity = (float) ($claimItem->quantity ?? 0) + $qty;
                $claimItem->save();

                $rev = ClaimSendReversal::create([
                    'purchase_item_id' => $line->id,
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                    'quantity' => $qty,
                    'reason' => $reason,
                    'created_by' => auth()->id(),
                ]);

                $purchase = $line->purchase;
                $invNo = $purchase ? trim((string) ($purchase->invoice_no ?? '')) : '';
                $claimRef = $invNo !== '' ? ('INV ' . $invNo) : ($purchase ? ('P#' . (int) $purchase->id) : null);

                return [
                    'reversal_id' => (int) $rev->id,
                    'purchase_item_id' => (int) $line->id,
                    'purchase_id' => $line->purchase_id ? (int) $line->purchase_id : null,
                    'warehouse_id' => $warehouseId,
                    'item_id' => $itemId,
                    'quantity' => $qty,
                    'remaining_quantity' => max(0.0, $remaining - $qty),
                    'reason' => $reason,
                    'claim_id' => $claimRef,
                    'reversed_at' => $rev->created_at ? $rev->created_at->toDateTimeString() : now()->toDateTimeString(),
                ];
            });

            return response()->json(['success' => true, 'data' => $res]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get scrap stock summary for a branch (for display next to SCRAP IN button).
     */
    public function getScrapStockSummary(Request $request)
    {
        $branchId = $request->query('branch_id');
        if (!$branchId) {
            return response()->json(['total_quantity' => 0, 'display' => '0 Unit']);
        }

        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
        if ($warehouseIds->isEmpty()) {
            return response()->json(['total_quantity' => 0, 'display' => '0 Unit']);
        }

        $total = (float) WarehouseItem::whereIn('warehouse_id', $warehouseIds)
            ->whereHas('item', function ($q) {
                $q->where('type', 'scrap');
            })
            ->sum('quantity');

        $display = $total == (int) $total ? ((int) $total . ' Unit') : (number_format($total, 2) . ' Unit');
        return response()->json(['total_quantity' => $total, 'display' => $display]);
    }

    /**
     * Get detailed scrap stock history for a branch (+ optional item).
     * This is used by the "Scrap Stock" badge next to SCRAP IN.
     */
    public function getScrapStockDetail(Request $request)
    {
        $branchId = (int) $request->query('branch_id');
        $itemId = $request->query('item_id') ? (int) $request->query('item_id') : null;

        if ($branchId <= 0) {
            return response()->json([
                'records' => [],
                'totals' => [
                    'total_scrap_in' => 0,
                    'total_scrap_sent' => 0,
                    'current_scrap_stock' => 0,
                ],
            ]);
        }

        $warehouseIds = Warehouse::where('branch_id', $branchId)->pluck('id');
        if ($warehouseIds->isEmpty()) {
            return response()->json([
                'records' => [],
                'totals' => [
                    'total_scrap_in' => 0,
                    'total_scrap_sent' => 0,
                    'current_scrap_stock' => 0,
                ],
            ]);
        }

        $records = [];

        // Keep "Item" column formatting consistent with the Add-Item modal.
        // Add-Item modal builds a "productName" with priority:
        // product_item.name -> short_disc -> pro_dis -> partnumber_item.name -> Item #id
        // and it intentionally avoids falling back to barcode except as last resort.
        $isDummyText = function (?string $t): bool {
            if ($t === null) return true;
            $t = trim(strip_tags($t));
            if ($t === '') return true;
            if (mb_strlen($t) > 200) return true;
            $lower = mb_strtolower($t);
            if (str_contains($lower, 'lorem')) return true;
            if (str_contains($lower, 'dummy')) return true;
            if (str_contains($lower, 'simply')) return true;
            if ($lower === 'sdfsdf' || $lower === 'test') return true;
            // All same letter repeated (e.g. "aaaaaa")
            if (preg_match('/^[a-z]{5,}$/', $lower) && preg_match('/^(.)\\1+$/', $lower)) return true;
            return false;
        };

        $computeItemTitle = function ($item) use ($isDummyText) {
            if (!$item) return '';

            $productFromRelation = $item->product_item ? (string) $item->product_item->name : '';
            $shortDisc = isset($item->short_disc) ? (string) $item->short_disc : '';
            $proDis = isset($item->pro_dis) ? (string) $item->pro_dis : '';
            $partNumber = $item->partnumber_item ? (string) $item->partnumber_item->name : '';

            $productName = '';
            if ($productFromRelation !== '' && !$isDummyText($productFromRelation)) {
                $productName = trim(strip_tags($productFromRelation));
            }
            if ($productName === '' && $shortDisc !== '' && !$isDummyText($shortDisc)) {
                $productName = trim(strip_tags($shortDisc));
            }
            if ($productName === '' && $proDis !== '' && !$isDummyText($proDis)) {
                $productName = trim(strip_tags($proDis));
            }
            if ($productName === '' && $partNumber !== '' && !$isDummyText($partNumber)) {
                $productName = trim(strip_tags($partNumber));
            }
            // Last resort: keep same as Add-Item modal (never use barcode as main title)
            if ($productName === '') {
                $productName = 'Item #' . (int) ($item->id ?? 0);
            }

            // Truncate if too long (matches add-item modal behavior)
            if (mb_strlen($productName) > 100) {
                $productName = mb_substr($productName, 0, 97) . '...';
            }

            return $productName;
        };

        // 1) Scrap In from Sales (customer bringing scrap in)
        $saleInItems = SaleItem::with([
                'sale.customer',
                'sale.branch',
                'warehouse',
                'item.partnumber_item',
                'item.product_item',
                'item.company_item',
                'item.plate_item',
                'item.amphors_item',
                'item.volt_item',
                'item.cca_item',
            ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereHas('sale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->whereIn('entry_type', ['scrap', 'scrap_in'])
            ->whereHas('item', function ($q) {
                $q->where('type', 'scrap');
            })
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        foreach ($saleInItems as $line) {
            $sale = $line->sale;
            $customer = $sale ? $sale->customer : null;
            $branch = $sale ? $sale->branch : null;
            $warehouse = $line->warehouse;
            $item = $line->item;

            $createdAt = $sale && $sale->created_at ? $sale->created_at : $line->created_at;
            $dt = $createdAt ? \Carbon\Carbon::parse($createdAt) : now();

            $itemTitle = $computeItemTitle($item);

            $records[] = [
                'date' => $dt->format('d/m/Y'),
                'time' => $dt->format('h:i A'),
                'datetime_sort' => $dt->timestamp,
                'item_id' => (int) ($line->item_id ?? ($item ? $item->id : 0)),
                'warehouse_id' => (int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0)),
                'customer_name' => $customer ? ($customer->name ?? ($customer->names[0] ?? $customer->names ?? 'Customer')) : 'Customer',
                'invoice_no' => $sale ? ($sale->reference ?? $sale->id) : null,
                'item_name' => $itemTitle,
                'item_code' => $item ? ($item->bar_code ?? $item->sku ?? null) : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'warehouse_name' => $warehouse ? $warehouse->warehouse_name : null,
                'quantity' => (float) $line->quantity,
                'entry_type_label' => 'Scrap In',
                'remarks' => $sale ? ($sale->description ?? $sale->notes ?? null) : null,
            ];
        }

        // 2) Scrap Sent (from Sales)
        $saleOutItems = SaleItem::with([
                'sale.customer',
                'sale.branch',
                'warehouse',
                'item.partnumber_item',
                'item.product_item',
                'item.company_item',
                'item.plate_item',
                'item.amphors_item',
                'item.volt_item',
                'item.cca_item',
            ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereHas('sale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'scrap_sale')
            ->whereHas('item', function ($q) {
                $q->where('type', 'scrap');
            })
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        foreach ($saleOutItems as $line) {
            $sale = $line->sale;
            $customer = $sale ? $sale->customer : null;
            $branch = $sale ? $sale->branch : null;
            $warehouse = $line->warehouse;
            $item = $line->item;

            $createdAt = $sale && $sale->created_at ? $sale->created_at : $line->created_at;
            $dt = $createdAt ? \Carbon\Carbon::parse($createdAt) : now();

            $itemTitle = $computeItemTitle($item);

            $records[] = [
                'date' => $dt->format('d/m/Y'),
                'time' => $dt->format('h:i A'),
                'datetime_sort' => $dt->timestamp,
                'item_id' => (int) ($line->item_id ?? ($item ? $item->id : 0)),
                'warehouse_id' => (int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0)),
                'customer_name' => $customer ? ($customer->name ?? ($customer->names[0] ?? $customer->names ?? 'Customer')) : 'Customer',
                'invoice_no' => $sale ? ($sale->reference ?? $sale->id) : null,
                'item_name' => $itemTitle,
                'item_code' => $item ? ($item->bar_code ?? $item->sku ?? null) : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'warehouse_name' => $warehouse ? $warehouse->warehouse_name : null,
                // Negative quantity for "sent" to show red in UI.
                'quantity' => -(float) $line->quantity,
                'entry_type_label' => 'Scrap Out',
                'remarks' => $sale ? ($sale->description ?? $sale->notes ?? null) : null,
            ];
        }

        // 3) Scrap In from Purchases (supplier gives scrap into store)
        $purchaseInItems = PurchaseItem::with([
                'purchase.supplier',
                'purchase.branch',
                'warehouse',
                'item.partnumber_item',
                'item.product_item',
                'item.company_item',
                'item.plate_item',
                'item.amphors_item',
                'item.volt_item',
                'item.cca_item',
            ])
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereHas('purchase', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('entry_type', 'scrap')
            ->whereHas('item', function ($q) {
                $q->where('type', 'scrap');
            })
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();

        foreach ($purchaseInItems as $line) {
            $purchase = $line->purchase;
            $supplier = $purchase ? $purchase->supplier : null;
            $branch = $purchase ? $purchase->branch : null;
            $warehouse = $line->warehouse;
            $item = $line->item;

            $createdAt = $line->created_at ?? ($purchase && $purchase->created_at ? $purchase->created_at : now());
            $dt = $createdAt ? \Carbon\Carbon::parse($createdAt) : now();

            $itemTitle = $computeItemTitle($item);

            $records[] = [
                'date' => $dt->format('d/m/Y'),
                'time' => $dt->format('h:i A'),
                'datetime_sort' => $dt->timestamp,
                'item_id' => (int) ($line->item_id ?? ($item ? $item->id : 0)),
                'warehouse_id' => (int) ($line->warehouse_id ?? ($warehouse ? $warehouse->id : 0)),
                'customer_name' => $supplier ? ($supplier->name ?? $supplier->company_name ?? 'Supplier') : 'Supplier',
                'invoice_no' => $purchase ? ($purchase->reference ?? $purchase->id) : null,
                'item_name' => $itemTitle,
                'item_code' => $item ? ($item->bar_code ?? $item->sku ?? null) : null,
                'branch_name' => $branch ? $branch->branch_name : null,
                'warehouse_name' => $warehouse ? $warehouse->warehouse_name : null,
                'quantity' => (float) $line->quantity,
                'entry_type_label' => 'Scrap In',
                'remarks' => $purchase ? ($purchase->description ?? $purchase->reference ?? null) : null,
            ];
        }

        // Sort chronologically for running balance.
        usort($records, function ($a, $b) {
            return $a['datetime_sort'] <=> $b['datetime_sort'];
        });

        $totalScrapIn = 0.0;
        $totalScrapSent = 0.0;
        foreach ($records as $rec) {
            $qty = (float) $rec['quantity'];
            if ($qty >= 0) $totalScrapIn += $qty;
            else $totalScrapSent += abs($qty);
        }

        // Current scrap stock per item+warehouse (used as the "Balance After" end-point).
        // Note: No stock-ledger table here; we compute running balance from current stock + returned movements.
        $currentScrapByKey = [];
        $currentWiQuery = WarehouseItem::whereIn('warehouse_id', $warehouseIds)
            ->whereHas('item', function ($q) {
                $q->where('type', 'scrap');
            })
            ->when($itemId, function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->get(['item_id', 'warehouse_id', 'quantity']);

        foreach ($currentWiQuery as $wi) {
            $k = ((int) $wi->item_id) . ':' . ((int) $wi->warehouse_id);
            $currentScrapByKey[$k] = (float) ($wi->quantity ?? 0);
        }

        // Current total for the selected scope (branch + optional item).
        $currentScrapStock = array_sum($currentScrapByKey);

        // Running balance state per item+warehouse.
        $netMovementByKey = [];
        foreach ($records as $rec) {
            $qty = (float) $rec['quantity'];
            $key = ((int) $rec['item_id']) . ':' . ((int) $rec['warehouse_id']);
            $netMovementByKey[$key] = ($netMovementByKey[$key] ?? 0.0) + $qty; // qty already signed (+ in, - out)
        }

        // startingBalance = currentBalanceAfter - netMovement
        $balanceByKey = [];
        foreach ($netMovementByKey as $key => $netMovement) {
            $currentAfter = $currentScrapByKey[$key] ?? 0.0;
            $balanceByKey[$key] = (float) $currentAfter - (float) $netMovement;
        }

        foreach ($records as &$rec) {
            $qty = (float) $rec['quantity'];
            $key = ((int) $rec['item_id']) . ':' . ((int) $rec['warehouse_id']);
            $prev = $balanceByKey[$key] ?? 0.0;

            $stockIn = $qty >= 0 ? $qty : 0.0;
            $stockOut = $qty < 0 ? abs($qty) : 0.0;
            $balanceAfter = $prev + $stockIn - $stockOut;

            $rec['previous_stock'] = $prev;
            $rec['stock_in'] = $stockIn;
            $rec['stock_out'] = $stockOut;
            $rec['balance_after'] = $balanceAfter;

            $balanceByKey[$key] = $balanceAfter;
        }
        unset($rec);

        return response()->json([
            'records' => $records,
            'totals' => [
                'total_scrap_in' => $totalScrapIn,
                'total_scrap_sent' => $totalScrapSent,
                'current_scrap_stock' => $currentScrapStock,
            ],
        ]);
    }

    /**
     * Get stock status for an item across all branches and warehouses (Sales).
     * For normal sales we use main WarehouseItem stock.
     * For claim flows we use ClaimWarehouseItem so only claim stock is shown.
     */
    public function getItemStockStatus($id)
    {
        // Mirror PurchaseController oil-stock metadata so sales screen can show Can/Liter/ML controls
        $item = Item::with(['unit_item', 'unit_item.baseUnits'])->findOrFail($id);
        $packingSize = (float) ($item->packing ?? 1);
        $unitName = $item->unit_item ? trim($item->unit_item->name ?? $item->unit_item->short_name ?? 'Unit') : 'Unit';
        $baseUnitName = null;
        $baseUnitMultiplier = null;

        // Prefer multiplier from unit_option (e.g. "12_8_4" => 1 can = 4 liter)
        $unitOption = $item->unit_option ? trim((string) $item->unit_option) : '';
        if ($unitOption !== '' && strpos($unitOption, '_') !== false) {
            $parts = explode('_', $unitOption);
            $lastPart = end($parts);
            if (is_numeric($lastPart) && (float) $lastPart > 0) {
                $baseUnitMultiplier = (float) $lastPart;
                $baseUnitName = 'Liter';
            }
        }

        // Parse "1 can = X liter" from unit name (e.g. "Can - 3 Liter")
        $parsedMultiplierFromName = null;
        if (preg_match('/(\d+(?:\.\d+)?)\s*[-\s]*(?:liter|ltr|L)\b/ui', $unitName, $m)) {
            $parsedMultiplierFromName = (float) $m[1];
        } elseif (preg_match('/\b(?:liter|ltr|L)\s*[-\s]*(\d+(?:\.\d+)?)/ui', $unitName, $m)) {
            $parsedMultiplierFromName = (float) $m[1];
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*L\b/ui', $unitName, $m)) {
            $parsedMultiplierFromName = (float) $m[1];
        }
        if ($parsedMultiplierFromName !== null && $parsedMultiplierFromName <= 0) {
            $parsedMultiplierFromName = null;
        }

        // Use pivot/name multiplier only when we didn't get multiplier from unit_option
        if ($baseUnitMultiplier === null || (float) $baseUnitMultiplier <= 0) {
            if ($item->unit_item && $item->unit_item->baseUnits && $item->unit_item->baseUnits->count() > 0) {
                $firstBase = $item->unit_item->baseUnits->first();
                if ($baseUnitName === null) {
                    $baseUnitName = $firstBase->name ?? $firstBase->short_name ?? null;
                }
                if ($baseUnitMultiplier === null || (float) $baseUnitMultiplier <= 0) {
                    if ($firstBase->pivot !== null) {
                        $m = $firstBase->pivot->multiplier ?? $firstBase->pivot->getAttribute('multiplier');
                        $baseUnitMultiplier = $m !== null && $m !== '' ? (float) $m : null;
                    }
                }
                // If pivot is 1 or null, but name has multiplier, prefer name multiplier
                if ($parsedMultiplierFromName !== null && ($baseUnitMultiplier === null || (float) $baseUnitMultiplier <= 1)) {
                    $baseUnitMultiplier = $parsedMultiplierFromName;
                }
            }
            // If still missing, but name had multiplier, set base to Liter with that multiplier
            if (($baseUnitName === null || $baseUnitMultiplier === null || (float) $baseUnitMultiplier <= 1) && $parsedMultiplierFromName !== null) {
                $baseUnitName = $baseUnitName ?: 'Liter';
                $baseUnitMultiplier = $parsedMultiplierFromName;
            }
        }

        $rawEntry = request()->get('entry_type');
        $entryType = is_array($rawEntry) ? (string) ($rawEntry[0] ?? '') : (string) $rawEntry;
        $entryType = strtolower(trim($entryType)) ?: null;
        $requestedBranchId = request()->query('branch_id');

        // If branch_id is provided: return ALL warehouses of that branch.
        // Claim: quantities come ONLY from claim_warehouse_items (0 if not found).
        // Normal: quantities come ONLY from warehouse_items (0 if not found).
        if ($requestedBranchId !== null && $requestedBranchId !== '') {
            $branch = \App\Models\Branch::find($requestedBranchId);
            $warehouses = \App\Models\Warehouse::where('branch_id', $requestedBranchId)->orderBy('warehouse_name')->get()->unique('id')->values();

            if ($entryType === 'claim') {
                $quantitiesByWarehouse = \App\Models\ClaimWarehouseItem::where('item_id', $id)
                    ->whereIn('warehouse_id', $warehouses->pluck('id'))
                    ->get()
                    ->keyBy('warehouse_id');
            } else {
                $quantitiesByWarehouse = \App\Models\WarehouseItem::where('item_id', $id)
                    ->whereIn('warehouse_id', $warehouses->pluck('id'))
                    ->get()
                    ->keyBy('warehouse_id');
            }

            $stockStatus = [];
            $branchName = $branch ? $branch->branch_name : 'No Branch';
            $branchCode = $branch ? ($branch->branch_code ?? '') : '';
            $totalQty = 0;

            $stockStatus[] = [
                'type' => 'branch',
                'id' => (int) $requestedBranchId,
                'name' => $branchName,
                'code' => $branchCode,
                // UI: don't show branch_code inside stock status list display.
                'display' => $branchName,
                'cartons' => 0,
                'loose' => 0,
                'loose_liters' => 0,
                'quantity' => 0,
                'unit' => $unitName,
                'base_unit' => $baseUnitName,
                'base_unit_multiplier' => $baseUnitMultiplier,
            ];

            $isOil = ($baseUnitName === 'Liter' && (float) $baseUnitMultiplier > 0);
            foreach ($warehouses as $warehouse) {
                $wi = $quantitiesByWarehouse->get($warehouse->id);
                $quantity = $wi ? floatval($wi->quantity ?? 0) : 0;
                $totalQty += $quantity;
                $cartons = $packingSize > 0 ? floor($quantity / $packingSize) : 0;
                $loose = $packingSize > 0 ? fmod($quantity, $packingSize) : 0;
                $looseLiters = $isOil && $packingSize > 0
                    ? (float) ($loose * ((float) $baseUnitMultiplier / $packingSize))
                    : 0;

                $whDisplay = $warehouse->warehouse_name . (($warehouse->warehouse_code ?? '') !== '' ? ' (' . $warehouse->warehouse_code . ')' : '');
                $stockStatus[] = [
                    'type' => 'warehouse',
                    'id' => $warehouse->id,
                    'name' => $warehouse->warehouse_name,
                    'code' => $warehouse->warehouse_code ?? '',
                    'display' => $whDisplay,
                    'cartons' => (int) $cartons,
                    'loose' => $loose,
                    'loose_liters' => $looseLiters,
                    'quantity' => $quantity,
                    'branch_id' => (int) $requestedBranchId,
                    'unit' => $unitName,
                    'base_unit' => $baseUnitName,
                    'base_unit_multiplier' => $baseUnitMultiplier,
                ];
            }

            // Update branch total
            $totalCartons = $packingSize > 0 ? (int) floor($totalQty / $packingSize) : 0;
            $totalLoose = $packingSize > 0 ? fmod($totalQty, $packingSize) : 0;
            $totalLooseLiters = $isOil && $packingSize > 0
                ? (float) ($totalLoose * ((float) $baseUnitMultiplier / $packingSize))
                : 0;
            $stockStatus[0]['quantity'] = $totalQty;
            $stockStatus[0]['cartons'] = $totalCartons;
            $stockStatus[0]['loose'] = $totalLoose;
            $stockStatus[0]['loose_liters'] = $totalLooseLiters;

            return response()->json($stockStatus);
        }

        // No branch filter: show only warehouses that have stock rows (claim/new)
        if ($entryType === 'claim') {
            $warehouseItems = \App\Models\ClaimWarehouseItem::with(['warehouse.branch'])
                ->where('item_id', $id)
                ->get();
        } else {
            // Default: use main (new) stock
            $warehouseItems = \App\Models\WarehouseItem::with(['warehouse.branch'])
                ->where('item_id', $id)
                ->get();
        }
        
        $stockStatus = [];
        $branchStocks = [];
        $isOil = ($baseUnitName === 'Liter' && (float) $baseUnitMultiplier > 0);

        foreach ($warehouseItems as $warehouseItem) {
            $warehouse = $warehouseItem->warehouse;
            $branch = $warehouse ? $warehouse->branch : null;
            $branchId = $branch ? $branch->id : 0;
            $branchName = $branch ? $branch->branch_name : 'No Branch';
            $branchCode = $branch ? $branch->branch_code : '';
            
            // Quantity source: claim vs normal (claim is fully isolated from new stock)
            if ($entryType === 'claim') {
                $quantity = floatval($warehouseItem->quantity ?? 0);
            } else {
                $quantity = floatval($warehouseItem->quantity ?? 0);
            }

            $cartons = $packingSize > 0 ? floor($quantity / $packingSize) : 0;
            $loose = $packingSize > 0 ? fmod($quantity, $packingSize) : 0;
            $looseLiters = $isOil && $packingSize > 0
                ? (float) ($loose * ((float) $baseUnitMultiplier / $packingSize))
                : 0;
            
            if (!isset($branchStocks[$branchId])) {
                $branchStocks[$branchId] = [
                    'branch_id' => $branchId,
                    'branch_name' => $branchName,
                    'branch_code' => $branchCode,
                    // Branch display can keep code if needed
                    'display' => $branchName,
                    'total_cartons' => 0,
                    'total_loose' => 0,
                    'total_loose_liters' => 0,
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
                'loose_liters' => $looseLiters,
                // Hatao warehouse code from display: show name only
                'display' => $warehouse->warehouse_name,
            ];
            
            $branchStocks[$branchId]['warehouses'][$warehouse->id] = $warehouseData;
            $branchStocks[$branchId]['total_cartons'] += $cartons;
            $branchStocks[$branchId]['total_loose'] += $loose;
            $branchStocks[$branchId]['total_loose_liters'] += $looseLiters;
        }
        
        // Convert to array format
        foreach ($branchStocks as $branchStock) {
            $branchQty = $branchStock['total_cartons'] * $packingSize + $branchStock['total_loose'];
            // Add branch total
            $stockStatus[] = [
                'type' => 'branch',
                'id' => $branchStock['branch_id'],
                'name' => $branchStock['branch_name'],
                'code' => $branchStock['branch_code'],
                'display' => $branchStock['display'],
                'cartons' => $branchStock['total_cartons'],
                'loose' => $branchStock['total_loose'],
                'loose_liters' => $branchStock['total_loose_liters'],
                'quantity' => $branchQty,
                'unit' => $unitName,
                'base_unit' => $baseUnitName,
                'base_unit_multiplier' => $baseUnitMultiplier,
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
                    'loose_liters' => $warehouse['loose_liters'],
                    'quantity' => $warehouse['quantity'],
                    'branch_id' => $branchStock['branch_id'],
                    'unit' => $unitName,
                    'base_unit' => $baseUnitName,
                    'base_unit_multiplier' => $baseUnitMultiplier,
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
        $submissionUuid = trim((string) $request->input('submission_uuid', ''));
        $submissionCacheKey = $submissionUuid !== '' ? ('sales_submit_response_'.$submissionUuid) : null;
        $submissionLockKey = $submissionUuid !== '' ? ('sales_submit_lock_'.$submissionUuid) : null;

        if ($submissionCacheKey && $request->ajax()) {
            $cachedResponse = session($submissionCacheKey);
            if (is_array($cachedResponse)) {
                return response()->json($cachedResponse);
            }
        }
        if ($submissionLockKey && $request->ajax()) {
            $acquired = Cache::add($submissionLockKey, time(), now()->addSeconds(45));
            if (! $acquired) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale save is already in progress. Please wait.'
                ], 409);
            }
        }

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
                'items.*.total' => 'required|numeric', // allow negative for claim/return/scrap
                'items.*.warranty_proofs' => 'nullable|array',
                'items.*.warranty_proofs.*.unit_no' => 'required_with:items.*.warranty_proofs|integer|min:1',
                'items.*.warranty_proofs.*.warehouse_id' => 'nullable|integer|exists:warehouses,id',
                'items.*.warranty_proofs.*.code' => 'nullable|string|max:255',
                'items.*.warranty_proofs.*.final_code' => 'nullable|string|max:255',
                'items.*.warranty_proofs.*.scanned_code' => 'nullable|string|max:255',
                'items.*.warranty_proofs.*.extracted_codes' => 'nullable|array',
                'items.*.warranty_proofs.*.extracted_codes.*' => 'nullable|string|max:255',
                'items.*.warranty_proofs.*.image_data' => 'nullable|string',
                'items.*.mileage_id' => 'nullable|integer|min:1',
                'items.*.mileage_name' => 'nullable|string|max:255',
                'items.*.line_note' => 'nullable|string|max:5000',
                'items.*.line_image' => 'nullable|string|max:700000',
                'items.*.temporary_item_name' => 'nullable|string|max:500',
                'items.*.temporary_quality' => 'nullable|string|max:255',
                'items.*.voice_transcript' => 'nullable|string|max:5000',
                'items.*.voice_data' => 'nullable|string|max:700000',
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
                'vehicles.*.make' => 'nullable|string|max:255',
                'vehicles.*.model' => 'nullable|string|max:255',
                'vehicles.*.year' => 'required|string|max:4',
                'vehicles.*.car_manufacturer_id' => 'nullable|integer|exists:car_manufacturers,id',
                'vehicles.*.car_model_id' => 'nullable|integer|exists:car_models,id',
                'vehicles.*.current_km' => 'nullable|string|max:50',
                'vehicles.*.next_km' => 'nullable|string|max:50',
                'vehicles.*.next_date' => 'nullable|string|max:20',
                'vehicles.*.daily_run_km' => 'nullable|string|max:50',
                'vehicles.*.interval_days' => 'nullable|numeric|min:0',
                'vehicles.*.interval_months' => 'nullable|numeric|min:0',
                'existing_sale_id' => 'nullable|integer|exists:sales,id',
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

        $isRetail = $this->isRetailCustomerId((int) $request->customer_id);
        if ($isRetail) {
            foreach (($request->items ?? []) as $idx => $itemData) {
                $itemId = isset($itemData['item_id']) ? (int) $itemData['item_id'] : 0;
                $itemModel = $itemId ? Item::find($itemId) : null;
                $isBatteryItem = $itemModel && (strtolower((string) ($itemModel->type ?? '')) === 'battery');
                if (!$isBatteryItem) {
                    continue; // only battery items require warranty-card proof
                }
                $qtyRaw = $itemData['quantity'] ?? null;
                $qty = is_numeric($qtyRaw) ? (float) $qtyRaw : 0.0;
                $qtyInt = (int) round($qty);
                if ($qtyInt < 1 || abs($qty - $qtyInt) > 0.00001) {
                    throw ValidationException::withMessages([
                        "items.$idx.quantity" => ['Retail battery sale requires integer quantity for warranty proof capture.'],
                    ]);
                }
                $proofs = $itemData['warranty_proofs'] ?? null;
                if (!is_array($proofs) || count($proofs) !== $qtyInt) {
                    throw ValidationException::withMessages([
                        "items.$idx.warranty_proofs" => ['Please attach warranty card proof for all selected quantity units.'],
                    ]);
                }
                $seen = [];
                foreach ($proofs as $pIdx => $p) {
                    $preferred = $p['final_code'] ?? ($p['code'] ?? null);
                    $codeNorm = $this->normalizeWarrantyCode(is_string($preferred) ? $preferred : null);
                    $hasImage = !empty($p['image_data']);
                    if (!$codeNorm && !$hasImage) {
                        throw ValidationException::withMessages([
                            "items.$idx.warranty_proofs.$pIdx" => ['Warranty proof requires an image or a scanned/entered code.'],
                        ]);
                    }
                    if ($codeNorm) {
                        if (isset($seen[$codeNorm])) {
                            throw ValidationException::withMessages([
                                "items.$idx.warranty_proofs.$pIdx.code" => ['Duplicate warranty code is not allowed for the same item.'],
                            ]);
                        }
                        $seen[$codeNorm] = true;
                    }
                }
            }
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
            $branchWarehouseIds = Warehouse::where('branch_id', $request->branch_id)->pluck('id')->all();
            $editingSale = null;
            if ($request->filled('existing_sale_id')) {
                $editingSale = Sale::lockForUpdate()->with('saleItems')->findOrFail((int) $request->input('existing_sale_id'));
                $this->reverseSaleStockEffectsForEdit($editingSale);
                SaleItemWarrantyProof::where('sale_id', $editingSale->id)->delete();
                SaleItemWarrantyCode::where('sale_id', $editingSale->id)->delete();
                SalePayment::where('sale_id', $editingSale->id)->delete();
                $editingSale->saleItems()->delete();
            }
            foreach ($request->items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $saleQuantity = floatval($itemData['quantity']);
                $rawEntry = $itemData['entry_type'] ?? 'purchase';
                $entryType = is_array($rawEntry) ? (string) ($rawEntry[0] ?? 'purchase') : (string) $rawEntry;
                $entryType = strtolower(trim($entryType)) ?: 'purchase';
                $itemWarehouseId = !empty($itemData['warehouse_id']) ? (int) $itemData['warehouse_id'] : null;
                $checkWarehouse = $itemWarehouseId ? Warehouse::where('id', $itemWarehouseId)->where('branch_id', $request->branch_id)->first() : null;
                $wh = ($checkWarehouse ?: $warehouse);

                // Placeholder / temporary lines: no stock check (inventory added later).
                if ($entryType === 'placeholder' || $entryType === 'temporary') {
                    continue;
                }

                // Claim In requires a warehouse for the selected branch
                if ($entryType === 'claim' && $wh === null) {
                    throw new \Exception('Claim In requires a warehouse. Please select a warehouse for the branch or ensure the branch has a warehouse.');
                }

                // Claim In is isolated: it must NOT depend on new stock availability.
                // Scrap In uses entry_type="scrap" (and sometimes entry_type="scrap_in"): treat both as incoming.
                $isIncomingScrap = in_array($entryType, ['scrap', 'scrap_in'], true);
                if ($entryType !== 'claim' && !$isIncomingScrap) {
                    $warehouseItem = WarehouseItem::lockForUpdate()
                        ->where('warehouse_id', $wh->id)
                        ->where('item_id', $itemData['item_id'])
                        ->first();

                    if (!$warehouseItem) {
                        // Fallback: if warehouse selection is missing/mismatched, use any warehouse in the branch
                        // that has stock row for this item.
                        $warehouseItem = WarehouseItem::lockForUpdate()
                            ->where('item_id', $itemData['item_id'])
                            ->whereIn('warehouse_id', $branchWarehouseIds)
                            ->first();

                        if (!$warehouseItem) {
                            throw new \Exception("Item '{$item->bar_code}' not found in warehouse stock.");
                        }
                    }

                    $availableQuantity = floatval($warehouseItem->available_quantity ?? 0);
                    if ($availableQuantity < $saleQuantity) {
                        throw new \Exception("Insufficient stock for item '{$item->bar_code}'. Available: {$availableQuantity}, Required: {$saleQuantity}");
                    }
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

            $status = $request->status ?? 'pending';
            if ($editingSale) {
                $sale = $editingSale;
                $reference = $request->filled('reference') ? trim((string) $request->reference) : (string) ($sale->reference ?? '');
                if ($reference === '') {
                    $reference = (string) ($sale->reference ?? '');
                }
                $sale->update([
                    'customer_id' => $request->customer_id,
                    'branch_id' => $request->branch_id,
                    'sale_date' => $request->sale_date,
                    'reference' => $reference,
                    'status' => $status,
                    'subtotal' => $itemsTotal,
                    'order_tax' => $orderTax,
                    'discount' => $discount,
                    'shipping' => $shipping,
                    'grand_total' => $grandTotal,
                ]);
                $sale->refresh();
            } else {
                $reference = $status === 'estimate'
                    ? 'EST #' . $this->getNextReferenceNumberForBranchAndStatus((int) $request->branch_id, 'estimate', 'EST')
                    : ($status === 'sale_order'
                        ? 'SO #' . $this->getNextReferenceNumberForBranchAndStatus((int) $request->branch_id, 'sale_order', 'SO')
                        : 'INV #' . $this->getNextReferenceNumberForBranchAndStatus((int) $request->branch_id, 'pending', 'INV'));

                $sale = Sale::create([
                    'customer_id' => $request->customer_id,
                    'branch_id' => $request->branch_id,
                    'sale_date' => $request->sale_date,
                    'reference' => $reference,
                    'status' => $status,
                    'subtotal' => $itemsTotal,
                    'order_tax' => $orderTax,
                    'discount' => $discount,
                    'shipping' => $shipping,
                    'grand_total' => $grandTotal,
                    'user_id' => auth()->id(),
                ]);
            }

            $this->processSaleStoreItemsBody($request, $sale, $warehouse, $branchWarehouseIds, $isRetail);

            DB::commit();

            // Clear purchase_to_sale session data after successful sale creation
            if (session()->has('purchase_to_sale')) {
                session()->forget('purchase_to_sale');
            }

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                $responsePayload = [
                    'success' => true,
                    'message' => $request->filled('existing_sale_id') ? 'Sale updated successfully!' : 'Sale created successfully!',
                    'sale_id' => $sale->id,
                    'invoice_no' => $sale->reference,
                ];
                if ($submissionCacheKey) {
                    session([$submissionCacheKey => $responsePayload]);
                }

                return response()->json($responsePayload);
            }

            return redirect()->route('all_sales')
                ->with('success', $request->filled('existing_sale_id') ? 'Sale updated successfully!' : 'Sale created successfully!');

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
        } finally {
            if ($submissionLockKey) {
                Cache::forget($submissionLockKey);
            }
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
     * Thermal receipt / invoice print view (80mm browser print only).
     */
    public function printInvoice(Request $request, $id)
    {
        $sale = Sale::with([
            'customer',
            'branch',
            'payments',
            'saleItems.item.partnumber_item',
            'saleItems.item.category',
        ])->findOrFail($id);

        $returnTo = $request->query('return', 'show');
        if (! in_array($returnTo, ['show', 'list'], true)) {
            $returnTo = 'show';
        }

        $thermalPaperMm = (string) $request->query('paper', '80');
        if (! in_array($thermalPaperMm, ['58', '80'], true)) {
            $thermalPaperMm = '80';
        }
        $thermalAutoCut = $request->boolean('autocut', true);

        return view('admin.sales.print', compact('sale', 'returnTo', 'thermalPaperMm', 'thermalAutoCut'));
    }

    /**
     * Structured payload for browser-side thermal printer integrations.
     */
    public function printPayload($id)
    {
        $sale = Sale::with([
            'customer',
            'branch',
            'payments',
            'saleItems.item.partnumber_item',
            'saleItems.item.category',
        ])->findOrFail($id);

        $items = $sale->saleItems->map(function ($saleItem) {
            $item = $saleItem->item;
            $entryType = (string) ($saleItem->entry_type ?? '');
            $isTemporary = $entryType === 'temporary';
            $isPlaceholder = $entryType === 'placeholder';

            $itemName = $item
                ? ($item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A')
                : 'N/A';

            if ($item && $item->partnumber_item) {
                $itemName = $item->partnumber_item->name ?? $itemName;
            }
            if ($item && $item->category) {
                $itemName .= ' - ' . $item->category->name;
            }
            if ($isTemporary) {
                $itemName = $saleItem->temporary_item_name ?: ($saleItem->voice_transcript ?: 'Temporary item');
            } elseif ($isPlaceholder) {
                $itemName = $saleItem->line_note ?: 'Placeholder line';
            }

            return [
                'name' => $itemName,
                'quantity' => (float) $saleItem->quantity,
                'rate' => (float) $saleItem->rate,
                'total' => (float) $saleItem->total,
            ];
        })->values();

        $subtotal = $sale->subtotal;
        if ($subtotal === null || $subtotal === '') {
            $subtotal = (float) $sale->saleItems->sum('total');
        }

        $customer = $sale->customer;
        $customerName = null;
        if ($customer && is_array($customer->names ?? null) && isset($customer->names[0])) {
            $customerName = $customer->names[0];
        }
        $customerPhone = '';
        if ($customer && is_array($customer->phones ?? null) && isset($customer->phones[0])) {
            $customerPhone = trim((string) $customer->phones[0]);
        }
        $customerLine = trim($customerPhone.' '.($customerName ?? ''));
        if ($customerLine === '') {
            $customerLine = null;
        }

        $totalPaid = (float) $sale->total_paid;
        $discountCalc = (float) ($sale->discount ?? 0);
        if ($discountCalc > 0 && $totalPaid == 0) {
            $balance = max(0, (float) $sale->grand_total - $discountCalc);
        } else {
            $balance = max(0, (float) $sale->grand_total - $totalPaid);
        }

        $refStr = (string) ($sale->reference ?? '');
        if (preg_match('/(\d+)/', $refStr, $m)) {
            $invoiceShort = $m[1];
        } else {
            $invoiceShort = (string) $sale->id;
        }

        $totalQty = (float) $sale->saleItems->sum('quantity');
        $lineCount = $sale->saleItems->count();

        $logoPath = setting_value('logo');
        if ($logoPath && (str_starts_with((string) $logoPath, 'http://') || str_starts_with((string) $logoPath, 'https://'))) {
            $logoUrl = $logoPath;
        } elseif ($logoPath) {
            $logoUrl = asset(ltrim((string) $logoPath, '/'));
        } else {
            $logoUrl = asset('assets/img/logo.svg');
        }

        $refClean = $sale->reference ? preg_replace('/[^A-Z0-9]/i', '', (string) $sale->reference) : '';
        $barcodeText = strlen($refClean) >= 4
            ? strtoupper(substr($refClean, 0, 40))
            : ('SALE'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT));

        return response()->json([
            'id' => (int) $sale->id,
            'invoice_no' => $sale->reference ?: ('SALE-' . $sale->id),
            'invoice_number_short' => $invoiceShort,
            'logo_url' => $logoUrl,
            'barcode_text' => $barcodeText,
            'include_barcode' => false,
            'sale_date' => optional($sale->sale_date)->format('d M Y'),
            'sale_date_slash' => optional($sale->sale_date)->format('d/m/Y'),
            'sale_time' => optional($sale->created_at)->format('H:i'),
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone !== '' ? $customerPhone : null,
            'customer_line' => $customerLine,
            'branch_name' => optional($sale->branch)->branch_name,
            'shop' => [
                'name' => setting_value('logo_text', 'MUBARAK TRADERS'),
                'phone' => setting_value('helpline', '+92-335-08-999-08'),
                'email' => setting_value('email', ''),
                'address' => trim(implode(', ', array_filter([
                    setting_value('address', ''),
                    setting_value('city', ''),
                    setting_value('state', ''),
                    setting_value('zip', ''),
                    setting_value('country', ''),
                ]))),
            ],
            'bank' => [
                'bank_name' => setting_value('bank_name', ''),
                'account_holder' => setting_value('account_holder_name', setting_value('logo_text', '')),
                'account_number' => setting_value('account_number', ''),
                'ifsc' => setting_value('ifsc_code', ''),
            ],
            'terms_footer' => setting_value('thermal_terms', 'Thank you for doing business with us.'),
            'items' => $items,
            'subtotal' => (float) $subtotal,
            'discount' => (float) ($sale->discount ?? 0),
            'tax' => (float) ($sale->order_tax ?? 0),
            'shipping' => (float) ($sale->shipping ?? 0),
            'grand_total' => (float) ($sale->grand_total ?? 0),
            'total_qty' => $totalQty,
            'line_count' => $lineCount,
            'total_paid' => $totalPaid,
            'balance' => $balance,
        ]);
    }

    /**
     * Edit sale — same screen as create/sale/new.
     */
    public function edit($id)
    {
        $sale = Sale::with([
            'customer',
            'branch',
            'saleItems' => function ($q) {
                $q->orderBy('id', 'asc');
            },
            'saleItems.item.partnumber_item',
            'saleItems.item.company_item',
            'saleItems.item.quality_item',
            'saleItems.item.category',
            'saleItems.item.subcategory',
            'saleItems.item.product_item',
            'saleItems.warehouse',
        ])->findOrFail($id);

        $customers = Customer::with('customerCars', 'branch')->orderBy('created_at', 'desc')->get();
        $branches = \App\Models\Branch::where('status', 'active')->orderBy('branch_name', 'asc')->get();
        $units = \App\Models\Unit::all();
        $suppliers = \App\Models\Supplier::orderBy('created_at', 'desc')->get();
        $mileages = Mileage::orderBy('name')->get();
        $temporaryItemId = Item::where('bar_code', '__SALE_TEMPORARY__')->value('id');
        $saleEditPayload = $this->buildSaleEditPayload($sale);

        return view('admin.sales.create-new', compact(
            'customers',
            'branches',
            'units',
            'suppliers',
            'mileages',
            'temporaryItemId',
            'sale',
            'saleEditPayload'
        ));
    }

    /**
     * Update sale (header only). When item-level edit is added: for claim lines,
     * reverse old claim_warehouse_items quantity by sale_item.warehouse_id then apply new quantity.
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
            // Reverse stock: claim lines reduce claim_warehouse_items; other lines restore WarehouseItem
            $warehouse = Warehouse::where('branch_id', $sale->branch_id)->first();
            foreach ($sale->saleItems as $saleItem) {
                $entryType = strtolower(trim((string) ($saleItem->entry_type ?? 'purchase')));
                $qty = (float) $saleItem->quantity;

                if ($entryType === 'claim') {
                    // Reverse claim stock when sale is deleted
                    $whId = $saleItem->warehouse_id ?? $warehouse?->id;
                    if ($whId && $qty > 0) {
                        $claimRow = ClaimWarehouseItem::lockForUpdate()
                            ->where('warehouse_id', $whId)
                            ->where('item_id', $saleItem->item_id)
                            ->first();
                        if ($claimRow) {
                            $claimRow->quantity = max(0, (float) $claimRow->quantity - $qty);
                            $claimRow->save();
                            Log::info('Claim stock reversed on sale delete', [
                                'sale_id' => $sale->id,
                                'item_id' => $saleItem->item_id,
                                'warehouse_id' => $whId,
                                'quantity_reversed' => $qty,
                            ]);
                        }
                    }
                } elseif ($warehouse) {
                    $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
                        ->where('item_id', $saleItem->item_id)
                        ->first();

                    // Scrap In lines are stored as entry_type="scrap" (incoming) or entry_type="scrap_in".
                    $isScrapIn = ($entryType === 'scrap_in') || ($entryType === 'scrap');

                    if ($warehouseItem) {
                        if ($isScrapIn) {
                            // scrap_in originally increased stock; deleting sale should undo by subtracting.
                            $warehouseItem->quantity = max(0, (float) $warehouseItem->quantity - $qty);
                        } else {
                            // outgoing movements: sale/scrap_sale/return restore by adding back
                            $warehouseItem->quantity = (float) $warehouseItem->quantity + $qty;
                        }
                        $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                        $warehouseItem->save();
                    }

                    $item = Item::find($saleItem->item_id);
                    if ($item) {
                        if ($isScrapIn) {
                            $item->on_hand = max(0, (float) ($item->on_hand ?? 0) - $qty);
                        } else {
                            $item->on_hand = (float) ($item->on_hand ?? 0) + $qty;
                        }
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

    /**
     * Get last 5 sale price history for an item (for add-item modal).
     */
    public function getItemSaleHistory($id)
    {
        $item = Item::findOrFail($id);

        $saleHistory = SaleItem::with(['sale.customer'])
            ->where('item_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $history = [];
        foreach ($saleHistory as $line) {
            $sale = $line->sale;
            $customerName = 'Walk-in';
            if ($sale && $sale->customer) {
                $names = $sale->customer->names;
                if (is_array($names) && !empty($names)) {
                    $customerName = $names[0];
                } elseif (is_string($names)) {
                    $customerName = $names;
                }
            }
            $saleDate = $sale && $sale->sale_date ? $sale->sale_date : $line->created_at;
            $dt = $saleDate ? \Carbon\Carbon::parse($saleDate) : \Carbon\Carbon::parse($line->created_at);
            $dtTime = $sale && $sale->created_at ? \Carbon\Carbon::parse($sale->created_at) : $dt;
            $daysAgo = $dt->diffInDays(now());

            $history[] = [
                'customer_name' => $customerName,
                'quantity' => (float) $line->quantity,
                'unit' => $line->unit ?? 'Unit',
                'rate' => (float) $line->rate,
                'sale_date' => $dt->format('d/m/Y'),
                'sale_date_time' => $dtTime->format('d/m/Y h:i A'),
                'days_ago' => $daysAgo,
            ];
        }

        return response()->json([
            'item_id' => (int) $item->id,
            'history' => $history,
        ]);
    }

    /**
     * Get claim-specific history for an item and customer: sale history (when this item was sold to this customer)
     * and last 5 returns for this customer for this item. Used in Claim In / Claim Return / Claim Send flow.
     */
    public function getClaimItemHistory($id, Request $request)
    {
        $item = Item::findOrFail($id);
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
        ]);
        $customerId = isset($validated['customer_id']) ? (int) $validated['customer_id'] : null;

        $excludedEntryTypes = ['return', 'claim', 'scrap', 'scrap_in', 'scrap_sale', 'delivery'];

        // Section 1: latest 5 sales of this product across all customers (Claim In context)
        $last5GlobalLines = SaleItem::query()
            ->with([
                'sale.customer',
                'sale.branch',
                'warehouse',
                'warrantyProofs',
            ])
            ->where('sale_items.item_id', $id)
            ->where(function ($q) use ($excludedEntryTypes) {
                $q->whereNull('entry_type')
                    ->orWhereNotIn('entry_type', $excludedEntryTypes);
            })
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->orderByDesc('sales.sale_date')
            ->orderByDesc('sales.id')
            ->orderByDesc('sale_items.created_at')
            ->select('sale_items.*')
            ->limit(5)
            ->get();

        $last5AllCustomers = [];
        foreach ($last5GlobalLines as $line) {
            $sale = $line->sale;
            $custName = '—';
            if ($sale && $sale->customer) {
                $names = $sale->customer->names;
                if (is_array($names) && ! empty($names)) {
                    $custName = $names[0];
                } elseif (is_string($names)) {
                    $custName = $names;
                }
            }
            $saleDate = $sale && $sale->sale_date ? $sale->sale_date : $line->created_at;
            $dt = $saleDate ? \Carbon\Carbon::parse($saleDate) : \Carbon\Carbon::parse($line->created_at);
            $dtTime = $sale && $sale->created_at ? \Carbon\Carbon::parse($sale->created_at) : $dt;
            $branchName = optional($sale?->branch)->branch_name;
            $warehouseName = optional($line->warehouse)->warehouse_name;
            $hasProofImages = $line->warrantyProofs->contains(fn ($p) => ! empty($p->proof_image));
            $last5WarrantyProofsPayload = [];
            foreach ($line->warrantyProofs as $proof) {
                $img = $proof->proof_image ? asset($proof->proof_image) : null;
                $last5WarrantyProofsPayload[] = [
                    'unit_no' => (int) ($proof->unit_no ?? 0),
                    'code' => $proof->proof_code,
                    'image_url' => $img,
                ];
            }
            $last5AllCustomers[] = [
                'sale_id' => $sale?->id,
                'sale_item_id' => $line->id,
                'customer_name' => $custName,
                'quantity' => (float) $line->quantity,
                'unit' => $line->unit ?? 'Unit',
                'rate' => (float) $line->rate,
                'sale_date' => $dt->format('d/m/Y'),
                'sale_date_time' => $dtTime->format('d/m/Y h:i A'),
                'reference' => $sale->reference ?? null,
                'branch_name' => $branchName,
                'warehouse_name' => $warehouseName,
                'warranty_proofs' => $last5WarrantyProofsPayload,
                'has_warranty_proof' => $line->warrantyProofs->isNotEmpty(),
                'has_warranty_images' => $hasProofImages,
            ];
        }

        // Section 2: sales to this customer for this item (actual sales only — not returns, claim-in, scrap, etc.)
        $saleHistory = [];
        $returnHistory = [];
        if ($customerId) {
            $saleItems = SaleItem::query()
                ->with([
                    'sale.customer',
                    'sale.branch',
                    'warehouse',
                    'warrantyProofs',
                    'warrantyCodes',
                ])
                ->where('sale_items.item_id', $id)
                ->whereHas('sale', fn ($q) => $q->where('customer_id', $customerId))
                ->where(function ($q) use ($excludedEntryTypes) {
                    $q->whereNull('entry_type')
                        ->orWhereNotIn('entry_type', $excludedEntryTypes);
                })
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->orderByDesc('sales.sale_date')
                ->orderByDesc('sales.id')
                ->orderByDesc('sale_items.created_at')
                ->select('sale_items.*')
                ->limit(25)
                ->get();

            foreach ($saleItems as $line) {
            $sale = $line->sale;
            $customerName = 'Walk-in';
            if ($sale && $sale->customer) {
                $names = $sale->customer->names;
                if (is_array($names) && ! empty($names)) {
                    $customerName = $names[0];
                } elseif (is_string($names)) {
                    $customerName = $names;
                }
            }
            $saleDate = $sale && $sale->sale_date ? $sale->sale_date : $line->created_at;
            $dt = $saleDate ? \Carbon\Carbon::parse($saleDate) : \Carbon\Carbon::parse($line->created_at);
            $dtTime = $sale && $sale->created_at ? \Carbon\Carbon::parse($sale->created_at) : $dt;

            $branchName = optional($sale?->branch)->branch_name;
            $warehouseName = optional($line->warehouse)->warehouse_name;

            $warrantyProofsPayload = [];
            foreach ($line->warrantyProofs as $proof) {
                $img = $proof->proof_image ? asset($proof->proof_image) : null;
                $warrantyProofsPayload[] = [
                    'unit_no' => (int) ($proof->unit_no ?? 0),
                    'code' => $proof->proof_code,
                    'image_url' => $img,
                ];
            }

            $codesFinal = $line->warrantyCodes->where('is_final', true)->pluck('code')->filter()->unique()->values()->all();
            $codesAll = $line->warrantyCodes->pluck('code')->filter()->unique()->values()->all();
            $codesFromProofs = $line->warrantyProofs->pluck('proof_code')->filter()->unique()->values()->all();
            $codesDisplay = ! empty($codesFinal) ? $codesFinal : (! empty($codesAll) ? $codesAll : $codesFromProofs);

            $hasProofImages = collect($warrantyProofsPayload)->contains(fn ($p) => ! empty($p['image_url']));

                $saleHistory[] = [
                    'sale_id' => $sale?->id,
                    'sale_item_id' => $line->id,
                    'customer_name' => $customerName,
                    'quantity' => (float) $line->quantity,
                    'unit' => $line->unit ?? 'Unit',
                    'rate' => (float) $line->rate,
                    'sale_date' => $dt->format('d/m/Y'),
                    'sale_date_time' => $dtTime->format('d/m/Y h:i A'),
                    'reference' => $sale->reference ?? null,
                    'branch_name' => $branchName,
                    'warehouse_name' => $warehouseName,
                    'warranty_codes' => $codesDisplay,
                    'warranty_codes_final' => $codesFinal,
                    'warranty_proofs' => $warrantyProofsPayload,
                    'has_warranty_proof' => $hasProofImages || $line->warrantyProofs->isNotEmpty(),
                ];
            }

            // Last 5 returns for this customer for this item
            $returnItems = SaleItem::with(['sale'])
                ->where('sale_items.item_id', $id)
                ->whereHas('sale', fn ($q) => $q->where('customer_id', $customerId))
                ->where('entry_type', 'return')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->orderByDesc('sale_items.created_at')
                ->select('sale_items.*')
                ->limit(5)
                ->get();

            foreach ($returnItems as $line) {
                $sale = $line->sale;
                $created = $line->created_at ? \Carbon\Carbon::parse($line->created_at) : now();
                $returnHistory[] = [
                    'return_date' => $created->format('d/m/Y'),
                    'return_date_time' => $created->format('d/m/Y h:i A'),
                    'quantity' => (float) $line->quantity,
                    'unit' => $line->unit ?? 'Unit',
                    'rate' => (float) $line->rate,
                    'reference' => $sale->reference ?? null,
                ];
            }
        }

        return response()->json([
            'item_id' => (int) $item->id,
            'customer_id' => $customerId,
            'last_5_all_customers' => $last5AllCustomers,
            'sale_history' => $saleHistory,
            'return_history' => $returnHistory,
        ]);
    }

    /**
     * Get latest service bill (repeat source) for selected customer/vehicle.
     * Prefers bill on vehicle last_visit_date, then falls back to latest non-estimate bill.
     */
    public function getLatestServiceBillForVehicle(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'plate_number' => 'nullable|string|max:255',
        ]);

        $customerId = (int) $validated['customer_id'];
        $branchId = isset($validated['branch_id']) ? (int) $validated['branch_id'] : null;
        $plateNorm = strtoupper(preg_replace('/\s+/', '', trim((string) ($validated['plate_number'] ?? ''))));

        $vehicle = null;
        if ($plateNorm !== '') {
            $vehicle = CustomerCar::where('customer_id', $customerId)
                ->get()
                ->first(function (CustomerCar $c) use ($plateNorm) {
                    $p = strtoupper(preg_replace('/\s+/', '', trim((string) ($c->plate_number ?? ''))));
                    return $p === $plateNorm;
                });
        }

        $baseQuery = Sale::query()
            ->where('customer_id', $customerId)
            ->whereNotIn('status', ['estimate', 'sale_order'])
            ->with([
                'saleItems' => function ($q) {
                    $q->orderBy('id', 'asc');
                },
                'saleItems.item.partnumber_item',
                'saleItems.item.company_item',
                'saleItems.item.quality_item',
                'saleItems.item.category',
                'saleItems.item.subcategory',
                'saleItems.item.product_item',
                'saleItems.warehouse',
            ]);

        if ($branchId) {
            $baseQuery->where('branch_id', $branchId);
        }

        $sale = null;
        if ($vehicle && $vehicle->last_visit_date) {
            $visitDate = $vehicle->last_visit_date instanceof \Carbon\Carbon
                ? $vehicle->last_visit_date->toDateString()
                : substr((string) $vehicle->last_visit_date, 0, 10);
            if ($visitDate !== '') {
                $sale = (clone $baseQuery)
                    ->whereDate('sale_date', $visitDate)
                    ->orderByDesc('id')
                    ->first();
            }
        }
        if (! $sale) {
            $sale = (clone $baseQuery)
                ->orderByDesc('sale_date')
                ->orderByDesc('id')
                ->first();
        }

        if (! $sale) {
            return response()->json([
                'success' => false,
                'message' => 'No previous service bill found for this vehicle/customer.',
            ], 404);
        }

        $items = [];
        foreach ($sale->saleItems as $line) {
            $item = $line->item;
            if (! $item) {
                continue;
            }

            $categoryName = trim((string) optional($item->category)->name);
            $subcategoryName = trim((string) optional($item->subcategory)->name);
            $itemType = strtolower(trim((string) ($item->type ?? '')));
            $displayName = trim((string) ($item->short_disc ?? ''));
            if ($displayName === '') {
                $displayName = trim((string) ($item->pro_dis ?? ''));
            }
            if ($displayName === '') {
                $displayName = trim((string) ($item->bar_code ?? ''));
            }
            if ($displayName === '') {
                $displayName = 'Item #' . $line->item_id;
            }

            $items[] = [
                'item_id' => (int) $line->item_id,
                'name' => $displayName,
                'item_type' => $itemType !== '' ? $itemType : null,
                'part_number' => trim((string) optional($item->partnumber_item)->name) ?: null,
                'quality_name' => trim((string) optional($item->quality_item)->name) ?: null,
                'company_name' => trim((string) optional($item->company_item)->name) ?: null,
                'category_name' => $categoryName !== '' ? $categoryName : null,
                'product_type_label' => ItemProductTypeLabel::resolve($categoryName, $itemType, $subcategoryName !== '' ? $subcategoryName : null),
                'product_title' => trim(strip_tags((string) optional($item->product_item)->name)) ?: null,
                'quantity' => (float) $line->quantity,
                'unit' => $line->unit ?: 'Unit',
                'rate' => (float) $line->rate,
                'discount' => (float) ($line->discount ?? 0),
                'tax_percentage' => (float) ($line->tax_percentage ?? 0),
                'tax_amount' => (float) ($line->tax_amount ?? 0),
                'total' => (float) $line->total,
                'warranty' => $line->warranty ?: null,
                'entry_type' => (string) ($line->entry_type ?: 'sale'),
                'warehouse_id' => $line->warehouse_id ? (int) $line->warehouse_id : null,
                'warehouse_name' => optional($line->warehouse)->warehouse_name ?: null,
            ];
        }

        return response()->json([
            'success' => true,
            'sale_id' => (int) $sale->id,
            'reference' => (string) ($sale->reference ?? ''),
            'sale_date' => optional($sale->sale_date)->format('Y-m-d'),
            'items' => $items,
            'vehicle' => $vehicle ? [
                'plate_number' => (string) ($vehicle->plate_number ?? ''),
                'last_visit_date' => $vehicle->last_visit_date ? (string) $vehicle->last_visit_date : null,
            ] : null,
        ]);
    }

    /**
     * AJAX: search saved Temporary Sale item names for autocomplete (branch-scoped).
     */
    public function searchTemporaryItemNames(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $branchId = (int) $request->query('branch_id', 0);
        if ($branchId < 1) {
            return response()->json(['results' => []]);
        }

        $q = trim((string) $request->query('q', ''));
        $limit = min(50, max(5, (int) $request->query('limit', 40)));

        $results = TemporaryItemNameSuggestion::searchForBranch($branchId, $q, $limit);

        return response()->json([
            'results' => $results->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'display_name' => (string) $row->display_name,
                    'last_rate' => $row->last_rate !== null ? (float) $row->last_rate : null,
                    'last_quality' => $row->last_quality !== null ? (string) $row->last_quality : null,
                ];
            })->values()->all(),
        ]);
    }

    /**
     * Undo warehouse / claim / on_hand effects for a sale before replacing lines (same logic as destroy).
     */
    private function reverseSaleStockEffectsForEdit(Sale $sale): void
    {
        $warehouse = Warehouse::where('branch_id', $sale->branch_id)->first();
        foreach ($sale->saleItems as $saleItem) {
            $entryType = strtolower(trim((string) ($saleItem->entry_type ?? 'purchase')));
            $qty = (float) $saleItem->quantity;

            if ($entryType === 'claim') {
                $whId = $saleItem->warehouse_id ?? $warehouse?->id;
                if ($whId && $qty > 0) {
                    $claimRow = ClaimWarehouseItem::lockForUpdate()
                        ->where('warehouse_id', $whId)
                        ->where('item_id', $saleItem->item_id)
                        ->first();
                    if ($claimRow) {
                        $claimRow->quantity = max(0, (float) $claimRow->quantity - $qty);
                        $claimRow->save();
                    }
                }
            } elseif ($warehouse) {
                $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
                    ->where('item_id', $saleItem->item_id)
                    ->first();

                $isScrapIn = ($entryType === 'scrap_in') || ($entryType === 'scrap');

                if ($warehouseItem) {
                    if ($isScrapIn) {
                        $warehouseItem->quantity = max(0, (float) $warehouseItem->quantity - $qty);
                    } else {
                        $warehouseItem->quantity = (float) $warehouseItem->quantity + $qty;
                    }
                    $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
                    $warehouseItem->save();
                }

                $item = Item::find($saleItem->item_id);
                if ($item) {
                    if ($isScrapIn) {
                        $item->on_hand = max(0, (float) ($item->on_hand ?? 0) - $qty);
                    } else {
                        $item->on_hand = (float) ($item->on_hand ?? 0) + $qty;
                    }
                    $item->save();
                }
            }
        }
    }

    /**
     * After Sale record exists: lines, stock, optional payment, vehicles.
     */
    private function processSaleStoreItemsBody(Request $request, Sale $sale, Warehouse $warehouse, array $branchWarehouseIds, bool $isRetail): void
    {
        // Check if any item has supplier selected (zero stock items)
        $hasSupplierItems = false;
        foreach ($request->items as $itemData) {
            $supplierId = $itemData['supplier_id'] ?? null;
            $isZeroStock = isset($itemData['is_zero_stock']) && $itemData['is_zero_stock'] == true;
            if ($supplierId && $isZeroStock) {
                $hasSupplierItems = true;
                break;
            }
        }
        
        // Set status to pending if supplier items exist
        if ($hasSupplierItems) {
            $sale->status = 'pending';
            $sale->save();
        }
        
        // Debug: log incoming request payload for claim debugging
        Log::info('Claim In store: request payload', [
            'branch_id' => $request->branch_id,
            'customer_id' => $request->customer_id,
            'items_count' => count($request->items),
            'items_raw' => array_map(function ($it) {
                return [
                    'item_id' => $it['item_id'] ?? null,
                    'entry_type' => $it['entry_type'] ?? null,
                    'warehouse_id' => $it['warehouse_id'] ?? null,
                    'quantity' => $it['quantity'] ?? null,
                ];
            }, $request->items),
        ]);

        foreach ($request->items as $itemData) {
            // Normalize entry_type (may come as string or array from form)
            $rawEntryType = $itemData['entry_type'] ?? 'purchase';
            $entryTypeNormalized = is_array($rawEntryType) ? (string) ($rawEntryType[0] ?? 'purchase') : (string) $rawEntryType;
            $entryTypeNormalized = strtolower(trim($entryTypeNormalized)) ?: 'purchase';

            // Resolve warehouse for this line (needed for claim stock and for storing on sale_item)
            $itemWarehouseId = !empty($itemData['warehouse_id']) ? (int) $itemData['warehouse_id'] : null;
            $itemWarehouse = $itemWarehouseId ? Warehouse::where('id', $itemWarehouseId)->where('branch_id', $request->branch_id)->first() : null;
            $whForLine = ($itemWarehouse ?: $warehouse);

            if ($entryTypeNormalized === 'claim' && $whForLine === null) {
                throw new \Exception('Claim In requires a warehouse. Please select a warehouse for the branch or ensure the branch has a warehouse.');
            }

            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'item_id' => $itemData['item_id'],
                'warehouse_id' => $whForLine?->id,
                'entry_type' => $entryTypeNormalized,
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'] ?? 'Unit',
                'rate' => $itemData['rate'],
                'discount' => $itemData['discount'] ?? 0,
                'tax_percentage' => $itemData['tax_percentage'] ?? 0,
                'tax_amount' => $itemData['tax_amount'] ?? 0,
                'total' => $itemData['total'],
                'warranty' => $itemData['warranty'] ?? null,
                'line_note' => $itemData['line_note'] ?? null,
                'line_image' => $itemData['line_image'] ?? null,
                'temporary_item_name' => $itemData['temporary_item_name'] ?? null,
                'temporary_quality' => $itemData['temporary_quality'] ?? null,
                'voice_transcript' => $itemData['voice_transcript'] ?? null,
                'voice_data' => $itemData['voice_data'] ?? null,
            ]);

            if ($entryTypeNormalized === 'temporary') {
                $displayForSuggestion = trim((string) ($itemData['temporary_item_name'] ?? ''));
                if ($displayForSuggestion === '') {
                    $displayForSuggestion = trim((string) ($itemData['voice_transcript'] ?? ''));
                }
                if ($displayForSuggestion !== '') {
                    TemporaryItemNameSuggestion::recordUsage(
                        (int) $request->branch_id,
                        $displayForSuggestion,
                        isset($itemData['rate']) ? (float) $itemData['rate'] : null,
                        isset($itemData['temporary_quality']) ? (trim((string) $itemData['temporary_quality']) !== '' ? trim((string) $itemData['temporary_quality']) : null) : null
                    );
                }
            }

            if ($isRetail) {
                $itemModel = Item::find((int) $itemData['item_id']);
                $isBatteryItem = $itemModel && (strtolower((string) ($itemModel->type ?? '')) === 'battery');
                if ($isBatteryItem) {
                    $proofs = is_array($itemData['warranty_proofs'] ?? null) ? $itemData['warranty_proofs'] : [];
                    foreach ($proofs as $p) {
                        $imgPath = null;
                        if (!empty($p['image_data'])) {
                            $imgPath = $this->saveWarrantyProofImageDataUrl((string) $p['image_data']);
                        }
                        $preferredCode = $p['final_code'] ?? ($p['code'] ?? null);
                        $proof = SaleItemWarrantyProof::create([
                            'sale_id' => $sale->id,
                            'sale_item_id' => $saleItem->id,
                            'item_id' => (int) $itemData['item_id'],
                            'warehouse_id' => !empty($p['warehouse_id']) ? (int) $p['warehouse_id'] : $saleItem->warehouse_id,
                            'unit_no' => (int) ($p['unit_no'] ?? 1),
                            'proof_code' => !empty($preferredCode) ? trim((string) $preferredCode) : null,
                            'proof_image' => $imgPath,
                        ]);

                        // Persist searchable codes for traceability (final preferred, then scanned/ocr candidates)
                        $codes = $this->extractWarrantyCodesFromProofPayload(is_array($p) ? $p : []);
                        foreach ($codes as $c) {
                            $norm = $this->normalizeWarrantyCode($c['code'] ?? null);
                            if (!$norm) continue;
                            SaleItemWarrantyCode::updateOrCreate(
                                [
                                    'sale_item_id' => $saleItem->id,
                                    'code_norm' => $norm,
                                ],
                                [
                                    'sale_id' => $sale->id,
                                    'customer_id' => (int) $sale->customer_id,
                                    'item_id' => (int) $saleItem->item_id,
                                    'warehouse_id' => $proof->warehouse_id,
                                    'sale_item_warranty_proof_id' => $proof->id,
                                    'unit_no' => (int) ($proof->unit_no ?? 1),
                                    'code' => (string) ($c['code'] ?? ''),
                                    'is_final' => (bool) ($c['is_final'] ?? false),
                                    'source' => (string) ($c['source'] ?? 'unknown'),
                                ]
                            );
                        }
                    }
                }
            }

            $saleQuantity = floatval($itemData['quantity']);
            $entryType = $entryTypeNormalized;
            $supplierId = $itemData['supplier_id'] ?? null;
            $isZeroStock = isset($itemData['is_zero_stock']) && $itemData['is_zero_stock'] == true;

            // Placeholder / temporary: no warehouse stock movement.
            if ($entryType === 'placeholder' || $entryType === 'temporary') {
                continue;
            }

            // Skip stock update only for normal "sale" lines coming from supplier when stock is 0.
            // For scrap/claim/return flows we MUST still update the correct stock source.
            if ($supplierId && $isZeroStock && $entryType === 'sale') {
                continue;
            }

            $wh = $whForLine;
            if ($wh === null) {
                throw new \Exception(
                    $entryType === 'claim'
                        ? 'Claim In requires a warehouse. Please select a warehouse for the branch or ensure the branch has a warehouse.'
                        : 'No warehouse available for branch. Cannot update stock.'
                );
            }

            // Claim In: persist to claim_warehouse_items (update or create)
            if ($entryType === 'claim') {
                $claimItemId = (int) $itemData['item_id'];
                $claimWarehouseId = (int) $wh->id;
                Log::info('Claim In store: resolved claim line', [
                    'request_item_id' => $itemData['item_id'] ?? null,
                    'request_warehouse_id' => $itemData['warehouse_id'] ?? null,
                    'resolved_warehouse_id' => $claimWarehouseId,
                    'quantity' => $saleQuantity,
                ]);
                try {
                    $claimRow = ClaimWarehouseItem::lockForUpdate()
                        ->firstOrCreate(
                            [
                                'warehouse_id' => $claimWarehouseId,
                                'item_id' => $claimItemId,
                            ],
                            [
                                'quantity' => 0,
                                'reserved_quantity' => 0,
                                'available_quantity' => 0,
                            ]
                        );
                    $previousQty = (float) ($claimRow->quantity ?? 0);
                    $claimRow->quantity = $previousQty + $saleQuantity;
                    $claimRow->save();

                    $expectedQty = $previousQty + $saleQuantity;
                    Log::info('Claim In stock updated', [
                        'item_id' => $claimItemId,
                        'warehouse_id' => $claimWarehouseId,
                        'quantity_added' => $saleQuantity,
                        'quantity_after' => (float) $claimRow->quantity,
                    ]);

                    // Post-save verification: re-read from DB and fail if not as expected
                    $readBack = ClaimWarehouseItem::where('item_id', $claimItemId)
                        ->where('warehouse_id', $claimWarehouseId)
                        ->first();
                    if (!$readBack || (float) $readBack->quantity !== (float) $expectedQty) {
                        Log::error('Claim In verification failed: readback mismatch', [
                            'item_id' => $claimItemId,
                            'warehouse_id' => $claimWarehouseId,
                            'expected_quantity' => $expectedQty,
                            'readback_quantity' => $readBack ? (float) $readBack->quantity : null,
                        ]);
                        throw new \Exception('Claim stock verification failed: saved quantity could not be read back correctly.');
                    }
                } catch (\Throwable $e) {
                    Log::error('Claim In stock update failed', [
                        'item_id' => $claimItemId,
                        'warehouse_id' => $claimWarehouseId,
                        'quantity' => $saleQuantity,
                        'error' => $e->getMessage(),
                    ]);
                    throw new \Exception('Claim stock could not be updated: ' . $e->getMessage());
                }
            } else {
                // Scrap In uses entry_type="scrap" (and sometimes "scrap_in") and must add to stock.
                $isIncomingScrap = in_array($entryType, ['scrap', 'scrap_in'], true);

                if ($isIncomingScrap) {
                    // Scrap In means "incoming scrap from customer" => add to Warehouse stock.
                    $itemId = (int) $itemData['item_id'];
                    $warehouseId = (int) $wh->id;

                    $warehouseItem = WarehouseItem::lockForUpdate()
                        ->where('warehouse_id', $warehouseId)
                        ->where('item_id', $itemId)
                        ->first();

                    if (!$warehouseItem) {
                        WarehouseItem::create([
                            'warehouse_id' => $warehouseId,
                            'item_id' => $itemId,
                            'quantity' => 0,
                            'reserved_quantity' => 0,
                            'available_quantity' => 0,
                        ]);

                        $warehouseItem = WarehouseItem::lockForUpdate()
                            ->where('warehouse_id', $warehouseId)
                            ->where('item_id', $itemId)
                            ->first();
                    }

                    if (!$warehouseItem) {
                        throw new \Exception('Scrap In stock update failed: WarehouseItem row could not be created/read.');
                    }

                    $previousQty = (float) ($warehouseItem->quantity ?? 0);
                    $warehouseItem->quantity = $previousQty + $saleQuantity;
                    $warehouseItem->available_quantity = $warehouseItem->quantity - (float) ($warehouseItem->reserved_quantity ?? 0);
                    $warehouseItem->save();

                    $expectedQty = $previousQty + $saleQuantity;

                    // Post-save verification: read back.
                    $readBack = WarehouseItem::where('warehouse_id', $warehouseId)
                        ->where('item_id', $itemId)
                        ->first();
                    if (!$readBack || (float) $readBack->quantity !== (float) $expectedQty) {
                        Log::error('Scrap In verification failed: readback mismatch', [
                            'item_id' => $itemId,
                            'warehouse_id' => $warehouseId,
                            'expected_quantity' => $expectedQty,
                            'readback_quantity' => $readBack ? (float) $readBack->quantity : null,
                        ]);
                        throw new \Exception('Scrap In stock verification failed: saved quantity could not be read back correctly.');
                    }

                    // Update item master on_hand so search/stock UI can immediately see it.
                    $item = Item::find($itemId);
                    if ($item) {
                        $item->on_hand = (float) ($item->on_hand ?? 0) + $saleQuantity;
                        $item->save();
                    }
                } else {
                    // Outgoing stock movements: sale/return/scrap_sale/other types decrement warehouse quantities.
                $warehouseItem = WarehouseItem::lockForUpdate()
                    ->where('warehouse_id', $wh->id)
                    ->where('item_id', $itemData['item_id'])
                    ->first();

                if (!$warehouseItem) {
                    $warehouseItem = WarehouseItem::lockForUpdate()
                        ->where('item_id', $itemData['item_id'])
                        ->whereIn('warehouse_id', $branchWarehouseIds)
                        ->firstOrFail();
                }

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

        // Save vehicles to customer_cars table (including last service/oil reminder for next visit)
        if ($request->filled('vehicles') && is_array($request->vehicles)) {
            $oilIntervalKmFromCart = $this->firstOilMileageIntervalKmFromItems($request->input('items', []));
            foreach ($request->vehicles as $vehicleData) {
                $make = trim((string) ($vehicleData['make'] ?? ''));
                $model = trim((string) ($vehicleData['model'] ?? ''));
                $carManufacturerId = ! empty($vehicleData['car_manufacturer_id']) ? (int) $vehicleData['car_manufacturer_id'] : null;
                $carModelId = ! empty($vehicleData['car_model_id']) ? (int) $vehicleData['car_model_id'] : null;
                if ($carManufacturerId) {
                    $m = CarManufacturer::find($carManufacturerId);
                    if ($m) {
                        $make = $m->name;
                    }
                }
                if ($carModelId) {
                    $md = CarModel::find($carModelId);
                    if ($md) {
                        $model = $md->name;
                    }
                }
                if ($make === '' || $model === '') {
                    throw ValidationException::withMessages([
                        'vehicles' => ['Each vehicle must have make and model.'],
                    ]);
                }

                $plateNorm = strtoupper(preg_replace('/\s+/', '', trim((string) ($vehicleData['plate_number'] ?? ''))));
                $custId = (int) ($vehicleData['customer_id'] ?? 0);
                $existingVehicle = CustomerCar::where('customer_id', $custId)
                    ->get()
                    ->first(function (CustomerCar $c) use ($plateNorm) {
                        $p = strtoupper(preg_replace('/\s+/', '', trim((string) ($c->plate_number ?? ''))));

                        return $p === $plateNorm;
                    });

                $currentKm = isset($vehicleData['current_km']) ? preg_replace('/[^\d.]/', '', $vehicleData['current_km']) : null;
                $nextKm = isset($vehicleData['next_km']) ? preg_replace('/[^\d.]/', '', $vehicleData['next_km']) : null;
                if (($nextKm === null || $nextKm === '') && $oilIntervalKmFromCart !== null && $currentKm !== null && $currentKm !== '') {
                    $curF = (float) $currentKm;
                    if ($curF >= 0) {
                        $nextKm = (string) (int) round($curF + $oilIntervalKmFromCart);
                    }
                }
                $nextDate = !empty($vehicleData['next_date']) ? $vehicleData['next_date'] : null;
                if ($nextDate && strlen($nextDate) > 10) {
                    $nextDate = substr($nextDate, 0, 10);
                }
                $dailyRunKm = isset($vehicleData['daily_run_km']) ? preg_replace('/[^\d.]/', '', $vehicleData['daily_run_km']) : null;
                $intervalDays = isset($vehicleData['interval_days']) ? $vehicleData['interval_days'] : null;
                $intervalMonths = isset($vehicleData['interval_months']) ? $vehicleData['interval_months'] : null;
                $visitDate = !empty($request->sale_date) ? substr($request->sale_date, 0, 10) : null;

                if ($existingVehicle) {
                    $update = [
                        'plate_number' => $plateNorm !== '' ? $plateNorm : $existingVehicle->plate_number,
                        'make' => $make,
                        'model' => $model,
                        'year' => $vehicleData['year'],
                        'car_manufacturer_id' => $carManufacturerId,
                        'car_model_id' => $carModelId,
                    ];
                    if ($visitDate !== null && $visitDate !== '') {
                        $update['last_visit_date'] = $visitDate;
                    }
                    if ($currentKm !== null && $currentKm !== '') {
                        $update['last_service_current_km'] = $currentKm;
                    }
                    if ($nextKm !== null && $nextKm !== '') {
                        $update['last_service_next_km'] = $nextKm;
                    }
                    if ($nextDate !== null && $nextDate !== '') {
                        $update['last_service_next_date'] = $nextDate;
                    }
                    if ($dailyRunKm !== null && $dailyRunKm !== '') {
                        $update['last_service_daily_run_km'] = $dailyRunKm;
                    }
                    if ($intervalDays !== null && $intervalDays !== '') {
                        $update['last_service_interval_days'] = $intervalDays;
                    }
                    if ($intervalMonths !== null && $intervalMonths !== '') {
                        $update['last_service_interval_months'] = $intervalMonths;
                    }
                    $existingVehicle->update($update);
                } else {
                    CustomerCar::create([
                        'customer_id' => $vehicleData['customer_id'],
                        'plate_number' => $plateNorm !== '' ? $plateNorm : trim((string) ($vehicleData['plate_number'] ?? '')),
                        'make' => $make,
                        'model' => $model,
                        'year' => $vehicleData['year'],
                        'car_manufacturer_id' => $carManufacturerId,
                        'car_model_id' => $carModelId,
                        'last_visit_date' => $visitDate,
                        'last_service_current_km' => $currentKm ?: null,
                        'last_service_next_km' => ($nextKm !== null && $nextKm !== '') ? $nextKm : null,
                        'last_service_next_date' => $nextDate,
                        'last_service_daily_run_km' => $dailyRunKm ?: null,
                        'last_service_interval_days' => $intervalDays,
                        'last_service_interval_months' => $intervalMonths,
                    ]);
                }
            }
        }
    }

}