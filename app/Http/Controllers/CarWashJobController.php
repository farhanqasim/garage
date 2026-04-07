<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasBranchAccess;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CarWashJob;
use App\Models\CarWashWorker;
use App\Models\CashTransfer;
use App\Models\Customer;
use App\Models\CustomerCar;
use App\Models\WorkerCashAccount;
use App\Models\WorkerCashTransaction;
use App\Services\CashAccountService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $paginator = $query->with(['worker', 'customer', 'customerCar'])->orderBy('created_at', 'desc')
            ->paginate(50);

        $jobs = $paginator->getCollection()->map(function ($job) {
            $workerCommission = 0;
            $commissionAmount = 0;
            if ($job->worker && $job->worker->commission) {
                $workerCommission = (float) $job->worker->commission;
                $commissionAmount = (($job->price ?? 0) * $workerCommission) / 100;
            }

            return [
                'id' => $job->id,
                'serviceId' => $job->service_id,
                'workerId' => $job->worker_user_id ?? $job->worker_id,
                'customerId' => $job->customer_id,
                'customerCarId' => $job->customer_car_id,
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
                'voiceNote' => $job->voice_note ? url($job->voice_note) : null,
                'createdAt' => $job->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'jobs' => $jobs,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
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
            ->with(['expense', 'customer', 'customerCar'])
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($job) {
                $expenseTotal = $job->expense ? (float) ($job->expense->total_amount ?? 0) : 0;
                // Resolve customer name, vehicle, mobile from relations (customers + customer_cars)
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
            'jobs' => $jobs,
        ]);
    }

    /**
     * Search past jobs by vehicle plate number (for autocomplete).
     * Uses customer_cars relation (plate_number); returns vehicle/customer name/mobile from relations.
     */
    public function searchByPlate(Request $request)
    {
        $q = $request->get('q', '');
        $q = trim(strtoupper((string) $q));
        if (strlen($q) < 1) {
            return response()->json(['success' => true, 'suggestions' => []]);
        }

        $jobs = CarWashJob::query()
            ->with(['customer', 'customerCar'])
            ->whereHas('customerCar', function ($qb) use ($q) {
                $qb->where('plate_number', 'like', $q.'%');
            })
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

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
     * Last 2 months job history for a vehicle (by plate: linked customer_car or job.vehicle_no).
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
            ->where(function ($q) use ($vehicleNo) {
                $q->whereHas('customerCar', function ($qb) use ($vehicleNo) {
                    $qb->where(DB::raw('UPPER(TRIM(plate_number))'), $vehicleNo);
                })
                    ->orWhere(DB::raw('UPPER(TRIM(vehicle_no))'), $vehicleNo);
            })
            ->where(DB::raw('COALESCE(end_time, created_at)'), '>=', $twoMonthsAgo)
            ->with(['worker', 'customer', 'customerCar'])
            ->orderByRaw('COALESCE(end_time, created_at) DESC')
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
     * Find customer (and car) by vehicle plate from customer_cars.
     * Used by check-in: when admin enters plate, auto-fill customer name/phone and set customer_id + customer_car_id.
     */
    public function findCustomerByPlate(Request $request)
    {
        $plate = $request->get('plate', '');
        $plate = trim(strtoupper((string) $plate));
        if (strlen($plate) < 1) {
            return response()->json(['success' => true, 'matches' => []]);
        }

        // Match plate anywhere: start, middle or end (e.g. "123" finds "ABC-123", "LEC-22-1234")
        $likePattern = '%'.str_replace(['%', '_'], ['\%', '\_'], $plate).'%';
        $cars = CustomerCar::with('customer.branch')
            ->where('plate_number', 'like', $likePattern)
            ->orderBy('plate_number')
            ->limit(20)
            ->get();

        $matches = $cars->map(function ($car) {
            $customer = $car->customer;
            $names = $customer && isset($customer->names) && is_array($customer->names) ? $customer->names : [];
            $phones = $customer && isset($customer->phones) && is_array($customer->phones) ? $customer->phones : [];
            $branchName = $customer && $customer->branch ? $customer->branch->branch_name : '';

            return [
                'customerId' => $car->customer_id,
                'customerCarId' => $car->id,
                'vehicleNo' => $car->plate_number ?? '',
                'customerName' => isset($names[0]) ? (string) $names[0] : '',
                'mobile' => isset($phones[0]) ? (string) $phones[0] : '',
                'branchName' => $branchName,
            ];
        })->values()->all();

        return response()->json(['success' => true, 'matches' => $matches]);
    }

    /**
     * Show job detail page
     */
    public function show($id)
    {
        $user = Auth::user();
        $query = CarWashJob::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $job = $query->with(['worker', 'inspection', 'expense', 'customer', 'customerCar'])->findOrFail($id);

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
            'workerId' => $job->worker_user_id ?? $job->worker_id,
            'customerId' => $job->customer_id,
            'customerCarId' => $job->customer_car_id,
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
        $branchName = ($user->role === 'admin' && ! $branchId) ? 'All Branches' : ($branch ? $branch->branch_name : 'Guest');

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

        $paginator = $query->with(['worker', 'worker.workerCashAccount', 'workerUser', 'workerUser.workerCashAccount', 'customer', 'customerCar'])->orderBy('end_time', 'desc')
            ->paginate(50);

        $jobs = $paginator->getCollection()->map(function ($job) {
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

            $workerBalance = null;
            $workerTotalPaid = null;
            $workerTotalEarned = null;
            if ($job->worker_user_id) {
                $acc = $job->workerUser && $job->workerUser->relationLoaded('workerCashAccount') && $job->workerUser->workerCashAccount
                    ? $job->workerUser->workerCashAccount
                    : WorkerCashAccount::where('user_id', $job->worker_user_id)->first();
                if ($acc) {
                    $workerBalance = (float) $acc->balance;
                    $workerTotalPaid = (float) ($acc->total_paid ?? 0);
                    $workerTotalEarned = (float) ($acc->total_earned ?? 0);
                }
            } elseif ($job->worker && $job->worker->relationLoaded('workerCashAccount') && $job->worker->workerCashAccount) {
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

            $commissionPaid = (float) \App\Models\CarWashPayment::where('car_wash_job_id', $job->id)
                ->where('status', 'completed')
                ->sum('amount');

            return [
                'id' => $job->id,
                'serviceId' => $job->service_id,
                'workerId' => $job->worker_user_id ?? $job->worker_id,
                'customerId' => $job->customer_id,
                'customerCarId' => $job->customer_car_id,
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
            'jobs' => $jobs,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Start a new job.
     * Jobs table stores only customer_id and customer_car_id.
     * If customer_id/customer_car_id not sent but vehicle_no + (customer_name or mobile) sent:
     * create new Customer + CustomerCar, then link job to them.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'nullable|exists:car_wash_services,id',
            'worker_id' => 'required|exists:users,id', // User with role=worker
            'customer_id' => 'nullable|exists:customers,id',
            'customer_car_id' => 'nullable|exists:customer_cars,id',
            'customer_name' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'service_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'additional_prices' => 'nullable|array',
            'worker_name' => 'nullable|string|max:255',
            'voice_note' => 'nullable|string', // Base64 data URL (e.g. data:audio/webm;base64,...)
        ]);

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $customerId = $request->customer_id ? (int) $request->customer_id : null;
        $customerCarId = $request->customer_car_id ? (int) $request->customer_car_id : null;

        // When existing customer selected: update customer name/phone in customers table if sent
        if ($customerId && ($request->has('customer_name') || $request->has('mobile'))) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $updates = [];
                if ($request->has('customer_name')) {
                    $name = trim((string) $request->customer_name);
                    $updates['names'] = $name !== '' ? [$name] : ($customer->names ?? []);
                }
                if ($request->has('mobile')) {
                    $mobile = trim((string) $request->mobile);
                    $updates['phones'] = $mobile !== '' ? [$mobile] : ($customer->phones ?? []);
                }
                if (! empty($updates)) {
                    $customer->update($updates);
                }
            }
        }

        // When vehicle not in DB: create new customer + vehicle from name/mobile/vehicle_no
        if (! $customerId || ! $customerCarId) {
            $vehicleNo = $request->filled('vehicle_no') ? trim(strtoupper((string) $request->vehicle_no)) : null;
            $customerName = $request->filled('customer_name') ? trim((string) $request->customer_name) : null;
            $mobile = $request->filled('mobile') ? trim((string) $request->mobile) : null;
            if ($vehicleNo && ($customerName !== null && $customerName !== '' || $mobile !== null && $mobile !== '')) {
                $customer = null;
                if ($customerId) {
                    $customer = Customer::find($customerId);
                }
                if (! $customer) {
                    $customer = Customer::create([
                        'branch_id' => $branchId,
                        'names' => $customerName ? [$customerName] : [],
                        'phones' => $mobile ? [$mobile] : [],
                        'password' => Hash::make(Str::random(12)),
                        'opening_balance' => 0,
                        'balance_type' => 'receive',
                        'credit_limit_type' => 'no_limit',
                    ]);
                    $customerId = $customer->id;
                }
                $car = null;
                if ($customerCarId) {
                    $car = CustomerCar::where('id', $customerCarId)->where('customer_id', $customerId)->first();
                }
                if (! $car) {
                    $car = CustomerCar::firstOrCreate(
                        [
                            'customer_id' => $customerId,
                            'plate_number' => $vehicleNo,
                        ],
                        [
                            'make' => null,
                            'model' => null,
                            'year' => null,
                        ]
                    );
                    $customerCarId = $car->id;
                }
            }
        }

        $workerUser = \App\Models\User::where('role', 'worker')->find($request->worker_id);
        $workerName = $workerUser ? $workerUser->name : ($request->worker_name ? strtoupper($request->worker_name) : null);

        $job = CarWashJob::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'service_id' => $request->service_id,
            'worker_id' => null,
            'worker_user_id' => $request->worker_id,
            'customer_id' => $customerId,
            'customer_car_id' => $customerCarId,
            'customer_name' => $request->filled('customer_name') ? trim((string) $request->customer_name) : null,
            'vehicle_no' => $request->filled('vehicle_no') ? trim(strtoupper((string) $request->vehicle_no)) : null,
            'mobile' => $request->filled('mobile') ? trim((string) $request->mobile) : null,
            'service_name' => strtoupper($request->service_name),
            'price' => $request->price,
            'additional_prices' => $request->additional_prices ?? [],
            'worker_name' => $workerName,
            'status' => 'active',
            'start_time' => now(),
        ]);

        // Save voice note if provided (base64 data URL from frontend)
        if ($request->filled('voice_note')) {
            $dataUrl = $request->voice_note;
            if (preg_match('/^data:audio\/(\w+);base64,(.+)$/s', $dataUrl, $matches)) {
                $extension = $matches[1] === 'webm' ? 'webm' : ($matches[1] === 'ogg' ? 'ogg' : 'webm');
                $decoded = base64_decode($matches[2], true);
                if ($decoded !== false && strlen($decoded) > 0) {
                    $dir = 'CarWash_audio';
                    $fullPath = public_path($dir);
                    if (! is_dir($fullPath)) {
                        mkdir($fullPath, 0755, true);
                    }
                    $filename = time().'_'.uniqid().'.'.$extension;
                    if (file_put_contents($fullPath.DIRECTORY_SEPARATOR.$filename, $decoded) !== false) {
                        $job->voice_note = $dir.'/'.$filename;
                        $job->save();
                    }
                }
            }
        }

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
                    'description' => $request->expense_description ?? '',
                ]
            );
        }

        $job->load(['customer', 'customerCar']);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Job started successfully',
                'job' => [
                    'id' => $job->id,
                    'serviceId' => $job->service_id,
                    'workerId' => $job->worker_user_id ?? $job->worker_id,
                    'customerId' => $job->customer_id,
                    'customerCarId' => $job->customer_car_id,
                    'customerName' => $job->customer_name,
                    'vehicleNo' => $job->vehicle_no,
                    'mobile' => $job->mobile,
                    'serviceName' => $job->service_name,
                    'workerName' => $job->worker_name,
                    'price' => (float) $job->price,
                    'additionalPrices' => $job->additional_prices ?? [],
                    'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                    'status' => $job->status,
                    'voiceNote' => $job->voice_note ? url($job->voice_note) : null,
                ],
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

        if (! $this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to complete this job',
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

        // Store bank account when payment is bank: only logged-in user's account (the one selected in modal).
        if ($pm === 'bank' && $request->filled('bank_account_id')) {
            $accountQuery = BankAccount::where('id', $request->bank_account_id)
                ->where('account_type', 'bank')
                ->where('user_id', $user->id);
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

            // Commission: credit to worker cash account (User worker or legacy CarWashWorker)
            $commissionPct = 0;
            $workerUserId = $job->worker_user_id;
            $legacyWorkerId = $job->worker_id;
            if ($workerUserId) {
                $workerUser = \App\Models\User::find($workerUserId);
                $commissionPct = $workerUser ? (float) ($workerUser->commission ?? 0) : 0;
            } elseif ($job->worker) {
                $commissionPct = (float) ($job->worker->commission ?? 0);
            }
            if ($commissionPct > 0) {
                $jobPrice = (float) ($job->price ?? 0);
                $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                $totalJobPrice = $jobPrice + (float) $additionalPrices;
                $commissionAmount = ($totalJobPrice * $commissionPct) / 100;
                if ($commissionAmount > 0) {
                    if ($workerUserId) {
                        $workerCash = WorkerCashAccount::firstOrCreate(
                            ['user_id' => $workerUserId],
                            ['balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
                        );
                        $workerCash->balance = (float) $workerCash->balance + $commissionAmount;
                        $workerCash->total_earned = (float) $workerCash->total_earned + $commissionAmount;
                        $workerCash->save();
                        WorkerCashTransaction::create([
                            'worker_id' => null,
                            'user_id' => $workerUserId,
                            'amount' => $commissionAmount,
                            'type' => 'credit',
                            'reference_type' => 'car_wash_jobs',
                            'reference_id' => $job->id,
                            'note' => "Commission {$commissionPct}% on job #{$job->id} - Rs ".number_format($totalJobPrice, 2),
                        ]);
                    } else {
                        $worker = $job->worker;
                        $workerCash = WorkerCashAccount::firstOrCreate(
                            ['worker_id' => $worker->id],
                            ['balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
                        );
                        $workerCash->balance = (float) $workerCash->balance + $commissionAmount;
                        $workerCash->total_earned = (float) $workerCash->total_earned + $commissionAmount;
                        $workerCash->save();
                        WorkerCashTransaction::create([
                            'worker_id' => $worker->id,
                            'amount' => $commissionAmount,
                            'type' => 'credit',
                            'reference_type' => 'car_wash_jobs',
                            'reference_id' => $job->id,
                            'note' => "Commission {$commissionPct}% on job #{$job->id} - Rs ".number_format($totalJobPrice, 2),
                        ]);
                    }
                }
            }

            // Cash/Bank credit: CarWashJobObserver handles it on status=completed (full amount = price + additional_prices + job expenses to user cash or bank). No double-credit here.

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $job->load(['customer', 'customerCar']);

        return response()->json([
            'success' => true,
            'message' => 'Job completed successfully',
            'job' => [
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
                'status' => $job->status,
                'startTime' => $job->start_time ? $job->start_time->toISOString() : null,
                'endTime' => $job->end_time->toISOString(),
                'durationSeconds' => $job->duration_seconds,
                'notes' => $job->notes,
            ],
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
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
                'bankAccounts' => [],
            ], 401);
        }

        $branchId = $this->getUserBranchId($user);
        $userIdParam = $request->get('user_id');

        $userIds = [];
        if ($userIdParam !== null && $userIdParam !== '') {
            $targetId = (int) $userIdParam;
            if ($targetId === $user->id) {
                $userIds = [$user->id];
            } else {
                $branchUserIds = $this->getBankBranchUserIds($branchId, $user);
                if (! in_array($targetId, $branchUserIds)) {
                    return response()->json(['success' => false, 'message' => 'User not found or access denied.', 'bankAccounts' => []], 403);
                }
                $userIds = [$targetId];
            }
        } else {
            // No user_id = header / "my balance": show only logged-in user's bank accounts
            $userIds = [$user->id];
        }

        $query = BankAccount::with('bank')
            ->where('account_type', 'bank')
            ->where(function ($q) {
                $q->where('status', true)->orWhereNull('status');
            })
            ->whereIn('user_id', $userIds);

        $allAccounts = $query->orderBy('is_primary', 'desc')
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
        } elseif ($allAccounts->count() === 1) {
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
            $label = trim($bankName.($title ? ' - '.$title : '').($num ? ' ('.$num.')' : ''));
            if ($label === '' || (! $title && ! $num)) {
                $label = $bankName.' — Account #'.$a->id;
            }

            // Calculate balance: opening_balance + credits - debits (including all transactions, not just tallied)
            $openingBalance = (float) ($a->opening_balance ?? 0);

            // Get all credits (not just tallied)
            $credits = $a->bankTransactions()
                ->where('type', 'credit')
                ->sum('amount');

            // Get all debits (not just tallied)
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
                'balance' => $balance, // Current balance (opening + credits - debits)
                'opening_balance' => $openingBalance, // Opening balance for header display
                'isDisplayAccount' => ($a->id === $displayAccountId), // Mark which account to display
            ];
        });

        return response()->json(['success' => true, 'bankAccounts' => $formattedAccounts->values()->all()]);
    }

    /**
     * Get user IDs for the given branch (owners + assigned), including current user. For bank/cash balance "all users".
     */
    protected function getBankBranchUserIds($branchId, $currentUser)
    {
        if (! $branchId) {
            return [$currentUser->id];
        }

        return \App\Models\User::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
                ->orWhereHas('assignedBranches', function ($q2) use ($branchId) {
                    $q2->where('branch_id', $branchId);
                });
        })->pluck('id')->toArray();
    }

    /**
     * Get bank accounts of other users from same branch (for transfer)
     * Excludes logged-in user's accounts
     */
    public function bankAccountsForTransfer(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch found for user',
                'bankAccounts' => [],
            ], 400);
        }

        // Same branch ke doosre users (login user exclude) — dropdown mein sirf inhi ke accounts
        $branchUserIds = \App\Models\User::where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->orWhereHas('assignedBranches', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
        })
            ->where('id', '!=', $user->id)
            ->pluck('id');

        // Admin users (role = 'admin') bhi allow — unke accounts par transfer ho sake
        $adminUserIds = \App\Models\User::where('role', 'admin')->pluck('id');
        $userIdsForTransfer = $branchUserIds->merge($adminUserIds)->unique()->filter(function ($id) use ($user) {
            return (int) $id !== (int) $user->id;
        })->values();

        $query = BankAccount::with(['bank', 'user:id,name'])
            ->whereIn('account_type', ['bank', 'cash'])
            ->where(function ($q) {
                $q->where('status', true)->orWhereNull('status');
            })
            ->whereIn('user_id', $userIdsForTransfer);

        $accounts = $query->orderBy('account_title')
            ->get()
            ->map(function ($a) use ($user) {
                $bankName = $a->bank ? $a->bank->name : 'N/A';
                $title = $a->account_title ?? '';
                $num = $a->account_number ?? '';
                $label = trim($bankName.($title ? ' - '.$title : '').($num ? ' ('.$num.')' : ''));
                if ($label === '' || (! $title && ! $num)) {
                    $label = $bankName.' — Account #'.$a->id;
                }
                $ownerName = $a->user ? $a->user->name : '';
                $isOwn = ($a->user_id == $user->id);
                $bankLogo = $a->bank && ! empty($a->bank->logo) ? $a->bank->logo : null;

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
     * Balance History: usi branch ke saare users (login + doosre) ke bank/cash accounts + balance.
     * Total = in sab ka sum; list = saare accounts.
     */
    public function bankAccountsBranchBalanceHistory(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch found for user',
                'bankAccounts' => [],
            ], 400);
        }

        // Usi branch ke saare users (login + doosre) + admin
        $branchUserIds = \App\Models\User::where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->orWhereHas('assignedBranches', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
        })->pluck('id');
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
                $label = trim($bankName.($title ? ' - '.$title : '').($num ? ' ('.$num.')' : ''));
                if ($label === '' || (! $title && ! $num)) {
                    $label = $bankName.' — Account #'.$a->id;
                }
                $ownerName = $a->user ? $a->user->name : '';
                $isOwn = ($a->user_id == $user->id);

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

        if (! $account) {
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

        $accountLabel = trim(($account->bank ? $account->bank->name : 'Bank').($account->account_title ? ' - '.$account->account_title : '').($account->account_number ? ' ('.$account->account_number.')' : ''));
        if ($accountLabel === '') {
            $accountLabel = 'This Account';
        }

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

        $label = trim(($account->bank ? $account->bank->name : 'Bank').($account->account_title ? ' - '.$account->account_title : '').($account->account_number ? ' ('.$account->account_number.')' : ''));
        if ($label === '') {
            $label = 'Bank Account #'.$account->id;
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
        if (! $account || $account->user_id !== $user->id || $account->account_type !== 'bank') {
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
        if (! $account || $account->user_id !== $user->id || $account->account_type !== 'bank') {
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

        if (! $this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this job',
            ], 403);
        }

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_car_id' => 'nullable|exists:customer_cars,id',
            'customer_name' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'service_name' => 'nullable|string|max:255',
            'worker_name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $serviceName = $job->service_name;
        if ($request->service_id) {
            $service = \App\Models\CarWashService::find($request->service_id);
            if ($service) {
                $serviceName = $service->label;
            }
        }

        $workerName = $job->worker_name;
        if ($request->worker_id) {
            $workerUser = \App\Models\User::where('role', 'worker')->find($request->worker_id);
            if ($workerUser) {
                $workerName = $workerUser->name;
            }
        }

        // Update linked Customer when customer_name or mobile sent
        if ($job->customer_id && ($request->has('customer_name') || $request->has('mobile'))) {
            $customer = Customer::find($job->customer_id);
            if ($customer) {
                $updates = [];
                if ($request->has('customer_name')) {
                    $name = trim((string) $request->customer_name);
                    $updates['names'] = $name !== '' ? [$name] : ($customer->names ?? []);
                }
                if ($request->has('mobile')) {
                    $mobile = trim((string) $request->mobile);
                    $updates['phones'] = $mobile !== '' ? [$mobile] : ($customer->phones ?? []);
                }
                if (! empty($updates)) {
                    $customer->update($updates);
                }
            }
        }

        // Update linked CustomerCar when vehicle_no sent
        if ($job->customer_car_id && $request->filled('vehicle_no')) {
            $car = CustomerCar::where('id', $job->customer_car_id)->first();
            if ($car) {
                $car->update(['plate_number' => trim(strtoupper((string) $request->vehicle_no))]);
            }
        }

        $job->update([
            'service_id' => $request->service_id ?? $job->service_id,
            'worker_id' => null,
            'worker_user_id' => $request->worker_id ?? $job->worker_user_id,
            'customer_id' => $request->has('customer_id') ? ($request->customer_id ? (int) $request->customer_id : null) : $job->customer_id,
            'customer_car_id' => $request->has('customer_car_id') ? ($request->customer_car_id ? (int) $request->customer_car_id : null) : $job->customer_car_id,
            'service_name' => $serviceName ? strtoupper($serviceName) : $job->service_name,
            'worker_name' => $workerName ? strtoupper($workerName) : $job->worker_name,
            'price' => $request->price ?? $job->price,
            'notes' => $request->notes ?? $job->notes,
        ]);

        $job->load(['customer', 'customerCar']);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Job updated successfully',
                'job' => [
                    'id' => $job->id,
                    'serviceId' => $job->service_id,
                    'workerId' => $job->worker_user_id ?? $job->worker_id,
                    'customerId' => $job->customer_id,
                    'customerCarId' => $job->customer_car_id,
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
                ],
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

        if (! $this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel this job',
            ], 403);
        }

        $job->update([
            'status' => 'cancelled',
            'end_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job cancelled successfully',
        ]);
    }

    /**
     * Delete a job
     */
    public function destroy(Request $request, $id)
    {
        $job = CarWashJob::findOrFail($id);
        $user = Auth::user();

        if (! $this->canAccessJobBranch($job, $user)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this job',
                ], 403);
            }

            return redirect()->route('car.wash')->with('error', 'You do not have permission to delete this job');
        }

        $job->delete();

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully',
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
            ],
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
        $branchName = ($user->role === 'admin' && ! $branchId) ? 'All Branches' : ($branch ? $branch->branch_name : 'All');
        $userName = $user->name ?? 'Guest';
        $selectedDate = $request->get('date', today()->format('Y-m-d'));

        // Customers from customers table (CustomerCar + Customer) for dropdown on page load
        $customersQuery = CustomerCar::with('customer')
            ->whereHas('customer', function ($q) use ($branchId) {
                if ($branchId !== null) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->whereNotNull('plate_number')
            ->where('plate_number', '!=', '');
        $customerCars = $customersQuery->get();
        $customers = $customerCars->groupBy('plate_number')->map(function ($group) {
            $cc = $group->first();
            $name = $cc->customer && is_array($cc->customer->names ?? null) && count($cc->customer->names) > 0
                ? ($cc->customer->names[0] ?? '')
                : '';
            $plate = $cc->plate_number ?? '';

            return ['value' => $plate, 'label' => $plate.($name ? ' ('.$name.')' : '')];
        })->values()->sortBy('value')->values()->all();

        // Workers from users table (role = worker) for dropdown on page load
        $workersQuery = \App\Models\User::where('role', 'worker')->orderBy('name');
        if ($branchId !== null) {
            $workersQuery->where('branch_id', $branchId);
        }
        $workers = $workersQuery->get()->map(function ($u) {
            return ['value' => (string) $u->id, 'label' => $u->name ?? 'User #'.$u->id];
        })->values()->all();

        // Users (branch users) for USER filter dropdown on page load
        $usersQuery = \App\Models\User::orderBy('name');
        if ($branchId !== null) {
            $usersQuery->where('branch_id', $branchId);
        }
        $reportUsers = $usersQuery->get()->map(function ($u) {
            return ['value' => (string) $u->id, 'label' => $u->name ?? 'User #'.$u->id];
        })->values()->all();

        // Total distinct vehicles ever given car wash service (for TOTAL VEHICLES card)
        // Use customer_car_id (vehicles are in customer_cars table)
        $totalDistinctVehiclesServiced = 0;
        $vehiclesServicedQuery = CarWashJob::where('status', 'completed')
            ->whereNotNull('customer_car_id');
        if ($branchId !== null) {
            $this->applyBranchFilter($vehiclesServicedQuery, 'branch_id', $user);
        }
        $totalDistinctVehiclesServiced = (int) $vehiclesServicedQuery->distinct()->count('customer_car_id');

        // Total distinct workers who have completed at least one job (worker_user_id or legacy worker_id)
        $totalDistinctWorkersServicedQuery = CarWashJob::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNotNull('worker_user_id')->orWhereNotNull('worker_id');
            });
        if ($branchId !== null) {
            $this->applyBranchFilter($totalDistinctWorkersServicedQuery, 'branch_id', $user);
        }
        $totalDistinctWorkersServiced = (int) $totalDistinctWorkersServicedQuery->selectRaw('COUNT(DISTINCT COALESCE(worker_user_id, 1000000 + COALESCE(worker_id, 0))) as c')->value('c');

        // Initial report data: one call with no payment filter so ALL completed jobs for the date are returned
        $emptyReportPayload = [
            'success' => true,
            'rows' => [],
            'totals' => [],
            'customers' => [],
            'workers' => [],
            'users' => [],
        ];
        $initialCashPayload = $emptyReportPayload;
        $initialBankPayload = $emptyReportPayload;
        try {
            $reportRequest = Request::create('/car-wash/daily-report/data', 'GET', [
                'date_from' => $selectedDate,
                'date_to' => $selectedDate,
                'payment' => '', // no filter = all jobs (cash + bank) so table is never empty when jobs exist
                'customer' => '',
                'worker' => '',
                'user' => '',
            ]);
            $reportRequest->setUserResolver(fn () => $user);
            $reportRequest->headers->set('Accept', 'application/json');
            $allResponse = $this->dailyReportData($reportRequest);
            $decoded = json_decode($allResponse->getContent(), true);
            if (is_array($decoded) && isset($decoded['success']) && $decoded['success']) {
                $initialCashPayload = $decoded;
                // Bank payload: same opening row, no bank-only rows (frontend merge will show all jobs from cash payload)
                $opening = isset($decoded['rows']) ? collect($decoded['rows'])->firstWhere('isOpening', true) : null;
                $initialBankPayload = [
                    'success' => true,
                    'rows' => $opening ? [$opening] : [],
                    'totals' => $decoded['totals'] ?? [],
                    'customers' => $decoded['customers'] ?? [],
                    'workers' => $decoded['workers'] ?? [],
                    'users' => $decoded['users'] ?? [],
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Daily report initial payload failed: '.$e->getMessage());
        }

        // Initial CASH ON HAND = logged-in user's cash account balance (card shows login user by default)
        $cashService = app(CashAccountService::class);
        $initialCashOnHand = $cashService->getBalance($user->id);

        $branchUserIds = $this->getBankBranchUserIds($branchId, $user);
        // Initial BANK BALANCE = sum of branch users' bank account balances (same as bankAccountsIndex API)
        $initialBankBalance = 0;
        $bankAccounts = BankAccount::where('account_type', 'bank')
            ->where(function ($q) {
                $q->where('status', true)->orWhereNull('status');
            })
            ->whereIn('user_id', $branchUserIds)
            ->get();
        foreach ($bankAccounts as $a) {
            $openingBalance = (float) ($a->opening_balance ?? 0);
            $credits = (float) $a->bankTransactions()->where('type', 'credit')->sum('amount');
            $debits = (float) $a->bankTransactions()->where('type', 'debit')->sum('amount');
            $initialBankBalance += $openingBalance + $credits - $debits;
        }

        // Direct fetch from car_wash_jobs for server-rendered table (guaranteed to show when jobs exist)
        $reportJobsQuery = CarWashJob::where('status', 'completed')
            ->whereRaw('DATE(COALESCE(end_time, created_at)) BETWEEN ? AND ?', [$selectedDate, $selectedDate])
            ->with(['worker', 'workerUser', 'expense', 'customer', 'customerCar', 'user']);
        if ($branchId !== null) {
            $this->applyBranchFilter($reportJobsQuery, 'branch_id', $user);
        }
        $reportJobs = $reportJobsQuery->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->get();
        $initialReportRows = [];
        foreach ($reportJobs as $job) {
            $dt = $job->end_time ?: $job->created_at;
            $dateTime = $dt ? Carbon::parse($dt)->format('d/m/y').' time '.$dt->format('h:i A') : '-';
            $vehicle = $job->vehicle_no ?: 'N/A';
            $jobPrice = (float) ($job->price ?? 0);
            $additionalSum = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
            $expenseAmount = $job->expense ? round((float) ($job->expense->total_amount ?? 0), 2) : 0;
            $commissionPct = 0;
            if ($job->worker_user_id && $job->workerUser) {
                $commissionPct = (float) ($job->workerUser->commission ?? 0);
            } elseif ($job->worker && $job->worker->commission) {
                $commissionPct = (float) $job->worker->commission;
            }
            $totalJobPrice = $jobPrice + (float) $additionalSum;
            $commissionAmount = $commissionPct > 0 ? round(($totalJobPrice * $commissionPct) / 100, 2) : 0;
            $initialReportRows[] = [
                'dateTime' => $dateTime,
                'vehicle' => $vehicle,
                'credit' => round($jobPrice, 2),
                'debit' => $expenseAmount,
                'worker' => $job->worker_name ?: 'N/A',
                'commission' => $commissionAmount,
                'userName' => $job->user ? $job->user->name : null,
                'paymentMethod' => $job->payment_method ?? 'cash',
            ];
        }

        // JOB EXPENSE card = sum of debits in table rows (so card always matches table)
        $initialJobExpense = array_sum(array_column($initialReportRows, 'debit'));
        // COMMISSION card = sum of commission in table rows (so card matches table; avoids double-count from separate date logic)
        $initialCommission = array_sum(array_column($initialReportRows, 'commission'));

        $currentUserId = $user->id;

        return view('car-wash-daily-report', compact('branchName', 'userName', 'selectedDate', 'customers', 'workers', 'reportUsers', 'totalDistinctVehiclesServiced', 'totalDistinctWorkersServiced', 'initialCashPayload', 'initialBankPayload', 'initialCashOnHand', 'initialBankBalance', 'initialJobExpense', 'initialCommission', 'initialReportRows', 'currentUserId'));
    }

    /**
     * API: Get daily report data (ledger: date&time, vehicle, debit, credit, total, worker, commission)
     * Query: date (required), customer (vehicle_no), worker (worker_name)
     */
    public function dailyReportData(Request $request)
    {

        try {
            // Support both single date and date range
            $dateFrom = $request->get('date_from') ?: $request->get('date');
            $dateTo = $request->get('date_to') ?: $request->get('date');

            if (! $dateFrom || ! $dateTo) {
                $request->validate(['date' => 'required|date']);
                $dateFrom = $dateTo = $request->date;
            } else {
                $request->validate([
                    'date_from' => 'required|date',
                    'date_to' => 'required|date',
                ]);
            }

            $customerFilter = $request->get('customer'); // vehicle_no
            $workerFilter = $request->get('worker');     // worker_name
            $userFilter = $request->get('user');         // user_id (user who entered the job)
            $paymentFilter = $request->get('payment');   // 'cash' | 'bank' (default: cash for tab; if empty treat as all for old links)

            $tz = config('app.timezone', 'Asia/Karachi');

            $user = Auth::user();
            $branchId = $this->getUserBranchId($user);

            // Total distinct vehicles ever given car wash service (completed jobs, branch filter)
            // Use customer_car_id (table has no vehicle_no column; vehicles are in customer_cars)
            $totalDistinctVehiclesServicedQuery = CarWashJob::where('status', 'completed')
                ->whereNotNull('customer_car_id');
            if ($branchId !== null) {
                $this->applyBranchFilter($totalDistinctVehiclesServicedQuery, 'branch_id', $user);
            }
            $totalDistinctVehiclesServiced = (int) $totalDistinctVehiclesServicedQuery->distinct()->count('customer_car_id');

            // Total distinct workers who have completed at least one job (worker_user_id or legacy worker_id)
            $totalDistinctWorkersServicedQuery = CarWashJob::where('status', 'completed')
                ->where(function ($q) {
                    $q->whereNotNull('worker_user_id')->orWhereNotNull('worker_id');
                });
            if ($branchId !== null) {
                $this->applyBranchFilter($totalDistinctWorkersServicedQuery, 'branch_id', $user);
            }
            $totalDistinctWorkersServiced = (int) $totalDistinctWorkersServicedQuery->selectRaw('COUNT(DISTINCT COALESCE(worker_user_id, 1000000 + COALESCE(worker_id, 0))) as c')->value('c');

            // Date filter: use DATE() so timezone does not exclude jobs (DB may store local or UTC)
            $baseQuery = function ($q) use ($user, $dateFrom, $dateTo, $paymentFilter, $branchId) {
                // Only apply branch filter if user has a branch (otherwise show all jobs so report is visible)
                if ($branchId !== null) {
                    $this->applyBranchFilter($q, 'branch_id', $user);
                }
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

            $query = CarWashJob::where($baseQuery)->with(['worker', 'workerUser', 'expense', 'bank', 'bankAccount.bank', 'user', 'customer', 'customerCar']);
            if ($customerFilter !== null && $customerFilter !== '') {
                $query->whereHas('customerCar', function ($qb) use ($customerFilter) {
                    $qb->where('plate_number', $customerFilter);
                });
            }
            if ($workerFilter !== null && $workerFilter !== '') {
                $query->where('worker_user_id', (int) $workerFilter);
            }
            if ($userFilter !== null && $userFilter !== '') {
                $query->where('user_id', (int) $userFilter);
            }

            $jobs = $query->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->get();

            // Customers: from customers table (CustomerCar + Customer), branch filter
            $customersQuery = CustomerCar::with('customer')
                ->whereHas('customer', function ($q) use ($branchId) {
                    if ($branchId !== null) {
                        $q->where('branch_id', $branchId);
                    }
                })
                ->whereNotNull('plate_number')
                ->where('plate_number', '!=', '');
            $customerCars = $customersQuery->get();
            $customers = $customerCars->groupBy('plate_number')->map(function ($group) {
                $cc = $group->first();
                $name = $cc->customer && is_array($cc->customer->names ?? null) && count($cc->customer->names) > 0
                    ? ($cc->customer->names[0] ?? '')
                    : '';
                $plate = $cc->plate_number ?? '';

                return ['value' => $plate, 'label' => $plate.($name ? ' ('.$name.')' : '')];
            })->values()->sortBy('value')->values()->all();

            // Workers: from users table where role = worker, same branch
            $workersQuery = \App\Models\User::where('role', 'worker')->orderBy('name');
            if ($branchId !== null) {
                $workersQuery->where('branch_id', $branchId);
            }
            $workers = $workersQuery->get()->map(function ($u) {
                return ['value' => (string) $u->id, 'label' => $u->name ?? 'User #'.$u->id];
            })->values()->all();

            // Users: who entered jobs (from jobs in date range) for filter dropdown - query only user IDs to avoid loading all jobs into memory
            $userIds = CarWashJob::where($baseQuery)->distinct()->pluck('user_id')->filter()->values()->all();
            $users = collect();
            if (! empty($userIds)) {
                $userList = \App\Models\User::whereIn('id', $userIds)->orderBy('name')->get()->keyBy('id');
                $users = collect($userIds)->map(function ($uid) use ($userList) {
                    $u = $userList->get($uid);

                    return ['value' => (string) $uid, 'label' => $u ? $u->name : 'User #'.$uid];
                })->values();
            }

            // Image jaisa: Credit ek row, Debit (expense) alag row. Debit row par credit/worker/commission empty; Credit row par debit empty.
            $dateCarbon = Carbon::parse($dateFrom, $tz);
            $previousDate = $dateCarbon->copy()->subDay();
            $prevStart = Carbon::parse($previousDate->format('Y-m-d').' 00:00:00', $tz)->timezone('UTC');
            $prevEnd = Carbon::parse($previousDate->format('Y-m-d').' 23:59:59', $tz)->timezone('UTC');

            // Get last transaction time from previous date
            $lastTransactionQuery = function ($q) use ($user, $prevStart, $prevEnd, $branchId) {
                if ($branchId !== null) {
                    $this->applyBranchFilter($q, 'branch_id', $user);
                }
                $q->where('status', 'completed');
                $q->whereBetween(\DB::raw('COALESCE(end_time, created_at)'), [$prevStart->toDateTimeString(), $prevEnd->toDateTimeString()]);
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
            $openingBalanceQuery = function ($q) use ($user, $prevEnd, $paymentFilter, $branchId) {
                if ($branchId !== null) {
                    $this->applyBranchFilter($q, 'branch_id', $user);
                }
                $q->where('status', 'completed');
                $q->where(\DB::raw('COALESCE(end_time, created_at)'), '<=', $prevEnd->toDateTimeString());
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
                if ($branchId !== null) {
                    $this->applyBranchFilter($previousBankJobsQuery, 'branch_id', $user);
                }
                $previousBankJobs = $previousBankJobsQuery
                    ->where('status', 'completed')
                    ->where('payment_method', 'bank')
                    ->where(\DB::raw('COALESCE(end_time, created_at)'), '<=', $prevEnd->toDateTimeString())
                    ->with('expense')->get();
                foreach ($previousBankJobs as $job) {
                    $expenseAmount = $job->expense ? round((float) ($job->expense->total_amount ?? 0), 2) : 0;
                    $openingBalance -= $expenseAmount;
                }
                // Cash transfers up to previous date
                $prevCashTransfers = CashTransfer::query();
                $this->applyBranchFilter($prevCashTransfers, 'branch_id', $user);
                $prevCashTransfers->where('status', 'completed')
                    ->where('created_at', '<=', $previousDate->format('Y-m-d').' 23:59:59');
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
                        $endOfPrev = $previousDate->format('Y-m-d').' 23:59:59';
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
                'dateTime' => $previousDate->format('d/m/y').' Time '.$openingTime,
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
                $commissionPct = 0;
                if ($job->worker_user_id && $job->workerUser) {
                    $commissionPct = (float) ($job->workerUser->commission ?? 0);
                } elseif ($job->worker && $job->worker->commission) {
                    $commissionPct = (float) $job->worker->commission;
                }
                $jobPrice = (float) ($job->price ?? 0);
                $additionalSum = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                $totalJobPrice = $jobPrice + (float) $additionalSum;
                $commissionAmount = $commissionPct > 0 ? round(($totalJobPrice * $commissionPct) / 100, 2) : 0;
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
                $dateTime = $dt ? Carbon::parse($dt)->format('d/m/y').' time '.$dt->format('h:i A') : '-';
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
                        $totalTime = $hours.'h '.$minutes.'m';
                    } else {
                        $totalTime = $minutes.'m';
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
                    'paymentMethod' => $job->payment_method ?? 'cash', // so frontend can set paymentType correctly
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
                    ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);
                $cashTransfers = $cashTransferQuery->orderBy('created_at', 'asc')
                    ->get();
                $totalCashTransfer = (float) $cashTransfers->sum('amount');

                // Add cash transfers as separate rows (before shop expenses)
                foreach ($cashTransfers as $transfer) {
                    $transferDate = $transfer->created_at;
                    $transferDateTime = $transferDate ? Carbon::parse($transferDate)->format('d/m/y').' time '.$transferDate->format('h:i A') : '-';
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
                        'vehicle' => 'Cash Transfer: '.($toUserName ?: 'Admin'),
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
                    $expDateTime = $expDate ? Carbon::parse($expDate)->format('d/m/y').' time '.($shopExp->created_at ? $shopExp->created_at->format('h:i A') : '12:00AM') : '-';
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
                        'vehicle' => 'Shop Expense: '.($shopExp->category ?: 'N/A'),
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
                    $bankTransferQuery->whereHas('user', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                } elseif ($user->role !== 'admin') {
                    // Non-admin users without branch should see nothing
                    $bankTransferQuery->whereRaw('1 = 0');
                }
                $bankTransferQuery->where('status', 'approved')
                    ->where(function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('approved_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
                            ->orWhere(function ($q2) use ($dateFrom, $dateTo) {
                                $q2->whereNull('approved_at')
                                    ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);
                            });
                    });
                $bankTransfers = $bankTransferQuery->orderByRaw('COALESCE(approved_at, created_at) ASC')
                    ->get();
                $totalBankTransfer = (float) $bankTransfers->sum('amount');

                // Add bank transfers as separate rows
                foreach ($bankTransfers as $transfer) {
                    $transferDate = $transfer->approved_at ?: $transfer->created_at;
                    $transferDateTime = $transferDate ? Carbon::parse($transferDate)->format('d/m/y').' time '.Carbon::parse($transferDate)->format('h:i A') : '-';
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
            usort($otherRows, function ($a, $b) {
                // Parse dateTime string to compare
                $dateTimeA = $a['dateTime'] ?? '';
                $dateTimeB = $b['dateTime'] ?? '';

                if (empty($dateTimeA) && empty($dateTimeB)) {
                    return 0;
                }
                if (empty($dateTimeA)) {
                    return 1;
                }
                if (empty($dateTimeB)) {
                    return -1;
                }

                // Extract date and time from format "d/m/y time h:i A" or "d/m/y Time h:i A"
                $parseDateTime = function ($dtStr) {
                    if (preg_match('/(\d{2}\/\d{2}\/\d{2})\s+(?:time|Time)\s+(\d{1,2}):(\d{2})\s+(AM|PM)/i', $dtStr, $matches)) {
                        $date = $matches[1];
                        $hour = (int) $matches[2];
                        $minute = (int) $matches[3];
                        $ampm = strtoupper($matches[4]);

                        if ($ampm === 'PM' && $hour !== 12) {
                            $hour += 12;
                        }
                        if ($ampm === 'AM' && $hour === 12) {
                            $hour = 0;
                        }

                        // Parse date: d/m/y
                        [$d, $m, $y] = explode('/', $date);
                        $year = 2000 + (int) $y; // Assuming 20xx

                        return Carbon::create($year, $m, $d, $hour, $minute, 0);
                    }

                    return null;
                };

                $dtA = $parseDateTime($dateTimeA);
                $dtB = $parseDateTime($dateTimeB);

                if (! $dtA && ! $dtB) {
                    return 0;
                }
                if (! $dtA) {
                    return 1;
                }
                if (! $dtB) {
                    return -1;
                }

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
                    'totalDistinctVehiclesServiced' => $totalDistinctVehiclesServiced,
                    'totalDistinctWorkersServiced' => $totalDistinctWorkersServiced,
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Daily report data error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->only(['date_from', 'date_to', 'payment']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Report load failed: '.$e->getMessage(),
                'rows' => [],
                'customers' => [],
                'workers' => [],
                'users' => [],
                'totals' => [],
            ], 500);
        }
    }

    /**
     * Download daily report as PDF (ledger format). Query: date, customer (vehicle_no), worker (worker_name)
     */
    public function dailyReportPdf(Request $request)
    {
        // Support both single date and date range
        $dateFrom = $request->get('date_from') ?: $request->get('date');
        $dateTo = $request->get('date_to') ?: $request->get('date');

        if (! $dateFrom || ! $dateTo) {
            $request->validate(['date' => 'required|date']);
            $dateFrom = $dateTo = $request->date;
        } else {
            $request->validate([
                'date_from' => 'required|date',
                'date_to' => 'required|date',
            ]);
        }

        $customerFilter = $request->get('customer');
        $workerFilter = $request->get('worker');
        $userFilter = $request->get('user');
        $paymentFilter = $request->get('payment');

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $branch = $branchId ? \App\Models\Branch::find($branchId) : null;
        $branchName = ($user->role === 'admin' && ! $branchId) ? 'All Branches' : ($branch ? $branch->branch_name : 'All');

        $baseClosure = function ($q) use ($user, $dateFrom, $dateTo, $paymentFilter, $branchId) {
            if ($branchId !== null) {
                $this->applyBranchFilter($q, 'branch_id', $user);
            }
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

        $query = CarWashJob::where($baseClosure)->with(['worker', 'workerUser', 'expense', 'bank', 'bankAccount.bank', 'customer', 'customerCar']);
        if ($customerFilter !== null && $customerFilter !== '') {
            $query->whereHas('customerCar', function ($qb) use ($customerFilter) {
                $qb->where('plate_number', $customerFilter);
            });
        }
        if ($workerFilter !== null && $workerFilter !== '') {
            $query->where('worker_user_id', (int) $workerFilter);
        }
        if ($userFilter !== null && $userFilter !== '') {
            $query->where('user_id', (int) $userFilter);
        }
        $jobs = $query->orderBy('end_time', 'asc')->orderBy('created_at', 'asc')->get();

        $dateCarbon = Carbon::parse($dateFrom);
        $previousDate = $dateCarbon->copy()->subDay();

        // Get last transaction time from previous date
        $lastTransactionQuery = function ($q) use ($user, $previousDate, $branchId) {
            if ($branchId !== null) {
                $this->applyBranchFilter($q, 'branch_id', $user);
            }
            $q->where('status', 'completed');
            $prevDateStr = $previousDate->format('Y-m-d');
            $q->whereRaw('DATE(COALESCE(end_time, created_at)) = ?', [$prevDateStr]);
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
            'dateTime' => $previousDate->format('d/m/y').' Time '.$openingTime,
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
            $commissionPct = 0;
            if ($job->worker_user_id && $job->workerUser) {
                $commissionPct = (float) ($job->workerUser->commission ?? 0);
            } elseif ($job->worker && $job->worker->commission) {
                $commissionPct = (float) $job->worker->commission;
            }
            $jobPrice = (float) ($job->price ?? 0);
            $additionalSum = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
            $totalJobPrice = $jobPrice + (float) $additionalSum;
            $commissionAmount = $commissionPct > 0 ? round(($totalJobPrice * $commissionPct) / 100, 2) : 0;
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
            $dateTime = $dt ? Carbon::parse($dt)->format('d/m/y').' time '.$dt->format('h:i A') : '-';
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
                $label = trim($bn.($t ? ' - '.$t : '').($n ? ' ('.$n.')' : ''));
                $bankName = ($label === '' || (! $t && ! $n)) ? ($bn.' — #'.$b->id) : $label;
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
            ? $dateFromFormatted.'-'.$currentTime
            : $dateFromFormatted.'_to_'.$dateToFormatted.'-'.$currentTime;

        // Check if inline display is requested (for WhatsApp links)
        $inline = $request->get('inline', false);
        if ($inline) {
            return $pdf->stream('elite-car-wash-daily-Report-'.$dateForFilename.'.pdf');
        }

        return $pdf->download('elite-car-wash-daily-Report-'.$dateForFilename.'.pdf');
    }
}
