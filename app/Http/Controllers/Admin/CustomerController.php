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
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function all_customers()
    {
        $branches = Branch::all();
        $customers = Customer::paginate(10);
        $carManufacturers = CarManufacturer::orderBy('name')->get();
        $carModels        = CarModel::orderBy('name')->get();
        $engineccs      = EngineCc::where('status', 'active')->get();
        $carCountries     = CarCountry::orderBy('name')->get();
        //  return $customers;
        return view('admin.customers.index', compact('customers', 'branches', 'carManufacturers', 'carModels', 'engineccs', 'carCountries'));
    }

    public function customer_store(Request $request)
    {
        $plainPassword = $request->password ?? Str::random(12);

        $customer = new Customer();
        $customer->names            = $request->names ?? [];
        $customer->phones           = array_filter($request->phones ?? []);
        $customer->company          = $request->company;
        $customer->email            = $request->email;
        $customer->carnumber        = $request->carnumber;
        $customer->group_id         = $request->group_id;
        $customer->opening_balance  = $request->opening_balance ?? 0;
        $customer->as_of_date       = $request->as_of_date;
        $customer->balance_type     = $request->balance_type ?? 'receive';
        $customer->password         = Hash::make($plainPassword);
        $customer->credit_limit_type = $request->credit_limit_type ?? 'no_limit';
        $customer->credit_limit     = $request->credit_limit_type === 'custom' ? $request->credit_limit : null;

        if ($request->hasFile('profile_img')) {
            $customer->profile_img = saveSingleFile($request->file('profile_img'), 'Customer_img');
        }

        if ($request->hasFile('visiting_doc')) {
            $customer->visiting_doc = saveSingleFile($request->file('visiting_doc'), 'Customer_docs');
        }

        if ($request->hasFile('multiple_images')) {
            $multipleImages = saveMultipleFiles($request->file('multiple_images'), 'Customer_images');
            $customer->multiple_images = $multipleImages;
        }

        if ($request->hasFile('voice_note')) {
            $customer->voice_note = saveSingleFile($request->file('voice_note'), 'Customer_audio');
        }

        $customer->save();

        if ($customer->email) {
            Mail::to($customer->email)->send(new WelcomeCustomerMail($customer->email, $plainPassword));
        }

        return redirect()->back()->with('success', 'Customer Added Successfully');
    }

    public function customer_update(Request $request, Customer $customer)
    {
        $plainPassword = $request->password ? $request->password : null;

        $customer->names            = $request->names ?? $customer->names;
        $customer->phones           = array_filter($request->phones ?? $customer->phones);
        $customer->company          = $request->company ?? $customer->company;
        $customer->email            = $request->email ?? $customer->email;
        $customer->carnumber        = $request->carnumber ?? $customer->carnumber;
        $customer->group_id         = $request->group_id ?? $customer->group_id;
        $customer->opening_balance  = $request->opening_balance ?? $customer->opening_balance;
        $customer->as_of_date       = $request->as_of_date ?? $customer->as_of_date;
        $customer->balance_type     = $request->balance_type ?? $customer->balance_type;
        $customer->credit_limit_type = $request->credit_limit_type ?? $customer->credit_limit_type;
        $customer->credit_limit     = $request->credit_limit_type === 'custom' ? ($request->credit_limit ?? $customer->credit_limit) : null;
        if ($plainPassword) {
            $customer->password = Hash::make($plainPassword);
        }
        if ($request->hasFile('profile_img')) {
            // Delete old image if exists
            if ($customer->profile_img && file_exists(public_path($customer->profile_img))) {
                unlink(public_path($customer->profile_img));
            }
            $customer->profile_img = saveSingleFile($request->file('profile_img'), 'Customer_img');
        }
        if ($request->hasFile('visiting_doc')) {
            // Delete old document if exists
            if ($customer->visiting_doc && file_exists(public_path($customer->visiting_doc))) {
                unlink(public_path($customer->visiting_doc));
            }
            $customer->visiting_doc = saveSingleFile($request->file('visiting_doc'), 'Customer_docs');
        }
        if ($request->hasFile('multiple_images')) {
            // Append new images to existing ones
            $existingImages = $customer->multiple_images ?? [];
            $newImages = saveMultipleFiles($request->file('multiple_images'), 'Customer_images');
            $customer->multiple_images = array_merge($existingImages, $newImages);
        }

        if ($request->hasFile('voice_note')) {
            // Delete old voice note if exists
            if ($customer->voice_note && file_exists(public_path($customer->voice_note))) {
                unlink(public_path($customer->voice_note));
            }
            $customer->voice_note = saveSingleFile($request->file('voice_note'), 'Customer_audio');
        }

        $customer->save();

        if ($plainPassword && $customer->email) {
            Mail::to($customer->email)->send(new WelcomeCustomerMail($customer->email, $plainPassword));
        }

        return redirect()->back()->with('success', 'Customer Updated Successfully');
    }

    // Assuming you need a delete method as per routes
    public function customer_delete(Customer $customer)
    {
        // Delete files before deleting the record
        if ($customer->profile_img && file_exists(public_path($customer->profile_img))) {
            unlink(public_path($customer->profile_img));
        }
        if ($customer->visiting_doc && file_exists(public_path($customer->visiting_doc))) {
            unlink(public_path($customer->visiting_doc));
        }
        if ($customer->voice_note && file_exists(public_path($customer->voice_note))) {
            unlink(public_path($customer->voice_note));
        }
        if (!empty($customer->multiple_images)) {
            foreach ($customer->multiple_images as $imagePath) {
                if (file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
            }
        }

        $customer->delete();

        return redirect()->back()->with('success', 'Customer Deleted Successfully');
    }
}
