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



    public function all_items(Request $request)
    {
        $items = Item::with([
            'item_user', 
            'product_item', 
            'partnumber_item', 
            'updated_by_user', 
            'category',
            'company_item',
            'unit_item',
            'vehical_item'
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
                        'image' => asset($item->image ?? 'assets/img/media/default.png'),
                        'bar_code' => $item->bar_code ?? '',
                        'barcode_image' => $item->barcode_image,
                        'type' => $item->type ?? '',
                        'is_active' => $item->is_active ?? true,
                        'user_name' => $item->item_user->name ?? '',
                        'product_name' => $item->product_item->name ?? '',
                        'part_number' => $item->partnumber_item->name ?? '',
                        'category_name' => $item->category ? $item->category->name : 'N/A',
                        'company_name' => $item->company_item->name ?? '',
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
        
        // Regular page load
        return view('admin.item.index', compact('items'));
    }


    public function items_create()
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect('/')->with('error', 'Please login to continue.');
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
            'item_user:id,name',
            'product_item:id,name',
            'category:id,name',
            'partnumber_item:id,name',
            'company_item:id,name',
            'quality_item:id,name'
        ])
            ->latest()
            ->take(5)
            ->get();
        // Get all vehicles and group by configuration (part, manufacturer, model, engine, country)
        // Multiple records exist per vehicle configuration with different year ranges
        // Optimize: Remove eager loading to prevent timeout - select only needed columns and limit results
        $Vehis = VehicalType::where('status', 'active')
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
        return view('admin.item.create', compact(
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
            'technologies',
            'grades',
            'brands',
            'product',
            'formulas',
            'qualities',
            'partnumbers',
            'engineccs',
            'latestItems',
            'Vehis',
            'warrenties',
            'groups',
            'made_ins',
            'levels',
            'services',
            'units'
        ));
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
        // Validate fields
        $validated = $request->validate([
            'bar_code' => 'required|unique:items,bar_code',
            'user_id' => 'nullable|exists:users,id',
            'p_id' => 'nullable|string|max:255',
            'vehical_id' => 'nullable|string',
            'total_price' => 'nullable',
            'price_per_unit' => 'nullable',
            'on_hand' => 'nullable',
            'sale_price' => 'nullable',
            'total_sale_price' => 'nullable',
            'sale_price_per_base' => 'nullable',
            'mileage' => 'nullable|numeric|min:0',
            'type' => 'nullable|string',
            'plat_id' => 'nullable|string',
            'amphors' => 'nullable|string',
            'lineitems' => 'nullable|string',
            'company_id' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'p_brochure' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'volt' => 'nullable|string',
            'cca' => 'nullable|string',
            'minus_pole_direction' => 'nullable|string',
            'minus_pool_direction' => 'nullable|string', // Keep both for backward compatibility
            'technology' => 'nullable|string',
            'grade' => 'nullable|string',
            'services' => 'nullable|string',
            'formulas' => 'nullable|string',
            'farmula' => 'nullable|string', // Keep both for backward compatibility
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
            'packing_purchase_rate' => 'nullable|numeric|min:0',
            'update_date' => 'nullable|date',
            'rack' => 'nullable|string',
            'supplier' => 'nullable|string',
            'warrenty' => 'nullable|string',
            'group' => 'nullable|string',
            'gorup' => 'nullable|string', // Keep both for backward compatibility
            'made_in' => 'nullable|string',
            'pro_dis' => 'nullable|string',
            'part_number_id' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'pro_dis' => 'nullable|string',
            'auto_deactive' => 'sometimes|boolean',
            'is_dead' => 'sometimes|boolean',
        ]);

        // Duplicate combination check - Only for parts, filters, and breakpad types
        $type = $request->input('type');
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

            /* ============================
            ✅ Create Item
            ============================ */
            $item = Item::create($data);

            DB::commit();

            /* ============================
            ✅ Redirects
            ============================ */
            if ($request->action === 'save_new') {
                Log::info('Item created (Save & New)', ['item_id' => $item->id]);
                return redirect()->route('all.items.create')
                    ->with('success', 'Item created successfully!');
            }

            Log::info('Item created (Save)', ['item_id' => $item->id]);

            return redirect()->back()
                ->withInput()
                ->with('success', 'Item created successfully!');
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Item creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['image', 'images'])
            ]);

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
        //   return $item;
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
            'product_item',
            'category',
            'partnumber_item',
            'company_item',
            'quality_item'
        ])->latest()->take(5)->get();
        $services      = Services::where('status', 'active')->get();
        $groups      = Group::where('status', 'active')->get();
        $warrenties      = Warrenty::where('status', 'active')->get();
        $made_ins      = MadeIn::where('status', 'active')->get();
        $levels      = Level::where('status', 'active')->get();
        // Get all vehicles - each record already has all year ranges in years JSON column
        $Vehis = VehicalType::with([
            'manutacturer_vehical',
            'model_vehical',
            'engine_vehical',
            'country_vehical',
            'vehical_part_number'
        ])
            ->where('status', 'active')
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
        return view('admin.item.edit', compact(
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
        // return $request->all();
        $item = Item::findOrFail($id);
        // Validate ONLY fields that exist in $fillable
        $validated = $request->validate([
            'bar_code' => 'required|unique:items,bar_code,' . $item->id,
            'user_id' => 'nullable|exists:users,id',
            'p_id' => 'nullable|string|max:255',
            'vehical_id' => 'nullable|string',
            'total_price' => 'nullable',
            'price_per_unit' => 'nullable',
            'on_hand' => 'nullable',
            'sale_price' => 'nullable',
            'total_sale_price' => 'nullable',
            'sale_price_per_base' => 'nullable',
            'mileage' => 'nullable|numeric|min:0',
            'type' => 'nullable|string',
            'plat_id' => 'nullable|string',
            'amphors' => 'nullable|string',
            'lineitems' => 'nullable|string',
            'company_id' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'p_brochure' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif',
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
                    return redirect()->back()
                        ->withInput()
                        ->withErrors([
                            'duplicate' => 'This combination of Category, Quality, Part Number and Company already exists for this type. Please change one value.'
                        ]);
                }
            }
        }

        try {
            DB::beginTransaction();

            $data = $validated;

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

            // === Track who updated and when ===
            // Only set if columns exist (migration has been run)
            if (auth()->check()) {
                $data['updated_by'] = auth()->id();
                $data['last_updated_at'] = now();
            }

            // === Update using mass assignment (safe via $fillable) ===
            $item->update($data);

            DB::commit();

            Log::info('Item updated successfully', ['item_id' => $item->id]);
            return redirect()->back()->with('success', 'Item updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Item update failed', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['image', 'images'])
            ]);

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
            'product_item', 
            'category',
            'partnumber_item',
            'company_item',
            'quality_item',
            'updated_by_user'
        ])
            ->where('type', $type)
            ->latest();
        
        // Check if 'all' parameter is passed to get all items
        if ($request->has('all') && $request->get('all') == 'true') {
            $items = $query->get();
        } else {
            $items = $query->take(5)->get();
        }

        $totalItemsCount = Item::where('type', $type)->count(); // Get total count for the type

        return response()->json([
            'success' => true,
            'items' => $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'image' => asset($item->image ?? 'assets/img/media/default.png'),
                    'bar_code' => $item->bar_code,
                    'barcode_image' => $item->barcode_image,
                    'user_name' => $item->item_user->name ?? '-',
                    'product_name' => $item->product_item->name ?? '-',
                    'type' => $item->type,
                    'is_active' => $item->is_active,
                    'category_name' => $item->category ? $item->category->name : 'N/A',
                    'part_number' => $item->partnumber_item ? $item->partnumber_item->name : '-',
                    'company_name' => $item->company_item ? $item->company_item->name : '-',
                    'quality_name' => $item->quality_item ? $item->quality_item->name : '-',
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
        if (count($ids) > 0) {
            Item::whereIn('id', $ids)->delete();
            return back()->with('success', 'Selected items deleted successfully.');
        }
        return back()->with('error', 'No items selected.');
    }



    public function item_delete($id)
    {
        // return $id;
        $items = Item::findOrFail($id);
        $items->delete();
        return redirect()->back()->with('success', 'Item deleted successfully.');
    }

    public function recycleBin()
    {
        $items = Item::onlyTrashed()->get();

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
        $items = Item::with('quality_item')
            ->where('part_number_id', $partNumberId)
            ->get();
        $count = $items->count();
        
        // Group by quality/grade (React-style stats)
        $grouped = [];
        $items->each(function($item) use (&$grouped) {
            $quality = $item->quality_item->name ?? ($item->grade ?? 'Standard');
            if (!isset($grouped[$quality])) {
                $grouped[$quality] = 0;
            }
            $grouped[$quality]++;
        });
        
        $details = [];
        foreach ($grouped as $quality => $qualityCount) {
            $details[] = $qualityCount . ' ' . $quality;
        }
        
        return response()->json([
            'success' => true,
            'count' => $count,
            'total' => $count,
            'details' => $details
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
                    'image' => asset($item->image ?? 'assets/img/media/default.png'),
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
}
