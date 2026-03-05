<?php

namespace App\Http\Controllers;

use App\Models\TaskReminder;
use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
use App\Models\CarWashJob;
use Illuminate\Http\Request;

class TaskReminderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $branchId = session('selected_branch_id') ?? auth()->user()->branch_id ?? null;

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
        $defaultBranchId = $branchId;

        // Sales invoices pending (for reminder cards – no purchase)
        $salesPending = collect([]);
        if (class_exists(Sale::class) && \Schema::hasTable('sales')) {
            $q = Sale::with(['customer', 'branch']);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $salesList = $q->orderByDesc('sale_date')->get();
            foreach ($salesList as $sale) {
                $remaining = (float) ($sale->grand_total ?? 0) - (float) ($sale->total_paid ?? 0);
                if ($remaining > 0) {
                    $customerName = 'N/A';
                    if ($sale->customer && isset($sale->customer->names) && is_array($sale->customer->names) && count($sale->customer->names) > 0) {
                        $customerName = $sale->customer->names[0];
                    }
                    $salesPending->push((object)[
                        'id' => $sale->id,
                        'reference' => $sale->reference ?? 'INV #' . $sale->id,
                        'customer' => $customerName,
                        'grand_total' => $sale->grand_total,
                        'total_paid' => $sale->total_paid ?? 0,
                        'remaining' => round($remaining, 2),
                        'sale_date' => $sale->sale_date,
                        'link' => route('sales.show', $sale->id),
                    ]);
                }
            }
        }

        // Car wash – job / payment pending
        $carWashPending = collect([]);
        if (class_exists(CarWashJob::class) && \Schema::hasTable('car_wash_jobs')) {
            $q = CarWashJob::with(['customer', 'customerCar'])->orderByDesc('start_time');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $jobs = $q->get();
            foreach ($jobs as $job) {
                $status = $job->status ?? '';
                $isActive = $status === 'active';
                $isCancelled = $status === 'cancelled';
                if (!$isActive && !$isCancelled) continue;
                    $customerName = 'Walk-in';
                    if ($job->customer && isset($job->customer->names) && is_array($job->customer->names) && count($job->customer->names) > 0) {
                        $customerName = $job->customer->names[0];
                    }
                    $vehicleNo = '—';
                    if ($job->customerCar && !empty($job->customerCar->vehicle_no)) {
                        $vehicleNo = $job->customerCar->vehicle_no;
                    }
                    $carWashPending->push((object)[
                        'id' => $job->id,
                        'service_name' => $job->service_name ?? 'Job',
                        'price' => $job->price ?? 0,
                        'status' => $job->status,
                        'start_time' => $job->start_time,
                        'customer_name' => $customerName,
                        'vehicle_no' => $vehicleNo,
                        'link' => route('car.wash') . '?job=' . $job->id,
                    ]);
            }
        }

        // Combined list for "All Reminders" cards (sales + car wash + tasks; no purchase)
        $allReminders = collect([]);
        foreach ($salesPending as $s) {
            $allReminders->push((object)[
                'type' => 'sales_invoice',
                'sort_at' => $s->sale_date ?? now(),
                'reference' => $s->reference,
                'subtitle' => $s->customer,
                'amount' => $s->remaining,
                'meta' => $s->sale_date ? \Carbon\Carbon::parse($s->sale_date)->format('d M Y') : '—',
                'link' => $s->link,
                'badge' => 'Recovery',
                'badge_class' => 'warning',
            ]);
        }
        foreach ($carWashPending as $j) {
            $allReminders->push((object)[
                'type' => 'car_wash',
                'sort_at' => $j->start_time ?? now(),
                'reference' => $j->service_name,
                'subtitle' => $j->customer_name . ' — ' . $j->vehicle_no,
                'amount' => $j->price,
                'meta' => $j->start_time ? \Carbon\Carbon::parse($j->start_time)->format('d M Y H:i') : '—',
                'link' => $j->link,
                'badge' => strtoupper($j->status ?? '—'),
                'badge_class' => ($j->status ?? '') === 'active' ? 'info' : 'warning',
            ]);
        }
        foreach ($tasks as $t) {
            $allReminders->push((object)[
                'type' => 'task',
                'sort_at' => $t->created_at ?? now(),
                'reference' => $t->title,
                'subtitle' => $t->description ? \Str::limit($t->description, 60) : '—',
                'amount' => null,
                'meta' => ($t->branch->branch_name ?? '—') . ' · ' . ($t->assignee ?? '—'),
                'link' => null,
                'badge' => $t->status,
                'badge_class' => $t->status === 'Completed' ? 'success' : ($t->status === 'In-Progress' ? 'info' : ($t->status === 'Cancelled' ? 'danger' : ($t->status === 'On Hold' ? 'secondary' : 'warning'))),
                'priority' => $t->priority ?? 'Normal',
                'task' => $t,
            ]);
        }
        $allReminders = $allReminders->sortByDesc(function ($r) {
            $s = $r->sort_at;
            return $s instanceof \Carbon\Carbon ? $s->timestamp : (is_numeric($s) ? $s : strtotime($s));
        })->values();

        return view('task-reminder', compact('tasks', 'branches', 'workers', 'pendingCount', 'defaultBranchId', 'salesPending', 'carWashPending', 'allReminders'));
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

    public function updateStatus(Request $request, $id)
    {
        $task = TaskReminder::findOrFail($id);
        $status = $request->input('status', '');
        $allowed = ['Pending', 'In-Progress', 'Completed', 'Cancelled', 'On Hold'];
        if (!in_array($status, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 422);
        }
        $task->update(['status' => $status]);
        return response()->json(['success' => true, 'status' => $task->status]);
    }

    public function destroy($id)
    {
        TaskReminder::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
