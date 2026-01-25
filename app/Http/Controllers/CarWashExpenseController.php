<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashExpense;
use App\Models\CarWashJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashExpenseController extends Controller
{
    use HasBranchAccess;
    
    /**
     * List job expenses for a date range. Query: from, to (YYYY-MM-DD; default today).
     * Returns completed jobs with expenses where end_time date is between from and to.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $from = $request->get('from', now()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $jobs = CarWashJob::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)->orWhereNull('branch_id');
        })
            ->completed()
            ->whereNotNull('end_time')
            ->whereDate('end_time', '>=', $from)
            ->whereDate('end_time', '<=', $to)
            ->with(['expense', 'user'])
            ->orderBy('end_time', 'desc')
            ->get();

        $list = [];
        foreach ($jobs as $job) {
            $exp = $job->expense;
            if (!$exp) continue;
            $items = is_array($exp->expense_items) ? $exp->expense_items : [];
            $subtotal = (float) ($exp->total_amount ?? 0);
            $list[] = [
                'jobId' => $job->id,
                'dateTime' => $job->end_time ? $job->end_time->format('Y-m-d H:i') : null,
                'vehicleNo' => $job->vehicle_no,
                'customerName' => $job->customer_name,
                'mobile' => $job->mobile,
                'workerName' => $job->worker_name,
                'userName' => $job->user ? $job->user->name : null,
                'items' => $items,
                'subtotal' => $subtotal,
            ];
        }

        $total = collect($list)->sum('subtotal');

        return response()->json([
            'success' => true,
            'expenses' => $list,
            'total' => round($total, 2),
        ]);
    }

    /**
     * Store or update expense for a job
     */
    public function store(Request $request, $jobId)
    {
        $request->validate([
            'expense_items' => 'required|array',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $job = CarWashJob::findOrFail($jobId);
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        // Check permission - user must have access to the job's branch
        // Allow if: job has no branch (global) OR user has access to job's branch
        if ($job->branch_id !== null && $job->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add expense for this job'
            ], 403);
        }

        // Update or create expense
        $expense = CarWashExpense::updateOrCreate(
            ['job_id' => $jobId],
            [
                'branch_id' => $branchId,
                'expense_items' => $request->expense_items,
                'total_amount' => $request->total_amount,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Expense saved successfully',
            'expense' => [
                'id' => $expense->id,
                'jobId' => $expense->job_id,
                'expenseItems' => $expense->expense_items,
                'totalAmount' => (float) $expense->total_amount,
            ]
        ]);
    }

    /**
     * Get expense for a job
     */
    public function show($jobId)
    {
        $job = CarWashJob::findOrFail($jobId);
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        // Check permission - user must have access to the job's branch
        // Allow if: job has no branch (global) OR user has access to job's branch
        if ($job->branch_id !== null && $job->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this expense'
            ], 403);
        }

        $expense = CarWashExpense::where('job_id', $jobId)->first();

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'expense' => [
                'id' => $expense->id,
                'jobId' => $expense->job_id,
                'expenseItems' => $expense->expense_items,
                'totalAmount' => (float) $expense->total_amount,
            ]
        ]);
    }
}
