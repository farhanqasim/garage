<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashServiceController extends Controller
{
    use HasBranchAccess;
    /**
     * Get all services for the current user's branch
     */
    public function index()
    {
        $user = Auth::user();
        $query = CarWashService::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $services = $query->where('status', true)->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'services' => $services
        ]);
    }

    /**
     * Show rate list page (A4 print / PDF view)
     */
    public function rateList()
    {
        $user = Auth::user();
        $query = CarWashService::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $services = $query->where('status', true)->orderBy('created_at', 'desc')->get();
        return view('car-wash-services-rate-list', compact('services'));
    }


    /**
     * Store a new service
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'additional_prices' => 'nullable|array',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'color_value' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $service = CarWashService::create([
            'branch_id' => $branchId,
            'label' => strtoupper($request->label),
            'base_price' => $request->base_price,
            'additional_prices' => $request->additional_prices ?? [],
            'icon' => $request->icon ?? 'car',
            'color' => $request->color ?? 'bg-blue-600',
            'color_value' => $request->color_value ?? '#3b82f6',
            'is_default' => false,
            'status' => true,
        ]);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service created successfully',
                'service' => $service
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Service created successfully!');
    }

    /**
     * Update a service
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'additional_prices' => 'nullable|array',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'color_value' => 'nullable|string|max:20',
        ]);

        $service = CarWashService::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessResourceBranch($service, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this service'
            ], 403);
        }

        $service->update([
            'label' => strtoupper($request->label),
            'base_price' => $request->base_price,
            'additional_prices' => $request->additional_prices ?? [],
            'icon' => $request->icon ?? $service->icon,
            'color' => $request->color ?? $service->color,
            'color_value' => $request->color_value ?? $service->color_value,
        ]);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully',
                'service' => $service
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Service updated successfully!');
    }

    /**
     * Delete a service
     */
    public function destroy(Request $request, $id)
    {
        $service = CarWashService::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessResourceBranch($service, $user)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this service'
                ], 403);
            }
            return redirect()->route('car.wash')->with('error', 'You do not have permission to delete this service');
        }

        // Don't allow deletion of default services, just mark as inactive
        if ($service->is_default) {
            $service->update(['status' => false]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Default service deactivated successfully'
                ]);
            }
            return redirect()->route('car.wash')->with('success', 'Default service deactivated successfully!');
        }

        $service->delete();

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);
        }

        return redirect()->route('car.wash')->with('success', 'Service deleted successfully!');
    }

    /**
     * Toggle service status
     */
    public function toggleStatus($id)
    {
        $service = CarWashService::findOrFail($id);
        $user = Auth::user();

        if (!$this->canAccessResourceBranch($service, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this service'
            ], 403);
        }

        $service->update(['status' => !$service->status]);

        return response()->json([
            'success' => true,
            'message' => 'Service status updated successfully',
            'service' => $service
        ]);
    }
}
