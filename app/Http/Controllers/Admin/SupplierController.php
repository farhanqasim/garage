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
use App\Models\SupplierEditHistory;
use App\Models\Purchase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SupplierController extends Controller
{
    public function all_suppliers()
    {
        $branches = Branch::all();
        $suppliers = Supplier::with(['createdBy', 'createdByBranch'])->paginate(10);
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
        
        // Save who created this supplier and from which branch
        if (Auth::check()) {
            $supplier->created_by = Auth::id();
        }
        if (session('selected_branch_id')) {
            $supplier->branch_id = session('selected_branch_id');
        }

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
        
        // Store original values to track changes (get fresh copy)
        $supplier->refresh();
        $originalData = $supplier->getAttributes();
        $changes = [];

        // Track changes for each field
        $fieldsToTrack = [
            'names' => 'Names',
            'phones' => 'Phones',
            'company' => 'Company',
            'email' => 'Email',
            'carnumber' => 'Car Number',
            'group_id' => 'Group',
            'opening_balance' => 'Opening Balance',
            'as_of_date' => 'As of Date',
            'balance_type' => 'Balance Type',
            'credit_limit_type' => 'Credit Limit Type',
            'credit_limit' => 'Credit Limit',
            'profile_img' => 'Profile Image',
            'visiting_doc' => 'Visiting Document',
            'voice_note' => 'Voice Note',
        ];

        // Store old values before updating
        $oldNames = $supplier->names;
        $oldPhones = $supplier->phones;
        $oldCompany = $supplier->company;
        $oldEmail = $supplier->email;
        $oldCarnumber = $supplier->carnumber;
        $oldGroupId = $supplier->group_id;
        $oldOpeningBalance = $supplier->opening_balance;
        $oldAsOfDate = $supplier->as_of_date;
        $oldBalanceType = $supplier->balance_type;
        $oldCreditLimitType = $supplier->credit_limit_type;
        $oldCreditLimit = $supplier->credit_limit;
        $oldProfileImg = $supplier->profile_img;
        $oldVisitingDoc = $supplier->visiting_doc;
        $oldVoiceNote = $supplier->voice_note;

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
            $changes['profile_img'] = [
                'old' => $oldProfileImg ? 'File Exists' : 'N/A',
                'new' => 'File Updated',
                'label' => 'Profile Image'
            ];
        }
        if ($request->hasFile('visiting_doc')) {
            // Delete old document if exists
            if ($supplier->visiting_doc && file_exists(public_path($supplier->visiting_doc))) {
                unlink(public_path($supplier->visiting_doc));
            }
            $supplier->visiting_doc = saveSingleFile($request->file('visiting_doc'), 'Supplier_docs');
            $changes['visiting_doc'] = [
                'old' => $oldVisitingDoc ? 'File Exists' : 'N/A',
                'new' => 'File Updated',
                'label' => 'Visiting Document'
            ];
        }
        if ($request->hasFile('multiple_images')) {
            // Append new images to existing ones
            $existingImages = $supplier->multiple_images ?? [];
            $oldImageCount = is_array($existingImages) ? count($existingImages) : 0;
            $newImages = saveMultipleFiles($request->file('multiple_images'), 'Supplier_images');
            $supplier->multiple_images = array_merge($existingImages, $newImages);
            $newImageCount = is_array($supplier->multiple_images) ? count($supplier->multiple_images) : 0;
            $changes['multiple_images'] = [
                'old' => $oldImageCount . ' image(s)',
                'new' => $newImageCount . ' image(s)',
                'label' => 'Multiple Images'
            ];
        }

        if ($request->hasFile('voice_note')) {
            // Delete old voice note if exists
            if ($supplier->voice_note && file_exists(public_path($supplier->voice_note))) {
                unlink(public_path($supplier->voice_note));
            }
            $supplier->voice_note = saveSingleFile($request->file('voice_note'), 'Supplier_audio');
            $changes['voice_note'] = [
                'old' => $oldVoiceNote ? 'File Exists' : 'N/A',
                'new' => 'File Updated',
                'label' => 'Voice Note'
            ];
        }

        // Track changes for all text fields (file fields are handled separately above)
        $fieldMappings = [
            'names' => ['old' => $oldNames, 'new' => $supplier->names, 'label' => 'Names'],
            'phones' => ['old' => $oldPhones, 'new' => $supplier->phones, 'label' => 'Phones'],
            'company' => ['old' => $oldCompany, 'new' => $supplier->company, 'label' => 'Company'],
            'email' => ['old' => $oldEmail, 'new' => $supplier->email, 'label' => 'Email'],
            'carnumber' => ['old' => $oldCarnumber, 'new' => $supplier->carnumber, 'label' => 'Car Number'],
            'group_id' => ['old' => $oldGroupId, 'new' => $supplier->group_id, 'label' => 'Group'],
            'opening_balance' => ['old' => $oldOpeningBalance, 'new' => $supplier->opening_balance, 'label' => 'Opening Balance'],
            'as_of_date' => ['old' => $oldAsOfDate, 'new' => $supplier->as_of_date, 'label' => 'As of Date'],
            'balance_type' => ['old' => $oldBalanceType, 'new' => $supplier->balance_type, 'label' => 'Balance Type'],
            'credit_limit_type' => ['old' => $oldCreditLimitType, 'new' => $supplier->credit_limit_type, 'label' => 'Credit Limit Type'],
            'credit_limit' => ['old' => $oldCreditLimit, 'new' => $supplier->credit_limit, 'label' => 'Credit Limit'],
        ];
        
        foreach ($fieldMappings as $field => $data) {
            $oldValue = $data['old'];
            $newValue = $data['new'];
            
            // Handle array fields
            if (in_array($field, ['names', 'phones'])) {
                $oldValue = is_array($oldValue) ? implode(', ', $oldValue) : ($oldValue ?? 'N/A');
                $newValue = is_array($newValue) ? implode(', ', $newValue) : ($newValue ?? 'N/A');
            }
            
            // Format values for display
            $oldValue = $oldValue ?? 'N/A';
            $newValue = $newValue ?? 'N/A';
            
            // Only track if value actually changed
            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'label' => $data['label']
                ];
            }
        }
        
        // Track password change separately (don't store actual password)
        if ($plainPassword) {
            $changes['password'] = [
                'old' => '******',
                'new' => '****** (Changed)',
                'label' => 'Password'
            ];
        }

        $supplier->save();
        
        // Save edit history if there are any changes
        if (!empty($changes)) {
            SupplierEditHistory::create([
                'supplier_id' => $supplier->id,
                'edited_by' => Auth::id(),
                'branch_id' => session('selected_branch_id'),
                'changes' => $changes,
                'notes' => $request->notes ?? null,
            ]);
        }

        if ($plainPassword && $supplier->email) {
            Mail::to($supplier->email)->send(new WelcomeCustomerMail($supplier->email, $plainPassword));
        }

        return redirect()->back()->with('success', 'Supplier Updated Successfully');
    }
    
    /**
     * Get edit history for a supplier
     */
    public function getEditHistory(Supplier $supplier)
    {
        $history = $supplier->editHistory()->with(['editedBy', 'branch'])->get();
        
        return response()->json([
            'success' => true,
            'history' => $history->map(function($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->formatted_date,
                    'time' => $item->formatted_time,
                    'edited_by' => $item->editedBy ? $item->editedBy->name : 'N/A',
                    'branch' => $item->branch ? $item->branch->branch_name : 'N/A',
                    'changes' => $item->changes,
                    'notes' => $item->notes,
                ];
            })
        ]);
    }
    
    /**
     * Get Supplier Ledger Report
     */
    public function getSupplierLedger(Supplier $supplier)
    {
        // Get opening balance
        $openingBalance = $supplier->opening_balance ?? 0;
        $balanceType = $supplier->balance_type ?? 'pay'; // 'pay' means we owe supplier, 'receive' means supplier owes us
        
        // Get all purchases from this supplier
        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->with(['branch'])
            ->orderBy('purchase_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Calculate transactions
        $transactions = [];
        $runningBalance = $openingBalance;
        
        foreach ($purchases as $purchase) {
            $transaction = [
                'date' => $purchase->purchase_date->format('d/m/Y'),
                'time' => $purchase->created_at->format('h:i A'),
                'type' => 'Purchase',
                'reference' => $purchase->reference ?? $purchase->invoice_no ?? 'N/A',
                'invoice_no' => $purchase->invoice_no,
                'purchase_id' => $purchase->id,
                'description' => 'Purchase Invoice #' . $purchase->invoice_no,
                'debit' => 0,
                'credit' => 0,
                'balance' => 0,
                'branch' => $purchase->branch ? $purchase->branch->branch_name : 'N/A',
            ];
            
            // If balance_type is 'pay', purchases increase what we owe (debit)
            // If balance_type is 'receive', purchases decrease what supplier owes (credit)
            if ($balanceType == 'pay') {
                $transaction['debit'] = $purchase->grand_total;
                $runningBalance += $purchase->grand_total;
            } else {
                $transaction['credit'] = $purchase->grand_total;
                $runningBalance -= $purchase->grand_total;
            }
            
            $transaction['balance'] = $runningBalance;
            $transactions[] = $transaction;
        }
        
        // Calculate ending balance
        $endingBalance = $runningBalance;
        
        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->names[0] ?? 'N/A',
                'email' => $supplier->email ?? 'N/A',
                'phone' => $supplier->phones[0] ?? 'N/A',
            ],
            'opening_balance' => number_format($openingBalance, 2),
            'balance_type' => $balanceType,
            'transactions' => $transactions,
            'ending_balance' => number_format($endingBalance, 2),
            'total_debit' => number_format(collect($transactions)->sum('debit'), 2),
            'total_credit' => number_format(collect($transactions)->sum('credit'), 2),
        ]);
    }
    
    /**
     * Get Purchase Detail History for Supplier
     */
    public function getPurchaseDetailHistory(Supplier $supplier)
    {
        // Get all purchases with items for this supplier
        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->with(['branch', 'items.item'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $detailHistory = [];
        
        foreach ($purchases as $purchase) {
            $items = [];
            foreach ($purchase->items as $item) {
                $items[] = [
                    'item_name' => $item->item ? $item->item->name : 'N/A',
                    'barcode' => $item->item ? ($item->item->barcode ?? 'N/A') : 'N/A',
                    'quantity' => number_format($item->quantity, 2),
                    'unit' => $item->unit ?? 'pcs',
                    'rate' => number_format($item->rate, 2),
                    'discount' => number_format($item->discount, 2),
                    'tax_percentage' => number_format($item->tax_percentage, 2),
                    'tax_amount' => number_format($item->tax_amount, 2),
                    'unit_cost' => number_format($item->unit_cost, 2),
                    'total_cost' => number_format($item->total_cost, 2),
                ];
            }
            
            $detailHistory[] = [
                'purchase_id' => $purchase->id,
                'invoice_no' => $purchase->invoice_no,
                'reference' => $purchase->reference ?? 'N/A',
                'date' => $purchase->purchase_date->format('d/m/Y'),
                'time' => $purchase->created_at->format('h:i A'),
                'branch' => $purchase->branch ? $purchase->branch->branch_name : 'N/A',
                'status' => $purchase->status,
                'subtotal' => number_format($purchase->subtotal, 2),
                'discount' => number_format($purchase->discount, 2),
                'order_tax' => number_format($purchase->order_tax, 2),
                'shipping' => number_format($purchase->shipping, 2),
                'grand_total' => number_format($purchase->grand_total, 2),
                'description' => $purchase->description ?? 'N/A',
                'items' => $items,
            ];
        }
        
        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->names[0] ?? 'N/A',
            ],
            'detail_history' => $detailHistory,
        ]);
    }
    
    /**
     * Generate Supplier Ledger PDF
     */
    public function generateSupplierLedgerPDF(Supplier $supplier)
    {
        // Get opening balance
        $openingBalance = $supplier->opening_balance ?? 0;
        $balanceType = $supplier->balance_type ?? 'pay';
        
        // Get all purchases from this supplier
        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->with(['branch'])
            ->orderBy('purchase_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Calculate transactions
        $transactions = [];
        $runningBalance = $openingBalance;
        
        foreach ($purchases as $purchase) {
            $transaction = [
                'date' => $purchase->purchase_date->format('d/m/Y'),
                'time' => $purchase->created_at->format('h:i A'),
                'type' => 'Purchase',
                'reference' => $purchase->reference ?? $purchase->invoice_no ?? 'N/A',
                'invoice_no' => $purchase->invoice_no,
                'description' => 'Purchase Invoice #' . $purchase->invoice_no,
                'debit' => 0,
                'credit' => 0,
                'balance' => 0,
                'branch' => $purchase->branch ? $purchase->branch->branch_name : 'N/A',
            ];
            
            if ($balanceType == 'pay') {
                $transaction['debit'] = $purchase->grand_total;
                $runningBalance += $purchase->grand_total;
            } else {
                $transaction['credit'] = $purchase->grand_total;
                $runningBalance -= $purchase->grand_total;
            }
            
            $transaction['balance'] = $runningBalance;
            $transactions[] = $transaction;
        }
        
        $endingBalance = $runningBalance;
        $totalDebit = collect($transactions)->sum('debit');
        $totalCredit = collect($transactions)->sum('credit');
        
        $data = [
            'supplier' => [
                'name' => $supplier->names[0] ?? 'N/A',
                'email' => $supplier->email ?? 'N/A',
                'phone' => $supplier->phones[0] ?? 'N/A',
            ],
            'opening_balance' => $openingBalance,
            'balance_type' => $balanceType,
            'transactions' => $transactions,
            'ending_balance' => $endingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'generated_at' => Carbon::now()->format('d/m/Y h:i A'),
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.suppliers.pdf.ledger', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);
        
        return $pdf->download('Supplier_Ledger_' . str_replace(' ', '_', $supplier->names[0] ?? 'Supplier') . '_' . date('Y-m-d') . '.pdf');
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
