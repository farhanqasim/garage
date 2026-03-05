<?php

namespace App\Http\Controllers;

use App\Models\CarWashPayment;
use App\Models\CarWashWorker;
use App\Models\CarWashJob;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\BankTransfer;
use App\Models\Branch;
use App\Models\WorkerCashAccount;
use App\Models\WorkerCashTransaction;
use App\Services\CashAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashPaymentController extends Controller
{
    use HasBranchAccess;
    
    /**
     * Get all payments for current branch
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = CarWashPayment::with(['worker', 'paymentMethod', 'bankAccount', 'fromAccount', 'toAccount', 'createdBy']);
        $this->applyBranchFilter($query, 'branch_id', $user);

        // Filter by payment type
        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by worker (user id for role=worker)
        if ($request->has('worker_id') && $request->worker_id) {
            $query->where(function ($q) use ($request) {
                $q->where('worker_user_id', $request->worker_id)->orWhere('worker_id', $request->worker_id);
            });
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

        $branchId = $this->getUserBranchId($user);
        $availableCash = $this->getAvailableCash($branchId);

        $workersQuery = \App\Models\User::where('role', 'worker');
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $workers = $workersQuery->orderBy('name')->get(['id', 'name']);

        return view('car-wash-payments.index', compact('payments', 'availableCash', 'workers'));
    }

    /**
     * Show payment form
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $paymentType = $request->get('type', 'commission');
        $workerId = $request->get('worker_id');

        $workersQuery = \App\Models\User::where('role', 'worker');
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $workers = $workersQuery->orderBy('name')->get();

        $paymentMethods = PaymentMethod::active()->get();
        $bankAccounts = BankAccount::where('status', true)->with('bank')->get();

        // Get pending commission for selected worker (User role=worker)
        $pendingCommission = 0;
        if ($workerId && $paymentType === 'commission') {
            $pendingCommission = $this->calculatePendingCommissionForUserWorker($workerId, $branchId);
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
        $branchId = $this->getUserBranchId($user);

        $request->validate([
            'payment_type' => 'required|in:commission,cash_transfer,bank_transfer,expense,other',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'worker_id' => 'nullable',
            'job_ids' => 'nullable|array',
            'job_ids.*' => 'exists:car_wash_jobs,id',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate based on payment type (worker_id can be user id; we resolve in code for clear errors)
        if ($request->payment_type === 'commission') {
            $request->validate([
                'worker_id' => 'required',
            ]);
            // When commission: bank_account_id required only if payment method requires bank
            $paymentMethod = $request->payment_method_id ? PaymentMethod::find($request->payment_method_id) : null;
            if ($paymentMethod && $paymentMethod->requires_bank_account && !$request->bank_account_id) {
                $request->validate(['bank_account_id' => 'required|exists:bank_accounts,id']);
            }
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
            $payment = null;
            $jobIdsRaw = $request->input('job_ids') ?? $request->input('job_ids[]') ?? $request->job_ids ?? [];
            $jobIds = is_array($jobIdsRaw) ? array_values(array_filter(array_map('intval', $jobIdsRaw))) : [];
            $paymentMethod = $request->payment_method_id ? PaymentMethod::find($request->payment_method_id) : null;
            $methodCode = $paymentMethod ? strtolower($paymentMethod->code ?? '') : '';
            $isCashCommission = ($request->payment_type === 'commission' && $methodCode === 'cash');

            // Cash Pay from Completed Jobs: create one payment per (unpaid) job (worker = User role=worker)
            if ($isCashCommission && $request->worker_id && count($jobIds) > 0) {
                $workerIdInput = (int) $request->worker_id;
                $workerUser = \App\Models\User::where('role', 'worker')->find($workerIdInput);
                if (!$workerUser) {
                    throw new \Exception('Worker not found. Ensure the worker is added as a User with role Worker.');
                }
                if (!$this->canAccessResourceBranch($workerUser, $user)) {
                    throw new \Exception('Access denied to this worker\'s branch.');
                }
                $this->applyBranchFilter(CarWashJob::whereIn('id', $jobIds), 'branch_id', $user);
                $jobs = CarWashJob::whereIn('id', $jobIds)->where('worker_user_id', $request->worker_id)->get();
                $paymentsToCreate = [];
                $commissionPct = (float) ($workerUser->commission ?? 0);
                foreach ($jobs as $job) {
                    if (!$this->canAccessResourceBranch($job, $user)) {
                        continue;
                    }
                    $existing = CarWashPayment::where('car_wash_job_id', $job->id)->whereIn('status', ['completed'])->exists();
                    if ($existing) {
                        continue;
                    }
                    if ($commissionPct <= 0) {
                        continue;
                    }
                    $jobPrice = (float) ($job->price ?? 0);
                    $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                    $totalJobPrice = $jobPrice + (float) $additionalPrices;
                    $commissionAmount = round(($totalJobPrice * $commissionPct) / 100, 2);
                    if ($commissionAmount <= 0) {
                        continue;
                    }
                    $paymentsToCreate[] = ['job' => $job, 'amount' => $commissionAmount];
                }
                if (count($paymentsToCreate) === 0) {
                    throw new \Exception('No unpaid jobs found for this worker with the given job IDs.');
                }
                $totalAmount = array_sum(array_column($paymentsToCreate, 'amount'));
                $requestedAmount = (float) $request->amount;
                
                // Allow custom/edited amounts - distribute proportionally or add to first job
                $amountDifference = $requestedAmount - $totalAmount;
                $createdPayments = [];
                foreach ($paymentsToCreate as $index => $item) {
                    $paymentAmount = $item['amount'];
                    // If amount is different, add the difference to the first job
                    if ($index === 0 && abs($amountDifference) > 0.01) {
                        $paymentAmount += $amountDifference;
                    }
                    $p = CarWashPayment::create([
                        'branch_id' => $branchId,
                        'worker_user_id' => $request->worker_id,
                        'car_wash_job_id' => $item['job']->id,
                        'payment_type' => 'commission',
                        'amount' => $paymentAmount,
                        'payment_method_id' => $request->payment_method_id,
                        'bank_account_id' => $request->bank_account_id,
                        'from_account_id' => null,
                        'to_account_id' => null,
                        'transaction_id' => $request->transaction_id,
                        'payment_date' => $request->payment_date,
                        'status' => 'completed',
                        'notes' => $request->notes,
                        'created_by' => $user->id,
                    ]);
                    $createdPayments[] = $p;
                }
                $firstPayment = $createdPayments[0];
                $cashService = app(CashAccountService::class);
                $cashService->debit(
                    $user->id,
                    $requestedAmount,
                    'commission',
                    $firstPayment->id,
                    'car_wash_payments',
                    $branchId,
                    $request->notes ?? "Commission payment (" . count($paymentsToCreate) . " jobs) to worker #{$request->worker_id}"
                );
                $workerCash = WorkerCashAccount::where('user_id', $request->worker_id)->lockForUpdate()->first();
                if (!$workerCash) {
                    $workerCash = WorkerCashAccount::create([
                        'user_id' => $request->worker_id,
                        'worker_id' => null,
                        'balance' => 0,
                        'total_earned' => 0,
                        'total_paid' => 0,
                    ]);
                }
                // Ensure each job's commission is credited to worker (fixes jobs completed before worker_user_id or missed credits)
                $commissionPct = (float) ($workerUser->commission ?? 0);
                foreach ($paymentsToCreate as $item) {
                    $job = $item['job'];
                    $alreadyCredited = WorkerCashTransaction::where('user_id', $request->worker_id)
                        ->where('reference_type', 'car_wash_jobs')
                        ->where('reference_id', $job->id)
                        ->where('type', 'credit')
                        ->exists();
                    if (!$alreadyCredited && $item['amount'] > 0) {
                        $workerCash->balance = (float) $workerCash->balance + $item['amount'];
                        $workerCash->total_earned = (float) $workerCash->total_earned + $item['amount'];
                        $workerCash->save();
                        WorkerCashTransaction::create([
                            'worker_id' => null,
                            'user_id' => $request->worker_id,
                            'amount' => $item['amount'],
                            'type' => 'credit',
                            'reference_type' => 'car_wash_jobs',
                            'reference_id' => $job->id,
                            'note' => 'Commission ' . $commissionPct . '% on job #' . $job->id . ' (credited on pay)',
                        ]);
                    }
                }
                $balance = (float) $workerCash->balance;
                if ($balance < $requestedAmount) {
                    throw new \Exception('Cannot pay more than worker pending balance. Pending: Rs ' . number_format($balance, 2) . ', Requested: Rs ' . number_format($requestedAmount, 2) . '.');
                }
                $workerCash->balance = $balance - $requestedAmount;
                $workerCash->total_paid = (float) $workerCash->total_paid + $requestedAmount;
                $workerCash->save();
                foreach ($createdPayments as $p) {
                    WorkerCashTransaction::create([
                        'worker_id' => null,
                        'user_id' => $request->worker_id,
                        'amount' => $p->amount,
                        'type' => 'debit',
                        'reference_type' => 'car_wash_payments',
                        'reference_id' => $p->id,
                        'note' => $request->notes ?? "Commission payment #{$p->id}",
                    ]);
                }
                DB::commit();
                if ($request->ajax() || $request->wantsJson()) {
                    $firstPayment->load(['paymentMethod', 'bankAccount']);
                    if ($firstPayment->worker_user_id) {
                        $firstPayment->load('workerUser');
                    } else {
                        $firstPayment->load('worker');
                    }
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment created successfully',
                        'payment' => $firstPayment,
                        'payments_count' => count($paymentsToCreate),
                    ]);
                }
                return redirect()->route('car-wash.payments.index')->with('success', 'Payment created successfully');
            }

            $payment = CarWashPayment::create([
                'branch_id' => $branchId,
                'worker_user_id' => $request->worker_id,
                'car_wash_job_id' => null,
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

            // Commission: debit from logged-in user's cash or bank; debit worker's cash account (User role=worker)
            if ($request->payment_type === 'commission') {
                $workerUserId = $request->worker_id;
                $paymentMethod = $request->payment_method_id ? PaymentMethod::find($request->payment_method_id) : null;
                $methodCode = $paymentMethod ? strtolower($paymentMethod->code ?? '') : '';
                $isCash = ($methodCode === 'cash');
                if ($isCash && $workerUserId) {
                    $cashService = app(CashAccountService::class);
                    $cashService->debit(
                        $user->id,
                        (float) $request->amount,
                        'commission',
                        $payment->id,
                        'car_wash_payments',
                        $branchId,
                        $request->notes ?? "Commission payment to worker #{$workerUserId}"
                    );
                    $workerCash = WorkerCashAccount::where('user_id', $workerUserId)->lockForUpdate()->first();
                    if (!$workerCash) {
                        throw new \Exception("Worker cash account not found. Create it from Staff page.");
                    }
                    $amount = (float) $request->amount;
                    if ((float) $workerCash->balance < $amount) {
                        throw new \Exception("Worker cash balance (Rs " . number_format($workerCash->balance, 2) . ") is less than payment amount (Rs " . number_format($amount, 2) . ").");
                    }
                    $workerCash->balance = (float) $workerCash->balance - $amount;
                    $workerCash->total_paid = (float) $workerCash->total_paid + $amount;
                    $workerCash->save();
                    WorkerCashTransaction::create([
                        'worker_id' => null,
                        'user_id' => $workerUserId,
                        'amount' => $amount,
                        'type' => 'debit',
                        'reference_type' => 'car_wash_payments',
                        'reference_id' => $payment->id,
                        'note' => $request->notes ?? "Commission payment #{$payment->id}",
                    ]);
                } elseif ($request->bank_account_id) {
                    BankTransaction::create([
                        'bank_account_id' => $request->bank_account_id,
                        'transaction_date' => $request->payment_date,
                        'description' => "Commission payment - " . ($request->notes ?? ''),
                        'amount' => $request->amount,
                        'type' => 'debit',
                        'statement_reference' => $request->transaction_id ?? 'COMMISSION-' . $payment->id,
                        'reconciled' => false,
                    ]);
                    $workerUser = $workerUserId ? \App\Models\User::find($workerUserId) : null;
                    if ($workerUser && $workerUser->bank_account_id) {
                        BankTransaction::create([
                            'bank_account_id' => $workerUser->bank_account_id,
                            'transaction_date' => $request->payment_date,
                            'description' => "Commission credit - " . ($workerUser->name) . ($request->notes ? ' - ' . $request->notes : ''),
                            'amount' => $request->amount,
                            'type' => 'credit',
                            'statement_reference' => $request->transaction_id ?? 'COMMISSION-' . $payment->id,
                            'reconciled' => false,
                        ]);
                        $payment->update(['to_account_id' => $workerUser->bank_account_id]);
                    }
                    if ($workerUserId) {
                        $workerCash = WorkerCashAccount::where('user_id', $workerUserId)->lockForUpdate()->first();
                        if ($workerCash) {
                            $amount = (float) $request->amount;
                            if ((float) $workerCash->balance < $amount) {
                                throw new \Exception("Worker pending balance (Rs " . number_format($workerCash->balance, 2) . ") is less than payment amount (Rs " . number_format($amount, 2) . ").");
                            }
                            $workerCash->balance = (float) $workerCash->balance - $amount;
                            $workerCash->total_paid = (float) $workerCash->total_paid + $amount;
                            $workerCash->save();
                            WorkerCashTransaction::create([
                                'worker_id' => null,
                                'user_id' => $workerUserId,
                                'amount' => $amount,
                                'type' => 'debit',
                                'reference_type' => 'car_wash_payments',
                                'reference_id' => $payment->id,
                                'note' => ($request->notes ?? "Commission payment #{$payment->id}") . ' (Bank)',
                            ]);
                        }
                    }
                }
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
     * Worker Bank Accounts page: workers linked to system bank accounts with separate history
     */
    public function workerBankAccounts()
    {
        $user = Auth::user();
        $workersQuery = \App\Models\User::with(['bankAccount.bank', 'branch'])->where('role', 'worker')
            ->whereNotNull('bank_account_id');
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $workers = $workersQuery->orderBy('name')->get()->map(function ($workerUser) {
            $account = $workerUser->bankAccount;
            $transactions = $account
                ? BankTransaction::where('bank_account_id', $account->id)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(50)
                    ->get()
                : collect();
            $credits = $account ? BankTransaction::where('bank_account_id', $account->id)->where('type', 'credit')->sum('amount') : 0;
            $debits = $account ? BankTransaction::where('bank_account_id', $account->id)->where('type', 'debit')->sum('amount') : 0;
            $opening = $account ? (float) ($account->opening_balance ?? 0) : 0;
            $balance = $opening + $credits - $debits;
            return [
                'worker' => $workerUser,
                'account' => $account,
                'transactions' => $transactions,
                'balance' => round($balance, 2),
            ];
        });
        return view('car-wash-worker-bank-accounts', compact('workers'));
    }

    /**
     * Worker Cash Accounts page: workers with cash account – commission paid by cash is credited here
     */
    public function workerCashAccounts()
    {
        $user = Auth::user();
        $workersQuery = \App\Models\User::with(['workerCashAccount', 'branch'])->where('role', 'worker');
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $workers = $workersQuery->orderBy('name')->get()->map(function ($workerUser) {
            $cashAccount = $workerUser->workerCashAccount;
            $transactions = $cashAccount
                ? WorkerCashTransaction::where('user_id', $workerUser->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get()
                : collect();
            $balance = $cashAccount ? (float) $cashAccount->balance : 0;
            $totalEarned = $cashAccount ? (float) $cashAccount->total_earned : 0;
            $totalPaid = $cashAccount ? (float) $cashAccount->total_paid : 0;
            return [
                'worker' => $workerUser,
                'cash_account' => $cashAccount,
                'transactions' => $transactions,
                'balance' => round($balance, 2),
                'total_earned' => round($totalEarned, 2),
                'total_paid' => round($totalPaid, 2),
            ];
        });
        return view('car-wash-worker-cash-accounts', compact('workers'));
    }

    /**
     * Worker Commission / Pay / Total Balance page
     * Shows detailed transaction history with running balance
     */
    public function workerCommissionPayBalance()
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $workersQuery = \App\Models\User::with(['workerCashAccount', 'branch'])->where('role', 'worker');
        $this->applyBranchFilter($workersQuery, 'branch_id', $user);
        $workers = $workersQuery->orderBy('name')->get();
        return view('car-wash-worker-commission-pay-balance', compact('workers', 'branchId'));
    }

    /**
     * Worker cash timeline (credits + debits) for Commission / Pay / Running balance table (API)
     */
    public function workerCashTimeline($workerId)
    {
        $user = Auth::user();
        $q = \App\Models\User::where('role', 'worker')->where('id', $workerId);
        $this->applyBranchFilter($q, 'branch_id', $user);
        $workerUser = $q->first();
        if (!$workerUser) {
            return response()->json(['success' => false, 'transactions' => []], 404);
        }
        $transactions = WorkerCashTransaction::where('user_id', $workerUser->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($tx) {
                return [
                    'type' => $tx->type,
                    'amount' => (float) $tx->amount,
                    'createdAt' => $tx->created_at->toISOString(),
                    'referenceType' => $tx->reference_type,
                    'referenceId' => $tx->reference_id,
                    'note' => $tx->note,
                ];
            });
        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Print transaction history for one worker (opens in new window for printing)
     */
    public function workerCashAccountPrint($worker)
    {
        $user = Auth::user();
        $q = \App\Models\User::where('role', 'worker')->where('id', $worker);
        $this->applyBranchFilter($q, 'branch_id', $user);
        $worker = $q->first();
        if (!$worker) {
            abort(404);
        }
        $cashAccount = $worker->workerCashAccount;
        $transactions = $cashAccount
            ? WorkerCashTransaction::where('user_id', $worker->id)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();
        $balance = $cashAccount ? (float) $cashAccount->balance : 0;
        $totalEarned = $cashAccount ? (float) $cashAccount->total_earned : 0;
        $totalPaid = $cashAccount ? (float) $cashAccount->total_paid : 0;
        return view('car-wash-worker-cash-account-print', compact('worker', 'cashAccount', 'transactions', 'balance', 'totalEarned', 'totalPaid'));
    }

    /**
     * Calculate pending commission for a worker (user with role=worker).
     * Uses worker cash account balance when available; else jobs - payments by worker_user_id.
     */
    public function calculatePendingCommissionForUserWorker($userId, $branchId = null)
    {
        $workerUser = \App\Models\User::find($userId);
        if ($workerUser && $workerUser->workerCashAccount) {
            return max(0, (float) $workerUser->workerCashAccount->balance);
        }

        $user = Auth::user();
        if (!$branchId) {
            $branchId = $this->getUserBranchId($user);
        }

        $query = CarWashJob::where('worker_user_id', $userId)->where('status', 'completed');
        $this->applyBranchFilter($query, 'branch_id', $user);
        $completedJobs = $query->get();

        $commissionPercentage = $workerUser ? (float) ($workerUser->commission ?? 0) : 0;
        $totalCommission = 0;
        foreach ($completedJobs as $job) {
            $jobPrice = (float) ($job->price ?? 0);
            $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
            $totalJobPrice = $jobPrice + (float) $additionalPrices;
            if ($totalJobPrice > 0 && $commissionPercentage > 0) {
                $totalCommission += ($totalJobPrice * $commissionPercentage) / 100;
            }
        }

        $paidQuery = CarWashPayment::where('worker_user_id', $userId)
            ->where('payment_type', 'commission')
            ->where('status', 'completed');
        $this->applyBranchFilter($paidQuery, 'branch_id', $user);
        $paidCommission = $paidQuery->sum('amount');

        return max(0, $totalCommission - $paidCommission);
    }

    /**
     * Calculate pending commission for legacy CarWashWorker (worker_id).
     */
    public function calculatePendingCommission($workerId, $branchId = null)
    {
        $worker = CarWashWorker::find($workerId);
        if ($worker && $worker->workerCashAccount) {
            return max(0, (float) $worker->workerCashAccount->balance);
        }

        $user = Auth::user();
        if (!$branchId) {
            $branchId = $this->getUserBranchId($user);
        }

        $query = CarWashJob::where('worker_id', $workerId)->where('status', 'completed');
        $this->applyBranchFilter($query, 'branch_id', $user);
        $completedJobs = $query->get();

        $totalCommission = 0;
        foreach ($completedJobs as $job) {
            $w = $job->worker;
            if ($w && $w->commission) {
                $jobPrice = (float) ($job->price ?? 0);
                $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
                $totalJobPrice = $jobPrice + (float) $additionalPrices;
                $commissionPercentage = (float) $w->commission;
                $commissionAmount = ($totalJobPrice * $commissionPercentage) / 100;
                $totalCommission += $commissionAmount;
            }
        }

        $paidQuery = CarWashPayment::where('worker_id', $workerId)
            ->where('payment_type', 'commission')
            ->where('status', 'completed');
        $this->applyBranchFilter($paidQuery, 'branch_id', $user);
        $paidCommission = $paidQuery->sum('amount');

        return max(0, $totalCommission - $paidCommission);
    }

    /**
     * Get available cash for branch
     */
    public function getAvailableCash($branchId = null)
    {
        $user = Auth::user();
        if (!$branchId) {
            $branchId = $this->getUserBranchId($user);
        }

        $query = CarWashJob::where('status', 'completed');
        $this->applyBranchFilter($query, 'branch_id', $user);
        $completedJobs = $query->get();
        $totalIncome = 0;

        foreach ($completedJobs as $job) {
            $jobPrice = (float) ($job->price ?? 0);
            $additionalPrices = is_array($job->additional_prices) ? array_sum(array_column($job->additional_prices, 'price')) : 0;
            $totalIncome += $jobPrice + (float) $additionalPrices;
        }

        $expensesQuery = CarWashPayment::where('payment_type', '!=', 'commission')->where('status', 'completed');
        $this->applyBranchFilter($expensesQuery, 'branch_id', $user);
        $totalExpenses = $expensesQuery->sum('amount');

        $commissionQuery = CarWashPayment::where('payment_type', 'commission')->where('status', 'completed');
        $this->applyBranchFilter($commissionQuery, 'branch_id', $user);
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
     * Get Cash payment method id (for commission pay by cash)
     */
    public function getCashMethod()
    {
        $method = \App\Models\PaymentMethod::whereRaw('LOWER(code) = ?', ['cash'])->where('is_active', true)->first();
        return response()->json([
            'success' => (bool) $method,
            'id' => $method ? $method->id : null,
        ]);
    }

    /**
     * Get cash account balance (API).
     * Optional user_id: if provided (same branch), return that user's balance; else return sum of all branch users' cash balances.
     */
    public function getCashAccountBalance(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'balance' => 0, 'message' => 'Unauthenticated.'], 401);
        }
        $branchId = $this->getUserBranchId($user);
        $userIdParam = $request->get('user_id');

        try {
            $cashAccountService = app(\App\Services\CashAccountService::class);

            if ($userIdParam !== null && $userIdParam !== '') {
                $targetUserId = (int) $userIdParam;
                if ($targetUserId === $user->id) {
                    $balance = $cashAccountService->getBalance($user->id);
                    return response()->json(['success' => true, 'balance' => (float) $balance]);
                }
                $targetUser = \App\Models\User::find($targetUserId);
                $branchUserIds = $this->getBranchUserIds($branchId, $user, true);
                if (!$targetUser || !in_array($targetUserId, $branchUserIds)) {
                    return response()->json(['success' => false, 'balance' => 0, 'message' => 'User not found or access denied.'], 403);
                }
                $balance = $cashAccountService->getBalance($targetUserId);
                return response()->json(['success' => true, 'balance' => (float) $balance]);
            }

            // All users: sum of all branch users' cash balances
            $branchUserIds = $this->getBranchUserIds($branchId, $user, true);
            $balance = 0;
            foreach ($branchUserIds as $uid) {
                $balance += $cashAccountService->getBalance($uid);
            }
            return response()->json(['success' => true, 'balance' => (float) $balance]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'balance' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user IDs for the given branch (owners + assigned). Optionally include current user.
     */
    protected function getBranchUserIds($branchId, $currentUser, $includeCurrent = false)
    {
        if (!$branchId) {
            return $includeCurrent ? [$currentUser->id] : [];
        }
        $query = \App\Models\User::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
                ->orWhereHas('assignedBranches', function ($q2) use ($branchId) {
                    $q2->where('branch_id', $branchId);
                });
        });
        if (!$includeCurrent) {
            $query->where('id', '!=', $currentUser->id);
        }
        return $query->pluck('id')->toArray();
    }
    
    /**
     * Get admin's cash account balance (API)
     * Returns admin user's cash account balance (for transfer to admin)
     */
    public function getAdminCashAccountBalance(Request $request)
    {
        try {
            // Find admin user
            $adminUser = \App\Models\User::where('role', 'admin')->first();
            
            if (!$adminUser) {
                return response()->json([
                    'success' => false,
                    'balance' => 0,
                    'message' => 'Admin user not found',
                ], 404);
            }
            
            $cashAccountService = app(\App\Services\CashAccountService::class);
            $balance = $cashAccountService->getBalance($adminUser->id);
            
            return response()->json([
                'success' => true,
                'balance' => (float) $balance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'balance' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending commission for worker (API)
     */
    public function getPendingCommission(Request $request, $workerId)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

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
        $branchId = $this->getUserBranchId($user);

        $cash = $this->getAvailableCash($branchId);

        return response()->json($cash);
    }

    /**
     * Get same-branch users for cash transfer (API)
     */
    public function getBranchUsers(Request $request)
    {
        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch found for user',
                'users' => []
            ], 400);
        }

        // Get all users connected to this branch (owners or assigned)
        $users = \App\Models\User::where(function($query) use ($branchId) {
            // Branch owners
            // Users with matching branch_id
            $query->where('branch_id', $branchId)
            // Assigned users
            ->orWhereHas('assignedBranches', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        })
        ->where('id', '!=', $user->id) // Exclude current user
        ->select('id', 'name', 'email', 'phone', 'role')
        ->orderBy('name')
        ->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Reverse the last cash commission payment for a worker (API).
     * Supports User workers (worker_user_id) and legacy CarWashWorker (worker_id).
     * Credits the payer's cash account and the worker's cash balance; marks the payment as reversed.
     */
    public function reverseLastForWorker(Request $request)
    {
        $request->validate([
            'worker_id' => 'required',
            'job_id' => 'nullable|exists:car_wash_jobs,id',
        ]);

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $workerIdInput = (int) $request->worker_id;
        $jobId = $request->has('job_id') && $request->job_id ? (int) $request->job_id : null;

        // Resolve worker: User (role=worker) or legacy CarWashWorker
        $workerUser = \App\Models\User::where('role', 'worker')->find($workerIdInput);
        $legacyWorker = CarWashWorker::find($workerIdInput);
        $isUserWorker = (bool) $workerUser;
        if ($workerUser && !$this->canAccessResourceBranch($workerUser, $user)) {
            return response()->json(['success' => false, 'message' => 'Worker not found or access denied.'], 404);
        }
        if ($legacyWorker && !$this->canAccessResourceBranch($legacyWorker, $user)) {
            return response()->json(['success' => false, 'message' => 'Worker not found or access denied.'], 404);
        }
        if (!$workerUser && !$legacyWorker) {
            return response()->json(['success' => false, 'message' => 'Worker not found.'], 404);
        }

        $paymentMethod = PaymentMethod::whereRaw('LOWER(code) = ?', ['cash'])->where('is_active', true)->first();
        if (!$paymentMethod) {
            return response()->json(['success' => false, 'message' => 'Cash payment method not found.'], 400);
        }

        // Find last completed cash commission payment (by worker_user_id or worker_id)
        $query = CarWashPayment::where('payment_type', 'commission')
            ->where('status', 'completed')
            ->where('payment_method_id', $paymentMethod->id);
        if ($isUserWorker) {
            $query->where('worker_user_id', $workerIdInput);
        } else {
            $query->where('worker_id', $workerIdInput);
        }
        if ($jobId) {
            $query->where('car_wash_job_id', $jobId);
        }
        $payment = $query->orderBy('created_at', 'desc')->first();

        if (!$payment || !$this->canAccessResourceBranch($payment, $user)) {
            return response()->json(['success' => false, 'message' => 'No cash commission payment found to reverse for this worker.'], 404);
        }

        $amount = (float) $payment->amount;
        // Credit current logged-in user (who is reversing), not the original creator
        // This ensures the amount goes back to the account of the user who reversed it
        $currentUserId = $user->id;

        DB::beginTransaction();
        try {
            // Credit current user's cash account (the one who is reversing the payment)
            $cashService = app(CashAccountService::class);
            $cashService->credit(
                $currentUserId,
                $amount,
                'admin_adjustment',
                $payment->id,
                'car_wash_payments',
                $branchId,
                "Reversal of commission payment #{$payment->id} to worker #{$workerIdInput}"
            );

            // Credit worker's cash balance and reduce total_paid (User worker by user_id, legacy by worker_id)
            if ($isUserWorker) {
                $workerCash = WorkerCashAccount::where('user_id', $workerIdInput)->lockForUpdate()->first();
            } else {
                $workerCash = WorkerCashAccount::where('worker_id', $workerIdInput)->lockForUpdate()->first();
            }
            if (!$workerCash) {
                throw new \Exception('Worker cash account not found.');
            }
            $workerCash->balance = (float) $workerCash->balance + $amount;
            $workerCash->total_paid = max(0, (float) $workerCash->total_paid - $amount);
            $workerCash->save();

            if ($isUserWorker) {
                WorkerCashTransaction::create([
                    'worker_id' => null,
                    'user_id' => $workerIdInput,
                    'amount' => $amount,
                    'type' => 'credit',
                    'reference_type' => 'car_wash_payments',
                    'reference_id' => $payment->id,
                    'note' => "Reversal of payment #{$payment->id}",
                ]);
            } else {
                WorkerCashTransaction::create([
                    'worker_id' => $workerIdInput,
                    'amount' => $amount,
                    'type' => 'credit',
                    'reference_type' => 'car_wash_payments',
                    'reference_id' => $payment->id,
                    'note' => "Reversal of payment #{$payment->id}",
                ]);
            }

            $payment->update(['status' => 'reversed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment reversed successfully.',
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reverse payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transfer cash to another user (API)
     * If to_user_id is null, transfer to admin user
     */
    public function transferToUser(Request $request)
    {
        $request->validate([
            'to_user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);
        $amount = (float) $request->amount;
        $note = $request->note;
        
        // If to_user_id is null, find admin user
        if (!$request->to_user_id) {
            $adminUser = \App\Models\User::where('role', 'admin')->first();
            if (!$adminUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin user not found'
                ], 404);
            }
            $toUserId = $adminUser->id;
        } else {
            $toUserId = $request->to_user_id;
        }

        // Verify both users are in the same branch
        $toUser = \App\Models\User::findOrFail($toUserId);
        $toUserBranchId = null;

        // If transferring to admin, skip branch restrictions (admin can receive from any branch)
        $isAdminTransfer = $toUser->role === 'admin';
        
        if (!$isAdminTransfer) {
            // Get to_user's branch
            if ($toUser->branch_id) {
                $toUserBranchId = $toUser->branch_id;
            } else {
                $assignedBranch = $toUser->assignedBranches()->first();
                if ($assignedBranch) {
                    $toUserBranchId = $assignedBranch->id;
                }
            }

            // Check if both users are in the same branch
            if ($branchId && $toUserBranchId && $branchId != $toUserBranchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only transfer to users in the same branch'
                ], 403);
            }

            // If current user has no branch but to_user has, allow if to_user's branch allows it
            // Or if both have no branch, allow (global users)
            if (!$branchId && !$toUserBranchId) {
                // Both are global users, allow transfer
                $branchId = null;
            } elseif (!$branchId && $toUserBranchId) {
                // Current user is global, to_user has branch - check if allowed
                // For now, we'll allow it
                $branchId = $toUserBranchId;
            }
        }

        try {
            $cashAccountService = app(\App\Services\CashAccountService::class);
            
            // Perform transfer
            $transfer = $cashAccountService->transfer(
                fromUserId: $user->id,
                toUserId: $toUserId,
                amount: $amount,
                branchId: $branchId,
                note: $note ?? "Cash transfer from {$user->name} to {$toUser->name}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Cash transferred successfully',
                'transfer' => [
                    'id' => $transfer->id,
                    'from_user' => $user->name,
                    'to_user' => $toUser->name,
                    'amount' => $amount,
                    'status' => $transfer->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete/Reverse a cash transfer (API)
     * Reverses the transfer by crediting back to sender and debiting from receiver
     */
    public function deleteCashTransfer($id)
    {
        $user = Auth::user();
        $transfer = \App\Models\CashTransfer::find($id);
        
        if (!$transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Cash transfer not found.',
            ], 404);
        }
        
        // Check if user has permission (must be from_user or admin)
        if ($transfer->from_user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this transfer.',
            ], 403);
        }
        
        // Only allow deletion of completed transfers
        if ($transfer->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed transfers can be deleted.',
            ], 400);
        }
        
        $branchId = $this->getUserBranchId($user);
        $amount = (float) $transfer->amount;
        
        DB::beginTransaction();
        try {
            $cashAccountService = app(\App\Services\CashAccountService::class);
            
            // Reverse: Credit back to sender (from_user)
            $cashAccountService->credit(
                $transfer->from_user_id,
                $amount,
                'admin_adjustment',
                $transfer->id,
                'cash_transfers',
                $branchId,
                "Reversal of cash transfer #{$transfer->id} to user #{$transfer->to_user_id}"
            );
            
            // Reverse: Debit from receiver (to_user)
            $cashAccountService->debit(
                $transfer->to_user_id,
                $amount,
                'admin_adjustment',
                $transfer->id,
                'cash_transfers',
                $branchId,
                "Reversal of cash transfer #{$transfer->id} from user #{$transfer->from_user_id}"
            );
            
            // Mark transfer as failed (soft delete by changing status)
            $transfer->status = 'failed';
            $transfer->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Cash transfer deleted successfully.',
                'transfer_id' => $transfer->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cash transfer: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Delete a bank transfer
     */
    public function deleteBankTransfer($id)
    {
        $user = Auth::user();
        $transfer = BankTransfer::find($id);
        
        if (!$transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Bank transfer not found.',
            ], 404);
        }
        
        // Check if user has permission (must be user who requested or admin)
        if ($transfer->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this transfer.',
            ], 403);
        }
        
        // Only allow deletion of approved transfers
        if ($transfer->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved transfers can be deleted.',
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            $branchId = $this->getUserBranchId($user);
            $amount = (float) $transfer->amount;
            
            // Find the bank account associated with this transfer
            // We need to reverse the bank account balance
            // Since bank transfers are approved, they should have affected a bank account
            // We'll need to find which bank account was credited
            
            // For now, we'll just mark the transfer as rejected (soft delete)
            $transfer->status = 'rejected';
            $transfer->save();
            
            // TODO: If needed, reverse the bank account balance here
            // This would require tracking which bank account was credited when the transfer was approved
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bank transfer deleted successfully.',
                'transfer_id' => $transfer->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bank transfer: ' . $e->getMessage(),
            ], 500);
        }
    }
}
