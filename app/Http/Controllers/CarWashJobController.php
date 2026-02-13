<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CarWashJob;
use App\Models\CarWashWorker;
use App\Models\WorkerCashAccount;
use App\Models\WorkerCashTransaction;
use App\Models\CashTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashJobController extends Controller
{
    use HasBranchAccess;
    
    /**
     * Get all jobs for the current user's branch
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter for today only
        if ($request->has('today') && $request->today) {
            $query->whereDate('created_at', today());
        }

        $jobs = $query->with('worker')->orderBy('created_at', 'desc')
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
                    'createdAt' => $job->created_at->toISOString(),
                ];
            });
        
        return response()->json([
            'success' => true,
            'jobs' => $jobs
        ]);
    }

    /**
     * Get active jobs
     */
    public function activeJobs()
    {
        $user = Auth::user();
        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $jobs = $query->active()
        ->with('expense')
        ->orderBy('start_time', 'asc')
        ->get()
        ->map(function($job) {
            $expenseTotal = $job->expense ? (float) ($job->expense->total_amount ?? 0) : 0;
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
                'status' => $job->status,
                'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                'expenseTotalAmount' => $expenseTotal,
            ];
        });
        
        return response()->json([
            'success' => true,
            'jobs' => $jobs
        ]);
    }

    /**
     * Search past jobs by vehicle plate number (for autocomplete).
     * Returns unique vehicle_no with latest customer_name and mobile per plate.
     */
    public function searchByPlate(Request $request)
    {
        $q = $request->get('q', '');
        $q = trim(strtoupper((string) $q));
        if (strlen($q) < 1) {
            return response()->json(['success' => true, 'suggestions' => []]);
        }

        $user = Auth::user();
        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);

        $jobs = $query
            ->whereNotNull('vehicle_no')
            ->where('vehicle_no', 'like', $q . '%')
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'vehicle_no', 'customer_name', 'mobile', 'end_time', 'created_at']);

        // Dedupe by vehicle_no, keep first (most recent) for customer_name/mobile
        $seen = [];
        $suggestions = [];
        foreach ($jobs as $job) {
            $v = $job->vehicle_no ? trim(strtoupper($job->vehicle_no)) : '';
            if ($v === '' || isset($seen[$v])) {
                continue;
            }
            $seen[$v] = true;
            $suggestions[] = [
                'vehicleNo' => $job->vehicle_no,
                'customerName' => $job->customer_name ?? '',
                'mobile' => $job->mobile ?? '',
            ];
        }

        return response()->json(['success' => true, 'suggestions' => $suggestions]);
    }

    /**
     * Last 2 months job history for a vehicle (by vehicle_no).
     */
    public function vehicleHistory(Request $request)
    {
        $vehicleNo = $request->get('vehicle_no', '');
        $vehicleNo = trim(strtoupper((string) $vehicleNo));
        if ($vehicleNo === '') {
            return response()->json(['success' => false, 'message' => 'Vehicle number required', 'jobs' => []]);
        }

        $user = Auth::user();
        $twoMonthsAgo = Carbon::now()->subMonths(2)->startOfDay();

        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $jobs = $query
            ->where('vehicle_no', $vehicleNo)
            ->where(DB::raw('COALESCE(end_time, created_at)'), '>=', $twoMonthsAgo)
            ->with('worker')
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $list = $jobs->map(function ($job) {
            $workerCommission = 0;
            $commissionAmount = 0;
            if ($job->worker && $job->worker->commission) {
                $workerCommission = (float) $job->worker->commission;
                $commissionAmount = (($job->price ?? 0) * $workerCommission) / 100;
            }
            return [
                'id' => $job->id,
                'vehicleNo' => $job->vehicle_no,
                'customerName' => $job->customer_name,
                'mobile' => $job->mobile,
                'serviceName' => $job->service_name,
                'price' => (float) $job->price,
                'workerName' => $job->worker_name,
                'commissionAmount' => round($commissionAmount, 2),
                'status' => $job->status,
                'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                'endTime' => $job->end_time ? $job->end_time->toISOString() : null,
                'createdAt' => $job->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'vehicleNo' => $vehicleNo,
            'jobs' => $list,
        ]);
    }

    /**
     * Show job detail page
     */
    public function show($id)
    {
        $user = Auth::user();
        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $job = $query->with(['worker', 'inspection', 'expense'])->findOrFail($id);

        // Calculate commission
        $workerCommission = 0;
        $commissionAmount = 0;
        if ($job->worker && $job->worker->commission) {
            $workerCommission = (float) $job->worker->commission;
            $commissionAmount = (($job->price ?? 0) * $workerCommission) / 100;
        }
        
        // Format inspection data
        $inspectionData = null;
        if ($job->inspection) {
            $inspectionData = [
                'id' => $job->inspection->id,
                'inspectionItems' => $job->inspection->inspection_items ?? [],
                'isCompleted' => $job->inspection->is_completed ?? false,
                'completedAt' => $job->inspection->completed_at ? $job->inspection->completed_at->toISOString() : null,
            ];
        }
        
        // Format expense data
        $expenseData = null;
        if ($job->expense) {
            $expenseData = [
                'id' => $job->expense->id,
                'expenseItems' => $job->expense->expense_items ?? [],
                'totalAmount' => (float) ($job->expense->total_amount ?? 0),
            ];
        }
        
        $jobData = [
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
            'inspection' => $inspectionData,
            'expense' => $expenseData,
        ];
        
        $userName = $user->name ?? 'Guest';
        $branchId = $this->getUserBranchId($user);
        $branch = $branchId ? \App\Models\Branch::find($branchId) : null;
        $branchName = ($user->role === 'admin' && !$branchId) ? 'All Branches' : ($branch ? $branch->branch_name : 'Guest');

        return view('car-wash-job-detail', compact('jobData', 'userName', 'branchName'));
    }

    /**
     * Get completed jobs (today or all)
     */
    public function completedJobs(Request $request)
    {
        $user = Auth::user();
        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $query->completed();

        if ($request->has('today') && $request->today) {
            $query->today();
        }

        $jobs = $query->with(['worker', 'worker.workerCashAccount'])->orderBy('end_time', 'desc')
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
                // Worker cash account balance and total paid
                $workerBalance = null;
                $workerTotalPaid = null;
                $workerTotalEarned = null;
                if ($job->worker && $job->worker->relationLoaded('workerCashAccount') && $job->worker->workerCashAccount) {
                    $acc = $job->worker->workerCashAccount;
                    $workerBalance = (float) $acc->balance;
                    $workerTotalPaid = (float) ($acc->total_paid ?? 0);
                    $workerTotalEarned = (float) ($acc->total_earned ?? 0);
                } elseif ($job->worker_id) {
                    $cashAccount = WorkerCashAccount::where('worker_id', $job->worker_id)->first();
                    if ($cashAccount) {
                        $workerBalance = (float) $cashAccount->balance;
                        $workerTotalPaid = (float) ($cashAccount->total_paid ?? 0);
                        $workerTotalEarned = (float) ($cashAccount->total_earned ?? 0);
                    }
                }

                // Commission paid for this job: sum of completed (non-reversed) payments linked to this job; 0 if reversed
                $commissionPaid = (float) \App\Models\CarWashPayment::where('car_wash_job_id', $job->id)
                    ->where('status', 'completed')
                    ->sum('amount');

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
                    'commissionPaid' => round($commissionPaid, 2),
                    'workerBalance' => $workerBalance !== null ? round($workerBalance, 2) : null,
                    'workerTotalPaid' => $workerTotalPaid !== null ? round($workerTotalPaid, 2) : null,
                    'workerTotalEarned' => $workerTotalEarned !== null ? round($workerTotalEarned, 2) : null,
                    'status' => $job->status,
                    'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                    'endTime' => $job->end_time ? $job->end_time->toISOString() : null,
                    'durationSeconds' => $job->duration_seconds,
                ];
            });
        
        return response()->json([
            'success' => true,
            'jobs' => $jobs
        ]);
    }

    /**
     * Start a new job
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'nullable|exists:car_wash_services,id',
            'worker_id' => 'required|exists:car_wash_workers,id',
            'customer_name' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'service_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'additional_prices' => 'nullable|array',
            'worker_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $job = CarWashJob::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'service_id' => $request->service_id,
            'worker_id' => $request->worker_id,
            'customer_name' => $request->customer_name ? strtoupper($request->customer_name) : null,
            'vehicle_no' => $request->vehicle_no ? strtoupper($request->vehicle_no) : null,
            'mobile' => $request->mobile,
            'service_name' => strtoupper($request->service_name),
            'price' => $request->price,
            'additional_prices' => $request->additional_prices ?? [],
            'worker_name' => $request->worker_name ? strtoupper($request->worker_name) : null,
            'status' => 'active',
            'start_time' => now(),
        ]);

        // Handle inspection and expense if provided
        if ($request->has('inspection_notes')) {
            \App\Models\CarWashInspection::updateOrCreate(
                ['job_id' => $job->id],
                ['notes' => $request->inspection_notes]
            );
        }

        if ($request->has('expense_amount') && $request->expense_amount > 0) {
            \App\Models\CarWashExpense::updateOrCreate(
                ['job_id' => $job->id],
                [
                    'amount' => $request->expense_amount,
                    'description' => $request->expense_description ?? ''
                ]
            );
        }

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Job started successfully',
                'job' => [
                    'id' => $job->id,
                    'serviceId' => $job->service_id,
                    'workerId' => $job->worker_id,
                    'customerName' => $job->customer_name,
                    'vehicleNo' => $job->vehicle_no,
                    'mobile' => $job->mobile,
                    'serviceName' => $job->service_name,
                    'workerName' => $job->worker_name,
                    'price' => (float) $job->price,
                    'additionalPrices' => $job->additional_prices ?? [],
                    'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                    'workerName' => $job->worker_name,
                    'status' => $job->status,
                    'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                ]
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Job created successfully!');
    }

    /**
     * Complete a job
     */
    public function complete(Request $request, $id)
    {
        $job = CarWashJob::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to complete this job'
            ], 403);
        }

        $branchId = $this->getUserBranchId($user);
        $endTime = now();
        $durationSeconds = $job->start_time ? $endTime->diffInSeconds($job->start_time) : 0;

        $updateData = [
            'status' => 'completed',
            'end_time' => $endTime,
            'duration_seconds' => $durationSeconds,
        ];

        // Update notes if provided (rating and comment)
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        // Store payment method: cash or bank (from complete modal)
        $pm = $request->get('payment_method');
        if (in_array($pm, ['cash', 'bank'], true)) {
            $updateData['payment_method'] = $pm;
        } else {
            $updateData['payment_method'] = 'cash';
        }

        // Store bank account when payment is bank (branch ke ya branch_id null). Admin: any branch.
        if ($pm === 'bank' && $request->filled('bank_account_id')) {
            $accountQuery = BankAccount::where('id', $request->bank_account_id)->where('account_type', 'bank');
            if ($user->role !== 'admin') {
                $accountQuery->where(function ($q) use ($branchId) {
                    if ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    } else {
                        $q->whereNull('branch_id');
                    }
                });
            }
            $account = $accountQuery->first();
            if ($account) {
                $updateData['bank_account_id'] = $account->id;
                $updateData['bank_id'] = $account->bank_id;
            } else {
                $updateData['bank_account_id'] = null;
                $updateData['bank_id'] = null;
            }
        } elseif ($pm === 'bank' && $request->filled('bank_id')) {
            // Backward compat: still accept bank_id if sent from old clients
            $updateData['bank_id'] = $request->bank_id;
            $updateData['bank_account_id'] = null;
        } else {
            $updateData['bank_account_id'] = null;
            $updateData['bank_id'] = null;
        }

        DB::beginTransaction();
        try {
            $job->update($updateData);

            // Commission percentage ky hesab say worker ke cash account main auto credit
            $worker = $job->worker;
            if ($worker && ($worker->commission ?? 0) > 0) {
                $jobPrice = (float) ($job->price ?? 0);
                $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                $totalJobPrice = $jobPrice + (float) $additionalPrices;
                $commissionPct = (float) $worker->commission;
                $commissionAmount = ($totalJobPrice * $commissionPct) / 100;
                if ($commissionAmount > 0) {
                    $workerCash = WorkerCashAccount::firstOrCreate(
                        ['worker_id' => $worker->id],
                        ['balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
                    );
                    $workerCash->balance += $commissionAmount;
                    $workerCash->total_earned = (float) $workerCash->total_earned + $commissionAmount;
                    $workerCash->save();
                    WorkerCashTransaction::create([
                        'worker_id' => $worker->id,
                        'amount' => $commissionAmount,
                        'type' => 'credit',
                        'reference_type' => 'car_wash_jobs',
                        'reference_id' => $job->id,
                        'note' => "Commission {$commissionPct}% on job #{$job->id} - Rs " . number_format($totalJobPrice, 2),
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Job completed successfully',
            'job' => [
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
                'status' => $job->status,
                'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                'endTime' => $job->end_time->toISOString(),
                'durationSeconds' => $job->duration_seconds,
                'notes' => $job->notes,
            ]
        ]);
    }

    /**
     * List bank accounts for the authenticated user ONLY.
     * Shows only the logged-in user's bank accounts (where user_id matches authenticated user).
     * Har item bank account detail (bank, account title, number) ke sath.
     */
    public function bankAccountsIndex(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
                'bankAccounts' => []
            ], 401);
        }

        $query = BankAccount::with('bank')
            ->where('account_type', 'bank')
            ->where(function ($q) {
                $q->where('status', true)->orWhereNull('status');
            });
        
        // STRICT FILTER: Only show accounts belonging to the authenticated user
        // This ensures only the logged-in user's bank accounts are returned
        $query->where('user_id', $user->id);

        // Get all accounts for the authenticated user (for total balance calculation)
        $allAccounts = $query->orderBy('is_primary', 'desc') // Primary accounts first
            ->orderBy('account_title')
            ->get();

        // Determine which account to display in the UI
        // If multiple accounts exist, show only the primary account
        // If single account exists, show that one
        // If no primary account is set but multiple accounts exist, show the first one
        $displayAccountId = null;
        if ($allAccounts->count() > 1) {
            // Multiple accounts: find primary account
            $primaryAccount = $allAccounts->firstWhere('is_primary', true);
            
            // If no primary account is set, use the first account as fallback
            $selectedAccount = $primaryAccount ?: $allAccounts->first();
            $displayAccountId = $selectedAccount->id;
        } else if ($allAccounts->count() === 1) {
            // Single account: show that one
            $displayAccountId = $allAccounts->first()->id;
        }

        // Format all accounts with balance information
        // Mark which account should be displayed in the UI
        $formattedAccounts = $allAccounts->map(function ($a) use ($displayAccountId) {
            $bankName = $a->bank ? $a->bank->name : 'N/A';
            $title = $a->account_title ?? '';
            $num = $a->account_number ?? '';
            // Build account-level detail: Bank - Title (Number). If title+number empty, use "Bank — Account #id".
            $label = trim($bankName . ($title ? ' - ' . $title : '') . ($num ? ' (' . $num . ')' : ''));
            if ($label === '' || (!$title && !$num)) {
                $label = $bankName . ' — Account #' . $a->id;
            }
            
            // Calculate balance: opening_balance + credits - debits (including all transactions, not just reconciled)
            $openingBalance = (float) ($a->opening_balance ?? 0);
            
            // Get all credits (not just reconciled)
            $credits = $a->bankTransactions()
                ->where('type', 'credit')
                ->sum('amount');
            
            // Get all debits (not just reconciled)
            $debits = $a->bankTransactions()
                ->where('type', 'debit')
                ->sum('amount');
            
            $balance = $openingBalance + (float) $credits - (float) $debits;
            
            return [
                'id' => $a->id,
                'bank_id' => $a->bank_id,
                'bankName' => $bankName,
                'accountTitle' => $title,
                'accountNumber' => $num,
                'displayLabel' => $label,
                'balance' => $balance, // Calculated balance (opening + credits - debits)
                'isDisplayAccount' => ($a->id === $displayAccountId), // Mark which account to display
            ];
        });

        return response()->json(['success' => true, 'bankAccounts' => $formattedAccounts->values()->all()]);
    }

    /**
     * Get bank accounts of other users from same branch (for transfer)
     * Excludes logged-in user's accounts
     */
    public function bankAccountsForTransfer(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch found for user',
                'bankAccounts' => []
            ], 400);
        }

        // Get all users from same branch (including admin accounts) — show all users' bank accounts including own
        $branchUserIds = \App\Models\User::where(function($query) use ($branchId) {
            $query->where('branch_id', $branchId)
            ->orWhereHas('assignedBranches', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        })
        ->pluck('id');
        
        // Also include admin users (role = 'admin')
        $adminUserIds = \App\Models\User::where('role', 'admin')->pluck('id');
        $allUserIds = $branchUserIds->merge($adminUserIds)->unique();

        $query = BankAccount::with(['bank', 'user:id,name'])
            ->whereIn('account_type', ['bank', 'cash'])
            ->where(function ($q) {
                $q->where('status', true)->orWhereNull('status');
            })
            ->whereIn('user_id', $allUserIds);

        $accounts = $query->orderBy('account_title')
            ->get()
            ->map(function ($a) use ($user) {
                $bankName = $a->bank ? $a->bank->name : 'N/A';
                $title = $a->account_title ?? '';
                $num = $a->account_number ?? '';
                $label = trim($bankName . ($title ? ' - ' . $title : '') . ($num ? ' (' . $num . ')' : ''));
                if ($label === '' || (!$title && !$num)) {
                    $label = $bankName . ' — Account #' . $a->id;
                }
                $ownerName = $a->user ? $a->user->name : '';
                $isOwn = ($a->user_id == $user->id);
                $bankLogo = $a->bank && !empty($a->bank->logo) ? $a->bank->logo : null;
                return [
                    'id' => $a->id,
                    'bank_id' => $a->bank_id,
                    'bankName' => $bankName,
                    'accountTitle' => $title,
                    'accountNumber' => $num,
                    'displayLabel' => $label,
                    'balance' => (float) $a->current_balance,
                    'userName' => $ownerName,
                    'isOwn' => $isOwn,
                    'bankLogo' => $bankLogo,
                ];
            });

        return response()->json(['success' => true, 'bankAccounts' => $accounts]);
    }

    /**
     * Get ledger (debit, credit, total) for a bank account belonging to the logged-in user.
     * Query params: date_from, date_to (Y-m-d). Default: today.
     */
    public function bankAccountLedger(Request $request, $id)
    {
        $user = Auth::user();
        $account = BankAccount::with('bank')
            ->where('id', $id)
            ->where('account_type', 'bank')
            ->where('user_id', $user->id)
            ->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found or access denied'], 404);
        }

        $today = Carbon::today()->format('Y-m-d');
        $dateFrom = $request->get('date_from') ?: $today;
        $dateTo = $request->get('date_to') ?: $today;

        $openingBalance = (float) ($account->opening_balance ?? 0);

        // Opening balance = sum of all transactions before date_from
        $prevTransactions = BankTransaction::where('bank_account_id', $account->id)
            ->where('transaction_date', '<', $dateFrom)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($prevTransactions as $tx) {
            $amount = (float) $tx->amount;
            $openingBalance += ($tx->type === 'credit' ? $amount : -$amount);
        }

        $transactions = BankTransaction::where('bank_account_id', $account->id)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $running = $openingBalance;
        $rows = [];

            $dateFromFormatted = Carbon::parse($dateFrom)->format('d-m-Y');
            $rows[] = [
                'id' => null,
                'date' => $dateFromFormatted,
                'time' => null,
                'description' => 'Opening Balance',
                'fromAccount' => '-',
                'toAccount' => '-',
                'debit' => 0,
                'credit' => 0,
                'total' => round($running, 2),
            ];

        $accountLabel = trim(($account->bank ? $account->bank->name : 'Bank') . ($account->account_title ? ' - ' . $account->account_title : '') . ($account->account_number ? ' (' . $account->account_number . ')' : ''));
        if ($accountLabel === '') $accountLabel = 'This Account';

        foreach ($transactions as $tx) {
            $amount = (float) $tx->amount;
            $debit = $tx->type === 'debit' ? $amount : 0;
            $credit = $tx->type === 'credit' ? $amount : 0;
            $running += $credit - $debit;

            $desc = $tx->description ?? 'Transaction';
            if ($tx->type === 'debit') {
                $fromAccount = $accountLabel;
                $toAccount = preg_match('/Transfer to (.+)/i', $desc, $m) ? trim($m[1]) : (preg_match('/Cash Transfer to (.+)/i', $desc, $m2) ? trim($m2[1]) : $desc);
            } else {
                $fromAccount = preg_match('/Transfer from (.+)/i', $desc, $m) ? trim($m[1]) : (preg_match('/Cash Transfer from (.+)/i', $desc, $m2) ? trim($m2[1]) : $desc);
                $toAccount = $accountLabel;
            }

            $timeStr = $tx->created_at ? $tx->created_at->format('h:i A') : null;

            $rows[] = [
                'id' => $tx->id,
                'date' => $tx->transaction_date ? $tx->transaction_date->format('d-m-Y') : '-',
                'time' => $timeStr,
                'description' => $desc,
                'fromAccount' => $fromAccount,
                'toAccount' => $toAccount,
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
                'total' => round($running, 2),
                'type' => $tx->type,
                'amount' => round($amount, 2),
            ];
        }

        $label = trim(($account->bank ? $account->bank->name : 'Bank') . ($account->account_title ? ' - ' . $account->account_title : '') . ($account->account_number ? ' (' . $account->account_number . ')' : ''));
        if ($label === '') {
            $label = 'Bank Account #' . $account->id;
        }

        return response()->json([
            'success' => true,
            'account' => [
                'id' => $account->id,
                'label' => $label,
                'balance' => round($running, 2),
            ],
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'ledger' => $rows,
        ]);
    }

    /**
     * Update a bank transaction (only for user's own account).
     */
    public function updateBankTransaction(Request $request, $id)
    {
        $user = Auth::user();
        $tx = BankTransaction::with('bankAccount')->findOrFail($id);
        $account = $tx->bankAccount;
        if (!$account || $account->user_id !== $user->id || $account->account_type !== 'bank') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $tx->update([
            'transaction_date' => $validated['transaction_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Transaction updated']);
    }

    /**
     * Delete a bank transaction (only for user's own account).
     */
    public function destroyBankTransaction(Request $request, $id)
    {
        $user = Auth::user();
        $tx = BankTransaction::with('bankAccount')->findOrFail($id);
        $account = $tx->bankAccount;
        if (!$account || $account->user_id !== $user->id || $account->account_type !== 'bank') {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $tx->delete();
        return response()->json(['success' => true, 'message' => 'Transaction deleted']);
    }

    /**
     * Update a job
     */
    public function update(Request $request, $id)
    {
        $job = CarWashJob::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this job'
            ], 403);
        }

        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'service_name' => 'nullable|string|max:255',
            'worker_name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Get service and worker names if IDs are provided
        $serviceName = $job->service_name;
        if ($request->service_id) {
            $service = \App\Models\CarWashService::find($request->service_id);
            if ($service) {
                $serviceName = $service->label;
            }
        }

        $workerName = $job->worker_name;
        if ($request->worker_id) {
            $worker = \App\Models\CarWashWorker::find($request->worker_id);
            if ($worker) {
                $workerName = $worker->name;
            }
        }

        $job->update([
            'service_id' => $request->service_id ?? $job->service_id,
            'worker_id' => $request->worker_id ?? $job->worker_id,
            'customer_name' => $request->customer_name ? strtoupper($request->customer_name) : $job->customer_name,
            'vehicle_no' => $request->vehicle_no ? strtoupper($request->vehicle_no) : $job->vehicle_no,
            'mobile' => $request->mobile ? trim($request->mobile) : $job->mobile,
            'service_name' => $serviceName ? strtoupper($serviceName) : $job->service_name,
            'worker_name' => $workerName ? strtoupper($workerName) : $job->worker_name,
            'price' => $request->price ?? $job->price,
            'notes' => $request->notes ?? $job->notes,
        ]);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Job updated successfully',
                'job' => [
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
                    'status' => $job->status,
                    'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                    'endTime' => $job->end_time ? $job->end_time->toISOString() : null,
                    'durationSeconds' => $job->duration_seconds,
                    'notes' => $job->notes,
                ]
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Job updated successfully!');
    }

    /**
     * Cancel a job
     */
    public function cancel($id)
    {
        $job = CarWashJob::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel this job'
            ], 403);
        }

        $job->update([
            'status' => 'cancelled',
            'end_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job cancelled successfully'
        ]);
    }

    /**
     * Delete a job
     */
    public function destroy(Request $request, $id)
    {
        $job = CarWashJob::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessJobBranch($job, $user)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this job'
                ], 403);
            }
            return redirect()->route('car.wash')->with('error', 'You do not have permission to delete this job');
        }

        $job->delete();

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully'
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Job deleted successfully!');
    }

    /**
     * Get today's stats
     */
    public function todayStats()
    {
        $user = Auth::user();
        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $todayJobs = $query->today()->completed()->get();

        $todayRevenue = $todayJobs->sum('price');
        $todayJobsCount = $todayJobs->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'todayRevenue' => (float) $todayRevenue,
                'todayJobsCount' => $todayJobsCount,
            ]
        ]);
    }

    /**
     * Daily Jobs Report page
     */
    public function dailyReport(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $branch = $branchId ? \App\Models\Branch::find($branchId) : null;
        $branchName = ($user->role === 'admin' && !$branchId) ? 'All Branches' : ($branch ? $branch->branch_name : 'All');
        $userName = $user->name ?? 'Guest';
        $selectedDate = $request->get('date', today()->format('Y-m-d'));

        return view('car-wash-daily-report', compact('branchName', 'userName', 'selectedDate'));
    }

    /**
     * API: Get daily report data (ledger: date&time, vehicle, debit, credit, total, worker, commission)
     * Query: date (required), customer (vehicle_no), worker (worker_name)
     */
    public function dailyReportData(Request $request)
    {
        // Support both single date and date range
        $dateFrom = $request->get('date_from') ?: $request->get('date');
        $dateTo = $request->get('date_to') ?: $request->get('date');
        
        if (!$dateFrom || !$dateTo) {
            $request->validate(['date' => 'required|date']);
            $dateFrom = $dateTo = $request->date;
        } else {
            $request->validate([
                'date_from' => 'required|date',
                'date_to' => 'required|date'
            ]);
        }
        
        $customerFilter = $request->get('customer'); // vehicle_no
        $workerFilter = $request->get('worker');     // worker_name
        $userFilter = $request->get('user');         // user_id (user who entered the job)
        $paymentFilter = $request->get('payment');   // 'cash' | 'bank' (default: cash for tab; if empty treat as all for old links)

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $baseQuery = function ($q) use ($user, $dateFrom, $dateTo, $paymentFilter) {
            $this->applyBranchFilter($q, 'branch_id', $user);
            $q->where('status', 'completed');
            $q->whereBetween(\DB::raw('DATE(COALESCE(end_time, created_at))'), [$dateFrom, $dateTo]);
            if ($paymentFilter === 'bank') {
                $q->where('payment_method', 'bank');
            } elseif ($paymentFilter === 'cash') {
                $q->where(function ($q2) {
                    $q2->where('payment_method', 'cash')->orWhereNull('payment_method');
                });
            }
        };

        $query = CarWashJob::where($baseQuery)->with(['worker', 'expense', 'bank', 'bankAccount.bank', 'user']);
        if ($customerFilter !== null && $customerFilter !== '') {
            $query->where('vehicle_no', $customerFilter);
        }
        if ($workerFilter !== null && $workerFilter !== '') {
            $query->where('worker_name', $workerFilter);
        }
        if ($userFilter !== null && $userFilter !== '') {
            $query->where('user_id', (int) $userFilter);
        }

        $jobs = $query->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->get();

        // Filter options for the date (same payment filter, no customer/worker/user filter)
        $allJobsForFilters = CarWashJob::where($baseQuery)->with('user')->get();
        $customers = $allJobsForFilters->groupBy('vehicle_no')->keys()->filter()->map(function ($v) use ($allJobsForFilters) {
            $j = $allJobsForFilters->where('vehicle_no', $v)->first();
            return ['value' => $v, 'label' => $v . ($j && $j->customer_name ? ' (' . $j->customer_name . ')' : '')];
        })->values();
        $workers = $allJobsForFilters->pluck('worker_name')->unique()->filter()->map(function ($w) {
            return ['value' => $w, 'label' => $w];
        })->values();
        $users = $allJobsForFilters->pluck('user_id')->unique()->filter()->map(function ($uid) {
            $u = \App\Models\User::find($uid);
            return ['value' => (string) $uid, 'label' => $u ? $u->name : 'User #' . $uid];
        })->values();

        // Image jaisa: Credit ek row, Debit (expense) alag row. Debit row par credit/worker/commission empty; Credit row par debit empty.
        $dateCarbon = Carbon::parse($dateFrom);
        $previousDate = $dateCarbon->copy()->subDay();
        
        // Get last transaction time from previous date
        $lastTransactionQuery = function ($q) use ($user, $previousDate) {
            $this->applyBranchFilter($q, 'branch_id', $user);
            $q->where('status', 'completed');
            $q->whereRaw('DATE(COALESCE(end_time, created_at)) = ?', [$previousDate->format('Y-m-d')]);
        };
        $lastTransaction = CarWashJob::where($lastTransactionQuery)
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $openingTime = '12:00AM';
        if ($lastTransaction) {
            $lastTime = $lastTransaction->end_time ?: $lastTransaction->created_at;
            if ($lastTime) {
                $openingTime = Carbon::parse($lastTime)->format('h:i A');
            }
        }

        // Previous date closing balance = opening balance for selected date (same payment filter)
        // Cash opening = +cash receipts - job expenses - cash transfers - shop expenses (all up to previousDate)
        $openingBalanceQuery = function ($q) use ($user, $previousDate, $paymentFilter) {
            $this->applyBranchFilter($q, 'branch_id', $user);
            $q->where('status', 'completed');
            $q->whereRaw('DATE(COALESCE(end_time, created_at)) <= ?', [$previousDate->format('Y-m-d')]);
            if ($paymentFilter === 'bank') {
                $q->where('payment_method', 'bank');
            } else {
                $q->where(function ($q2) {
                    $q2->where('payment_method', 'cash')->orWhereNull('payment_method');
                });
            }
        };
        $previousJobs = CarWashJob::where($openingBalanceQuery)->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->with(['worker', 'expense'])->get();
        $openingBalance = 0;
        if ($paymentFilter !== 'bank') {
            // Cash: +receipts - job expenses - transfers - shop expenses
            foreach ($previousJobs as $job) {
                $price = round((float) $job->price, 2);
                $expenseAmount = $job->expense ? round((float) ($job->expense->total_amount ?? 0), 2) : 0;
                $openingBalance += $price;  // Cash receipt
                $openingBalance -= $expenseAmount;  // Job expense (paid from cash)
            }
            // Job expenses from bank jobs also reduce cash (paid from cash)
            $previousBankJobsQuery = CarWashJob::query();
            $this->applyBranchFilter($previousBankJobsQuery, 'branch_id', $user);
            $previousBankJobs = $previousBankJobsQuery
                ->where('status', 'completed')
                ->where('payment_method', 'bank')
                ->whereRaw('DATE(COALESCE(end_time, created_at)) <= ?', [$previousDate->format('Y-m-d')])
                ->with('expense')->get();
            foreach ($previousBankJobs as $job) {
                $expenseAmount = $job->expense ? round((float) ($job->expense->total_amount ?? 0), 2) : 0;
                $openingBalance -= $expenseAmount;
            }
            // Cash transfers up to previous date
            $prevCashTransfers = CashTransfer::query();
            $this->applyBranchFilter($prevCashTransfers, 'branch_id', $user);
            $prevCashTransfers->where('status', 'completed')
                ->where('created_at', '<=', $previousDate->format('Y-m-d') . ' 23:59:59');
            $openingBalance -= (float) $prevCashTransfers->sum('amount');
            // Shop expenses up to previous date
            $prevShopExpenses = \App\Models\CarWashShopExpense::query();
            $this->applyBranchFilter($prevShopExpenses, 'branch_id', $user);
            $prevShopExpenses->where('expense_date', '<=', $previousDate->format('Y-m-d'));
            $openingBalance -= (float) $prevShopExpenses->sum('amount');
        } else {
            // Bank: sum of bank job credits + bank transfers up to previous date
            foreach ($previousJobs as $job) {
                $price = round((float) $job->price, 2);
                $openingBalance += $price;
            }
            // Bank transfers (credits) up to previous date
            $prevBankTransferQuery = \App\Models\BankTransfer::query();
            if ($branchId) {
                $prevBankTransferQuery->whereHas('user', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            } elseif ($user->role !== 'admin') {
                $prevBankTransferQuery->whereRaw('1 = 0');
            }
            $prevBankTransferQuery->where('status', 'approved')
                ->where(function ($q) use ($previousDate) {
                    $endOfPrev = $previousDate->format('Y-m-d') . ' 23:59:59';
                    $q->where('approved_at', '<=', $endOfPrev)
                        ->orWhere(function ($q2) use ($endOfPrev) {
                            $q2->whereNull('approved_at')->where('created_at', '<=', $endOfPrev);
                        });
                });
            $openingBalance += (float) $prevBankTransferQuery->sum('amount');
        }
        $openingBalance = round($openingBalance, 2);
        
        $rows = [];
        $rows[] = [
            'dateTime' => $previousDate->format('d/m/y') . ' Time ' . $openingTime,
            'date' => $previousDate->format('d/m/y'),
            'startTime' => '-',
            'endTime' => '-',
            'totalTime' => '-',
            'vehicle' => 'Opening',
            'debit' => 0,
            'credit' => 0,
            'gTotal' => 0,
            'total' => 0,
            'worker' => '-',
            'bankName' => '-',
            'commission' => '-',
            'isOpening' => true,
            'openingBalance' => $openingBalance,
        ];
        $running = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;
        $commissionSum = 0;
        $vehicleSet = [];
        $workerSet = [];

        foreach ($jobs as $job) {
            $commissionAmount = 0;
            if ($job->worker && $job->worker->commission) {
                $commissionAmount = (($job->price ?? 0) * (float) $job->worker->commission) / 100;
            }
            $commissionAmount = round($commissionAmount, 2);
            $expenseAmount = $job->expense ? round((float) ($job->expense->total_amount ?? 0), 2) : 0;
            $price = round((float) $job->price, 2);
            // Cash Receipt column mein sirf job payment show hoga, expenses alag
            // But account mein job payment + expenses dono jayenge
            $totalWithExpense = $price + $expenseAmount; // For account credit
            $gTotal = round($price - $expenseAmount - $commissionAmount, 2);
            $totalDebit += $expenseAmount; // Keep for display in JOB EXPENSE column
            $totalCredit += $totalWithExpense; // Credit includes both price and expense (for account)
            $vehicleSet[$job->vehicle_no ?: 'N/A'] = true;
            $workerSet[$job->worker_name ?: 'N/A'] = true;
            $commissionSum += $commissionAmount;

            $dt = $job->end_time ?: $job->created_at;
            $dateTime = $dt ? Carbon::parse($dt)->format('d/m/y') . ' time ' . $dt->format('h:i A') : '-';
            $vehicle = $job->vehicle_no ?: 'N/A';
            $customerName = $job->customer_name ?: null;
            $mobile = $job->mobile ?: null;
            $userName = $job->user ? $job->user->name : null;
            
            // Calculate start time, end time, and duration
            $startTime = $job->start_time ? Carbon::parse($job->start_time)->format('h:i A') : '-';
            $endTime = $job->end_time ? Carbon::parse($job->end_time)->format('h:i A') : '-';
            $totalTime = '-';
            if ($job->start_time && $job->end_time) {
                $start = Carbon::parse($job->start_time);
                $end = Carbon::parse($job->end_time);
                $diff = $start->diff($end);
                $hours = $diff->h;
                $minutes = $diff->i;
                if ($hours > 0) {
                    $totalTime = $hours . 'h ' . $minutes . 'm';
                } else {
                    $totalTime = $minutes . 'm';
                }
            }
            $dateOnly = $dt ? Carbon::parse($dt)->format('d/m/y') : '-';

            // Row 1: Credit (debit=expense for display, credit=price only for Cash Receipt column, worker, bank, commission, gTotal)
            // Note: credit field ab sirf price hai (expenses nahi), but account mein totalWithExpense jayega
            
            // Bank column: separate fields for bank name, account title, and account number
            $bankName = '-';
            $bankNameOnly = null;
            $bankAccountTitle = null;
            $bankAccountNumber = null;
            if ($job->bankAccount) {
                $b = $job->bankAccount;
                $bankNameOnly = $b->bank ? $b->bank->name : 'N/A';
                $bankAccountTitle = $b->account_title ?? '';
                $bankAccountNumber = $b->account_number ?? '';
                if ($bankNameOnly && ($bankAccountTitle || $bankAccountNumber)) {
                    $bankName = $bankNameOnly; // Will be formatted in frontend
                } else {
                    $bankName = $bankNameOnly ?: '-';
                }
            } elseif ($job->bank) {
                $bankNameOnly = $job->bank->name;
                $bankName = $bankNameOnly;
            }
            
            // Step 1: Add receipt to running total
            // Cash filter: only cash payments add to cash total; bank payments go to bank, not cash
            // Bank filter: bank payments add to bank total
            if ($paymentFilter === 'bank') {
                if ($job->payment_method === 'bank') {
                    $running += $price; // Bank receipt increases bank total
                }
                // Job expenses are paid from cash, not bank - do not subtract from bank running
            } else {
                if ($job->payment_method === 'cash' || $job->payment_method === null) {
                    $running += $price; // Add price to running total (for cash receipt only)
                }
                // Step 2: Subtract job expenses from running total (they reduce cash)
                if ($expenseAmount > 0) {
                    $running -= $expenseAmount;
                }
            }
            
            $rows[] = [
                'dateTime' => $dateTime,
                'date' => $dateOnly,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'totalTime' => $totalTime,
                'vehicle' => $vehicle,
                'customerName' => $customerName,
                'mobile' => $mobile,
                'userName' => $userName,
                'debit' => $expenseAmount, // Show expense in JOB EXPENSE column
                'credit' => $price, // Cash Receipt column: sirf job payment (expenses alag)
                'gTotal' => $gTotal,
                'total' => $running, // Cash Total: Running balance after cash receipt and job expense deduction
                'worker' => $job->worker_name ?: 'N/A',
                'bankName' => $bankName,
                'bankNameOnly' => $bankNameOnly,
                'bankAccountTitle' => $bankAccountTitle,
                'bankAccountNumber' => $bankAccountNumber,
                'commission' => $commissionAmount,
                'jobId' => $job->id,
                'isOpening' => false,
            ];
        }

        // Since expenses are now added to credit, sumGtotal = totalCredit - commissionSum
        // (totalCredit already includes price + expense)
        $sumGtotal = round($totalCredit - $commissionSum, 2);

        // Pending commission: total commission from jobs minus paid (completed, non-reversed) per job
        $jobIds = $jobs->pluck('id')->toArray();
        $totalCommissionPaid = (float) \App\Models\CarWashPayment::whereIn('car_wash_job_id', $jobIds)
            ->where('payment_type', 'commission')
            ->where('status', 'completed')
            ->sum('amount');
        $pendingCommission = max(0, round($commissionSum - $totalCommissionPaid, 2));

        // Fetch cash transfers for the date range (only for cash filter)
        $totalCashTransfer = 0;
        $cashTransfers = collect([]);
        if ($paymentFilter !== 'bank') {
            // Only fetch cash transfers for cash filter
            $cashTransferQuery = \App\Models\CashTransfer::with(['fromUser', 'toUser']);
            $this->applyBranchFilter($cashTransferQuery, 'branch_id', $user);
            $cashTransferQuery->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            $cashTransfers = $cashTransferQuery->orderBy('created_at', 'asc')
                ->get();
            $totalCashTransfer = (float) $cashTransfers->sum('amount');
            
            // Add cash transfers as separate rows (before shop expenses)
            foreach ($cashTransfers as $transfer) {
                $transferDate = $transfer->created_at;
                $transferDateTime = $transferDate ? Carbon::parse($transferDate)->format('d/m/y') . ' time ' . $transferDate->format('h:i A') : '-';
                $transferDateOnly = $transferDate ? Carbon::parse($transferDate)->format('d/m/y') : '-';
                $transferAmount = round((float) $transfer->amount, 2);
                $fromUserName = $transfer->fromUser ? $transfer->fromUser->name : null;
                $toUserName = $transfer->toUser ? $transfer->toUser->name : 'Admin';
                
                // Subtract cash transfer from running total (they reduce cash)
                $running -= $transferAmount;
                
                $rows[] = [
                    'dateTime' => $transferDateTime,
                    'date' => $transferDateOnly,
                    'startTime' => '-',
                    'endTime' => '-',
                    'totalTime' => '-',
                    'vehicle' => 'Cash Transfer: ' . ($toUserName ?: 'Admin'),
                    'customerName' => null,
                    'mobile' => null,
                    'userName' => $fromUserName,
                    'debit' => 0,
                    'credit' => 0,
                    'cashTransfer' => $transferAmount,
                    'toUserName' => $toUserName,
                    'toUserId' => $transfer->to_user_id,
                    'gTotal' => null,
                    'total' => $running,
                    'worker' => '-',
                    'bankName' => '-',
                    'bankNameOnly' => null,
                    'bankAccountTitle' => null,
                    'bankAccountNumber' => null,
                    'commission' => '-',
                    'jobId' => null,
                    'transferId' => $transfer->id,
                    'isOpening' => false,
                    'isCashTransfer' => true,
                    'notes' => $transfer->note,
                ];
            }
        }
        
        // Fetch shop expenses for the date range (only for cash filter)
        $totalShopExpense = 0;
        $shopExpenses = collect([]);
        if ($paymentFilter !== 'bank') {
            // Only fetch shop expenses for cash filter
            $shopExpenseQuery = \App\Models\CarWashShopExpense::with('user');
            $this->applyBranchFilter($shopExpenseQuery, 'branch_id', $user);
            $shopExpenseQuery->whereBetween('expense_date', [$dateFrom, $dateTo]);
            $shopExpenses = $shopExpenseQuery->orderBy('expense_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
            $totalShopExpense = (float) $shopExpenses->sum('amount');
            
            // Add shop expenses as separate rows
            foreach ($shopExpenses as $shopExp) {
                $expDate = $shopExp->expense_date;
                $expDateTime = $expDate ? Carbon::parse($expDate)->format('d/m/y') . ' time ' . ($shopExp->created_at ? $shopExp->created_at->format('h:i A') : '12:00AM') : '-';
                $expDateOnly = $expDate ? Carbon::parse($expDate)->format('d/m/y') : '-';
                $expAmount = round((float) $shopExp->amount, 2);
                $expUserName = $shopExp->user ? $shopExp->user->name : null;
                
                // Step 3: Subtract shop expense from running total (they reduce cash)
                // SHOP EXPENSE column mein expense amount show hoga
                // Cash Total = Previous Balance - Shop Expense (after this step)
                $running -= $expAmount;
                
                $rows[] = [
                    'dateTime' => $expDateTime,
                    'date' => $expDateOnly,
                    'startTime' => '-',
                    'endTime' => '-',
                    'totalTime' => '-',
                    'vehicle' => 'Shop Expense: ' . ($shopExp->category ?: 'N/A'),
                    'customerName' => null,
                    'mobile' => null,
                    'userName' => $expUserName,
                    'debit' => 0, // Job expense nahi hai
                    'credit' => 0, // Cash receipt nahi hai
                    'shopExpense' => $expAmount, // Shop expense amount
                    'gTotal' => null,
                    'total' => $running,
                    'worker' => '-',
                    'bankName' => '-',
                    'bankNameOnly' => null,
                    'bankAccountTitle' => null,
                    'bankAccountNumber' => null,
                    'commission' => '-',
                    'jobId' => null,
                    'isOpening' => false,
                    'isShopExpense' => true,
                    'notes' => $shopExp->notes,
                ];
            }
        }
        
        // Fetch bank transfers for the date range (only for bank filter)
        $totalBankTransfer = 0;
        $bankTransfers = collect([]);
        if ($paymentFilter === 'bank') {
            // Only fetch bank transfers for bank filter
            // BankTransfer doesn't have branch_id, so filter by user's branch
            $bankTransferQuery = \App\Models\BankTransfer::with('user');
            if ($branchId) {
                $bankTransferQuery->whereHas('user', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            } elseif ($user->role !== 'admin') {
                // Non-admin users without branch should see nothing
                $bankTransferQuery->whereRaw('1 = 0');
            }
            $bankTransferQuery->where('status', 'approved')
                ->where(function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('approved_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                      ->orWhere(function($q2) use ($dateFrom, $dateTo) {
                          $q2->whereNull('approved_at')
                             ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                      });
                });
            $bankTransfers = $bankTransferQuery->orderByRaw('COALESCE(approved_at, created_at) ASC')
                ->get();
            $totalBankTransfer = (float) $bankTransfers->sum('amount');
            
            // Add bank transfers as separate rows
            foreach ($bankTransfers as $transfer) {
                $transferDate = $transfer->approved_at ?: $transfer->created_at;
                $transferDateTime = $transferDate ? Carbon::parse($transferDate)->format('d/m/y') . ' time ' . Carbon::parse($transferDate)->format('h:i A') : '-';
                $transferDateOnly = $transferDate ? Carbon::parse($transferDate)->format('d/m/y') : '-';
                $transferAmount = round((float) $transfer->amount, 2);
                $userName = $transfer->user ? $transfer->user->name : null;
                
                // Bank transfers add to bank total (they increase bank balance)
                $running += $transferAmount;

                $rows[] = [
                    'dateTime' => $transferDateTime,
                    'date' => $transferDateOnly,
                    'startTime' => '-',
                    'endTime' => '-',
                    'totalTime' => '-',
                    'vehicle' => 'Bank Transfer',
                    'customerName' => null,
                    'mobile' => null,
                    'userName' => $userName,
                    'debit' => 0,
                    'credit' => $transferAmount, // Bank credit
                    'cashTransfer' => 0,
                    'shopExpense' => 0,
                    'gTotal' => null,
                    'total' => $running,
                    'worker' => '-',
                    'bankName' => $transfer->bank_name ?? '-',
                    'bankNameOnly' => $transfer->bank_name ?? null,
                    'bankAccountTitle' => $transfer->account_title ?? null,
                    'bankAccountNumber' => $transfer->account_number ?? null,
                    'commission' => '-',
                    'jobId' => null,
                    'transferId' => $transfer->id,
                    'isOpening' => false,
                    'isBankTransfer' => true,
                    'paymentType' => 'bank',
                    'notes' => null,
                ];
            }
        }

        // Sort all rows by date and time (except opening row which should stay first)
        $openingRow = null;
        $otherRows = [];
        foreach ($rows as $row) {
            if (isset($row['isOpening']) && $row['isOpening']) {
                $openingRow = $row;
            } else {
                $otherRows[] = $row;
            }
        }
        
        // Sort other rows by dateTime (parse and compare)
        usort($otherRows, function($a, $b) {
            // Parse dateTime string to compare
            $dateTimeA = $a['dateTime'] ?? '';
            $dateTimeB = $b['dateTime'] ?? '';
            
            if (empty($dateTimeA) && empty($dateTimeB)) return 0;
            if (empty($dateTimeA)) return 1;
            if (empty($dateTimeB)) return -1;
            
            // Extract date and time from format "d/m/y time h:i A" or "d/m/y Time h:i A"
            $parseDateTime = function($dtStr) {
                if (preg_match('/(\d{2}\/\d{2}\/\d{2})\s+(?:time|Time)\s+(\d{1,2}):(\d{2})\s+(AM|PM)/i', $dtStr, $matches)) {
                    $date = $matches[1];
                    $hour = (int)$matches[2];
                    $minute = (int)$matches[3];
                    $ampm = strtoupper($matches[4]);
                    
                    if ($ampm === 'PM' && $hour !== 12) $hour += 12;
                    if ($ampm === 'AM' && $hour === 12) $hour = 0;
                    
                    // Parse date: d/m/y
                    list($d, $m, $y) = explode('/', $date);
                    $year = 2000 + (int)$y; // Assuming 20xx
                    
                    return Carbon::create($year, $m, $d, $hour, $minute, 0);
                }
                return null;
            };
            
            $dtA = $parseDateTime($dateTimeA);
            $dtB = $parseDateTime($dateTimeB);
            
            if (!$dtA && !$dtB) return 0;
            if (!$dtA) return 1;
            if (!$dtB) return -1;
            
            return $dtA->timestamp <=> $dtB->timestamp;
        });
        
        // Reconstruct rows array with opening first, then sorted rows
        $rows = $openingRow ? [$openingRow, ...$otherRows] : $otherRows;

        return response()->json([
            'success' => true,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'rows' => $rows,
            'customers' => $customers,
            'workers' => $workers,
            'users' => $users,
            'totals' => [
                'totalVehicles' => count($vehicleSet),
                'totalDebit' => round($totalDebit, 2),
                'totalCredit' => round($totalCredit, 2),
                'cashOnHand' => round($running, 2),
                'totalWorkers' => count($workerSet),
                'totalCommission' => round($commissionSum, 2),
                'totalCommissionPaid' => round($totalCommissionPaid, 2),
                'pendingCommission' => $pendingCommission,
                'sumGtotal' => $sumGtotal,
                'totalShopExpense' => round($totalShopExpense, 2),
                'totalCashTransfer' => round($totalCashTransfer, 2),
            ],
        ]);
    }

    /**
     * Download daily report as PDF (ledger format). Query: date, customer (vehicle_no), worker (worker_name)
     */
    public function dailyReportPdf(Request $request)
    {
        // Support both single date and date range
        $dateFrom = $request->get('date_from') ?: $request->get('date');
        $dateTo = $request->get('date_to') ?: $request->get('date');
        
        if (!$dateFrom || !$dateTo) {
            $request->validate(['date' => 'required|date']);
            $dateFrom = $dateTo = $request->date;
        } else {
            $request->validate([
                'date_from' => 'required|date',
                'date_to' => 'required|date'
            ]);
        }
        
        $customerFilter = $request->get('customer');
        $workerFilter = $request->get('worker');
        $userFilter = $request->get('user');
        $paymentFilter = $request->get('payment');

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $branch = $branchId ? \App\Models\Branch::find($branchId) : null;
        $branchName = ($user->role === 'admin' && !$branchId) ? 'All Branches' : ($branch ? $branch->branch_name : 'All');

        $baseClosure = function ($q) use ($user, $dateFrom, $dateTo, $paymentFilter) {
            $this->applyBranchFilter($q, 'branch_id', $user);
            $q->where('status', 'completed');
            $q->whereBetween(\DB::raw('DATE(COALESCE(end_time, created_at))'), [$dateFrom, $dateTo]);
            if ($paymentFilter === 'bank') {
                $q->where('payment_method', 'bank');
            } elseif ($paymentFilter === 'cash') {
                $q->where(function ($q2) {
                    $q2->where('payment_method', 'cash')->orWhereNull('payment_method');
                });
            }
        };

        $query = CarWashJob::where($baseClosure)->with(['worker', 'expense', 'bank', 'bankAccount.bank']);
        if ($customerFilter !== null && $customerFilter !== '') {
            $query->where('vehicle_no', $customerFilter);
        }
        if ($workerFilter !== null && $workerFilter !== '') {
            $query->where('worker_name', $workerFilter);
        }
        if ($userFilter !== null && $userFilter !== '') {
            $query->where('user_id', (int) $userFilter);
        }
        $jobs = $query->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->get();

        $dateCarbon = Carbon::parse($dateFrom);
        $previousDate = $dateCarbon->copy()->subDay();
        
        // Get last transaction time from previous date
        $lastTransactionQuery = function ($q) use ($user, $previousDate) {
            $this->applyBranchFilter($q, 'branch_id', $user);
            $q->where('status', 'completed');
            $q->whereRaw('DATE(COALESCE(end_time, created_at)) = ?', [$previousDate->format('Y-m-d')]);
        };
        $lastTransaction = CarWashJob::where($lastTransactionQuery)
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $openingTime = '12:00AM';
        if ($lastTransaction) {
            $lastTime = $lastTransaction->end_time ?: $lastTransaction->created_at;
            if ($lastTime) {
                $openingTime = Carbon::parse($lastTime)->format('h:i A');
            }
        }
        
        $rows = [];
        $rows[] = [
            'dateTime' => $previousDate->format('d/m/y') . ' Time ' . $openingTime,
            'vehicle' => 'Opening',
            'debit' => 0,
            'credit' => 0,
            'gTotal' => 0,
            'total' => 0,
            'worker' => '-',
            'bankName' => '-',
            'commission' => '-',
            'expenseItems' => [],
        ];
        $running = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        $commissionSum = 0;
        $vehicleSet = [];
        $workerSet = [];

        foreach ($jobs as $job) {
            $commissionAmount = 0;
            if ($job->worker && $job->worker->commission) {
                $commissionAmount = (($job->price ?? 0) * (float) $job->worker->commission) / 100;
            }
            $commissionAmount = round($commissionAmount, 2);
            $expenseAmount = $job->expense ? round((float) ($job->expense->total_amount ?? 0), 2) : 0;
            $price = round((float) $job->price, 2);
            // Cash Receipt column mein sirf job payment show hoga, expenses alag
            // But account mein job payment + expenses dono jayenge
            $totalWithExpense = $price + $expenseAmount; // For account credit
            $gTotal = round($price - $expenseAmount - $commissionAmount, 2);
            $totalDebit += $expenseAmount; // Keep for display in JOB EXPENSE column
            $totalCredit += $totalWithExpense; // Credit includes both price and expense (for account)
            $vehicleSet[$job->vehicle_no ?: 'N/A'] = true;
            $workerSet[$job->worker_name ?: 'N/A'] = true;
            $commissionSum += $commissionAmount;

            $dt = $job->end_time ?: $job->created_at;
            $dateTime = $dt ? Carbon::parse($dt)->format('d/m/y') . ' time ' . $dt->format('h:i A') : '-';
            $vehicle = $job->vehicle_no ?: 'N/A';
            $expenseItems = ($job->expense && is_array($job->expense->expense_items ?? null)) ? $job->expense->expense_items : [];

            // Row 1: Credit (debit=expense for display, credit=price only for Cash Receipt column, worker, bank, commission, gTotal)
            // Note: credit field ab sirf price hai (expenses nahi), but account mein totalWithExpense jayega
            $running += $price; // Add price to running total (for cash receipt)
            $bankName = '-';
            if ($job->bankAccount) {
                $b = $job->bankAccount;
                $bn = $b->bank ? $b->bank->name : 'N/A';
                $t = $b->account_title ?? '';
                $n = $b->account_number ?? '';
                $label = trim($bn . ($t ? ' - ' . $t : '') . ($n ? ' (' . $n . ')' : ''));
                $bankName = ($label === '' || (!$t && !$n)) ? ($bn . ' — #' . $b->id) : $label;
            } elseif ($job->bank) {
                $bankName = $job->bank->name;
            }
            // Subtract job expenses from running total (they reduce cash)
            if ($expenseAmount > 0) {
                $running -= $expenseAmount;
            }
            
            $rows[] = [
                'dateTime' => $dateTime,
                'vehicle' => $vehicle,
                'debit' => $expenseAmount, // Show expense in JOB EXPENSE column
                'credit' => $price, // Cash Receipt column: sirf job payment (expenses alag)
                'gTotal' => $gTotal,
                'total' => $running,
                'worker' => $job->worker_name ?: 'N/A',
                'bankName' => $bankName,
                'commission' => $commissionAmount,
                'expenseItems' => $expenseItems,
            ];
        }

        // Since expenses are now added to credit, sumGtotal = totalCredit - commissionSum
        // (totalCredit already includes price + expense)
        $sumGtotal = round($totalCredit - $commissionSum, 2);

        // Calculate shop expenses for the date range and deduct from running total
        $totalShopExpense = 0;
        $shopExpenseQuery = \App\Models\CarWashShopExpense::with('user');
        $this->applyBranchFilter($shopExpenseQuery, 'branch_id', $user);
        $shopExpenseQuery->whereBetween('expense_date', [$dateFrom, $dateTo]);
        $shopExpenses = $shopExpenseQuery->orderBy('expense_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        $totalShopExpense = (float) $shopExpenses->sum('amount');
        
        // Deduct shop expenses from running total (they reduce cash)
        // Shop expenses should be deducted from user's account balance
        $running -= $totalShopExpense;

        $data = [
            'date' => $dateCarbon->format('l, F d, Y'),
            'dateRaw' => $dateFrom,
            'branchName' => $branchName,
            'paymentFilter' => $paymentFilter ?? 'cash',
            'rows' => $rows,
            'totalVehicles' => count($vehicleSet),
            'totalDebit' => round($totalDebit, 2),
            'totalCredit' => round($totalCredit, 2),
            'cashOnHand' => round($running, 2), // Cash total after deducting shop expenses and job expenses
            'totalWorkers' => count($workerSet),
            'totalCommission' => round($commissionSum, 2),
            'sumGtotal' => $sumGtotal,
            'totalShopExpense' => round($totalShopExpense, 2),
        ];

        $pdf = Pdf::loadView('car-wash-daily-report-pdf', $data)
            ->setPaper('a4', count($rows) > 1 ? 'landscape' : 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        // Format date with current system time for filename
        $currentDateTime = Carbon::now();
        $currentTime = $currentDateTime->format('h-i-A'); // e.g., 09-24-PM
        $currentDate = $currentDateTime->format('d-M-y'); // e.g., 27-Jan-26
        $dateFromFormatted = Carbon::parse($dateFrom)->format('d-M-y');
        $dateToFormatted = Carbon::parse($dateTo)->format('d-M-y');
        $dateForFilename = $dateFrom === $dateTo 
            ? $dateFromFormatted . '-' . $currentTime 
            : $dateFromFormatted . '_to_' . $dateToFormatted . '-' . $currentTime;
        
        // Check if inline display is requested (for WhatsApp links)
        $inline = $request->get('inline', false);
        if ($inline) {
            return $pdf->stream('elite-car-wash-daily-Report-' . $dateForFilename . '.pdf');
        }
        
        return $pdf->download('elite-car-wash-daily-Report-' . $dateForFilename . '.pdf');
    }
}
