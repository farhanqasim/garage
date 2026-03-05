<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CarWashJob;
use App\Models\TaskReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemindersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard of all reminders: invoices pending, payments recovery, car wash jobs, task reminders.
     */
    public function index(Request $request)
    {
        $branchId = session('selected_branch_id') ?? auth()->user()->branch_id ?? null;

        // 1) Sales invoices pending (unpaid or partial)
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
                    $salesPending->push((object)[
                        'id' => $sale->id,
                        'reference' => $sale->reference ?? 'INV #' . $sale->id,
                        'customer' => $sale->customer ? ($sale->customer->name ?? 'N/A') : 'N/A',
                        'grand_total' => $sale->grand_total,
                        'total_paid' => $sale->total_paid ?? 0,
                        'remaining' => round($remaining, 2),
                        'sale_date' => $sale->sale_date,
                        'link' => route('sales.show', $sale->id),
                    ]);
                }
            }
        }

        // 2) Purchases pending (payment recovery / payable)
        $purchasesPending = collect([]);
        if (class_exists(Purchase::class) && \Schema::hasTable('purchases')) {
            $q = Purchase::with(['supplier', 'branch']);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $purchasesList = $q->orderByDesc('purchase_date')->get();
            foreach ($purchasesList as $p) {
                $remaining = (float) ($p->grand_total ?? 0) - (float) ($p->total_paid ?? 0);
                if ($remaining > 0) {
                    $purchasesPending->push((object)[
                        'id' => $p->id,
                        'reference' => $p->reference ?? $p->invoice_no ?? 'PO #' . $p->id,
                        'supplier' => $p->supplier ? ($p->supplier->name ?? 'N/A') : 'N/A',
                        'grand_total' => $p->grand_total,
                        'remaining' => round($remaining, 2),
                        'purchase_date' => $p->purchase_date,
                        'link' => route('purchases.show', $p->id),
                    ]);
                }
            }
        }

        // 3) Car wash – active or cancelled jobs only (no completed)
        $carWashPending = collect([]);
        if (class_exists(CarWashJob::class) && \Schema::hasTable('car_wash_jobs')) {
            $q = CarWashJob::with(['customer', 'customerCar'])->orderByDesc('start_time');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $jobs = $q->get();
            foreach ($jobs as $job) {
                $status = $job->status ?? '';
                if ($status !== 'active' && $status !== 'cancelled') {
                    continue;
                }
                $carWashPending->push((object)[
                    'id' => $job->id,
                    'service_name' => $job->service_name ?? 'Job',
                    'price' => $job->price ?? 0,
                    'status' => $job->status,
                    'start_time' => $job->start_time,
                    'customer_name' => $job->customer_name ?? ($job->customer->name ?? null) ?? 'Walk-in',
                    'vehicle_no' => $job->vehicle_no ?? ($job->customerCar->vehicle_no ?? null) ?? '—',
                    'link' => route('car.wash') . '?job=' . $job->id,
                ]);
            }
        }

        // 4) Task reminders (existing)
        $taskReminders = collect([]);
        if (class_exists(TaskReminder::class) && \Schema::hasTable('task_reminders')) {
            $q = TaskReminder::with('branch')->whereNotIn('status', ['Completed'])->orderByDesc('created_at');
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $taskReminders = $q->get();
        }

        $totalCount = $salesPending->count() + $purchasesPending->count() + $carWashPending->count() + $taskReminders->count();

        return view('reminders.index', compact(
            'salesPending',
            'purchasesPending',
            'carWashPending',
            'taskReminders',
            'totalCount'
        ));
    }

    /**
     * API: reminder counts for header badge.
     */
    public function counts(Request $request)
    {
        $branchId = session('selected_branch_id') ?? auth()->user()->branch_id ?? null;
        $counts = [
            'sales_pending' => 0,
            'purchases_pending' => 0,
            'car_wash_pending' => 0,
            'task_reminders' => 0,
            'total' => 0,
        ];

        if (class_exists(Sale::class) && \Schema::hasTable('sales')) {
            $q = Sale::query()->whereIn('status', ['pending', 'sale_order', 'estimate']);
            if ($branchId) $q->where('branch_id', $branchId);
            $sales = $q->get();
            foreach ($sales as $s) {
                if (((float)($s->grand_total ?? 0) - (float)($s->total_paid ?? 0)) > 0) {
                    $counts['sales_pending']++;
                }
            }
        }

        if (class_exists(Purchase::class) && \Schema::hasTable('purchases')) {
            $q = Purchase::query();
            if ($branchId) $q->where('branch_id', $branchId);
            $purchases = $q->get();
            foreach ($purchases as $p) {
                if (((float)($p->grand_total ?? 0) - (float)($p->total_paid ?? 0)) > 0) {
                    $counts['purchases_pending']++;
                }
            }
        }

        if (class_exists(CarWashJob::class) && \Schema::hasTable('car_wash_jobs')) {
            $q = CarWashJob::query()->whereIn('status', ['active', 'cancelled']);
            if ($branchId) $q->where('branch_id', $branchId);
            $counts['car_wash_pending'] = $q->count();
        }

        if (class_exists(TaskReminder::class) && \Schema::hasTable('task_reminders')) {
            $q = TaskReminder::query()->whereNotIn('status', ['Completed']);
            if ($branchId) $q->where('branch_id', $branchId);
            $counts['task_reminders'] = $q->count();
        }

        $counts['total'] = $counts['sales_pending'] + $counts['purchases_pending'] + $counts['car_wash_pending'] + $counts['task_reminders'];

        return response()->json($counts);
    }
}
