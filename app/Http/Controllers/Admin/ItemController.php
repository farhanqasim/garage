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


class ItemController extends Controller
{



    public function all_items()
    {
        $items = Item::with(['item_user', 'product_item', 'partnumber_item', 'updated_by_user', 'category'])->latest()->get();
        //   return $items;
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
        $Vehicals    = VehicalType::with(['manutacturer_vehical', 'model_vehical', 'engine_vehical', 'country_vehical', 'vehical_part_number'])->where('status', 'active')->get();

        $milleages   = Mileage::where('status', 'active')->get();
        $item_types  = Producttype::where('status', 'active')->get();
        // return $Vehicals;
        $items       = Item::all();
        $units = Unit::with('baseUnit')->orderBy('base_unit_id')->orderBy('base_unit_multiplier')
            ->get();

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
        $partnumbers      = PartNumber::with('part_number_vehical')->where('status', 'active')->get();
        // return $partnumbers;
        $engineccs      = EngineCc::where('status', 'active')->get();
        $latestItems = Item::with([
            'item_user',
            'product_item',
            'category',
            'partnumber_item',
            'company_item',
            'quality_item'
        ])->latest()->take(5)->get();
        // Get all vehicles and group by configuration (part, manufacturer, model, engine, country)
        // Multiple records exist per vehicle configuration with different year ranges
        $Vehis = VehicalType::with([
            'manutacturer_vehical',
            'model_vehical',
            'engine_vehical',
            'country_vehical',
            'vehical_part_number'
        ])
            ->where('status', 'active')
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
            ->values(); // Reset keys

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

        // Duplicate combination check removed - allowing duplicate combinations
        // $exists = Item::where('category_id', $request->category_id)
        //     ->where('quality_id', $request->quality_id)
        //     ->where('company_id', $request->company_id)
        //     ->exists();
        // if ($exists) {
        //     return redirect()->back()
        //         ->withInput()
        //         ->withErrors([
        //             'duplicate' => 'This combination of Category, Quality, Part Number and Company already exists. Please change one value.'
        //         ]);
        // }
        try {
            DB::beginTransaction();

            $data = $validated;

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
            'vehical_item',
            'category',
            'subcategory',
            'item_user',
            'product_item',
            'unit_item'
        ])->find($id);
        // return $item;
        if (!$item) {
            abort(404, 'Item not found');
        }
        return view('admin.item.show', compact('item'));
    }

    public function getItemsByType($type, Request $request)
    {
        $query = Item::with([
            'item_user', 
            'product_item', 
            'category',
            'partnumber_item',
            'company_item',
            'quality_item'
        ])
            ->where('type', $type)
            ->latest();
        
        // Check if 'all' parameter is passed to get all items
        if ($request->has('all') && $request->get('all') == 'true') {
            $items = $query->get();
        } else {
            $items = $query->take(5)->get();
        }

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
                    'category_name' => $item->category ? $item->category->name : 'N/A',
                    'part_number' => $item->partnumber_item ? $item->partnumber_item->name : '-',
                    'company_name' => $item->company_item ? $item->company_item->name : '-',
                    'quality_name' => $item->quality_item ? $item->quality_item->name : '-',
                    'show_url' => route('item.show', $item->id),
                    'edit_url' => route('item.edit', $item->id),
                    'delete_url' => route('item.delete', $item->id),
                    'duplicate_url' => route('item.duplicate', $item->id),
                ];
            }),
            'total' => $items->count()
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
}
