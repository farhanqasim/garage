<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashWorker;
use App\Models\CarWashJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashWorkerController extends Controller
{
    use HasBranchAccess;
    
    /**
     * Get all workers for the current user's branch
     */
    public function index()
    {
        $user = Auth::user();

        $workersQuery = CarWashWorker::query();
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $workers = $workersQuery->where('status', true)->orderBy('name', 'asc')->get()
        ->map(function($worker) use ($user) {
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
                'additionalMobiles' => $worker->additional_mobiles ?? [],
                'father_name' => $worker->father_name,
                'fatherName' => $worker->father_name,
                'father_mobile' => $worker->father_mobile,
                'fatherMobile' => $worker->father_mobile,
                'father_additional_mobiles' => $worker->father_additional_mobiles ?? [],
                'fatherAdditionalMobiles' => $worker->father_additional_mobiles ?? [],
                'location' => $worker->location,
                'commission' => $worker->commission,
                'id_card_front' => $worker->id_card_front ? asset($worker->id_card_front) : null,
                'idCardFront' => $worker->id_card_front ? asset($worker->id_card_front) : null,
                'id_card_back' => $worker->id_card_back ? asset($worker->id_card_back) : null,
                'idCardBack' => $worker->id_card_back ? asset($worker->id_card_back) : null,
                'father_card_front' => $worker->father_card_front ? asset($worker->father_card_front) : null,
                'fatherCardFront' => $worker->father_card_front ? asset($worker->father_card_front) : null,
                'father_card_back' => $worker->father_card_back ? asset($worker->father_card_back) : null,
                'fatherCardBack' => $worker->father_card_back ? asset($worker->father_card_back) : null,
                'status' => $worker->status,
                'completedJobs' => $worker->completedJobsCount(),
                'totalEarnings' => $worker->totalEarnings(),
                'daily_jobs_count' => $dailyJobsCount,
                'dailyJobsCount' => $dailyJobsCount,
                'daily_commission' => round($dailyCommission, 2),
                'dailyCommission' => round($dailyCommission, 2),
            ];
        });
        
        return response()->json([
            'success' => true,
            'workers' => $workers
        ]);
    }

    /**
     * Store a new worker
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'additional_mobiles' => 'nullable|array',
            'additional_mobiles.*.name' => 'nullable|string|max:255',
            'additional_mobiles.*.mobile' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:255',
            'father_mobile' => 'nullable|string|max:50',
            'father_additional_mobiles' => 'nullable|array',
            'father_additional_mobiles.*.name' => 'nullable|string|max:255',
            'father_additional_mobiles.*.mobile' => 'nullable|string|max:50',
            'location' => 'nullable|string',
            'commission' => 'nullable|integer|min:0|max:100',
            'id_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'father_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'father_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        // Handle image uploads using saveSingleFile helper
        $idCardFront = $request->hasFile('id_card_front') ? saveSingleFile($request->file('id_card_front'), 'workers/id_cards') : null;
        $idCardBack = $request->hasFile('id_card_back') ? saveSingleFile($request->file('id_card_back'), 'workers/id_cards') : null;
        $fatherCardFront = $request->hasFile('father_card_front') ? saveSingleFile($request->file('father_card_front'), 'workers/father_cards') : null;
        $fatherCardBack = $request->hasFile('father_card_back') ? saveSingleFile($request->file('father_card_back'), 'workers/father_cards') : null;

        // Clean and prepare additional mobiles array - filter out empty entries
        $additionalMobiles = [];
        if ($request->has('additional_mobiles') && is_array($request->additional_mobiles)) {
            foreach ($request->additional_mobiles as $mobile) {
                if (isset($mobile['mobile']) && !empty(trim($mobile['mobile']))) {
                    $additionalMobiles[] = [
                        'name' => isset($mobile['name']) ? trim($mobile['name']) : '',
                        'mobile' => trim($mobile['mobile'])
                    ];
                }
            }
        }
        
        // Clean and prepare father additional mobiles array
        $fatherAdditionalMobiles = [];
        if ($request->has('father_additional_mobiles') && is_array($request->father_additional_mobiles)) {
            foreach ($request->father_additional_mobiles as $mobile) {
                if (isset($mobile['mobile']) && !empty(trim($mobile['mobile']))) {
                    $fatherAdditionalMobiles[] = [
                        'name' => isset($mobile['name']) ? trim($mobile['name']) : '',
                        'mobile' => trim($mobile['mobile'])
                    ];
                }
            }
        }

        $worker = CarWashWorker::create([
            'branch_id' => $branchId,
            'name' => strtoupper($request->name),
            'mobile' => $request->mobile ? trim($request->mobile) : null,
            'additional_mobiles' => $additionalMobiles,
            'father_name' => $request->father_name && trim($request->father_name) !== '' ? strtoupper(trim($request->father_name)) : null,
            'father_mobile' => $request->father_mobile && trim($request->father_mobile) !== '' ? trim($request->father_mobile) : null,
            'father_additional_mobiles' => $fatherAdditionalMobiles,
            'location' => $request->location,
            'commission' => $request->commission ?? 0,
            'id_card_front' => $idCardFront,
            'id_card_back' => $idCardBack,
            'father_card_front' => $fatherCardFront,
            'father_card_back' => $fatherCardBack,
            'status' => true,
        ]);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker created successfully',
                'worker' => [
                    'id' => $worker->id,
                    'name' => $worker->name,
                    'mobile' => $worker->mobile,
                    'additionalMobiles' => $worker->additional_mobiles ?? [],
                    'fatherName' => $worker->father_name,
                    'fatherMobile' => $worker->father_mobile,
                    'fatherAdditionalMobiles' => $worker->father_additional_mobiles ?? [],
                    'location' => $worker->location,
                    'commission' => $worker->commission,
                    'idCardFront' => $worker->id_card_front ? asset($worker->id_card_front) : null,
                    'idCardBack' => $worker->id_card_back ? asset($worker->id_card_back) : null,
                    'fatherCardFront' => $worker->father_card_front ? asset($worker->father_card_front) : null,
                    'fatherCardBack' => $worker->father_card_back ? asset($worker->father_card_back) : null,
                ]
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Worker created successfully!');
    }

    /**
     * Update a worker
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'additional_mobiles' => 'nullable|array',
            'additional_mobiles.*.name' => 'nullable|string|max:255',
            'additional_mobiles.*.mobile' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:255',
            'father_mobile' => 'nullable|string|max:50',
            'father_additional_mobiles' => 'nullable|array',
            'father_additional_mobiles.*.name' => 'nullable|string|max:255',
            'father_additional_mobiles.*.mobile' => 'nullable|string|max:50',
            'location' => 'nullable|string',
            'commission' => 'nullable|integer|min:0|max:100',
            'id_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'father_card_front' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'father_card_back' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $worker = CarWashWorker::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessResourceBranch($worker, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this worker'
            ], 403);
        }

        // Handle image updates using saveSingleFile helper - only update if new file is uploaded
        if ($request->hasFile('id_card_front')) {
            // Delete old image if exists
            if ($worker->id_card_front && file_exists(public_path($worker->id_card_front))) {
                @unlink(public_path($worker->id_card_front));
            }
            $worker->id_card_front = saveSingleFile($request->file('id_card_front'), 'workers/id_cards');
        }
        
        if ($request->hasFile('id_card_back')) {
            if ($worker->id_card_back && file_exists(public_path($worker->id_card_back))) {
                @unlink(public_path($worker->id_card_back));
            }
            $worker->id_card_back = saveSingleFile($request->file('id_card_back'), 'workers/id_cards');
        }
        
        if ($request->hasFile('father_card_front')) {
            if ($worker->father_card_front && file_exists(public_path($worker->father_card_front))) {
                @unlink(public_path($worker->father_card_front));
            }
            $worker->father_card_front = saveSingleFile($request->file('father_card_front'), 'workers/father_cards');
        }
        
        if ($request->hasFile('father_card_back')) {
            if ($worker->father_card_back && file_exists(public_path($worker->father_card_back))) {
                @unlink(public_path($worker->father_card_back));
            }
            $worker->father_card_back = saveSingleFile($request->file('father_card_back'), 'workers/father_cards');
        }

        // Clean and prepare additional mobiles array - filter out empty entries
        $additionalMobiles = $worker->additional_mobiles ?? [];
        if ($request->has('additional_mobiles') && is_array($request->additional_mobiles)) {
            $additionalMobiles = [];
            foreach ($request->additional_mobiles as $mobile) {
                if (isset($mobile['mobile']) && !empty(trim($mobile['mobile']))) {
                    $additionalMobiles[] = [
                        'name' => isset($mobile['name']) ? trim($mobile['name']) : '',
                        'mobile' => trim($mobile['mobile'])
                    ];
                }
            }
        }
        
        // Clean and prepare father additional mobiles array
        $fatherAdditionalMobiles = $worker->father_additional_mobiles ?? [];
        if ($request->has('father_additional_mobiles') && is_array($request->father_additional_mobiles)) {
            $fatherAdditionalMobiles = [];
            foreach ($request->father_additional_mobiles as $mobile) {
                if (isset($mobile['mobile']) && !empty(trim($mobile['mobile']))) {
                    $fatherAdditionalMobiles[] = [
                        'name' => isset($mobile['name']) ? trim($mobile['name']) : '',
                        'mobile' => trim($mobile['mobile'])
                    ];
                }
            }
        }

        // Update all fields - FormData always sends all fields
        // Convert empty strings to null, otherwise use provided values
        $updateData = [
            'name' => $request->name && trim($request->name) !== '' ? strtoupper(trim($request->name)) : $worker->name,
            'mobile' => $request->mobile && trim($request->mobile) !== '' ? trim($request->mobile) : null,
            'additional_mobiles' => $additionalMobiles,
            'father_name' => $request->father_name && trim($request->father_name) !== '' ? strtoupper(trim($request->father_name)) : null,
            'father_mobile' => $request->father_mobile && trim($request->father_mobile) !== '' ? trim($request->father_mobile) : null,
            'father_additional_mobiles' => $fatherAdditionalMobiles,
            'location' => $request->location && trim($request->location) !== '' ? trim($request->location) : null,
            'commission' => $request->commission ?? 0,
        ];
        
        $worker->update($updateData);

        // Refresh worker to get updated data
        $worker->refresh();
        
        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker updated successfully',
                'worker' => [
                    'id' => $worker->id,
                    'name' => $worker->name,
                    'mobile' => $worker->mobile,
                    'additionalMobiles' => $worker->additional_mobiles ?? [],
                    'fatherName' => $worker->father_name,
                    'fatherMobile' => $worker->father_mobile,
                    'fatherAdditionalMobiles' => $worker->father_additional_mobiles ?? [],
                    'location' => $worker->location,
                    'commission' => $worker->commission,
                    'idCardFront' => $worker->id_card_front ? asset($worker->id_card_front) : null,
                    'idCardBack' => $worker->id_card_back ? asset($worker->id_card_back) : null,
                    'fatherCardFront' => $worker->father_card_front ? asset($worker->father_card_front) : null,
                    'fatherCardBack' => $worker->father_card_back ? asset($worker->father_card_back) : null,
                ]
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Worker updated successfully!');
    }

    /**
     * Delete a worker
     */
    public function destroy($id)
    {
        $worker = CarWashWorker::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessResourceBranch($worker, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this worker'
            ], 403);
        }

        // Delete associated images
        if ($worker->id_card_front) {
            Storage::disk('public')->delete($worker->id_card_front);
        }
        if ($worker->id_card_back) {
            Storage::disk('public')->delete($worker->id_card_back);
        }
        if ($worker->father_card_front) {
            Storage::disk('public')->delete($worker->father_card_front);
        }
        if ($worker->father_card_back) {
            Storage::disk('public')->delete($worker->father_card_back);
        }

        $worker->delete();

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker deleted successfully'
            ]);
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
