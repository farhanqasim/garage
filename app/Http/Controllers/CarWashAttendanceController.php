<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashAttendance;
use App\Models\CarWashWorker;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashAttendanceController extends Controller
{
    use HasBranchAccess;

    /**
     * List employees for attendance dropdown: same as admin "All Employees" table.
     * Car Wash Workers + All Users (role user/manager/salesman/purchaser) so MUHAMMAD QASIM, WASEEM SALEEM etc. show in search.
     */
    public function employees()
    {
        $user = Auth::user();

        $query = CarWashWorker::query();
        $this->applyBranchFilter($query, 'branch_id', $user);
        $workers = $query->where('status', true)->orderBy('name', 'asc')->get(['id', 'name', 'branch_id'])
            ->map(fn ($w) => ['id' => 'worker_' . $w->id, 'employeeId' => (string) $w->id, 'name' => $w->name, 'type' => 'worker']);

        $users = User::whereIn('role', ['user', 'manager', 'salesman', 'purchaser'])
            ->orderBy('name', 'asc')
            ->get(['id', 'name'])
            ->map(fn ($u) => ['id' => 'user_' . $u->id, 'employeeId' => (string) $u->id, 'name' => $u->name, 'type' => 'user']);

        $employees = $workers->concat($users)->sortBy('name')->values()->unique('id')->values()->all();
        return response()->json(['success' => true, 'employees' => $employees]);
    }

    /**
     * Store attendance: photo + location + metadata.
     * Accepts type 'worker' (employeeId = worker id) or 'user' (employeeId = user id).
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'nullable|in:worker,user',
            'employeeId' => 'required',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'accuracy' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:1000',
            'mapsLink' => 'nullable|string|max:500',
            'capturedAt' => 'nullable|string',
            'isMockLocationDetected' => 'nullable|boolean',
            'deviceInfo' => 'nullable|array',
        ]);

        $user = Auth::user();
        $type = $request->input('type', 'worker');
        $employeeId = (int) $request->employeeId;
        $workerId = null;
        $attendedUserId = null;
        $employeeName = '';
        $branchId = $this->getUserBranchId($user);

        if ($type === 'user') {
            $attendee = \App\Models\User::find($employeeId);
            if (!$attendee) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            $canAccess = $user->role === 'admin' || $attendee->id === $user->id
                || ($attendee->branches && $attendee->branches->id == $branchId)
                || $attendee->assignedBranches()->where('branch_id', $branchId)->exists();
            if (!$canAccess) {
                return response()->json(['success' => false, 'message' => 'User not in your branch.'], 403);
            }
            $attendedUserId = $attendee->id;
            $employeeName = $attendee->name;
        } else {
            $worker = CarWashWorker::findOrFail($employeeId);
            if (!$this->canAccessResourceBranch($worker, $user)) {
                return response()->json(['success' => false, 'message' => 'Worker not in your branch.'], 403);
            }
            $workerId = $worker->id;
            $employeeName = $worker->name;
            $branchId = $branchId ?? $worker->branch_id;
        }

        if (!function_exists('saveSingleFile')) {
            require base_path('app/Helper/helper.php');
        }

        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = saveSingleFile($request->file('photo'), 'attendance_photos');
        }

        $capturedAt = $request->capturedAt ? \Carbon\Carbon::parse($request->capturedAt) : now();

        $attendance = CarWashAttendance::create([
            'worker_id' => $workerId,
            'attended_user_id' => $attendedUserId,
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'captured_photo' => $photoPath,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'accuracy' => $request->accuracy,
            'address' => $request->address,
            'maps_link' => $request->mapsLink,
            'captured_at' => $capturedAt,
            'device_info' => $request->deviceInfo,
            'is_mock_location_detected' => (bool) $request->isMockLocationDetected,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully.',
            'attendance' => [
                'id' => $attendance->id,
                'employeeId' => $workerId ?? $attendedUserId,
                'employeeName' => $employeeName,
                'capturedPhoto' => $attendance->captured_photo ? asset($attendance->captured_photo) : null,
                'lat' => $attendance->lat,
                'lng' => $attendance->lng,
                'accuracy' => $attendance->accuracy,
                'address' => $attendance->address,
                'mapsLink' => $attendance->maps_link,
                'capturedAt' => $attendance->captured_at?->toIso8601String(),
            ],
        ]);
    }
}
