<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CarWashWorker;
use App\Models\CarWashJob;
use App\Http\Controllers\CarWashPaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashWorkerController extends Controller
{
    use HasBranchAccess;
    
    /**
     * Get all workers for the current user's branch.
     * Workers = users with role 'worker' (users table).
     */
    public function index()
    {
        $user = Auth::user();
        $workersQuery = User::with(['workerCashAccount', 'bankAccount.bank'])
            ->where('role', 'worker');
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $branchId = $this->getUserBranchId($user);
        $paymentController = app(CarWashPaymentController::class);
        $workers = $workersQuery->orderBy('name', 'asc')->get()
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
                'additionalMobiles' => [],
                'father_name' => null,
                'fatherName' => null,
                'father_mobile' => null,
                'fatherMobile' => null,
                'father_additional_mobiles' => [],
                'fatherAdditionalMobiles' => [],
                'location' => $workerUser->current_location ?? null,
                'commission' => $workerUser->commission ?? 0,
                'id_card_front' => $workerUser->user_id_card_front ? asset($workerUser->user_id_card_front) : null,
                'idCardFront' => $workerUser->user_id_card_front ? asset($workerUser->user_id_card_front) : null,
                'id_card_back' => $workerUser->user_id_card_back ? asset($workerUser->user_id_card_back) : null,
                'idCardBack' => $workerUser->user_id_card_back ? asset($workerUser->user_id_card_back) : null,
                'father_card_front' => null,
                'fatherCardFront' => null,
                'father_card_back' => null,
                'fatherCardBack' => null,
                'status' => true,
                'bank_account_id' => $workerUser->bank_account_id,
                'bank_name' => $workerUser->bank_name,
                'bank_account_title' => $workerUser->bank_account_title,
                'bank_account_number' => $workerUser->bank_account_number,
                'bank_iban' => $workerUser->bank_iban,
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
                'completedJobs' => CarWashJob::where('worker_user_id', $workerUser->id)->where('status', 'completed')->count(),
                'totalEarnings' => CarWashJob::where('worker_user_id', $workerUser->id)->where('status', 'completed')->get()->sum(fn ($j) => (float) $j->price + (is_array($j->additional_prices) ? array_sum(array_column($j->additional_prices, 'price')) : 0)),
                'daily_jobs_count' => $dailyJobsCount,
                'dailyJobsCount' => $dailyJobsCount,
                'daily_commission' => round($dailyCommission, 2),
                'dailyCommission' => round($dailyCommission, 2),
                'pending_commission' => round($paymentController->calculatePendingCommissionForUserWorker($workerUser->id, $branchId), 2),
                'pendingCommission' => round($paymentController->calculatePendingCommissionForUserWorker($workerUser->id, $branchId), 2),
            ];
        });
        
        return response()->json([
            'success' => true,
            'workers' => $workers
        ]);
    }

    /**
     * Store a new worker (create User with role=worker).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'commission' => 'nullable|integer|min:0|max:100',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_title' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_iban' => 'nullable|string|max:100',
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $authUser = Auth::user();
        $branchId = $this->getUserBranchId($authUser);

        $profileImg = $request->hasFile('profile_img') ? saveSingleFile($request->file('profile_img'), 'workers/profiles') : null;
        $idCardFront = $request->hasFile('user_id_card_front') ? saveSingleFile($request->file('user_id_card_front'), 'workers/id_cards') : null;
        $idCardBack = $request->hasFile('user_id_card_back') ? saveSingleFile($request->file('user_id_card_back'), 'workers/id_cards') : null;

        $email = $request->filled('email') ? $request->email : ('worker_' . $branchId . '_' . Str::random(6) . '@carwash.local');
        $password = $request->filled('password') ? Hash::make($request->password) : Hash::make(Str::random(12));

        $workerUser = User::create([
            'name' => strtoupper(trim($request->name)),
            'email' => $email,
            'password' => $password,
            'role' => 'worker',
            'branch_id' => $branchId,
            'phone' => $request->mobile ? trim($request->mobile) : null,
            'profile_img' => $profileImg,
            'user_id_card_front' => $idCardFront,
            'user_id_card_back' => $idCardBack,
            'commission' => $request->commission ?? 0,
            'bank_account_id' => $request->bank_account_id ?: null,
            'bank_name' => $request->bank_name ? trim($request->bank_name) : null,
            'bank_account_title' => $request->bank_account_title ? trim($request->bank_account_title) : null,
            'bank_account_number' => $request->bank_account_number ? trim($request->bank_account_number) : null,
            'bank_iban' => $request->bank_iban ? trim($request->bank_iban) : null,
        ]);

        \App\Models\WorkerCashAccount::firstOrCreate(
            ['user_id' => $workerUser->id],
            ['balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker created successfully',
                'worker' => [
                    'id' => $workerUser->id,
                    'name' => $workerUser->name,
                    'mobile' => $workerUser->phone,
                    'commission' => $workerUser->commission,
                ]
            ]);
        }
        return redirect()->route('car.wash')->with('success', 'Worker created successfully!');
    }

    /**
     * Update a worker (User with role=worker).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'commission' => 'nullable|integer|min:0|max:100',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_title' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_iban' => 'nullable|string|max:100',
        ]);

        $workerUser = User::where('role', 'worker')->findOrFail($id);
        $authUser = Auth::user();
        if (!$this->canAccessResourceBranch($workerUser, $authUser)) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update this worker'], 403);
        }

        $workerUser->update([
            'name' => strtoupper(trim($request->name)),
            'phone' => $request->filled('mobile') ? trim($request->mobile) : null,
            'commission' => $request->commission ?? 0,
            'bank_account_id' => $request->has('bank_account_id') ? ($request->bank_account_id ?: null) : $workerUser->bank_account_id,
            'bank_name' => $request->has('bank_name') ? ($request->bank_name ? trim($request->bank_name) : null) : $workerUser->bank_name,
            'bank_account_title' => $request->has('bank_account_title') ? ($request->bank_account_title ? trim($request->bank_account_title) : null) : $workerUser->bank_account_title,
            'bank_account_number' => $request->has('bank_account_number') ? ($request->bank_account_number ? trim($request->bank_account_number) : null) : $workerUser->bank_account_number,
            'bank_iban' => $request->has('bank_iban') ? ($request->bank_iban ? trim($request->bank_iban) : null) : $workerUser->bank_iban,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker updated successfully',
                'worker' => [
                    'id' => $workerUser->id,
                    'name' => $workerUser->name,
                    'mobile' => $workerUser->phone,
                    'commission' => $workerUser->commission,
                ]
            ]);
        }
        return redirect()->route('car.wash')->with('success', 'Worker updated successfully!');
    }

    /**
     * Update worker bank account details (User with role=worker).
     */
    public function updateBankDetails(Request $request, $id)
    {
        $request->validate([
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_title' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_iban' => 'nullable|string|max:100',
        ]);

        $workerUser = User::where('role', 'worker')->findOrFail($id);
        $authUser = Auth::user();
        if (!$this->canAccessResourceBranch($workerUser, $authUser)) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update this worker'], 403);
        }

        $workerUser->update([
            'bank_account_id' => $request->bank_account_id ?: null,
            'bank_name' => $request->bank_name ? trim($request->bank_name) : null,
            'bank_account_title' => $request->bank_account_title ? trim($request->bank_account_title) : null,
            'bank_account_number' => $request->bank_account_number ? trim($request->bank_account_number) : null,
            'bank_iban' => $request->bank_iban ? trim($request->bank_iban) : null,
        ]);

        $workerUser->load('bankAccount.bank');
        return response()->json([
            'success' => true,
            'message' => 'Worker bank account saved',
            'worker' => [
                'id' => $workerUser->id,
                'bank_account_id' => $workerUser->bank_account_id,
                'bank_name' => $workerUser->bank_name,
                'bank_account_title' => $workerUser->bank_account_title,
                'bank_account_number' => $workerUser->bank_account_number,
                'bank_iban' => $workerUser->bank_iban,
                'bank_account' => $workerUser->bankAccount ? [
                    'id' => $workerUser->bankAccount->id,
                    'account_title' => $workerUser->bankAccount->account_title,
                    'account_number' => $workerUser->bankAccount->account_number,
                    'bank' => $workerUser->bankAccount->bank ? ['name' => $workerUser->bankAccount->bank->name] : null,
                ] : null,
            ]
        ]);
    }

    /**
     * Create worker cash account for User (role=worker). Commission paid by cash is credited here.
     */
    public function createCashAccount($id)
    {
        $workerUser = User::where('role', 'worker')->findOrFail($id);
        $authUser = Auth::user();
        if (!$this->canAccessResourceBranch($workerUser, $authUser)) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update this worker'], 403);
        }

        $cashAccount = \App\Models\WorkerCashAccount::firstOrCreate(
            ['user_id' => $workerUser->id],
            ['balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
        );

        return response()->json([
            'success' => true,
            'message' => 'Worker cash account created',
            'worker_cash_account' => [
                'id' => $cashAccount->id,
                'user_id' => $cashAccount->user_id,
                'balance' => (float) $cashAccount->balance,
            ]
        ]);
    }

    /**
     * Delete a worker (User with role=worker). Jobs keep worker_user_id null on delete.
     */
    public function destroy(Request $request, $id)
    {
        $workerUser = User::where('role', 'worker')->findOrFail($id);
        $authUser = Auth::user();
        if (!$this->canAccessResourceBranch($workerUser, $authUser)) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to delete this worker'], 403);
        }

        \App\Models\WorkerCashAccount::where('user_id', $workerUser->id)->delete();
        $workerUser->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Worker deleted successfully']);
        }
        return redirect()->route('car.wash')->with('success', 'Worker deleted successfully!');
    }

    /**
     * Helper function to save base64 image
     */
    private function saveBase64Image($base64Image, $path)
    {
        if (!$base64Image || !str_starts_with($base64Image, 'data:image')) {
            return null;
        }

        // Extract the image data
        $imageData = explode(',', $base64Image);
        if (count($imageData) < 2) {
            return null;
        }

        // Get the image extension
        preg_match('/data:image\/(\w+);base64/', $base64Image, $matches);
        $extension = $matches[1] ?? 'png';

        // Decode the image
        $image = base64_decode($imageData[1]);
        
        // Generate unique filename
        $filename = $path . '/' . uniqid() . '_' . time() . '.' . $extension;
        
        // Save the image
        Storage::disk('public')->put($filename, $image);
        
        return $filename;
    }
}
