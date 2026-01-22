<?php

namespace App\Http\Controllers;

use App\Models\CarWashPayment;
use App\Models\CarWashWorker;
use App\Models\CarWashJob;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarWashPaymentController extends Controller
{
    /**
     * Get all payments for current branch
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;

        $query = CarWashPayment::with(['worker', 'paymentMethod', 'bankAccount', 'fromAccount', 'toAccount', 'createdBy'])
            ->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });

        // Filter by payment type
        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by worker
        if ($request->has('worker_id') && $request->worker_id) {
            $query->where('worker_id', $request->worker_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get available cash
        $availableCash = $this->getAvailableCash($branchId);

        // Get workers for filter
        $workers = CarWashWorker::where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })
        ->where('status', true)
        ->orderBy('name')
        ->get();

        return view('car-wash-payments.index', compact('payments', 'availableCash', 'workers'));
    }

    /**
     * Show payment form
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;

        $paymentType = $request->get('type', 'commission');
        $workerId = $request->get('worker_id');

        $workers = CarWashWorker::where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })
        ->where('status', true)
        ->orderBy('name')
        ->get();

        $paymentMethods = PaymentMethod::active()->get();
        $bankAccounts = BankAccount::where('status', true)->with('bank')->get();

        // Get pending commission for selected worker
        $pendingCommission = 0;
        if ($workerId && $paymentType === 'commission') {
            $pendingCommission = $this->calculatePendingCommission($workerId, $branchId);
        }

        $availableCash = $this->getAvailableCash($branchId);

        return view('car-wash-payments.create', compact(
            'paymentType',
            'workers',
            'paymentMethods',
            'bankAccounts',
            'pendingCommission',
            'availableCash',
            'workerId'
        ));
    }

    /**
     * Store payment
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;

        $request->validate([
            'payment_type' => 'required|in:commission,cash_transfer,bank_transfer,expense,other',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'worker_id' => 'nullable|exists:car_wash_workers,id',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate based on payment type
        if ($request->payment_type === 'commission') {
            $request->validate([
                'worker_id' => 'required|exists:car_wash_workers,id',
            ]);
        }

        if ($request->payment_type === 'cash_transfer') {
            $request->validate([
                'from_account_id' => 'required|exists:bank_accounts,id',
                'to_account_id' => 'required|exists:bank_accounts,id',
            ]);
        }

        if ($request->payment_type === 'bank_transfer') {
            $request->validate([
                'bank_account_id' => 'required|exists:bank_accounts,id',
            ]);
        }

        DB::beginTransaction();
        try {
            $payment = CarWashPayment::create([
                'branch_id' => $branchId,
                'worker_id' => $request->worker_id,
                'payment_type' => $request->payment_type,
                'amount' => $request->amount,
                'payment_method_id' => $request->payment_method_id,
                'bank_account_id' => $request->bank_account_id,
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'transaction_id' => $request->transaction_id,
                'payment_date' => $request->payment_date,
                'status' => 'completed',
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);

            // Create bank transactions for transfers
            if ($request->payment_type === 'cash_transfer') {
                // Debit from source account
                BankTransaction::create([
                    'bank_account_id' => $request->from_account_id,
                    'transaction_date' => $request->payment_date,
                    'description' => "Cash Transfer to " . ($payment->toAccount->bank->name ?? 'Account') . " - " . ($request->notes ?? ''),
                    'amount' => $request->amount,
                    'type' => 'debit',
                    'statement_reference' => $request->transaction_id ?? 'CASH-TRANSFER-' . $payment->id,
                    'reconciled' => false,
                ]);

                // Credit to destination account
                BankTransaction::create([
                    'bank_account_id' => $request->to_account_id,
                    'transaction_date' => $request->payment_date,
                    'description' => "Cash Transfer from " . ($payment->fromAccount->bank->name ?? 'Account') . " - " . ($request->notes ?? ''),
                    'amount' => $request->amount,
                    'type' => 'credit',
                    'statement_reference' => $request->transaction_id ?? 'CASH-TRANSFER-' . $payment->id,
                    'reconciled' => false,
                ]);
            } elseif ($request->payment_type === 'bank_transfer' && $request->bank_account_id) {
                // Bank transfer - debit from account
                BankTransaction::create([
                    'bank_account_id' => $request->bank_account_id,
                    'transaction_date' => $request->payment_date,
                    'description' => "Car Wash Payment - " . ($request->notes ?? ''),
                    'amount' => $request->amount,
                    'type' => 'debit',
                    'statement_reference' => $request->transaction_id ?? 'BANK-TRANSFER-' . $payment->id,
                    'reconciled' => false,
                ]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment created successfully',
                    'payment' => $payment->load(['worker', 'paymentMethod', 'bankAccount'])
                ]);
            }

            return redirect()->route('car-wash.payments.index')
                ->with('success', 'Payment created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }

    /**
     * Calculate pending commission for a worker
     */
    public function calculatePendingCommission($workerId, $branchId = null)
    {
        $query = CarWashJob::where('worker_id', $workerId)
            ->where('status', 'completed');

        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });
        }

        $completedJobs = $query->get();

        $totalCommission = 0;
        foreach ($completedJobs as $job) {
            $worker = $job->worker;
            if ($worker && $worker->commission) {
                $jobPrice = (float) ($job->price ?? 0);
                $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                $totalJobPrice = $jobPrice + (float) $additionalPrices;
                
                $commissionPercentage = (float) $worker->commission;
                $commissionAmount = ($totalJobPrice * $commissionPercentage) / 100;
                $totalCommission += $commissionAmount;
            }
        }

        // Subtract already paid commission
        $paidCommission = CarWashPayment::where('worker_id', $workerId)
            ->where('payment_type', 'commission')
            ->where('status', 'completed')
            ->where(function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                }
            })
            ->sum('amount');

        return max(0, $totalCommission - $paidCommission);
    }

    /**
     * Get available cash for branch
     */
    public function getAvailableCash($branchId = null)
    {
        // Calculate total income from completed jobs
        $query = CarWashJob::where('status', 'completed');
        
        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });
        }

        $completedJobs = $query->get();
        $totalIncome = 0;

        foreach ($completedJobs as $job) {
            $jobPrice = (float) ($job->price ?? 0);
            $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
            $totalIncome += $jobPrice + (float) $additionalPrices;
        }

        // Calculate total expenses
        $expensesQuery = CarWashPayment::where('payment_type', '!=', 'commission')
            ->where('status', 'completed');
        
        if ($branchId) {
            $expensesQuery->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });
        }

        $totalExpenses = $expensesQuery->sum('amount');

        // Calculate total commission paid
        $commissionQuery = CarWashPayment::where('payment_type', 'commission')
            ->where('status', 'completed');
        
        if ($branchId) {
            $commissionQuery->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });
        }

        $totalCommissionPaid = $commissionQuery->sum('amount');

        // Available cash = Income - Expenses - Commission Paid
        $availableCash = $totalIncome - $totalExpenses - $totalCommissionPaid;

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'total_commission_paid' => $totalCommissionPaid,
            'available_cash' => max(0, $availableCash),
        ];
    }

    /**
     * Get pending commission for worker (API)
     */
    public function getPendingCommission(Request $request, $workerId)
    {
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;

        $pendingCommission = $this->calculatePendingCommission($workerId, $branchId);

        return response()->json([
            'pending_commission' => round($pendingCommission, 2)
        ]);
    }

    /**
     * Get available cash (API)
     */
    public function getAvailableCashApi()
    {
        $user = Auth::user();
        $branchId = $user->branches ? $user->branches->id : null;

        $cash = $this->getAvailableCash($branchId);

        return response()->json($cash);
    }
}
