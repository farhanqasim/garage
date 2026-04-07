<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function load(Request $request)
    {
        $type = $request->input('type');
        $search = $request->input('search', '');
        $limit = min((int) $request->input('limit', 50), 200);

        $data = match ($type) {
            'categories' => \App\Models\Category::whereNull('parent_id')
                ->where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->with('children')
                ->orderBy('name')->limit($limit)->get(),

            'subcategories' => \App\Models\Category::where('parent_id', $request->input('parent_id'))
                ->where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(),

            'companies' => \App\Models\Company::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'part_numbers' => \App\Models\PartNumber::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderByRaw($search ? 'name asc' : 'id desc')
                ->limit($search ? $limit : max($limit, 100))
                ->get(['id', 'name', 'type']),

            'products' => \App\Models\Product::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'brands' => \App\Models\Brand::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'units' => \App\Models\Unit::with('baseUnits')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(),

            'qualities' => \App\Models\Quality::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->limit($limit)->get(['id', 'name']),

            'volts' => \App\Models\Volt::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'ccas' => \App\Models\Cca::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'platos' => \App\Models\Platos::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'amphors' => \App\Models\Amphor::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'packings' => \App\Models\Packing::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'scales' => \App\Models\Scale::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'formulas' => \App\Models\Formula::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'grades' => \App\Models\Grade::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'technologies' => \App\Models\Technology::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'line_items' => \App\Models\LineItem::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'milleages' => \App\Models\Mileage::where('status', 'active')->limit($limit)->get(['id', 'name']),

            'car_companies' => \App\Models\CarCompany::query()
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'car_names' => \App\Models\CarName::query()
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'car_models' => \App\Models\CarModel::query()
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'car_countries' => \App\Models\CarCountry::query()
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'car_manufacturers' => \App\Models\CarManufacturer::query()
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'engine_ccs' => \App\Models\EngineCc::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->limit($limit)->get(['id', 'name']),

            'vehicles' => \App\Models\VehicalType::where('status', 'active')
                ->select('id', 'v_part_number_id', 'car_manufacturer', 'car_model_name', 'engine_cc', 'car_manufactured_country', 'year_from', 'year_to')
                ->when($search, fn ($q) => $q->where('car_model_name', 'like', "%{$search}%"))
                ->orderBy('id', 'desc')->limit($limit)->get(),

            'item_types' => \App\Models\Producttype::where('status', 'active')
                ->limit($limit)->get(['id', 'name']),

            'suppliers' => \App\Models\Supplier::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'customers' => \App\Models\Customer::query()
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')->limit($limit)->get(['id', 'name']),

            'pole_thicknesses' => \App\Models\PoleThickness::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'pool_directions' => \App\Models\PoolDirection::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'minus_pools' => \App\Models\Minuspool::where('status', 'active')->limit($limit)->get(['id', 'name']),

            'services' => \App\Models\Services::where('status', 'active')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->limit($limit)->get(['id', 'name']),
            'warrenties' => \App\Models\Warrenty::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'groups' => \App\Models\Group::orderBy('name')->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))->limit($limit)->get(['id', 'name']),
            'made_ins' => \App\Models\MadeIn::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'levels' => \App\Models\Level::where('status', 'active')->limit($limit)->get(['id', 'name']),
            'battery_sizes' => \App\Models\BatterySize::where('status', 'active')->orderBy('name')->limit($limit)->get(['id', 'name']),

            default => collect([]),
        };

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
