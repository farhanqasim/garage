<?php

namespace App\Observers;

use App\Models\CarWashJob;
use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Services\CashAccountService;
use Illuminate\Support\Facades\Log;

class CarWashJobObserver
{
    protected $cashAccountService;

    public function __construct(CashAccountService $cashAccountService)
    {
        $this->cashAccountService = $cashAccountService;
    }

    /**
     * Handle the CarWashJob "updated" event.
     */
    public function updated(CarWashJob $job): void
    {
        // Check if job status changed to completed
        if ($job->isDirty('status') && $job->status === 'completed' && $job->getOriginal('status') !== 'completed') {
            $this->processJobPayment($job);
        }
    }

    /**
     * Process payment when job is completed
     */
    protected function processJobPayment(CarWashJob $job): void
    {
        try {
            // Get the user who created the job (logged-in user)
            $userId = $job->user_id;
            
            if (!$userId) {
                Log::warning('Job completion payment skipped: No user_id found', [
                    'job_id' => $job->id,
                ]);
                return;
            }

            // Load expense relationship if not already loaded
            if (!$job->relationLoaded('expense')) {
                $job->load('expense');
            }

            // Calculate total service charges (price + additional prices + expenses)
            $basePrice = (float) ($job->price ?? 0);
            $additionalPrices = is_array($job->additional_prices) 
                ? array_sum(array_column($job->additional_prices, 'price')) 
                : 0;
            // Include job expenses in total amount
            $expenseAmount = $job->expense ? (float) ($job->expense->total_amount ?? 0) : 0;
            $totalServiceCharges = $basePrice + (float) $additionalPrices + $expenseAmount;

            if ($totalServiceCharges <= 0) {
                Log::info('Job completion payment skipped: Zero service charges', [
                    'job_id' => $job->id,
                ]);
                return;
            }

            // Calculate commission if worker has commission
            $commissionAmount = 0;
            if ($job->worker && $job->worker->commission) {
                $commissionPercentage = (float) $job->worker->commission;
                $commissionAmount = ($totalServiceCharges * $commissionPercentage) / 100;
            }

            // Route payment based on payment method
            $paymentMethod = $job->payment_method ?? 'cash';
            
            if ($paymentMethod === 'bank') {
                // Bank payment: Credit to the bank account selected in the job
                // Use the bank_account_id from the job if available, otherwise fallback to user's primary account
                $bankAccount = null;
                
                // First, try to use the bank account selected when completing the job
                if ($job->bank_account_id) {
                    $bankAccount = BankAccount::where('id', $job->bank_account_id)
                        ->where('account_type', 'bank')
                        ->where('user_id', $userId)
                        ->first();
                }
                
                // If no account from job, try to find primary bank account
                if (!$bankAccount) {
                    $bankAccount = BankAccount::where('user_id', $userId)
                        ->where('account_type', 'bank')
                        ->where('status', true)
                        ->where(function($query) {
                            $query->where('is_primary', true)
                                  ->orWhereNull('is_primary');
                        })
                        ->orderByDesc('is_primary')
                        ->orderBy('id')
                        ->first();
                }
                
                if ($bankAccount) {
                    $description = "Job payment for job #{$job->id} - {$job->service_name}";
                    if ($expenseAmount > 0) {
                        $description .= " (including expenses: Rs. " . number_format($expenseAmount, 2) . ")";
                    }
                    
                    BankTransaction::create([
                        'bank_account_id' => $bankAccount->id,
                        'transaction_date' => now(),
                        'description' => $description,
                        'amount' => $totalServiceCharges,
                        'type' => 'credit',
                        'statement_reference' => 'JOB-' . $job->id,
                        'reconciled' => true, // Auto-reconcile job payments as they are immediate
                    ]);
                    
                    Log::info('Job payment (with expenses) credited to user bank account', [
                        'job_id' => $job->id,
                        'user_id' => $userId,
                        'bank_account_id' => $bankAccount->id,
                        'amount' => $totalServiceCharges,
                        'price' => $basePrice,
                        'additional_prices' => $additionalPrices,
                        'expenses' => $expenseAmount,
                    ]);
                } else {
                    Log::warning('User bank account not found for job payment, falling back to cash', [
                        'job_id' => $job->id,
                        'user_id' => $userId,
                    ]);
                    // Fallback to cash account if user's bank account not found
                    $note = "Job payment for job #{$job->id} - {$job->service_name}";
                    if ($expenseAmount > 0) {
                        $note .= " (including expenses: Rs. " . number_format($expenseAmount, 2) . ")";
                    }
                    $note .= " (user bank account not found, credited to cash)";
                    
                    $this->cashAccountService->credit(
                        userId: $userId,
                        amount: $totalServiceCharges,
                        type: 'job_payment',
                        referenceId: $job->id,
                        referenceTable: 'car_wash_jobs',
                        branchId: $job->branch_id,
                        note: $note
                    );
                }
            } else {
                // Cash payment: Credit to cash account (default behavior)
                $note = "Job payment for job #{$job->id} - {$job->service_name}";
                if ($expenseAmount > 0) {
                    $note .= " (including expenses: Rs. " . number_format($expenseAmount, 2) . ")";
                }
                
                $this->cashAccountService->credit(
                    userId: $userId,
                    amount: $totalServiceCharges,
                    type: 'job_payment',
                    referenceId: $job->id,
                    referenceTable: 'car_wash_jobs',
                    branchId: $job->branch_id,
                    note: $note
                );
            }

            // Commission handling removed: Full payment amount goes to auth user's cash account
            // Commission is already credited to worker's cash account in CarWashJobController::complete()
            // No need to debit commission from user's account - full payment stays with user

            Log::info('Job completion payment processed successfully', [
                'job_id' => $job->id,
                'user_id' => $userId,
                'service_charges' => $totalServiceCharges,
            ]);

        } catch (\Exception $e) {
            Log::error('Job completion payment failed', [
                'job_id' => $job->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw exception to prevent job completion from failing
            // The payment can be retried manually if needed
        }
    }
}
