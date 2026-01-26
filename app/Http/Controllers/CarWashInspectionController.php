<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashInspection;
use App\Models\CarWashJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashInspectionController extends Controller
{
    use HasBranchAccess;
    
    /**
     * Store or update inspection for a job
     */
    public function store(Request $request, $jobId)
    {
        $request->validate([
            'inspection_items' => 'required|array',
            'is_completed' => 'boolean',
        ]);

        $job = CarWashJob::findOrFail($jobId);
        $user = Auth::user();

        if (!$this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add inspection for this job'
            ], 403);
        }

        $branchId = $this->getUserBranchId($user);
        // Update or create inspection
        $inspection = CarWashInspection::updateOrCreate(
            ['job_id' => $jobId],
            [
                'branch_id' => $branchId,
                'inspection_items' => $request->inspection_items,
                'is_completed' => $request->is_completed ?? false,
                'completed_at' => ($request->is_completed ?? false) ? now() : null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Inspection saved successfully',
            'inspection' => [
                'id' => $inspection->id,
                'jobId' => $inspection->job_id,
                'inspectionItems' => $inspection->inspection_items,
                'isCompleted' => $inspection->is_completed,
                'completedAt' => $inspection->completed_at ? $inspection->completed_at->toISOString() : null,
            ]
        ]);
    }

    /**
     * Get inspection for a job
     */
    public function show($jobId)
    {
        $job = CarWashJob::findOrFail($jobId);
        $user = Auth::user();

        if (!$this->canAccessJobBranch($job, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this inspection'
            ], 403);
        }

        $inspection = CarWashInspection::where('job_id', $jobId)->first();

        if (!$inspection) {
            return response()->json([
                'success' => false,
                'message' => 'Inspection not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'inspection' => [
                'id' => $inspection->id,
                'jobId' => $inspection->job_id,
                'inspectionItems' => $inspection->inspection_items,
                'isCompleted' => $inspection->is_completed,
                'completedAt' => $inspection->completed_at ? $inspection->completed_at->toISOString() : null,
            ]
        ]);
    }
}
