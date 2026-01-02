<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Branch;
use App\Models\CarModel;
use App\Models\EngineCc;
use App\Models\CarCountry;
use Illuminate\Http\Request;
use App\Models\CarManufacturer;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeCustomerMail;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SupplierController extends Controller
{
    public function all_suppliers()
    {
        $branches = Branch::all();
        $suppliers = Supplier::paginate(10);
        $carManufacturers = CarManufacturer::orderBy('name')->get();
        $carModels        = CarModel::orderBy('name')->get();
        $engineccs      = EngineCc::where('status', 'active')->get();
        $carCountries     = CarCountry::orderBy('name')->get();
        return view('admin.suppliers.index', compact('suppliers', 'branches', 'carManufacturers', 'carModels', 'engineccs', 'carCountries'));
    }

    public function supplier_store(Request $request)
    {
        $plainPassword = $request->password ?? Str::random(12);

        $supplier = new Supplier();
        $supplier->names            = $request->names ?? [];
        $supplier->phones           = array_filter($request->phones ?? []);
        $supplier->company          = $request->company;
        $supplier->email            = $request->email;
        $supplier->carnumber        = $request->carnumber;
        $supplier->group_id         = $request->group_id;
        $supplier->opening_balance  = $request->opening_balance ?? 0;
        // Convert DD/MM/YYYY to YYYY-MM-DD format
        if ($request->as_of_date) {
            try {
                $supplier->as_of_date = Carbon::createFromFormat('d/m/Y', $request->as_of_date)->format('Y-m-d');
            } catch (\Exception $e) {
                // If conversion fails, try to parse as is (in case it's already in YYYY-MM-DD format)
                try {
                    $supplier->as_of_date = Carbon::parse($request->as_of_date)->format('Y-m-d');
                } catch (\Exception $e2) {
                    $supplier->as_of_date = null;
                }
            }
        } else {
            $supplier->as_of_date = null;
        }
        $supplier->balance_type     = $request->balance_type ?? 'pay';
        $supplier->password         = Hash::make($plainPassword);
        $supplier->credit_limit_type = $request->credit_limit_type ?? 'no_limit';
        $supplier->credit_limit     = $request->credit_limit_type === 'custom' ? $request->credit_limit : null;

        if ($request->hasFile('profile_img')) {
            $supplier->profile_img = saveSingleFile($request->file('profile_img'), 'Supplier_img');
        }

        if ($request->hasFile('visiting_doc')) {
            $supplier->visiting_doc = saveSingleFile($request->file('visiting_doc'), 'Supplier_docs');
        }

        if ($request->hasFile('multiple_images')) {
            $multipleImages = saveMultipleFiles($request->file('multiple_images'), 'Supplier_images');
            $supplier->multiple_images = $multipleImages;
        }

        if ($request->hasFile('voice_note')) {
            $supplier->voice_note = saveSingleFile($request->file('voice_note'), 'Supplier_audio');
        }

        $supplier->save();

        if ($supplier->email) {
            Mail::to($supplier->email)->send(new WelcomeCustomerMail($supplier->email, $plainPassword));
        }

        return redirect()->back()->with('success', 'Supplier Added Successfully');
    }

    public function supplier_update(Request $request, Supplier $supplier)
    {
        $plainPassword = $request->password ? $request->password : null;

        $supplier->names            = $request->names ?? $supplier->names;
        $supplier->phones           = array_filter($request->phones ?? $supplier->phones);
        $supplier->company          = $request->company ?? $supplier->company;
        $supplier->email            = $request->email ?? $supplier->email;
        $supplier->carnumber        = $request->carnumber ?? $supplier->carnumber;
        $supplier->group_id         = $request->group_id ?? $supplier->group_id;
        $supplier->opening_balance  = $request->opening_balance ?? $supplier->opening_balance;
        // Convert DD/MM/YYYY to YYYY-MM-DD format
        if ($request->has('as_of_date') && $request->as_of_date) {
            try {
                $supplier->as_of_date = Carbon::createFromFormat('d/m/Y', $request->as_of_date)->format('Y-m-d');
            } catch (\Exception $e) {
                // If conversion fails, try to parse as is (in case it's already in YYYY-MM-DD format)
                try {
                    $supplier->as_of_date = Carbon::parse($request->as_of_date)->format('Y-m-d');
                } catch (\Exception $e2) {
                    // Keep existing date if conversion fails
                    $supplier->as_of_date = $supplier->as_of_date;
                }
            }
        }
        $supplier->balance_type     = $request->balance_type ?? $supplier->balance_type;
        $supplier->credit_limit_type = $request->credit_limit_type ?? $supplier->credit_limit_type;
        $supplier->credit_limit     = $request->credit_limit_type === 'custom' ? ($request->credit_limit ?? $supplier->credit_limit) : null;
        if ($plainPassword) {
            $supplier->password = Hash::make($plainPassword);
        }
        if ($request->hasFile('profile_img')) {
            // Delete old image if exists
            if ($supplier->profile_img && file_exists(public_path($supplier->profile_img))) {
                unlink(public_path($supplier->profile_img));
            }
            $supplier->profile_img = saveSingleFile($request->file('profile_img'), 'Supplier_img');
        }
        if ($request->hasFile('visiting_doc')) {
            // Delete old document if exists
            if ($supplier->visiting_doc && file_exists(public_path($supplier->visiting_doc))) {
                unlink(public_path($supplier->visiting_doc));
            }
            $supplier->visiting_doc = saveSingleFile($request->file('visiting_doc'), 'Supplier_docs');
        }
        if ($request->hasFile('multiple_images')) {
            // Append new images to existing ones
            $existingImages = $supplier->multiple_images ?? [];
            $newImages = saveMultipleFiles($request->file('multiple_images'), 'Supplier_images');
            $supplier->multiple_images = array_merge($existingImages, $newImages);
        }

        if ($request->hasFile('voice_note')) {
            // Delete old voice note if exists
            if ($supplier->voice_note && file_exists(public_path($supplier->voice_note))) {
                unlink(public_path($supplier->voice_note));
            }
            $supplier->voice_note = saveSingleFile($request->file('voice_note'), 'Supplier_audio');
        }

        $supplier->save();

        if ($plainPassword && $supplier->email) {
            Mail::to($supplier->email)->send(new WelcomeCustomerMail($supplier->email, $plainPassword));
        }

        return redirect()->back()->with('success', 'Supplier Updated Successfully');
    }

    public function supplier_delete(Supplier $supplier)
    {
        // Delete files before deleting the record
        if ($supplier->profile_img && file_exists(public_path($supplier->profile_img))) {
            unlink(public_path($supplier->profile_img));
        }
        if ($supplier->visiting_doc && file_exists(public_path($supplier->visiting_doc))) {
            unlink(public_path($supplier->visiting_doc));
        }
        if ($supplier->voice_note && file_exists(public_path($supplier->voice_note))) {
            unlink(public_path($supplier->voice_note));
        }
        if (!empty($supplier->multiple_images)) {
            foreach ($supplier->multiple_images as $imagePath) {
                if (file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
            }
        }

        $supplier->delete();

        return redirect()->back()->with('success', 'Supplier Deleted Successfully');
    }
}
