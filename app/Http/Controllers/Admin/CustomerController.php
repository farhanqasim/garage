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
use App\Models\Sale;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'customer' => $customer, 'message' => 'Customer added successfully']);
        }
        return redirect()->back()->with('success', 'Customer Added Successfully');
    }

    public function getCustomerForEdit($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
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
    
    /**
     * Get Customer Ledger Report
     */
    public function getCustomerLedger(Customer $customer)
    {
        // Get opening balance
        $openingBalance = $customer->opening_balance ?? 0;
        $balanceType = $customer->balance_type ?? 'receive'; // 'receive' means customer owes us, 'pay' means we owe customer
        
        // Get all sales for this customer
        $sales = Sale::where('customer_id', $customer->id)
            ->with(['branch', 'user', 'saleItems'])
            ->orderBy('sale_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Calculate transactions
        $transactions = [];
        $runningBalance = $openingBalance;
        
        foreach ($sales as $sale) {
            $transaction = [
                'date' => $sale->sale_date->format('d/m/Y'),
                'time' => $sale->created_at->format('h:i A'),
                'type' => 'Sale',
                'reference' => $sale->reference ?? 'N/A',
                'description' => 'Sale #' . $sale->id,
                'debit' => 0,
                'credit' => 0,
                'balance' => 0,
                'branch' => $sale->branch ? $sale->branch->branch_name : 'N/A',
                'user' => $sale->user ? $sale->user->name : 'N/A',
            ];
            
            // If balance_type is 'receive', sales increase what customer owes (debit)
            // If balance_type is 'pay', sales decrease what we owe (credit)
            if ($balanceType == 'receive') {
                $transaction['debit'] = $sale->grand_total;
                $runningBalance += $sale->grand_total;
            } else {
                $transaction['credit'] = $sale->grand_total;
                $runningBalance -= $sale->grand_total;
            }
            
            $transaction['balance'] = $runningBalance;
            $transactions[] = $transaction;
        }
        
        // Calculate ending balance
        $endingBalance = $runningBalance;
        
        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->names[0] ?? 'N/A',
                'email' => $customer->email ?? 'N/A',
                'phone' => $customer->phones[0] ?? 'N/A',
            ],
            'opening_balance' => number_format($openingBalance, 2),
            'balance_type' => $balanceType,
            'transactions' => $transactions,
            'ending_balance' => number_format($endingBalance, 2),
            'total_debit' => number_format(collect($transactions)->sum('debit'), 2),
            'total_credit' => number_format(collect($transactions)->sum('credit'), 2),
        ]);
    }
}
