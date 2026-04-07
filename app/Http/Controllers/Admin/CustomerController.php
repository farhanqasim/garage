<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeCustomerMail;
use App\Models\Branch;
use App\Models\CarCountry;
use App\Models\CarManufacturer;
use App\Models\CarModel;
use App\Models\Customer;
use App\Models\CustomerCar;
use App\Models\EngineCc;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    private const CUSTOMER_TYPES = ['retail', 'wholesaler'];

    public function all_customers()
    {
        $branches = Branch::orderBy('branch_name', 'asc')->get();
        $customers = Customer::with('customerCars', 'branch')->paginate(10);
        $carManufacturers = CarManufacturer::orderBy('name')->get();
        $carModels = CarModel::orderBy('name')->get();
        $engineccs = EngineCc::where('status', 'active')->get();
        $carCountries = CarCountry::orderBy('name')->get();

        //  return $customers;
        return view('admin.customers.index', compact('customers', 'branches', 'carManufacturers', 'carModels', 'engineccs', 'carCountries'));
    }

    public function customer_store(Request $request)
    {
        $request->validate([
            'customer_type' => ['nullable', 'in:'.implode(',', self::CUSTOMER_TYPES)],
        ]);

        $plainPassword = $request->password ?? Str::random(12);

        $branchId = $request->branch_id ?: session('selected_branch_id');
        if (! $branchId && auth()->user()) {
            $user = auth()->user();
            $branchId = $user->branch_id ?? $user->assignedBranches()->value('branch_id');
        }

        $customer = new Customer;
        $customer->customer_type = $request->input('customer_type', 'retail');
        $customer->branch_id = $branchId ? (int) $branchId : null;
        $customer->branch_name = $branchId ? (Branch::find($branchId)?->branch_name) : null;
        $customer->names = $request->names ?? [];
        $customer->phones = array_filter($request->phones ?? []);
        $customer->company = $request->company;
        $customer->email = $request->email;
        $customer->carnumber = $request->carnumber;
        $customer->group_id = $request->group_id;
        $customer->opening_balance = $request->opening_balance ?? 0;
        $customer->as_of_date = $request->as_of_date;
        $customer->balance_type = $request->balance_type ?? 'receive';
        $customer->password = Hash::make($plainPassword);
        $customer->credit_limit_type = $request->credit_limit_type ?? 'no_limit';
        $customer->credit_limit = $request->credit_limit_type === 'custom' ? $request->credit_limit : null;

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

        if ($request->filled('vehicles') && is_array($request->vehicles)) {
            foreach ($request->vehicles as $vehicleData) {
                $plate = trim($vehicleData['plate_number'] ?? '');
                $make = trim($vehicleData['make'] ?? '');
                $model = trim($vehicleData['model'] ?? '');
                $year = trim($vehicleData['year'] ?? '');
                if ($plate !== '' && $make !== '' && $model !== '' && $year !== '') {
                    CustomerCar::create([
                        'customer_id' => $customer->id,
                        'plate_number' => $plate,
                        'make' => $make,
                        'model' => $model,
                        'year' => $year,
                    ]);
                }
            }
        }

        if ($customer->email) {
            Mail::to($customer->email)->send(new WelcomeCustomerMail($customer->email, $plainPassword));
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'customer' => $customer, 'message' => 'Customer added successfully']);
        }

        return redirect()->back()->with('success', 'Customer Added Successfully');
    }

    /**
     * Save customer vehicle to database (customer_cars table).
     * Used when adding vehicle from sales create form - selected customer's ID is used.
     */
    public function storeVehicle(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plate_number' => 'required|string|max:255',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'required|string|max:4',
            'car_manufacturer_id' => 'nullable|exists:car_manufacturers,id',
            'car_model_id' => 'nullable|exists:car_models,id',
        ]);

        $make = trim((string) $request->make);
        $model = trim((string) $request->model);
        $manufacturerId = $request->car_manufacturer_id ? (int) $request->car_manufacturer_id : null;
        $modelId = $request->car_model_id ? (int) $request->car_model_id : null;

        if ($manufacturerId) {
            $m = CarManufacturer::find($manufacturerId);
            if ($m) {
                $make = $m->name;
            }
        }
        if ($modelId) {
            $md = CarModel::find($modelId);
            if ($md) {
                $model = $md->name;
            }
        }

        if ($make === '' || $model === '') {
            return response()->json([
                'success' => false,
                'message' => 'Make and model are required (select from list or add new).',
            ], 422);
        }

        $plateNorm = strtoupper(preg_replace('/\s+/', '', trim((string) $request->plate_number)));

        $payload = [
            'plate_number' => $plateNorm !== '' ? $plateNorm : trim((string) $request->plate_number),
            'make' => $make,
            'model' => $model,
            'year' => $request->year,
            'car_manufacturer_id' => $manufacturerId,
            'car_model_id' => $modelId,
        ];

        $existing = CustomerCar::where('customer_id', $request->customer_id)
            ->get()
            ->first(function (CustomerCar $c) use ($plateNorm) {
                $p = strtoupper(preg_replace('/\s+/', '', trim((string) ($c->plate_number ?? ''))));

                return $p === $plateNorm;
            });

        if ($existing) {
            $existing->update($payload);
            $vehicle = $existing->fresh();
        } else {
            $vehicle = CustomerCar::create(array_merge([
                'customer_id' => $request->customer_id,
            ], $payload));
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle saved successfully',
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Update an existing customer vehicle (customer_cars).
     */
    public function updateVehicle(Request $request, $id)
    {
        $request->validate([
            'plate_number' => 'required|string|max:255',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'required|string|max:4',
            'car_manufacturer_id' => 'nullable|exists:car_manufacturers,id',
            'car_model_id' => 'nullable|exists:car_models,id',
        ]);

        $make = trim((string) $request->make);
        $model = trim((string) $request->model);
        $manufacturerId = $request->car_manufacturer_id ? (int) $request->car_manufacturer_id : null;
        $modelId = $request->car_model_id ? (int) $request->car_model_id : null;

        if ($manufacturerId) {
            $m = CarManufacturer::find($manufacturerId);
            if ($m) {
                $make = $m->name;
            }
        }
        if ($modelId) {
            $md = CarModel::find($modelId);
            if ($md) {
                $model = $md->name;
            }
        }

        if ($make === '' || $model === '') {
            return response()->json([
                'success' => false,
                'message' => 'Make and model are required (select from list or add new).',
            ], 422);
        }

        $plateNorm = strtoupper(preg_replace('/\s+/', '', trim((string) $request->plate_number)));

        $car = CustomerCar::findOrFail($id);
        $car->update([
            'plate_number' => $plateNorm !== '' ? $plateNorm : trim((string) $request->plate_number),
            'make' => $make,
            'model' => $model,
            'year' => $request->year,
            'car_manufacturer_id' => $manufacturerId,
            'car_model_id' => $modelId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'vehicle' => $car->fresh(),
        ]);
    }

    /**
     * Get vehicles for a customer (for sales form - show below Add Vehicle button).
     */
    public function getCustomerVehicles(Customer $customer)
    {
        $cars = $customer->customerCars()->orderBy('plate_number')->get();

        $formatKmForApi = static function ($value): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            if (! is_numeric((string) $value)) {
                return null;
            }
            $f = (float) $value;
            if ($f < 0) {
                return null;
            }
            // Whole KM for display / JS (avoid "85000.00" vs "85000" mismatches)
            if (abs($f - round($f)) < 0.00001) {
                return (string) (int) round($f);
            }

            return rtrim(rtrim(number_format($f, 2, '.', ''), '0'), '.') ?: null;
        };

        return response()->json([
            'success' => true,
            'vehicles' => $cars->map(function ($car) use ($formatKmForApi) {
                $nextDate = $car->last_service_next_date;
                $nextDateStr = $nextDate ? $nextDate->format('Y-m-d') : '';
                $lastVisitDate = $car->last_visit_date;
                $lastVisitDateStr = $lastVisitDate ? $lastVisitDate->format('Y-m-d') : '';

                $rawNextKm = $car->getRawOriginal('last_service_next_km') ?? $car->last_service_next_km;
                $nextKmStr = $formatKmForApi($rawNextKm);

                $rawCurrentKm = $car->getRawOriginal('last_service_current_km') ?? $car->last_service_current_km;
                $currentKmStr = $formatKmForApi($rawCurrentKm);

                return [
                    'id' => $car->id,
                    'customerId' => $car->customer_id,
                    'plateNumber' => $car->plate_number,
                    'make' => $car->make ?? '',
                    'model' => $car->model ?? '',
                    'year' => $car->year ?? '',
                    'car_manufacturer_id' => $car->car_manufacturer_id,
                    'car_model_id' => $car->car_model_id,
                    'oil_capacity' => '', // legacy; not stored on car
                    'current_km' => $currentKmStr ?? '',
                    'daily_run_km' => $car->last_service_daily_run_km !== null ? (string) $car->last_service_daily_run_km : '',
                    'next_date' => $nextDateStr,
                    'next_km' => $nextKmStr ?? '',
                    'interval_days' => $car->last_service_interval_days !== null ? (string) $car->last_service_interval_days : '',
                    'interval_months' => $car->last_service_interval_months !== null ? (string) $car->last_service_interval_months : '',
                    'last_visit_date' => $lastVisitDateStr,
                    'last_service_current_km' => $currentKmStr,
                    'last_service_next_km' => $nextKmStr,
                    'last_service_next_date' => $nextDateStr,
                    'last_service_daily_run_km' => $car->last_service_daily_run_km,
                    'last_service_interval_days' => $car->last_service_interval_days,
                    'last_service_interval_months' => $car->last_service_interval_months,
                ];
            }),
        ]);
    }

    public function getCustomerForEdit($id)
    {
        $customer = Customer::findOrFail($id);

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    public function customer_update(Request $request, Customer $customer)
    {
        $request->validate([
            'customer_type' => ['nullable', 'in:'.implode(',', self::CUSTOMER_TYPES)],
        ]);

        $plainPassword = $request->password ? $request->password : null;

        if ($request->has('branch_id')) {
            $customer->branch_id = $request->branch_id ? (int) $request->branch_id : null;
            $customer->branch_name = $customer->branch_id ? (Branch::find($customer->branch_id)?->branch_name) : null;
        }
        if ($request->has('customer_type')) {
            $customer->customer_type = $request->input('customer_type', $customer->customer_type ?? 'retail');
        }
        $customer->names = $request->names ?? $customer->names;
        $customer->phones = array_filter($request->phones ?? $customer->phones);
        $customer->company = $request->company ?? $customer->company;
        $customer->email = $request->email ?? $customer->email;
        $customer->carnumber = $request->carnumber ?? $customer->carnumber;
        $customer->group_id = $request->group_id ?? $customer->group_id;
        $customer->opening_balance = $request->opening_balance ?? $customer->opening_balance;
        $customer->as_of_date = $request->as_of_date ?? $customer->as_of_date;
        $customer->balance_type = $request->balance_type ?? $customer->balance_type;
        $customer->credit_limit_type = $request->credit_limit_type ?? $customer->credit_limit_type;
        $customer->credit_limit = $request->credit_limit_type === 'custom' ? ($request->credit_limit ?? $customer->credit_limit) : null;
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
        if (! empty($customer->multiple_images)) {
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
     * Build customer ledger data (for JSON API and HTML report).
     * Optional $dateFrom and $dateTo (Y-m-d strings or Carbon) filter transactions and set period opening/ending.
     */
    private function getCustomerLedgerData(Customer $customer, $dateFrom = null, $dateTo = null): array
    {
        $balanceType = $customer->balance_type ?? 'receive';
        $baseQuery = Sale::where('customer_id', $customer->id)
            ->with(['branch', 'user', 'saleItems'])
            ->orderBy('sale_date', 'asc')
            ->orderBy('created_at', 'asc');

        $salesQuery = clone $baseQuery;
        if ($dateFrom !== null) {
            $from = $dateFrom instanceof Carbon ? $dateFrom : Carbon::parse($dateFrom)->startOfDay();
            $salesQuery->where('sale_date', '>=', $from);
        }
        if ($dateTo !== null) {
            $to = $dateTo instanceof Carbon ? $dateTo : Carbon::parse($dateTo)->endOfDay();
            $salesQuery->where('sale_date', '<=', $to);
        }
        $sales = $salesQuery->get();

        // Opening balance: either customer's opening_balance (no filter) or balance brought forward at start of period
        $openingBalance = $customer->opening_balance ?? 0;
        if ($dateFrom !== null) {
            $from = $dateFrom instanceof Carbon ? $dateFrom : Carbon::parse($dateFrom)->startOfDay();
            $salesBeforePeriod = Sale::where('customer_id', $customer->id)->where('sale_date', '<', $from)->get();
            foreach ($salesBeforePeriod as $sale) {
                if ($balanceType == 'receive') {
                    $openingBalance += $sale->grand_total;
                } else {
                    $openingBalance -= $sale->grand_total;
                }
            }
        }

        $transactions = [];
        $runningBalance = $openingBalance;

        foreach ($sales as $sale) {
            $transaction = [
                'date' => $sale->sale_date->format('d/m/Y'),
                'time' => $sale->created_at->format('h:i A'),
                'type' => 'Sale',
                'reference' => $sale->reference ?? 'N/A',
                'description' => 'Sale #'.$sale->id,
                'debit' => 0,
                'credit' => 0,
                'balance' => 0,
                'branch' => $sale->branch ? $sale->branch->branch_name : 'N/A',
                'user' => $sale->user ? $sale->user->name : 'N/A',
            ];

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

        $endingBalance = $runningBalance;
        $totalDebit = collect($transactions)->sum('debit');
        $totalCredit = collect($transactions)->sum('credit');

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->names[0] ?? 'N/A',
                'email' => $customer->email ?? 'N/A',
                'phone' => $customer->phones[0] ?? 'N/A',
            ],
            'opening_balance' => $openingBalance,
            'ending_balance' => $endingBalance,
            'balance_type' => $balanceType,
            'transactions' => $transactions,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ];
    }

    /**
     * Net remaining on a sale for balance / aging (matches sales payment UI: discount counts when no payments).
     */
    private function saleNetRemainingForCustomerBalance(Sale $sale): float
    {
        $grand = (float) ($sale->grand_total ?? 0);
        $paid = 0.0;
        foreach ($sale->payments as $p) {
            $paid += (float) ($p->pivot->allocated_amount ?? 0);
        }
        $discount = (float) ($sale->discount ?? 0);
        if ($discount > 0.00001 && $paid <= 0.00001) {
            return max(0.0, $grand - $discount - $paid);
        }

        return max(0.0, $grand - $paid);
    }

    /**
     * Payment-aware balance + labels + oldest outstanding date (excludes estimate invoices).
     */
    private function computeCustomerSaleBalanceDetail(Customer $customer): array
    {
        $balanceType = $customer->balance_type ?? 'receive';
        $opening = (float) ($customer->opening_balance ?? 0);

        $saleIds = Sale::query()
            ->where('customer_id', $customer->id)
            ->where('status', '!=', 'estimate')
            ->pluck('id');

        $totalSales = $saleIds->isEmpty()
            ? 0.0
            : (float) Sale::query()->whereIn('id', $saleIds)->sum('grand_total');

        $totalAllocated = $saleIds->isEmpty()
            ? 0.0
            : (float) DB::table('sale_payments')->whereIn('sale_id', $saleIds)->sum('allocated_amount');

        if ($balanceType === 'receive') {
            $signedBalance = $opening + $totalSales - $totalAllocated;
        } else {
            $signedBalance = $opening - $totalSales - $totalAllocated;
        }

        $eps = 0.005;
        $abs = abs($signedBalance);
        $nearZero = $abs < $eps;

        if ($nearZero) {
            return [
                'signed_balance' => 0.0,
                'display_amount' => 0.0,
                'balance_type' => $balanceType,
                'classification' => 'zero',
                'label' => 'No previous balance',
                'secondary_label' => null,
                'due_age_days' => null,
                'due_age_text' => null,
                'due_since_text' => null,
                'oldest_outstanding_date' => null,
            ];
        }

        $classification = 'mixed';
        $label = 'Balance';
        $secondaryLabel = null;

        if ($balanceType === 'receive') {
            if ($signedBalance > 0) {
                $classification = 'receivable';
                $label = 'Due from customer';
                $secondaryLabel = 'Outstanding receivable';
            } else {
                $classification = 'advance';
                $label = 'Advance balance';
                $secondaryLabel = 'Credit in favour of customer';
            }
        } else {
            if ($signedBalance > 0) {
                $classification = 'payable_to_customer';
                $label = 'Party payable to us';
                $secondaryLabel = 'Amount we owe the party';
            } else {
                $classification = 'receivable';
                $label = 'Due from customer';
                $secondaryLabel = 'Outstanding receivable';
            }
        }

        $oldestDate = null;
        $needsAging = in_array($classification, ['receivable'], true);

        if ($needsAging) {
            $sales = Sale::query()
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'estimate')
                ->with('payments')
                ->orderBy('sale_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($sales as $sale) {
                if ($this->saleNetRemainingForCustomerBalance($sale) > $eps) {
                    $oldestDate = $sale->sale_date instanceof Carbon ? $sale->sale_date->copy()->startOfDay() : Carbon::parse($sale->sale_date)->startOfDay();
                    break;
                }
            }

            if ($oldestDate === null && $opening > $eps && $balanceType === 'receive') {
                if (! empty($customer->as_of_date)) {
                    try {
                        $oldestDate = Carbon::parse($customer->as_of_date)->startOfDay();
                    } catch (\Throwable $e) {
                        $oldestDate = null;
                    }
                }
            }
        } elseif ($classification === 'payable_to_customer' && $signedBalance > $eps) {
            if (! empty($customer->as_of_date)) {
                try {
                    $oldestDate = Carbon::parse($customer->as_of_date)->startOfDay();
                } catch (\Throwable $e) {
                    $oldestDate = null;
                }
            }
        }

        $agePayload = $this->buildCustomerDueAgePayload($oldestDate);

        return [
            'signed_balance' => round($signedBalance, 2),
            'display_amount' => round($abs, 2),
            'balance_type' => $balanceType,
            'classification' => $classification,
            'label' => $label,
            'secondary_label' => $secondaryLabel,
            'due_age_days' => $agePayload['due_age_days'],
            'due_age_text' => $agePayload['due_age_text'],
            'due_since_text' => $agePayload['due_since_text'],
            'oldest_outstanding_date' => $agePayload['oldest_outstanding_date'],
        ];
    }

    private function buildCustomerDueAgePayload(?Carbon $fromDate): array
    {
        if ($fromDate === null) {
            return [
                'due_age_days' => null,
                'due_age_text' => null,
                'due_since_text' => null,
                'oldest_outstanding_date' => null,
            ];
        }

        $from = $fromDate->copy()->startOfDay();
        $today = Carbon::today();

        if ($from->gt($today)) {
            return [
                'due_age_days' => 0,
                'due_age_text' => null,
                'due_since_text' => 'Since '.$from->format('d M Y'),
                'oldest_outstanding_date' => $from->format('Y-m-d'),
            ];
        }

        $days = (int) $from->diffInDays($today);
        $months = intdiv($days, 30);
        $remDays = $days - ($months * 30);

        if ($days === 0) {
            $dueText = 'Due today';
        } elseif ($months >= 1) {
            $dueText = 'Due for '.$months.' month'.($months !== 1 ? 's' : '');
            if ($remDays > 0) {
                $dueText .= ' '.$remDays.' day'.($remDays !== 1 ? 's' : '');
            }
        } else {
            $dueText = 'Due for '.$days.' day'.($days !== 1 ? 's' : '');
        }

        return [
            'due_age_days' => $days,
            'due_age_text' => $dueText,
            'due_since_text' => 'Due since '.$from->format('d M Y'),
            'oldest_outstanding_date' => $from->format('Y-m-d'),
        ];
    }

    /**
     * Get customer current balance (payment-aware) for sales form + mobile badge.
     */
    public function getCustomerBalance(Customer $customer)
    {
        $detail = $this->computeCustomerSaleBalanceDetail($customer);

        return response()->json([
            'success' => true,
            'balance' => (float) $detail['signed_balance'],
            'balance_type' => $detail['balance_type'],
            'classification' => $detail['classification'],
            'label' => $detail['label'],
            'secondary_label' => $detail['secondary_label'],
            'display_amount' => (float) $detail['display_amount'],
            'signed_balance' => (float) $detail['signed_balance'],
            'due_age_days' => $detail['due_age_days'],
            'due_age_text' => $detail['due_age_text'],
            'due_since_text' => $detail['due_since_text'],
            'oldest_outstanding_date' => $detail['oldest_outstanding_date'],
        ]);
    }

    /**
     * Get Customer Ledger (JSON for modal/AJAX).
     */
    public function getCustomerLedger(Customer $customer)
    {
        $data = $this->getCustomerLedgerData($customer);

        return response()->json([
            'success' => true,
            'customer' => $data['customer'],
            'opening_balance' => number_format($data['opening_balance'], 2),
            'ending_balance' => number_format($data['ending_balance'], 2),
            'balance_type' => $data['balance_type'],
            'transactions' => $data['transactions'],
            'total_debit' => number_format($data['total_debit'], 2),
            'total_credit' => number_format($data['total_credit'], 2),
        ]);
    }

    /**
     * Customer Ledger Report as full page (new tab – no modal freeze).
     * Query: date_from, date_to (Y-m-d). Optional: all=1 for no date filter. Default: today.
     */
    public function showCustomerLedgerReport(Request $request, Customer $customer)
    {
        $today = Carbon::today();
        $showAll = $request->query('all') || $request->query('clear');

        if ($showAll) {
            $dateFrom = null;
            $dateTo = null;
            $dateFromDisplay = null;
            $dateToDisplay = null;
            $dateFromStr = null;
            $dateToStr = null;
        } else {
            $dateFrom = $request->query('date_from')
                ? Carbon::parse($request->query('date_from'))->startOfDay()
                : $today->copy()->startOfDay();
            $dateTo = $request->query('date_to')
                ? Carbon::parse($request->query('date_to'))->endOfDay()
                : $today->copy()->endOfDay();
            $dateFromDisplay = $dateFrom->format('d/m/Y');
            $dateToDisplay = $dateTo->format('d/m/Y');
            $dateFromStr = $dateFrom->format('Y-m-d');
            $dateToStr = $dateTo->format('Y-m-d');
        }

        $data = $this->getCustomerLedgerData($customer, $dateFrom, $dateTo);

        return view('admin.customers.ledger-report', [
            'customer' => $data['customer'],
            'opening_balance' => $data['opening_balance'],
            'ending_balance' => $data['ending_balance'],
            'balance_type' => $data['balance_type'],
            'transactions' => $data['transactions'],
            'total_debit' => $data['total_debit'],
            'total_credit' => $data['total_credit'],
            'generated_at' => now()->format('d/m/Y h:i A'),
            'date_from' => $dateFromStr,
            'date_to' => $dateToStr,
            'date_from_display' => $dateFromDisplay,
            'date_to_display' => $dateToDisplay,
            'show_all' => $showAll,
        ]);
    }
}
