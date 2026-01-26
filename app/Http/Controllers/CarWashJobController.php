<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\CarWashJob;
use App\Models\CarWashWorker;
use Illuminate\Support\Facades\Auth;
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
        ->orderBy('start_time', 'asc')
        ->get()
        ->map(function($job) {
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
            ];
        });
        
        return response()->json([
            'success' => true,
            'jobs' => $jobs
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

        $jobs = $query->with('worker')->orderBy('end_time', 'desc')
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

        $job->update($updateData);

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
     * List bank accounts for the login user: uske branch ke + jinke branch_id null (unassigned).
     * Har item bank account detail (bank, account title, number) ke sath.
     */
    public function bankAccountsIndex(Request $request)
    {
        $user = Auth::user();

        $query = BankAccount::with('bank')
            ->where('account_type', 'bank')
            ->where(function ($q) {
                $q->where('status', true)->orWhereNull('status');
            });
        $this->applyBranchFilter($query, 'branch_id', $user);

        $accounts = $query->orderBy('account_title')
            ->get()
            ->map(function ($a) {
                $bankName = $a->bank ? $a->bank->name : 'N/A';
                $title = $a->account_title ?? '';
                $num = $a->account_number ?? '';
                // Build account-level detail: Bank - Title (Number). If title+number empty, use "Bank — Account #id".
                $label = trim($bankName . ($title ? ' - ' . $title : '') . ($num ? ' (' . $num . ')' : ''));
                if ($label === '' || (!$title && !$num)) {
                    $label = $bankName . ' — Account #' . $a->id;
                }
                return [
                    'id' => $a->id,
                    'bank_id' => $a->bank_id,
                    'bankName' => $bankName,
                    'accountTitle' => $title,
                    'accountNumber' => $num,
                    'displayLabel' => $label,
                ];
            });

        return response()->json(['success' => true, 'bankAccounts' => $accounts]);
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
        $request->validate(['date' => 'required|date']);
        $date = $request->date;
        $customerFilter = $request->get('customer'); // vehicle_no
        $workerFilter = $request->get('worker');     // worker_name
        $paymentFilter = $request->get('payment');   // 'cash' | 'bank' (default: cash for tab; if empty treat as all for old links)

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $baseQuery = function ($q) use ($user, $date, $paymentFilter) {
            $this->applyBranchFilter($q, 'branch_id', $user);
            $q->where('status', 'completed');
            $q->whereRaw('DATE(COALESCE(end_time, created_at)) = ?', [$date]);
            if ($paymentFilter === 'bank') {
                $q->where('payment_method', 'bank');
            } elseif ($paymentFilter === 'cash') {
                $q->where(function ($q2) {
                    $q2->where('payment_method', 'cash')->orWhereNull('payment_method');
                });
            }
        };

        $query = CarWashJob::where($baseQuery)->with(['worker', 'expense', 'bank', 'bankAccount.bank']);
        if ($customerFilter !== null && $customerFilter !== '') {
            $query->where('vehicle_no', $customerFilter);
        }
        if ($workerFilter !== null && $workerFilter !== '') {
            $query->where('worker_name', $workerFilter);
        }

        $jobs = $query->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->get();

        // Filter options for the date (same payment filter, no customer/worker filter)
        $allJobsForFilters = CarWashJob::where($baseQuery)->get();
        $customers = $allJobsForFilters->groupBy('vehicle_no')->keys()->filter()->map(function ($v) use ($allJobsForFilters) {
            $j = $allJobsForFilters->where('vehicle_no', $v)->first();
            return ['value' => $v, 'label' => $v . ($j && $j->customer_name ? ' (' . $j->customer_name . ')' : '')];
        })->values();
        $workers = $allJobsForFilters->pluck('worker_name')->unique()->filter()->map(function ($w) {
            return ['value' => $w, 'label' => $w];
        })->values();

        // Image jaisa: Credit ek row, Debit (expense) alag row. Debit row par credit/worker/commission empty; Credit row par debit empty.
        $dateCarbon = Carbon::parse($date);
        $rows = [];
        $rows[] = [
            'dateTime' => $dateCarbon->format('d/m/y') . ' time 12:00am',
            'vehicle' => 'Opening',
            'debit' => 0,
            'credit' => 0,
            'gTotal' => 0,
            'total' => 0,
            'worker' => '-',
            'bankName' => '-',
            'commission' => '-',
            'isOpening' => true,
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
            $gTotal = round($price - $expenseAmount - $commissionAmount, 2);
            $totalDebit += $expenseAmount;
            $totalCredit += $price;
            $vehicleSet[$job->vehicle_no ?: 'N/A'] = true;
            $workerSet[$job->worker_name ?: 'N/A'] = true;
            $commissionSum += $commissionAmount;

            $dt = $job->end_time ?: $job->created_at;
            $dateTime = $dt ? Carbon::parse($dt)->format('d/m/y') . ' time ' . $dt->format('h:i A') : '-';
            $vehicle = $job->vehicle_no ?: 'N/A';

            // Row 1: Credit (debit empty, credit=price, worker, bank, commission, gTotal)
            $running += $price;
            // Bank column: full bank account detail (Bank - Title (Number)) when bankAccount; else bank name
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
            $rows[] = [
                'dateTime' => $dateTime,
                'vehicle' => $vehicle,
                'debit' => 0,
                'credit' => $price,
                'gTotal' => $gTotal,
                'total' => $running,
                'worker' => $job->worker_name ?: 'N/A',
                'bankName' => $bankName,
                'commission' => $commissionAmount,
                'jobId' => $job->id,
                'isOpening' => false,
            ];

            // Row 2: Debit (credit empty, debit=expense, worker/bank/commission empty) — sirf jab expense > 0
            if ($expenseAmount > 0) {
                $running -= $expenseAmount;
                $rows[] = [
                    'dateTime' => $dateTime,
                    'vehicle' => $vehicle,
                    'debit' => $expenseAmount,
                    'credit' => 0,
                    'gTotal' => null,
                    'total' => $running,
                    'worker' => '-',
                    'bankName' => '-',
                    'commission' => '-',
                    'jobId' => $job->id,
                    'isOpening' => false,
                ];
            }
        }

        $sumGtotal = round($totalCredit - $totalDebit - $commissionSum, 2);

        return response()->json([
            'success' => true,
            'date' => $date,
            'rows' => $rows,
            'customers' => $customers,
            'workers' => $workers,
            'totals' => [
                'totalVehicles' => count($vehicleSet),
                'totalDebit' => round($totalDebit, 2),
                'totalCredit' => round($totalCredit, 2),
                'cashOnHand' => round($running, 2),
                'totalWorkers' => count($workerSet),
                'totalCommission' => round($commissionSum, 2),
                'sumGtotal' => $sumGtotal,
            ],
        ]);
    }

    /**
     * Download daily report as PDF (ledger format). Query: date, customer (vehicle_no), worker (worker_name)
     */
    public function dailyReportPdf(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;
        $customerFilter = $request->get('customer');
        $workerFilter = $request->get('worker');
        $paymentFilter = $request->get('payment');

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $branch = $branchId ? \App\Models\Branch::find($branchId) : null;
        $branchName = ($user->role === 'admin' && !$branchId) ? 'All Branches' : ($branch ? $branch->branch_name : 'All');

        $baseClosure = function ($q) use ($user, $date, $paymentFilter) {
            $this->applyBranchFilter($q, 'branch_id', $user);
            $q->where('status', 'completed');
            $q->whereRaw('DATE(COALESCE(end_time, created_at)) = ?', [$date]);
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
        $jobs = $query->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->get();

        $dateCarbon = Carbon::parse($date);
        $rows = [];
        $rows[] = [
            'dateTime' => $dateCarbon->format('d/m/y') . ' time 12:00am',
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
            $gTotal = round($price - $expenseAmount - $commissionAmount, 2);
            $totalDebit += $expenseAmount;
            $totalCredit += $price;
            $vehicleSet[$job->vehicle_no ?: 'N/A'] = true;
            $workerSet[$job->worker_name ?: 'N/A'] = true;
            $commissionSum += $commissionAmount;

            $dt = $job->end_time ?: $job->created_at;
            $dateTime = $dt ? Carbon::parse($dt)->format('d/m/y') . ' time ' . $dt->format('h:i A') : '-';
            $vehicle = $job->vehicle_no ?: 'N/A';
            $expenseItems = ($job->expense && is_array($job->expense->expense_items ?? null)) ? $job->expense->expense_items : [];

            // Row 1: Credit (debit empty, credit=price, worker, bank, commission, gTotal)
            $running += $price;
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
            $rows[] = [
                'dateTime' => $dateTime,
                'vehicle' => $vehicle,
                'debit' => 0,
                'credit' => $price,
                'gTotal' => $gTotal,
                'total' => $running,
                'worker' => $job->worker_name ?: 'N/A',
                'bankName' => $bankName,
                'commission' => $commissionAmount,
                'expenseItems' => [],
            ];

            // Row 2: Debit (credit empty, debit=expense, worker/bank/commission empty) — sirf jab expense > 0
            if ($expenseAmount > 0) {
                $running -= $expenseAmount;
                $rows[] = [
                    'dateTime' => $dateTime,
                    'vehicle' => $vehicle,
                    'debit' => $expenseAmount,
                    'credit' => 0,
                    'gTotal' => null,
                    'total' => $running,
                    'worker' => '-',
                    'bankName' => '-',
                    'commission' => '-',
                    'expenseItems' => $expenseItems,
                ];
            }
        }

        $sumGtotal = round($totalCredit - $totalDebit - $commissionSum, 2);

        $data = [
            'date' => $dateCarbon->format('l, F d, Y'),
            'dateRaw' => $date,
            'branchName' => $branchName,
            'paymentFilter' => $paymentFilter ?? 'cash',
            'rows' => $rows,
            'totalVehicles' => count($vehicleSet),
            'totalDebit' => round($totalDebit, 2),
            'totalCredit' => round($totalCredit, 2),
            'cashOnHand' => round($running, 2),
            'totalWorkers' => count($workerSet),
            'totalCommission' => round($commissionSum, 2),
            'sumGtotal' => $sumGtotal,
        ];

        $pdf = Pdf::loadView('car-wash-daily-report-pdf', $data)
            ->setPaper('a4', count($rows) > 1 ? 'landscape' : 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download('elite-car-wash-daily-report-' . $date . '.pdf');
    }
}
