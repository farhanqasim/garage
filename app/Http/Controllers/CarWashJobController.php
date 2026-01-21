<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashJob;
use App\Models\CarWashWorker;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CarWashJobController extends Controller
{
    /**
     * Get all jobs for the current user's branch
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;
        
        $query = CarWashJob::where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });

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
        $branchId = $user->branches ? $user->branches->id : null;
        
        $jobs = CarWashJob::where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })
        ->active()
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
        $branchId = $user->branches ? $user->branches->id : null;
        
        $job = CarWashJob::where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })
        ->with(['worker', 'inspection', 'expense'])
        ->findOrFail($id);
        
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
        $branchName = $user->branches ? $user->branches->branch_name : 'Guest';
        
        return view('car-wash-job-detail', compact('jobData', 'userName', 'branchName'));
    }

    /**
     * Get completed jobs (today or all)
     */
    public function completedJobs(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;
        
        $query = CarWashJob::where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->completed();

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
        $branchId = $user->branches ? $user->branches->id : null;

        $job = CarWashJob::create([
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
        
        // Check permission
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;
        
        if ($job->branch_id !== null && $job->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to complete this job'
            ], 403);
        }

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
     * Update a job
     */
    public function update(Request $request, $id)
    {
        $job = CarWashJob::findOrFail($id);
        
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;
        
        if ($job->branch_id !== null && $job->branch_id !== $branchId) {
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
        $branchId = $user->branches ? $user->branches->id : null;
        
        if ($job->branch_id !== null && $job->branch_id !== $branchId) {
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
        $branchId = $user->branches ? $user->branches->id : null;
        
        if ($job->branch_id !== null && $job->branch_id !== $branchId) {
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
        $branchId = $user->branches ? $user->branches->id : null;
        
        $todayJobs = CarWashJob::where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })
        ->today()
        ->completed()
        ->get();

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
}
