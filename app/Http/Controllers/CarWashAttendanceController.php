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
            'attendanceType' => 'required|in:in,out',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'accuracy' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:1000',
            'mapsLink' => 'nullable|string|max:500',
            'capturedAt' => 'nullable|string',
            'isMockLocationDetected' => 'nullable|boolean',
            'deviceInfo' => 'nullable', // Accept string or array, we'll parse it
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
                || ($attendee->branch_id == $branchId)
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

        // Get captured time in Pakistan timezone (Asia/Karachi)
        // Frontend sends UTC time (ISO string), convert it to Pakistan timezone
        if ($request->capturedAt) {
            // Parse the UTC time from frontend (ISO string) and convert to Pakistan timezone
            $capturedAt = \Carbon\Carbon::parse($request->capturedAt)->setTimezone('Asia/Karachi');
        } else {
            // Use current time in Pakistan timezone
            $capturedAt = \Carbon\Carbon::now('Asia/Karachi');
        }

        // Parse deviceInfo if it's a JSON string
        $deviceInfo = $request->deviceInfo;
        if (is_string($deviceInfo)) {
            $deviceInfo = json_decode($deviceInfo, true);
        }
        if (!is_array($deviceInfo)) {
            $deviceInfo = [];
        }

        $attendance = CarWashAttendance::create([
            'worker_id' => $workerId,
            'attended_user_id' => $attendedUserId,
            'attendance_type' => $request->attendanceType,
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'captured_photo' => $photoPath,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'accuracy' => $request->accuracy,
            'address' => $request->address,
            'maps_link' => $request->mapsLink,
            'captured_at' => $capturedAt,
            'device_info' => $deviceInfo,
            'is_mock_location_detected' => (bool) $request->isMockLocationDetected,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully.',
            'attendance' => [
                'id' => $attendance->id,
                'employeeId' => $workerId ?? $attendedUserId,
                'employeeName' => $employeeName,
                'attendanceType' => $attendance->attendance_type,
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

    /**
     * Get attendance history for an employee
     */
    public function history(Request $request)
    {
        $request->validate([
            'employeeId' => 'required',
            'type' => 'required|in:worker,user',
        ]);

        $user = Auth::user();
        $type = $request->type;
        $employeeId = (int) $request->employeeId;

        $query = CarWashAttendance::query();

        if ($type === 'worker') {
            $query->where('worker_id', $employeeId);
        } else {
            $query->where('attended_user_id', $employeeId);
        }

        // Apply branch filter
        $branchId = $this->getUserBranchId($user);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $attendances = $query->orderBy('captured_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'type' => $att->attendance_type,
                    'time' => $att->captured_at?->toIso8601String(),
                    'location' => $att->lat && $att->lng ? ['lat' => $att->lat, 'lng' => $att->lng] : null,
                    'address' => $att->address,
                    'photo' => $att->captured_photo ? asset($att->captured_photo) : null,
                ];
            });

        return response()->json(['success' => true, 'attendances' => $attendances]);
    }

    /**
     * Get completed attendance (IN-OUT pairs) for table view
     */
    public function completed(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $query = CarWashAttendance::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Apply date filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('captured_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('captured_at', '<=', $request->date_to);
        }

        // Apply worker filter
        if ($request->has('worker_id') && $request->worker_id) {
            $query->where('worker_id', $request->worker_id);
        }

        // Apply user filter
        if ($request->has('user_id') && $request->user_id) {
            $query->where('attended_user_id', $request->user_id);
        }

        // Get all attendances ordered by date
        $allAttendances = $query->orderBy('captured_at', 'desc')
            ->with(['worker', 'attendedUser', 'user'])
            ->get();

        // Group by employee and find IN-OUT pairs
        $completed = [];
        $employeeGroups = [];

        foreach ($allAttendances as $att) {
            $empId = $att->worker_id ? 'worker_' . $att->worker_id : 'user_' . $att->attended_user_id;
            $empName = $att->worker ? $att->worker->name : ($att->attendedUser ? $att->attendedUser->name : 'Unknown');
            $empType = $att->worker_id ? 'worker' : 'user';

            if (!isset($employeeGroups[$empId])) {
                $employeeGroups[$empId] = [
                    'id' => $empId,
                    'name' => $empName,
                    'type' => $empType,
                    'attendances' => []
                ];
            }

            $employeeGroups[$empId]['attendances'][] = $att;
        }

        // Find completed pairs (IN followed by OUT) and also show incomplete IN records
        foreach ($employeeGroups as $empId => $empData) {
            $attendances = collect($empData['attendances'])->sortBy('captured_at')->values();
            
            for ($i = 0; $i < $attendances->count(); $i++) {
                $inAttendance = $attendances[$i];
                
                if ($inAttendance->attendance_type === 'in') {
                    // Find next OUT attendance
                    $outAttendance = null;
                    for ($j = $i + 1; $j < $attendances->count(); $j++) {
                        if ($attendances[$j]->attendance_type === 'out') {
                            $outAttendance = $attendances[$j];
                            break;
                        }
                    }

                    if ($outAttendance) {
                        // Calculate hours for completed pair
                        $hours = $inAttendance->captured_at->diffInHours($outAttendance->captured_at, true);
                        $minutes = $inAttendance->captured_at->diffInMinutes($outAttendance->captured_at) % 60;
                        
                        $completed[] = [
                            'id' => $inAttendance->id . '_' . $outAttendance->id,
                            'employeeId' => $empId,
                            'employeeName' => $empData['name'],
                            'employeeType' => $empData['type'],
                            'inTime' => $inAttendance->captured_at->toIso8601String(),
                            'outTime' => $outAttendance->captured_at->toIso8601String(),
                            'hours' => round($hours + ($minutes / 60), 2),
                            'inLocation' => $inAttendance->lat && $inAttendance->lng ? [
                                'lat' => $inAttendance->lat,
                                'lng' => $inAttendance->lng,
                                'address' => $inAttendance->address
                            ] : null,
                            'outLocation' => $outAttendance->lat && $outAttendance->lng ? [
                                'lat' => $outAttendance->lat,
                                'lng' => $outAttendance->lng,
                                'address' => $outAttendance->address
                            ] : null,
                            'inPhoto' => $inAttendance->captured_photo ? asset($inAttendance->captured_photo) : null,
                            'outPhoto' => $outAttendance->captured_photo ? asset($outAttendance->captured_photo) : null,
                            'date' => $inAttendance->captured_at->format('Y-m-d'),
                            'status' => 'completed'
                        ];
                    } else {
                        // Show incomplete IN record (no OUT yet)
                        $completed[] = [
                            'id' => $inAttendance->id . '_incomplete',
                            'employeeId' => $empId,
                            'employeeName' => $empData['name'],
                            'employeeType' => $empData['type'],
                            'inTime' => $inAttendance->captured_at->toIso8601String(),
                            'outTime' => null,
                            'hours' => null,
                            'inLocation' => $inAttendance->lat && $inAttendance->lng ? [
                                'lat' => $inAttendance->lat,
                                'lng' => $inAttendance->lng,
                                'address' => $inAttendance->address
                            ] : null,
                            'outLocation' => null,
                            'inPhoto' => $inAttendance->captured_photo ? asset($inAttendance->captured_photo) : null,
                            'outPhoto' => null,
                            'date' => $inAttendance->captured_at->format('Y-m-d'),
                            'status' => 'incomplete'
                        ];
                    }
                }
            }
        }

        // Sort by date descending
        usort($completed, function($a, $b) {
            return strcmp($b['inTime'], $a['inTime']);
        });

        return response()->json(['success' => true, 'completed' => $completed]);
    }

    /**
     * Show attendance history page
     */
    public function historyPage(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $query = CarWashAttendance::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Apply date filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('captured_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('captured_at', '<=', $request->date_to);
        }

        // Apply worker filter
        if ($request->has('worker_id') && $request->worker_id) {
            $query->where('worker_id', $request->worker_id);
        }

        // Apply user filter
        if ($request->has('user_id') && $request->user_id) {
            $query->where('attended_user_id', $request->user_id);
        }

        // Get all attendances ordered by date
        $allAttendances = $query->orderBy('captured_at', 'desc')
            ->with(['worker', 'attendedUser', 'user'])
            ->get();

        // Group by employee and find IN-OUT pairs
        $completed = [];
        $employeeGroups = [];

        foreach ($allAttendances as $att) {
            $empId = $att->worker_id ? 'worker_' . $att->worker_id : 'user_' . $att->attended_user_id;
            $empName = $att->worker ? $att->worker->name : ($att->attendedUser ? $att->attendedUser->name : 'Unknown');
            $empType = $att->worker_id ? 'worker' : 'user';

            if (!isset($employeeGroups[$empId])) {
                $employeeGroups[$empId] = [
                    'id' => $empId,
                    'name' => $empName,
                    'type' => $empType,
                    'attendances' => []
                ];
            }

            $employeeGroups[$empId]['attendances'][] = $att;
        }

        // Find completed pairs (IN followed by OUT) and also show incomplete IN records
        foreach ($employeeGroups as $empId => $empData) {
            $attendances = collect($empData['attendances'])->sortBy('captured_at')->values();
            
            for ($i = 0; $i < $attendances->count(); $i++) {
                $inAttendance = $attendances[$i];
                
                if ($inAttendance->attendance_type === 'in') {
                    // Find next OUT attendance
                    $outAttendance = null;
                    for ($j = $i + 1; $j < $attendances->count(); $j++) {
                        if ($attendances[$j]->attendance_type === 'out') {
                            $outAttendance = $attendances[$j];
                            break;
                        }
                    }

                    if ($outAttendance) {
                        // Calculate hours for completed pair
                        $hours = $inAttendance->captured_at->diffInHours($outAttendance->captured_at, true);
                        $minutes = $inAttendance->captured_at->diffInMinutes($outAttendance->captured_at) % 60;
                        
                        $completed[] = [
                            'id' => $inAttendance->id . '_' . $outAttendance->id,
                            'employeeId' => $empId,
                            'employeeName' => $empData['name'],
                            'employeeType' => $empData['type'],
                            'inTime' => $inAttendance->captured_at,
                            'outTime' => $outAttendance->captured_at,
                            'hours' => round($hours + ($minutes / 60), 2),
                            'inLocation' => $inAttendance->lat && $inAttendance->lng ? [
                                'lat' => $inAttendance->lat,
                                'lng' => $inAttendance->lng,
                                'address' => $inAttendance->address
                            ] : null,
                            'outLocation' => $outAttendance->lat && $outAttendance->lng ? [
                                'lat' => $outAttendance->lat,
                                'lng' => $outAttendance->lng,
                                'address' => $outAttendance->address
                            ] : null,
                            'inPhoto' => $inAttendance->captured_photo,
                            'outPhoto' => $outAttendance->captured_photo,
                            'inAttendance' => $inAttendance,
                            'outAttendance' => $outAttendance,
                            'date' => $inAttendance->captured_at->format('Y-m-d'),
                            'status' => 'completed'
                        ];
                    } else {
                        // Show incomplete IN record (no OUT yet)
                        $completed[] = [
                            'id' => $inAttendance->id . '_incomplete',
                            'employeeId' => $empId,
                            'employeeName' => $empData['name'],
                            'employeeType' => $empData['type'],
                            'inTime' => $inAttendance->captured_at,
                            'outTime' => null,
                            'hours' => null,
                            'inLocation' => $inAttendance->lat && $inAttendance->lng ? [
                                'lat' => $inAttendance->lat,
                                'lng' => $inAttendance->lng,
                                'address' => $inAttendance->address
                            ] : null,
                            'outLocation' => null,
                            'inPhoto' => $inAttendance->captured_photo,
                            'outPhoto' => null,
                            'inAttendance' => $inAttendance,
                            'outAttendance' => null,
                            'date' => $inAttendance->captured_at->format('Y-m-d'),
                            'status' => 'incomplete'
                        ];
                    }
                }
            }
        }

        // Sort by date descending
        usort($completed, function($a, $b) {
            return $b['inTime']->timestamp - $a['inTime']->timestamp;
        });

        // Get workers and users for filters
        $workersQuery = CarWashWorker::query();
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $workers = $workersQuery->where('status', true)->orderBy('name', 'asc')->get(['id', 'name']);

        $users = User::whereIn('role', ['user', 'manager', 'salesman', 'purchaser'])
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('attendance-history', [
            'completed' => $completed,
            'workers' => $workers,
            'users' => $users,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
            'filters' => [
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
                'worker_id' => $request->get('worker_id', ''),
                'user_id' => $request->get('user_id', ''),
            ]
        ]);
    }
}
