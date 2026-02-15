<?php

namespace App\Http\Controllers;

use App\Models\TaskReminder;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class TaskReminderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tasks = TaskReminder::with('branch')
            ->orderByDesc('created_at')
            ->get();
        $branches = Branch::where('status', 'active')->orderBy('branch_name')->get();
        $workers = User::with('branch')
            ->orderBy('name')
            ->get()
            ->filter(function ($u) {
                return $u->name && trim($u->name) !== '';
            })
            ->values();
        $pendingCount = $tasks->whereNotIn('status', ['Completed'])->count();
        $defaultBranchId = session('selected_branch_id') ?? auth()->user()->branch_id ?? null;
        return view('task-reminder', compact('tasks', 'branches', 'workers', 'pendingCount', 'defaultBranchId'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge(['branch_id' => $request->input('branch_id') ?: null]);
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'task_audio' => 'nullable|string|max:10485760',
                'task_image' => 'nullable|string|max:10485760',
                'branch_id' => 'nullable|exists:branches,id',
                'assignee' => 'nullable|string|max:100',
                'priority' => 'nullable|in:Low,Normal,High,Critical',
            ]);
            TaskReminder::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'task_audio' => $request->task_audio ?: null,
                'task_image' => $request->task_image ?: null,
                'branch_id' => $request->branch_id ?: null,
                'assignee' => $request->assignee,
                'priority' => $request->priority ?? 'Normal',
                'status' => 'Pending',
            ]);
            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Save failed: ' . $e->getMessage()], 500);
        }
    }

    public function addResponse(Request $request, $id)
    {
        $task = TaskReminder::findOrFail($id);
        $text = $request->input('text', '');
        $photo = $request->input('photo'); // base64 data URL or null
        $audio = $request->input('audio'); // base64 data URL or null
        $location = $request->input('location'); // JSON string {lat,lng} or null
        $locationArr = null;
        if ($location && is_string($location)) {
            $decoded = json_decode($location, true);
            if (is_array($decoded) && isset($decoded['lat'], $decoded['lng'])) {
                $locationArr = $decoded;
            }
        }
        $responses = $task->responses ?? [];
        $responses[] = [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'User',
            'text' => $text,
            'photo' => $photo ?: null,
            'audio' => $audio ?: null,
            'location' => $locationArr,
            'attachment_type' => null,
            'attachment_value' => null,
            'created_at' => now()->toIso8601String(),
        ];
        $task->update([
            'responses' => $responses,
            'status' => 'In-Progress',
        ]);
        return response()->json(['success' => true, 'task' => $task->fresh()]);
    }

    public function complete($id)
    {
        $task = TaskReminder::findOrFail($id);
        $task->update(['status' => 'Completed']);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        TaskReminder::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
