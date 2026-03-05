<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CarWashService;
use App\Models\CarWashWorker;
use App\Models\CarWashJob;
use App\Models\CarWashShopExpense;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Http\Controllers\CarWashPaymentController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Traits\HasBranchAccess;

class HomeController extends Controller
{
    use HasBranchAccess;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * Data filtered by branch (same as sidebar permissions).
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Sales (branch-filtered)
        $saleQuery = Sale::query();
        $this->applyBranchFilter($saleQuery, 'branch_id');
        $todayOrdersCount = (clone $saleQuery)->whereDate('sale_date', $today)->count();
        $totalSales = (clone $saleQuery)->sum('grand_total');
        $totalSalesReturn = (clone $saleQuery)->where('status', 'return')->sum('grand_total');

        // Purchases (branch-filtered)
        $purchaseQuery = Purchase::query();
        $this->applyBranchFilter($purchaseQuery, 'branch_id');
        $totalPurchase = (clone $purchaseQuery)->sum('grand_total');
        $totalPurchaseReturn = (clone $purchaseQuery)->where('status', 'return')->sum('grand_total');
        // Unverified purchase bills (at least one item not verified)
        $unverifiedPurchasesCount = (clone $purchaseQuery)->whereHas('items', function ($q) {
            $q->whereNull('verified_by');
        })->count();

        // Low stock: on_hand <= l_stock or 5 (min_qty column may not exist)
        $lowStockItems = Item::where('is_active', true)
            ->whereRaw('COALESCE(on_hand, 0) <= COALESCE(l_stock, 5)')
            ->orderByRaw('COALESCE(on_hand, 0) ASC')
            ->limit(10)
            ->get(['id', 'p_id', 'on_hand', 'l_stock', 'image', 'sale_price', 'short_disc']);

        $supplierQuery = Supplier::query();
        $this->applyBranchFilter($supplierQuery, 'branch_id');
        $suppliersCount = $supplierQuery->count();
        $customersCount = Customer::count();
        $ordersCount = (clone $saleQuery)->count();

        // Recent sales (branch-filtered)
        $recentSalesQuery = Sale::query()->with('customer');
        $this->applyBranchFilter($recentSalesQuery, 'branch_id');
        $recentSales = $recentSalesQuery->orderBy('sale_date', 'desc')->orderBy('id', 'desc')->limit(5)->get();

        // Recent purchases (branch-filtered)
        $recentPurchasesQuery = Purchase::query()->with('supplier');
        $this->applyBranchFilter($recentPurchasesQuery, 'branch_id');
        $recentPurchases = $recentPurchasesQuery->orderBy('purchase_date', 'desc')->orderBy('id', 'desc')->limit(5)->get();

        // Top selling items (from sale_items, branch via sale)
        $topSellingQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('items', 'items.id', '=', 'sale_items.item_id')
            ->select('items.id', 'items.p_id', 'items.sale_price', DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->groupBy('items.id', 'items.p_id', 'items.sale_price');
        $branchId = $this->getUserBranchId($user);
        if ($branchId) {
            $topSellingQuery->where(function ($q) use ($branchId) {
                $q->where('sales.branch_id', $branchId)->orWhereNull('sales.branch_id');
            });
        }
        $topSellingProducts = $topSellingQuery->orderByDesc('total_qty')->limit(5)->get();

        // Top customers by total sales (branch-filtered)
        $topCustomersQuery = Sale::query()
            ->select('customer_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(grand_total) as total_amount'))
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->limit(5);
        $this->applyBranchFilter($topCustomersQuery, 'branch_id');
        $topCustomersIds = $topCustomersQuery->pluck('customer_id');
        $topCustomersData = $topCustomersQuery->get();
        $topCustomers = Customer::whereIn('id', $topCustomersIds)->get()->keyBy('id');
        $topCustomersList = $topCustomersData->map(function ($row) use ($topCustomers) {
            $c = $topCustomers->get($row->customer_id);
            return (object)[
                'id' => $row->customer_id,
                'name' => $c ? (is_array($c->names) ? ($c->names[0] ?? $c->company ?? 'N/A') : $c->company ?? 'N/A') : 'N/A',
                'order_count' => $row->order_count,
                'total_amount' => $row->total_amount,
            ];
        });

        return view('home', compact(
            'todayOrdersCount',
            'totalSales',
            'totalSalesReturn',
            'totalPurchase',
            'totalPurchaseReturn',
            'unverifiedPurchasesCount',
            'lowStockItems',
            'suppliersCount',
            'customersCount',
            'ordersCount',
            'recentSales',
            'recentPurchases',
            'topSellingProducts',
            'topCustomersList'
        ));
    }

    /**
     * In-app WhatsApp (replica) – login saved in browser once.
     */
    public function messenger()
    {
        return view('messenger');
    }

    public function userprofile($id){
      $user = User::find($id);
      $currentUser = auth()->user();

      if (!$user) {
          abort(404, 'User not found');
      }

      // Allow: own profile, or admin, or same-branch access (for staff management)
      $isOwn = (int) $currentUser->id === (int) $id;
      $isAdmin = $currentUser->role === 'admin';
      $sameBranch = $user->branch_id && $user->branch_id == $this->getUserBranchId($currentUser);
      $assignedToMyBranch = $user->assignedBranches()->where('branch_id', $this->getUserBranchId($currentUser))->exists();

      if ($isOwn || $isAdmin || $sameBranch || $assignedToMyBranch) {
          return view('admin.pages.profile', compact('user'));
      }

      abort(403, 'Unauthorized access');
     }



public function userprofileupdate(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'salary_per_month' => 'nullable|numeric|min:0',
        'salary_per_day' => 'nullable|numeric|min:0',
        'salary_percentage' => 'nullable|numeric|min:0|max:100',
    ]);
    $user = User::findOrFail($id);
    $currentUser = auth()->user();

    // Same access rules as userprofile: own, admin, or same-branch
    $isOwn = (int) $currentUser->id === (int) $id;
    $isAdmin = $currentUser->role === 'admin';
    $sameBranch = $user->branch_id && $user->branch_id == $this->getUserBranchId($currentUser);
    $assignedToMyBranch = $user->assignedBranches()->where('branch_id', $this->getUserBranchId($currentUser))->exists();

    if (!$isOwn && !$isAdmin && !$sameBranch && !$assignedToMyBranch) {
        abort(403, 'Unauthorized access');
    }

    // Profile Image Handling
    if ($request->hasFile('profile_img')) {
        $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
    }
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->phone = $request->input('phone');
    $user->salary_per_month = $request->filled('salary_per_month') ? $request->input('salary_per_month') : null;
    $user->salary_per_day = $request->filled('salary_per_day') ? $request->input('salary_per_day') : null;
    $user->salary_percentage = $request->filled('salary_percentage') ? $request->input('salary_percentage') : null;
    // Password Update Logic
    if ($request->filled('new_password')) {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        $user->password = Hash::make($request->new_password);
    }
    // return $user;
    $user->save();
    return redirect()->back()->with('success', 'Profile updated successfully!');
}

/**
 * Show employee profile page
 */
public function employeeProfile($id)
{
    $user = User::find($id);
    $currentUser = auth()->user();

    if (!$user) {
        abort(404, 'User not found');
    }

    // Allow: own profile, or admin, or same-branch access
    $isOwn = (int) $currentUser->id === (int) $id;
    $isAdmin = $currentUser->role === 'admin';
    $branchId = $this->getUserBranchId($currentUser);
    $sameBranch = $user->branch_id && $user->branch_id == $branchId;
    $assignedToMyBranch = $branchId && $user->assignedBranches()->where('branch_id', $branchId)->exists();

    if (!$isOwn && !$isAdmin && !$sameBranch && !$assignedToMyBranch) {
        abort(403, 'Unauthorized access');
    }

    // Get branch info
    $branchInfo = $this->getBranchInfoForDisplay($currentUser);
    $branchName = $branchInfo['name'];
    $userName = $user->name ?? 'Guest';
    
    return view('employee.profile', compact('user', 'branchName', 'userName'));
}

/**
 * Update employee profile
 */
public function employeeProfileUpdate(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    $user = User::findOrFail($id);
    // Profile Image Handling
    if ($request->hasFile('profile_img')) {
        $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
    }
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->phone = $request->input('phone');
    // Password Update Logic
    if ($request->filled('new_password')) {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        $user->password = Hash::make($request->new_password);
    }
    $user->save();
    return redirect()->route('employee.profile', $id)->with('success', 'Profile updated successfully!');
}

// Route for verifying old password
public function verifyOldPassword(Request $request)
{
    $request->validate([
        'old_password' => 'required|string',
    ]);

    if (Hash::check($request->old_password, auth()->user()->password)) {
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'message' => 'Old password is incorrect.']);
}

/**
 * Show the Elite Car Wash page.
 *
 * @return \Illuminate\Contracts\Support\Renderable
 */
public function carWash()
{
    $user = auth()->user();
    $userName = $user->name ?? 'Guest';
    
    // Get branch info using helper method
    $branchInfo = $this->getBranchInfoForDisplay($user);
    $branchId = $branchInfo['id'];
    $branchName = $branchInfo['name'];

    $svcQuery = CarWashService::query();
    $this->applyBranchFilter($svcQuery, 'branch_id', $user);
    $services = $svcQuery->where('status', true)->orderBy('created_at', 'desc')->get();

    $wrkQuery = \App\Models\User::where('role', 'worker');
    $this->applyBranchFilter($wrkQuery, 'branch_id', $user);
    $workers = $wrkQuery->orderBy('name', 'asc')->get(['id', 'name', 'commission', 'phone']);

    $jobsQuery = CarWashJob::query();
    $this->applyBranchFilter($jobsQuery, 'branch_id', $user);
    $activeJobs = $jobsQuery->active()
    ->with(['expense', 'customer', 'customerCar'])
    ->orderBy('start_time', 'asc')
    ->get()
    ->map(function($job) {
        $expenseTotal = $job->expense ? (float) ($job->expense->total_amount ?? 0) : 0;
        // Resolve customer name, vehicle, mobile from customers + customer_cars
        $customerName = null;
        $vehicleNo = null;
        $mobile = null;
        if ($job->relationLoaded('customer') && $job->customer) {
            $names = $job->customer->names ?? [];
            $customerName = is_array($names) && count($names) > 0 ? (string) ($names[0] ?? '') : null;
            $phones = $job->customer->phones ?? [];
            $mobile = is_array($phones) && count($phones) > 0 ? (string) ($phones[0] ?? '') : null;
        }
        if ($job->relationLoaded('customerCar') && $job->customerCar) {
            $vehicleNo = $job->customerCar->plate_number ? (string) $job->customerCar->plate_number : null;
        }
        return [
            'id' => $job->id,
            'serviceId' => $job->service_id,
            'workerId' => $job->worker_user_id ?? $job->worker_id,
            'customerId' => $job->customer_id,
            'customerCarId' => $job->customer_car_id,
            'customerName' => $customerName,
            'vehicleNo' => $vehicleNo,
            'mobile' => $mobile,
            'service' => $job->service_name ?? 'N/A',
            'serviceName' => $job->service_name ?? 'N/A',
            'price' => (float) $job->price,
            'worker' => $job->worker_name ?? '',
            'workerName' => $job->worker_name ?? '',
            'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
            'expenseTotalAmount' => $expenseTotal,
        ];
    });
    
    $doneQuery = CarWashJob::query();
    $this->applyBranchFilter($doneQuery, 'branch_id', $user);
    $completedJobs = $doneQuery->completed()
    ->whereDate('created_at', today())
    ->orderBy('end_time', 'desc')
    ->get();

    return view('car-wash', compact('branchName', 'userName', 'services', 'workers', 'activeJobs', 'completedJobs'));
}

/**
 * Show the Car Wash Home/Dashboard page.
 *
 * @return \Illuminate\Contracts\Support\Renderable
 */
public function carWashHome()
{
    $user = auth()->user();
    $userName = $user->name ?? 'Guest';
    
    // Get branch info using helper method
    $branchInfo = $this->getBranchInfoForDisplay($user);
    $branchId = $branchInfo['id'];
    $branchName = $branchInfo['name'];

    // Get today's stats
    // Today's completed jobs
    $todayCompletedQuery = CarWashJob::query();
    $this->applyBranchFilter($todayCompletedQuery, 'branch_id', $user);
    $todayCompletedJobs = $todayCompletedQuery
        ->whereDate('created_at', today())
        ->where('status', 'completed')
        ->get();
    
    $todayRevenue = $todayCompletedJobs->sum('price');
    $todayJobsCount = $todayCompletedJobs->count();
    
    // Active jobs count
    $activeJobsQuery = CarWashJob::query();
    $this->applyBranchFilter($activeJobsQuery, 'branch_id', $user);
    $activeJobsCount = $activeJobsQuery
        ->where('status', 'active')
        ->count();
    
    // Total workers (users with role=worker)
    $workersQuery = \App\Models\User::where('role', 'worker');
    $this->applyBranchFilter($workersQuery, 'branch_id', $user);
    $totalWorkers = $workersQuery->count();
    
    // Total services
    $servicesQuery = CarWashService::query();
    $this->applyBranchFilter($servicesQuery, 'branch_id', $user);
    $totalServices = $servicesQuery->where('status', true)->count();
    
    // Recent completed jobs (last 5)
    $recentJobsQuery = CarWashJob::query();
    $this->applyBranchFilter($recentJobsQuery, 'branch_id', $user);
    $recentJobs = $recentJobsQuery
        ->where('status', 'completed')
        ->orderBy('end_time', 'desc')
        ->limit(5)
        ->get();
    
    // Today's expenses (from shop expenses)
    $todayExpenses = \App\Models\CarWashShopExpense::query()
        ->whereDate('created_at', today())
        ->when($branchId, function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->sum('amount');
    
    $todayNetProfit = $todayRevenue - $todayExpenses;

    return view('car-wash-home', compact(
        'branchName', 
        'userName', 
        'todayRevenue', 
        'todayJobsCount', 
        'activeJobsCount',
        'totalWorkers',
        'totalServices',
        'recentJobs',
        'todayExpenses',
        'todayNetProfit'
    ));
}


/**
 * Show the Completed Jobs page.
 *
 * @return \Illuminate\Contracts\Support\Renderable
 */
public function completedJobs()
{
    $user = auth()->user();
    $userName = $user->name ?? 'Guest';
    
    // Get branch info using helper method
    $branchInfo = $this->getBranchInfoForDisplay($user);
    $branchId = $branchInfo['id'];
    $branchName = $branchInfo['name'];

    $query = CarWashJob::query();
    $this->applyBranchFilter($query, 'branch_id', $user);
    $completedJobs = $query->completed()
    ->with(['worker', 'workerUser'])
    ->orderBy('end_time', 'desc')
    ->get()
    ->map(function($job) {
        $workerCommission = 0;
        if ($job->worker_user_id && $job->workerUser) {
            $workerCommission = (float) ($job->workerUser->commission ?? 0);
        } elseif ($job->worker && $job->worker->commission) {
            $workerCommission = (float) $job->worker->commission;
        }
        $jobPrice = (float) ($job->price ?? 0);
        $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
        $totalJobPrice = $jobPrice + (float) $additionalPrices;
        $commissionAmount = $workerCommission > 0 ? ($totalJobPrice * $workerCommission) / 100 : 0;

        return [
            'id' => $job->id,
            'serviceId' => $job->service_id,
            'workerId' => $job->worker_user_id ?? $job->worker_id,
            'customerName' => $job->customer_name,
            'vehicleNo' => $job->vehicle_no,
            'mobile' => $job->mobile,
            'serviceName' => $job->service_name,
            'price' => (float) $job->price,
            'additionalPrices' => $job->additional_prices ?? [],
            'workerName' => $job->worker_name,
            'workerCommission' => $workerCommission,
            'commissionAmount' => round($commissionAmount, 2),
            'status' => $job->status,
            'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
            'endTime' => $job->end_time ? $job->end_time->toISOString() : null,
            'durationSeconds' => $job->duration_seconds,
            'notes' => $job->notes,
        ];
    });
    
    $currentUserId = $user->id;
    return view('car-wash-completed-jobs', compact('branchName', 'userName', 'completedJobs', 'currentUserId'));
}

/**
 * Show the Services Management page.
 *
 * @return \Illuminate\Contracts\Support\Renderable
 */
public function carWashServices()
{
    $user = auth()->user();
    $userName = $user->name ?? 'Guest';
    
    // Get branch info using helper method
    $branchInfo = $this->getBranchInfoForDisplay($user);
    $branchId = $branchInfo['id'];
    $branchName = $branchInfo['name'];

    $svcQuery = CarWashService::query();
    $this->applyBranchFilter($svcQuery, 'branch_id', $user);
    $services = $svcQuery->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->get()
    ->map(function($service) {
        return [
            'id' => $service->id,
            'label' => $service->label,
            'basePrice' => (float) $service->base_price,
            'additionalPrices' => $service->additional_prices ?? [],
            'icon' => $service->icon ?? null,
            'color' => $service->color ?? null,
            'colorValue' => $service->color_value ?? '#3b82f6',
            'isDefault' => $service->is_default ?? false,
            'status' => $service->status ?? true,
            'sortOrder' => (int) ($service->sort_order ?? 0),
        ];
    });
    
    return view('car-wash-services', compact('branchName', 'userName', 'services'));
}

    /**
     * Show the Staff Management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function carWashStaff()
    {
        $user = auth()->user();
        $userName = $user->name ?? 'Guest';
        
        // Get branch info using helper method
        $branchInfo = $this->getBranchInfoForDisplay($user);
        $branchId = $branchInfo['id'];
        $branchName = $branchInfo['name'];

    $wrkQuery = \App\Models\User::with(['workerCashAccount', 'bankAccount.bank'])->where('role', 'worker');
    $this->applyBranchFilter($wrkQuery, 'branch_id', $user);
    $paymentController = app(CarWashPaymentController::class);
    $workers = $wrkQuery->orderBy('name', 'asc')->get()
    ->map(function($workerUser) use ($user, $branchId, $paymentController) {
        $jobQuery = CarWashJob::query();
        $this->applyBranchFilter($jobQuery, 'branch_id', $user);
        $todayCompletedJobs = $jobQuery->where('worker_user_id', $workerUser->id)
            ->where('status', 'completed')
            ->where(function($query) {
                $query->whereDate('end_time', today())
                      ->orWhere(function($q) {
                          // If end_time is null, check created_at
                          $q->whereNull('end_time')
                            ->whereDate('created_at', today());
                      });
            })
            ->get();
            
            // Calculate daily commission
            $dailyCommission = 0;
            $dailyJobsCount = $todayCompletedJobs->count();
            $workerCommissionPercentage = (float) ($workerUser->commission ?? 0);
            foreach ($todayCompletedJobs as $job) {
                $jobPrice = (float) ($job->price ?? 0);
                $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                $totalJobPrice = $jobPrice + (float) $additionalPrices;
                if ($totalJobPrice > 0 && $workerCommissionPercentage > 0) {
                    $dailyCommission += ($totalJobPrice * $workerCommissionPercentage) / 100;
                }
            }
            $cashAccount = $workerUser->workerCashAccount;
            return [
                'id' => $workerUser->id,
                'name' => $workerUser->name,
                'mobile' => $workerUser->phone ?? null,
                'additional_mobiles' => [],
                'father_name' => null,
                'father_mobile' => null,
                'father_additional_mobiles' => [],
                'location' => $workerUser->current_location ?? null,
                'commission' => $workerUser->commission ?? 0,
                'id_card_front' => $workerUser->user_id_card_front,
                'id_card_back' => $workerUser->user_id_card_back,
                'father_card_front' => null,
                'father_card_back' => null,
                'status' => true,
                'bank_account_id' => $workerUser->bank_account_id,
                'bank_account' => $workerUser->bankAccount ? [
                    'id' => $workerUser->bankAccount->id,
                    'account_title' => $workerUser->bankAccount->account_title,
                    'account_number' => $workerUser->bankAccount->account_number,
                    'bank' => $workerUser->bankAccount->bank ? ['name' => $workerUser->bankAccount->bank->name] : null,
                ] : null,
                'has_cash_account' => (bool) $cashAccount,
                'cash_balance' => $cashAccount ? round((float) $cashAccount->balance, 2) : 0,
                'total_earned' => $cashAccount ? round((float) $cashAccount->total_earned, 2) : 0,
                'total_paid' => $cashAccount ? round((float) $cashAccount->total_paid, 2) : 0,
                'payment_status' => $cashAccount ? (
                    (float) $cashAccount->balance <= 0 ? 'paid' : (
                        (float) $cashAccount->total_paid > 0 ? 'partial' : 'unpaid'
                    )
                ) : 'unpaid',
                'bank_name' => $workerUser->bank_name,
                'bank_account_title' => $workerUser->bank_account_title,
                'bank_account_number' => $workerUser->bank_account_number,
                'bank_iban' => $workerUser->bank_iban,
                'daily_jobs_count' => $dailyJobsCount,
                'daily_commission' => round($dailyCommission, 2),
                'pending_commission' => round($paymentController->calculatePendingCommissionForUserWorker($workerUser->id, $branchId), 2),
            ];
        });

        $paymentMethods = PaymentMethod::active()->get(['id', 'name', 'code', 'requires_bank_account']);
        $bankAccounts = BankAccount::where('status', true)->with('bank:id,name')->get(['id', 'bank_id', 'account_title', 'account_number']);
        $userCashBalance = 0;
        try {
            $userCashBalance = (float) app(\App\Services\CashAccountService::class)->getBalance($user->id);
        } catch (\Exception $e) {
            // ignore
        }

        return view('car-wash-staff', compact('branchName', 'userName', 'workers', 'paymentMethods', 'bankAccounts', 'userCashBalance'));
    }

    /**
     * Show the Attendance Management System.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function attendance()
    {
        return view('attendance');
    }
}
