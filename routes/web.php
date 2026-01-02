<?php

use App\Http\Controllers\AddInputController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AmphorController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BrandCotroller;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LineitemController;
use App\Http\Controllers\Admin\MileageController;
use App\Http\Controllers\Admin\PackingController;
use App\Http\Controllers\Admin\PlatosController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductTypeController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\ScaleController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicalTypeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LoginController::class, 'showLoginForm']);

Auth::routes();

// Branch selection route (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/branch/select', function () {
        if (!session()->has('pending_branches')) {
            return redirect()->route('home')->with('info', 'No branch selection required.');
        }
        return view('auth.branch-select');
    })->name('branch.select');

    Route::post('/branch/select/complete', [LoginController::class, 'completeBranchSelection'])
        ->name('branch.select.complete');
});

// Normal user dashboard
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/users', [HomeController::class, 'users'])->name('users');
// ==============================
// 🧩 ADMIN ROUTES (No Middleware)
// ==============================
Route::prefix('admin')->name('admin.')->group(function () {
});

Route::get('setting',[SettingController::class,"setting"])->name('admin.setting');
Route::post('settings/save',[SettingController::class,"save"])->name('admin.setting.save');
Route::get('user/profile/{id}', [HomeController::class, 'userprofile'])->name('user.profile');
Route::post('user/password/verify', [HomeController::class, 'verifyOldPassword'])->name('user.password.verify');
Route::put('user/profile/update/{id}', [HomeController::class, 'userprofileupdate'])->name('user.profile.update');


Route::get('/all/category', [CategoryController::class, 'all_category'])->name('all.category');
Route::post('/post/category', [CategoryController::class, 'post_category'])->name('post.category');
Route::post('/update-category/{id}', [CategoryController::class, 'updatecategory'])->name('update.categorys');
Route::post('/update-status/{id}', [CategoryController::class, 'updateStatus'])->name('update.status');
Route::get('/all/subcategory', [CategoryController::class, 'all_sub_category'])->name('all.sub.category');
Route::post('/post/subcategory', [CategoryController::class, 'post_sub_category'])->name('post.sub.category');
Route::post('/update-subcategory/{id}', [CategoryController::class, 'updatesubcategory'])->name('update.subcategory');
Route::get('/delete-category/{id}', [CategoryController::class, 'deletecategory'])->name('delete.category');


Route::post('/post/item/category', [CategoryController::class, 'post_category_item'])->name('post.item.category');
Route::get('/show/category/{id}', [CategoryController::class, 'show_category'])->name('show.category');
Route::put('/update/category/{id}', [CategoryController::class, 'update_category'])->name('update.category');
Route::delete('/destory/category/{id}', [CategoryController::class, 'destory_category'])->name('destory.category');


// Brand Routes
Route::get('/all/brands', [BrandCotroller::class, 'all_brands'])->name('all.brands');
Route::post('/post/brands', [BrandCotroller::class, 'post_brand'])->name('post.brands');
Route::post('/update-brands/{id}', [BrandCotroller::class, 'updatebrand'])->name('update.brands');
Route::post('/update-brand-status/{id}', [BrandCotroller::class, 'updatebrandStatus'])->name('update.brands.status');
Route::get('/delete-brand/{id}', [BrandCotroller::class, 'deletebrand'])->name('delete.brand');


// Amphor Routes
Route::get('/all/amphors', [AmphorController::class, 'all_amphors'])->name('all.amphors');
Route::post('/post/amphors', [AmphorController::class, 'post_amphors'])->name('post.amphors');
Route::post('/update-amphors/{id}', [AmphorController::class, 'updateamphors'])->name('update.amphors');
Route::post('/update-amphors-status/{id}', [AmphorController::class, 'updateamphorsStatus'])->name('update.amphors.status');
Route::get('/delete-amphors/{id}', [AmphorController::class, 'deleteamphors'])->name('delete.amphors');

Route::get('/show/ampere/{id}', [AmphorController::class, 'show_ampere'])->name('show.ampere');
Route::put('/update/ampere/{id}', [AmphorController::class, 'update_ampere'])->name('update.ampere');
Route::delete('/destory/ampere/{id}', [AmphorController::class, 'destory_ampere'])->name('destory.ampere');


//  Platos Routes

Route::get('/all/platos', [PlatosController::class, 'all_platos'])->name('all.platos');
Route::post('/post/platos', [PlatosController::class, 'post_platos'])->name('post.platos');
Route::post('/update-platos/{id}', [PlatosController::class, 'updateplatos'])->name('update.platos');
Route::post('/update-platos-status/{id}', [PlatosController::class, 'updateplatosStatus'])->name('update.platos.status');
Route::get('/delete-platos/{id}', [PlatosController::class, 'deleteplatos'])->name('delete.platos');


Route::get('/show/plate/{id}', [PlatosController::class, 'show_plate'])->name('show.plate');
Route::put('/update/plate/{id}', [PlatosController::class, 'update_plate'])->name('update.plate');
Route::delete('/destory/plate/{id}', [PlatosController::class, 'destory_plate'])->name('destory.plate');
// Lineitems Routes

Route::get('/all/lineitems', [LineitemController::class, 'all_lineitems'])->name('all.lineitems');
Route::post('/post/lineitems', [LineitemController::class, 'post_lineitems'])->name('post.lineitems');
Route::post('/update-lineitems/{id}', [LineitemController::class, 'updatelineitems'])->name('update.lineitems');
Route::post('/update-lineitems-status/{id}', [LineitemController::class, 'updatelineitemsStatus'])->name('update.lineitems.status');
Route::get('/delete-lineitems/{id}', [LineitemController::class, 'deletelineitems'])->name('delete.lineitems');


// Companies Routes

Route::get('/all/companies', [CompanyController::class, 'all_companies'])->name('all.companies');
Route::post('/post/companies', [CompanyController::class, 'post_companies'])->name('post.companies');
Route::post('/update-companies/{id}', [CompanyController::class, 'updatecompanies'])->name('update.companies');
Route::post('/update-companies-status/{id}', [CompanyController::class, 'updatecompaniesStatus'])->name('update.companies.status');
Route::get('/delete-companies/{id}', [CompanyController::class, 'deletecompanies'])->name('delete.companies');
Route::get('/show/company/{id}', [CompanyController::class, 'show_company'])->name('show.company');
Route::put('/update/company/{id}', [CompanyController::class, 'update_company'])->name('update.company');
Route::delete('/destory/company/{id}', [CompanyController::class, 'destory_company'])->name('destory.company');

// Packings Routes

Route::get('/all/packings', [PackingController::class, 'all_packings'])->name('all.packings');
Route::post('/post/packings', [PackingController::class, 'post_packings'])->name('post.packings');
Route::post('/update-packings/{id}', [PackingController::class, 'updatepackings'])->name('update.packings');
Route::post('/update-packings-status/{id}', [PackingController::class, 'updatepackingsStatus'])->name('update.packings.status');
Route::get('/delete-packings/{id}', [PackingController::class, 'deletepackings'])->name('delete.packings');


// Scales Routes

Route::get('/all/scales', [ScaleController::class, 'all_scales'])->name('all.scales');
Route::post('/post/scales', [ScaleController::class, 'post_scales'])->name('post.scales');
Route::post('/update-scales/{id}', [ScaleController::class, 'updatescales'])->name('update.scales');
Route::post('/update-scales-status/{id}', [ScaleController::class, 'updatescalesStatus'])->name('update.scales.status');
Route::get('/delete-scales/{id}', [ScaleController::class, 'deletescales'])->name('delete.scales');


// Units Routes
Route::get('/all/units', [UnitController::class, 'all_units'])->name('all.units');
Route::post('/post/units', [UnitController::class, 'post_units'])->name('post.units');
Route::put('/units/{unit}', [UnitController::class, 'update_units'])->name('update.unit');
Route::delete('/units/{unit}', [UnitController::class, 'destroy_units'])->name('destory.unit');

Route::post('/post/units/detail', [UnitController::class, 'post_units_detail'])->name('post.units.detail');

Route::post('/update-units/{id}', [UnitController::class, 'updateunits'])->name('update.units');
Route::post('/update-units-status/{id}', [UnitController::class, 'updateunitsStatus'])->name('update.units.status');
Route::delete('/delete-units/{id}', [UnitController::class, 'deleteunits'])->name('delete.units');

Route::get('/all/vehical', [VehicalTypeController::class, 'all_vehical'])->name('all.vehicals');
Route::post('/post/vehical', [VehicalTypeController::class, 'post_vehical'])->name('post.vehical');
Route::post('/update-vehical/{id}', [VehicalTypeController::class, 'updatevehical'])->name('update.vehical');
Route::post('/update-vehical-status/{id}', [VehicalTypeController::class, 'updatevehicalStatus'])->name('update.vehical.status');
Route::get('/delete-vehical/{id}', [VehicalTypeController::class, 'deletevehical'])->name('delete.vehical');




Route::get('/all/mileage', [MileageController::class, 'all_mileage'])->name('all.mileage');
Route::post('/post/mileage', [MileageController::class, 'post_mileage'])->name('post.mileage');
Route::post('/update-mileage/{id}', [MileageController::class, 'updatemileage'])->name('update.mileages');
Route::post('/update-mileage-status/{id}', [MileageController::class, 'updatemileageStatus'])->name('update.mileage.status');
Route::get('/delete-mileage/{id}', [MileageController::class, 'deletemileage'])->name('delete.mileage');
Route::post('/post/item/mileage', [MileageController::class, 'post_item_mileage'])->name('post.item.mileage');

Route::get('/show/mileage/{id}', [MileageController::class, 'show_mileage'])->name('show.mileage');
Route::put('/update/mileage/{id}', [MileageController::class, 'update_mileage'])->name('update.mileage');
Route::delete('/destory/mileage/{id}', [MileageController::class, 'destory_mileage'])->name('destory.mileage');


Route::get('/all/product/type', [ProductTypeController::class, 'all_product_type'])->name('all.producttype');
Route::post('/post/product/type', [ProductTypeController::class, 'post_product_type'])->name('post.producttype');
Route::post('/update-product/type/{id}', [ProductTypeController::class, 'updateproduct_type'])->name('update.producttype');
Route::post('/update-product/type-status/{id}', [ProductTypeController::class, 'updateproduct_typeStatus'])->name('update.producttype.status');
Route::get('/delete-product/type/{id}', [ProductTypeController::class, 'deleteproduct_type'])->name('delete.producttype');


Route::get('/all/branch', [BranchController::class, 'all_branches'])->name('all.branches');
Route::post('/branches', [BranchController::class, 'store_branches'])->name('branch.store');
Route::post('/update-branch-status/{id}', [BranchController::class, 'updatebranchStatus'])->name('update.branch.status');
Route::put('/branch/update/{id}', [BranchController::class, 'update_branches'])->name('branch.update');
Route::get('/branch/delete/{id}', [BranchController::class, 'delete_branch'])->name('branch.delete');



// All Users
Route::get('/all/users', [UserController::class, 'all_users'])->name('all.users');
Route::get('/delete-user/{id}', [UserController::class, 'deleteuser'])->name('delete.user');
Route::put('/update-user/{id}', [UserController::class, 'updateuser'])->name('update.user');


// Customers
Route::get('/all/customers', [CustomerController::class, 'all_customers'])->name('all.customers');
//  Employees Routes

Route::get('/all/employees', [EmployeeController::class, 'all_employees'])->name('all.employees');
Route::post('/post/employees', [EmployeeController::class, 'post_employees'])->name('post.employees');
Route::post('/update-employees/{id}', [EmployeeController::class, 'updateemployees'])->name('update.employees');
Route::post('/update-employees-status/{id}', [EmployeeController::class, 'updateemployeesStatus'])->name('update.employees.status');



// items
Route::get('/all/items', [ItemController::class, 'all_items'])->name('all.items');
Route::get('/all/items/create', [ItemController::class, 'items_create'])->name('all.items.create');
Route::post('/all/items/store', [ItemController::class, 'items_store'])->name('all.items.store');
Route::get('/item/edit/{id}', [ItemController::class, 'item_edit'])->name('item.edit');
Route::put('/item/update/{id}', [ItemController::class, 'item_update'])->name('item.update');
Route::get('/item/show/{id}', [ItemController::class, 'item_show'])->name('item.show');
Route::post('/check-barcode', [ItemController::class, 'checkBarcode'])->name('check.barcode');
Route::delete('/all/items/bulk-delete', [ItemController::class, 'itembulkDelete'])->name('all.items.bulkDelete');
Route::get('/item/{id}/duplicate', [ItemController::class, 'duplicate'])->name('item.duplicate');
Route::post('/items/{id}/duplicate', [ItemController::class, 'itemduplicate'])->name('items.duplicate');
Route::delete('/item/delete/{id}', [ItemController::class, 'item_delete'])->name('item.delete');
Route::get('/items/recycle-bin', [ItemController::class, 'recycleBin'])->name('items.recycle.bin');
Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
Route::delete('/items/{id}/force-delete', [ItemController::class, 'forceDelete'])->name('items.forceDelete');


Route::get('/admin/categories/{id}/subcategories', [ItemController::class, 'getSubcategories'])
    ->name('categories.subcategories');



Route::post('/add-car-company', [ItemController::class, 'storeCompany'])
    ->name('post.car.company');

Route::post('/add-car-name', [ItemController::class, 'storeName'])
    ->name('post.car.name');

Route::post('/add-car-model', [ItemController::class, 'storeModel'])
    ->name('post.car.model');
Route::get('/show/car/model/{id}', [ItemController::class, 'show_car_model'])->name('show.car.model');
Route::put('/update/car/model/{id}', [ItemController::class, 'update_car_model'])->name('update.car.model');
Route::delete('/destory/car/model/{id}', [ItemController::class, 'destory_car_model'])->name('destory.car.model');

Route::post('/add-car-country', [ItemController::class, 'storeCountry'])
    ->name('post.car.country');

Route::get('/show/car/country/{id}', [ItemController::class, 'show_car_country'])->name('show.car.country');
Route::put('/update/car/country/{id}', [ItemController::class, 'update_car_country'])->name('update.car.country');
Route::delete('/destory/car/country/{id}', [ItemController::class, 'destory_car_country'])->name('destory.car.country');

Route::post('/add-car-manufacturer', [ItemController::class, 'storeManufacturer'])
    ->name('post.car.manufacturer');

Route::get('/show/car/manufacturer/{id}', [ItemController::class, 'show_car_manufacturer'])->name('show.car.manufacturer');
Route::put('/update/car/manufacturer/{id}', [ItemController::class, 'update_car_manufacturer'])->name('update.car.manufacturer');
Route::delete('/destory/car/manufacturer/{id}', [ItemController::class, 'destory_car_manufacturer'])->name('destory.car.manufacturer');

// Roles
Route::resource('admin/roles', RoleController::class);
Route::get('admin/users/edit/{id}', [RoleController::class,'edit_access_role'])->name('edit_access_role');
Route::post('admin/users/update/{id}', [RoleController::class,'update_access_role'])->name('update_access_role');
Route::get('admin/delete/customer/{id}', [RoleController::class,'admin_delete_customer'])->name('admin_delete_customer');




Route::post('/post/volt', [AddInputController::class, 'post_volts'])->name('post.volts');
Route::get('/show/volt/{id}', [AddInputController::class, 'show_volt'])->name('show.volt');
Route::put('/update/volt/{id}', [AddInputController::class, 'update_volt'])->name('update.volt');
Route::delete('/destory/volt/{id}', [AddInputController::class, 'destory_volt'])->name('destory.volt');


Route::post('/post/cca', [AddInputController::class, 'post_cca'])->name('post.cca');
Route::get('/show/cca/{id}', [AddInputController::class, 'show_cca'])->name('show.cca');
Route::put('/update/cca/{id}', [AddInputController::class, 'update_cca'])->name('update.cca');
Route::delete('/destory/cca/{id}', [AddInputController::class, 'destory_cca'])->name('destory.cca');


Route::post('/post/minuspool', [AddInputController::class, 'post_minuspool'])->name('post.minuspool');
Route::get('/show/minuspool/{id}', [AddInputController::class, 'show_minuspool'])->name('show.minuspool');
Route::put('/update/minuspool/{id}', [AddInputController::class, 'update_minuspool'])->name('update.minuspool');
Route::delete('/destory/minuspool/{id}', [AddInputController::class, 'destory_minuspool'])->name('destory.minuspool');



Route::post('/post/technology', [AddInputController::class, 'post_technology'])->name('post.technology');
Route::get('/show/technology/{id}', [AddInputController::class, 'show_technology'])->name('show.technology');
Route::put('/update/technology/{id}', [AddInputController::class, 'update_technology'])->name('update.technology');
Route::delete('/destory/technology/{id}', [AddInputController::class, 'destory_technology'])->name('destory.technology');

Route::post('/post/grade', [AddInputController::class, 'post_grade'])->name('post.grade');
Route::get('/show/grade/{id}', [AddInputController::class, 'show_grade'])->name('show.grade');
Route::put('/update/grade/{id}', [AddInputController::class, 'update_grade'])->name('update.grade');
Route::delete('/destory/grade/{id}', [AddInputController::class, 'destory_grade'])->name('destory.grade');

Route::post('/post/item/brand', [AddInputController::class, 'post_brand_item'])->name('post.item.brand');
Route::get('/show/brand/{id}', [AddInputController::class, 'show_brand'])->name('show.brand');
Route::put('/update/brand/{id}', [AddInputController::class, 'update_brand'])->name('update.brand');
Route::delete('/destory/brand/{id}', [AddInputController::class, 'destory_brand'])->name('destory.brand');

Route::post('/post/formulas', [AddInputController::class, 'post_formulas'])->name('post.formulas');




// Items Products
Route::post('/post/product', [AddInputController::class, 'post_product'])->name('post.product');
Route::get('/show/product/{id}', [AddInputController::class, 'show_product'])->name('show.product');
Route::put('/update/product/{id}', [AddInputController::class, 'update_product'])->name('update.product');
Route::delete('/destory/product/{id}', [AddInputController::class, 'destory_product'])->name('destory.product');


Route::post('/post/qualities', [AddInputController::class, 'post_qualities'])->name('post.qualities');
Route::get('/show/quality/{id}', [AddInputController::class, 'show_quality'])->name('show.quality');
Route::put('/update/quality/{id}', [AddInputController::class, 'update_quality'])->name('update.quality');
Route::delete('/destory/quality/{id}', [AddInputController::class, 'destory_quality'])->name('destory.quality');


Route::post('/post/part/number', [AddInputController::class, 'post_part_number'])->name('post.partnumber');
Route::get('/show/part/number/{id}', [AddInputController::class, 'show_partnumber'])->name('show.partnumber');
Route::put('/update/part/number/{id}', [AddInputController::class, 'update_partnumber'])->name('update.partnumber');
Route::delete('/destory/part/number/{id}', [AddInputController::class, 'destory_partnumber'])->name('destory.partnumber');

Route::post('/post/services', [AddInputController::class, 'post_services'])->name('post.services');
Route::get('/show/service/{id}', [AddInputController::class, 'show_service'])->name('show.service');
Route::put('/update/service/{id}', [AddInputController::class, 'update_service'])->name('update.service');
Route::delete('/destory/service/{id}', [AddInputController::class, 'destory_service'])->name('destory.service');


Route::post('/post/warrenty', [AddInputController::class, 'post_warrenty'])->name('post.warrenty');
Route::get('/show/warrenty/{id}', [AddInputController::class, 'show_warrenty'])->name('show.warrenty');
Route::put('/update/warrenty/{id}', [AddInputController::class, 'update_warrenty'])->name('update.warrenty');
Route::delete('/destory/warrenty/{id}', [AddInputController::class, 'destory_warrenty'])->name('destory.warrenty');


// Items Groups
Route::post('/post/groups', [AddInputController::class, 'post_groups'])->name('post.groups');
Route::get('/groups/{id}', [AddInputController::class, 'post_show'])->name('show.groups');
Route::put('/groups/{id}', [AddInputController::class, 'post_update'])->name('post.groups.update');
Route::delete('/groups/{id}', [AddInputController::class, 'post_destroy'])->name('post.groups.destroy');


Route::post('/post/made_ins', [AddInputController::class, 'post_made_ins'])->name('post.made_ins');
Route::get('/show/madeins/{id}', [AddInputController::class, 'show_madeins'])->name('show.madeins');
Route::put('/update/madeins/{id}', [AddInputController::class, 'update_madeins'])->name('update.madeins');
Route::delete('/destory/madeins/{id}', [AddInputController::class, 'destory_madeins'])->name('destory.madeins');

Route::post('/post/levels', [AddInputController::class, 'post_levels'])->name('post.levels');
Route::get('/show/level/{id}', [AddInputController::class, 'show_level'])->name('show.level');
Route::put('/update/level/{id}', [AddInputController::class, 'update_level'])->name('update.level');
Route::delete('/destory/level/{id}', [AddInputController::class, 'destory_level'])->name('destory.level');


Route::get('all/vehical/types', [AddInputController::class,'all_vehicals_types'])->name('all.vehical');
Route::post('/post/product/vehical', [AddInputController::class, 'post_product_vehical'])->name('post.product_vehical');

Route::put('vehicles/{id}', [AddInputController::class, 'update_vehical'])->name('vehicles.update');
Route::delete('vehicles/{id}', [AddInputController::class, 'destroy_vehical'])->name('vehicles.destroy');

Route::post('/post/engine/cc', [AddInputController::class, 'post_engine_cc'])->name('post.engine.cc');
Route::get('/show/engine/cc/{id}', [AddInputController::class, 'show_engine_cc'])->name('show.engine_cc');
Route::put('/update/engine/cc/{id}', [AddInputController::class, 'update_engine_cc'])->name('update.engine_cc');
Route::delete('/destory/engine/cc/{id}', [AddInputController::class, 'destory_engine_cc'])->name('destory.engine_cc');

Route::post('/vehical/update-inline/{id}', [AddInputController::class, 'updateVehicalInline'])->name('vehical.inline.update');
Route::get('/vehicals/get', function() {return response()->json([
        'data' => \App\Models\VehicalType::latest()->take(5)->get()]);
})->name('get.all.vehicals');

Route::delete('/vehical/delete/{id}', [AddInputController::class, 'delete_vehical'])
    ->name('vehical.delete');
Route::get('/search-vehicals-by-partnumber', [AddInputController::class, 'searchByPartNumber'])
    ->name('search.vehicals.by.partnumber');

Route::post('/item/delete-image/{id}', [ItemController::class, 'deleteSingleImage'])
    ->name('item.delete.image');
Route::post('/item/delete-single-from-array', [ItemController::class, 'deleteSingleFromArray']);



// Supliers
Route::get('all/suppliers',[SupplierController::class,'all_suppliers'])->name('all_suppliers');

// sales
Route::get('all/sales',[SalesController::class,'all_sales'])->name('all_sales');
Route::get('create/sale',[SalesController::class,'create_sale'])->name('create.sale');
Route::get('/sales/items/ajax-search', [SalesController::class, 'ajaxSearch'])
    ->name('sales.items.ajax.search');
Route::get('/sales/filter-options', [SalesController::class, 'getFilterOptions'])
    ->name('sales.filter.options');

// purchases
Route::get('all/purchases',[PurchaseController::class,'all_purchases'])->name('all_purchases');
Route::get('purchases/create',[PurchaseController::class,'create'])->name('purchases.create')->middleware('auth');
Route::post('purchases',[PurchaseController::class,'store'])->name('purchases.store');
Route::get('purchases/items/search',[PurchaseController::class,'searchItems'])->name('purchases.items.search');
Route::get('purchases/items/{id}',[PurchaseController::class,'getItemDetails'])->name('purchases.items.details');


//pos
Route::get('pos', [PosController::class, 'point_of_sale'])->name('pos.index');
Route::get('point/of/sale', [PosController::class, 'point_of_sale'])->name('point_of_sale'); // Keep for backward compatibility
Route::get('pos/search', [PosController::class, 'search'])->name('pos.search');
// Customers

Route::get('/customers', [CustomerController::class, 'all_customers'])->name('customers.index');
Route::post('/customers', [CustomerController::class, 'customer_store'])->name('customers.store');
Route::put('/customers/{customer}', [CustomerController::class, 'customer_update'])->name('customers.update');
Route::get('/customers/{customer}', [CustomerController::class, 'customer_delete'])->name('customers.delete');

// Suppliers
Route::get('/suppliers', [SupplierController::class, 'all_suppliers'])->name('suppliers.index');
Route::post('/suppliers', [SupplierController::class, 'supplier_store'])->name('suppliers.store');
Route::put('/suppliers/{supplier}', [SupplierController::class, 'supplier_update'])->name('suppliers.update');
Route::get('/suppliers/{supplier}', [SupplierController::class, 'supplier_delete'])->name('suppliers.delete');


