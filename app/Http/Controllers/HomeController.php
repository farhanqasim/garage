<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CarWashService;
use App\Models\CarWashWorker;
use App\Models\CarWashJob;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Http\Controllers\CarWashPaymentController;
use Illuminate\Support\Facades\Hash;
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
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function userprofile($id){
      $user = User::find($id);
      return view('admin.pages.profile',compact('user'));
     }



public function userprofileupdate(Request $request, $id)
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
    // return $user;
    $user->save();
    return redirect()->back()->with('success', 'Profile updated successfully!');
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

    $wrkQuery = CarWashWorker::query();
    $this->applyBranchFilter($wrkQuery, 'branch_id', $user);
    $workers = $wrkQuery->where('status', true)->orderBy('name', 'asc')->get();

    $jobsQuery = CarWashJob::query();
    $this->applyBranchFilter($jobsQuery, 'branch_id', $user);
    $activeJobs = $jobsQuery->active()
    ->with('expense')
    ->orderBy('start_time', 'asc')
    ->get()
    ->map(function($job) {
        $expenseTotal = $job->expense ? (float) ($job->expense->total_amount ?? 0) : 0;
        return [
            'id' => $job->id,
            'serviceId' => $job->service_id,
            'workerId' => $job->worker_id,
            'customerName' => $job->customer_name ?? 'N/A',
            'vehicleNo' => $job->vehicle_no ?? 'N/A',
            'mobile' => $job->mobile ?? '',
            'service' => $job->service_name ?? 'N/A',
            'price' => (float) $job->price,
            'worker' => $job->worker_name ?? '',
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
    ->with('worker')
    ->orderBy('end_time', 'desc')
    ->get()
    ->map(function($job) {
        // Get worker commission percentage
        $workerCommission = 0;
        $commissionAmount = 0;
        if ($job->worker && $job->worker->commission) {
            $workerCommission = (float) $job->worker->commission;
            // Calculate commission amount: (price * commission_percentage) / 100
            $commissionAmount = (($job->price ?? 0) * $workerCommission) / 100;
        }
        
        return [
            'id' => $job->id,
            'serviceId' => $job->service_id,
            'workerId' => $job->worker_id,
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
    
    return view('car-wash-completed-jobs', compact('branchName', 'userName', 'completedJobs'));
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

    $wrkQuery = CarWashWorker::with(['bankAccount.bank', 'workerCashAccount']);
    $this->applyBranchFilter($wrkQuery, 'branch_id', $user);
    $paymentController = app(CarWashPaymentController::class);
    $workers = $wrkQuery->orderBy('name', 'asc')->get()
    ->map(function($worker) use ($user, $branchId, $paymentController) {
        $jobQuery = CarWashJob::query();
        $this->applyBranchFilter($jobQuery, 'branch_id', $user);
        $todayCompletedJobs = $jobQuery->where('worker_id', $worker->id)
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
            $workerCommissionPercentage = (float) ($worker->commission ?? 0);
            
            foreach ($todayCompletedJobs as $job) {
                // Calculate total job price including additional prices
                $jobPrice = (float) ($job->price ?? 0);
                $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                $totalJobPrice = $jobPrice + (float) $additionalPrices;
                
                // Calculate commission on total price
                if ($totalJobPrice > 0 && $workerCommissionPercentage > 0) {
                    $commissionAmount = ($totalJobPrice * $workerCommissionPercentage) / 100;
                    $dailyCommission += $commissionAmount;
                }
            }
            
            return [
                'id' => $worker->id,
                'name' => $worker->name,
                'mobile' => $worker->mobile,
                'additional_mobiles' => $worker->additional_mobiles ?? [],
                'father_name' => $worker->father_name,
                'father_mobile' => $worker->father_mobile,
                'father_additional_mobiles' => $worker->father_additional_mobiles ?? [],
                'location' => $worker->location,
                'commission' => $worker->commission,
                'id_card_front' => $worker->id_card_front,
                'id_card_back' => $worker->id_card_back,
                'father_card_front' => $worker->father_card_front,
                'father_card_back' => $worker->father_card_back,
                'status' => $worker->status,
                'bank_account_id' => $worker->bank_account_id,
                'bank_account' => $worker->bankAccount ? [
                    'id' => $worker->bankAccount->id,
                    'account_title' => $worker->bankAccount->account_title,
                    'account_number' => $worker->bankAccount->account_number,
                    'bank' => $worker->bankAccount->bank ? ['name' => $worker->bankAccount->bank->name] : null,
                ] : null,
                'has_cash_account' => (bool) $worker->workerCashAccount,
                'cash_balance' => $worker->workerCashAccount ? round((float) $worker->workerCashAccount->balance, 2) : 0,
                'total_earned' => $worker->workerCashAccount ? round((float) $worker->workerCashAccount->total_earned, 2) : 0,
                'total_paid' => $worker->workerCashAccount ? round((float) $worker->workerCashAccount->total_paid, 2) : 0,
                'payment_status' => $worker->workerCashAccount ? (
                    (float) $worker->workerCashAccount->balance <= 0 ? 'paid' : (
                        (float) $worker->workerCashAccount->total_paid > 0 ? 'partial' : 'unpaid'
                    )
                ) : 'unpaid',
                'bank_name' => $worker->bank_name,
                'bank_account_title' => $worker->bank_account_title,
                'bank_account_number' => $worker->bank_account_number,
                'bank_iban' => $worker->bank_iban,
                'daily_jobs_count' => $dailyJobsCount,
                'daily_commission' => round($dailyCommission, 2),
                'pending_commission' => round($paymentController->calculatePendingCommission($worker->id, $branchId), 2),
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
