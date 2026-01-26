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

            // Calculate total service charges (price + additional prices)
            $basePrice = (float) ($job->price ?? 0);
            $additionalPrices = is_array($job->additional_prices) 
                ? array_sum(array_column($job->additional_prices, 'price')) 
                : 0;
            $totalServiceCharges = $basePrice + (float) $additionalPrices;

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
            
            if ($paymentMethod === 'bank' && $job->bank_account_id) {
                // Bank payment: Credit to bank account
                $bankAccount = BankAccount::find($job->bank_account_id);
                if ($bankAccount) {
                    BankTransaction::create([
                        'bank_account_id' => $bankAccount->id,
                        'transaction_date' => now(),
                        'description' => "Job payment for job #{$job->id} - {$job->service_name}",
                        'amount' => $totalServiceCharges,
                        'type' => 'credit',
                        'statement_reference' => 'JOB-' . $job->id,
                        'reconciled' => false,
                    ]);
                    
                    Log::info('Job payment credited to bank account', [
                        'job_id' => $job->id,
                        'bank_account_id' => $bankAccount->id,
                        'amount' => $totalServiceCharges,
                    ]);
                } else {
                    Log::warning('Bank account not found for job payment', [
                        'job_id' => $job->id,
                        'bank_account_id' => $job->bank_account_id,
                    ]);
                    // Fallback to cash account if bank account not found
                    $this->cashAccountService->credit(
                        userId: $userId,
                        amount: $totalServiceCharges,
                        type: 'job_payment',
                        referenceId: $job->id,
                        referenceTable: 'car_wash_jobs',
                        branchId: $job->branch_id,
                        note: "Job payment for job #{$job->id} - {$job->service_name} (bank account not found, credited to cash)"
                    );
                }
            } else {
                // Cash payment: Credit to cash account (default behavior)
                $this->cashAccountService->credit(
                    userId: $userId,
                    amount: $totalServiceCharges,
                    type: 'job_payment',
                    referenceId: $job->id,
                    referenceTable: 'car_wash_jobs',
                    branchId: $job->branch_id,
                    note: "Job payment for job #{$job->id} - {$job->service_name}"
                );
            }

            // Debit commission from user wallet if commission exists (always from cash account)
            if ($commissionAmount > 0) {
                $this->cashAccountService->debit(
                    userId: $userId,
                    amount: $commissionAmount,
                    type: 'commission',
                    referenceId: $job->id,
                    referenceTable: 'car_wash_jobs',
                    branchId: $job->branch_id,
                    note: "Commission ({$job->worker->commission}%) for worker: {$job->worker_name} - Job #{$job->id}"
                );
            }

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
