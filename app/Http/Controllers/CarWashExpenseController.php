<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashExpense;
use App\Models\CarWashJob;
use Illuminate\Support\Facades\Auth;

class CarWashExpenseController extends Controller
{
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
        $branchId = $user->branches ? $user->branches->id : null;

        // Check permission
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
        $branchId = $user->branches ? $user->branches->id : null;

        // Check permission
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
