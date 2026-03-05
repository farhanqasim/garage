<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\CashTransfer;
use App\Models\BankTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashAccountService
{
    /**
     * Credit amount to user's cash account
     */
    public function credit(int $userId, float $amount, string $type, ?int $referenceId = null, ?string $referenceTable = null, ?int $branchId = null, ?string $note = null): bool
    {
        return DB::transaction(function () use ($userId, $amount, $type, $referenceId, $referenceTable, $branchId, $note) {
            try {
                $account = CashAccount::where('user_id', $userId)->lockForUpdate()->first();
                
                if (!$account) {
                    // Create account if it doesn't exist (for existing users)
                    $account = CashAccount::create([
                        'user_id' => $userId,
                        'balance' => 0,
                    ]);
                }

                // Update balance
                $account->balance += $amount;
                $account->save();

                // Create transaction record
                CashTransaction::create([
                    'user_id' => $userId,
                    'amount' => $amount,
                    'direction' => 'credit',
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'reference_table' => $referenceTable,
                    'branch_id' => $branchId,
                    'note' => $note,
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Cash account credit failed: ' . $e->getMessage(), [
                    'user_id' => $userId,
                    'amount' => $amount,
                    'type' => $type,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Debit amount from user's cash account
     */
    public function debit(int $userId, float $amount, string $type, ?int $referenceId = null, ?string $referenceTable = null, ?int $branchId = null, ?string $note = null): bool
    {
        return DB::transaction(function () use ($userId, $amount, $type, $referenceId, $referenceTable, $branchId, $note) {
            try {
                $account = CashAccount::where('user_id', $userId)->lockForUpdate()->first();
                if (!$account) {
                    $account = CashAccount::create(['user_id' => $userId, 'balance' => 0]);
                }

                // Check sufficient balance (paying user's cash account)
                if ((float) $account->balance < $amount) {
                    throw new \Exception("Insufficient balance. Available: {$account->balance}, Required: {$amount}");
                }

                // Update balance
                $account->balance -= $amount;
                $account->save();

                // Create transaction record
                CashTransaction::create([
                    'user_id' => $userId,
                    'amount' => $amount,
                    'direction' => 'debit',
                    'type' => $type,
                    'reference_id' => $referenceId,
                    'reference_table' => $referenceTable,
                    'branch_id' => $branchId,
                    'note' => $note,
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Cash account debit failed: ' . $e->getMessage(), [
                    'user_id' => $userId,
                    'amount' => $amount,
                    'type' => $type,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Transfer cash from one user to another
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, ?int $branchId = null, ?string $note = null): CashTransfer
    {
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $branchId, $note) {
            try {
                // Check sender balance
                $fromAccount = CashAccount::where('user_id', $fromUserId)->lockForUpdate()->first();
                if (!$fromAccount) {
                    throw new \Exception("Cash account not found for sender user ID: {$fromUserId}");
                }

                if ($fromAccount->balance < $amount) {
                    throw new \Exception("Insufficient balance. Available: {$fromAccount->balance}, Required: {$amount}");
                }

                // Get or create receiver account
                $toAccount = CashAccount::where('user_id', $toUserId)->lockForUpdate()->first();
                if (!$toAccount) {
                    $toAccount = CashAccount::create([
                        'user_id' => $toUserId,
                        'balance' => 0,
                    ]);
                }

                // Update balances
                $fromAccount->balance -= $amount;
                $fromAccount->save();

                $toAccount->balance += $amount;
                $toAccount->save();

                // Create transfer record
                $transfer = CashTransfer::create([
                    'from_user_id' => $fromUserId,
                    'to_user_id' => $toUserId,
                    'amount' => $amount,
                    'status' => 'completed',
                    'branch_id' => $branchId,
                    'note' => $note,
                ]);

                // Create transaction records
                CashTransaction::create([
                    'user_id' => $fromUserId,
                    'related_user_id' => $toUserId,
                    'amount' => $amount,
                    'direction' => 'debit',
                    'type' => 'cash_transfer',
                    'reference_id' => $transfer->id,
                    'reference_table' => 'cash_transfers',
                    'branch_id' => $branchId,
                    'note' => $note ?? "Transfer to user ID: {$toUserId}",
                ]);

                CashTransaction::create([
                    'user_id' => $toUserId,
                    'related_user_id' => $fromUserId,
                    'amount' => $amount,
                    'direction' => 'credit',
                    'type' => 'cash_transfer',
                    'reference_id' => $transfer->id,
                    'reference_table' => 'cash_transfers',
                    'branch_id' => $branchId,
                    'note' => $note ?? "Transfer from user ID: {$fromUserId}",
                ]);

                return $transfer;
            } catch (\Exception $e) {
                Log::error('Cash transfer failed: ' . $e->getMessage(), [
                    'from_user_id' => $fromUserId,
                    'to_user_id' => $toUserId,
                    'amount' => $amount,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Create bank transfer request
     */
    public function createBankTransfer(int $userId, string $bankName, string $accountTitle, string $accountNumber, float $amount, ?string $iban = null): BankTransfer
    {
        return DB::transaction(function () use ($userId, $bankName, $accountTitle, $accountNumber, $amount, $iban) {
            try {
                // Check balance
                $account = CashAccount::where('user_id', $userId)->lockForUpdate()->first();
                if (!$account) {
                    throw new \Exception("Cash account not found for user ID: {$userId}");
                }

                if ($account->balance < $amount) {
                    throw new \Exception("Insufficient balance. Available: {$account->balance}, Required: {$amount}");
                }

                // Deduct amount immediately (pending approval)
                $account->balance -= $amount;
                $account->save();

                // Create bank transfer request
                $bankTransfer = BankTransfer::create([
                    'user_id' => $userId,
                    'bank_name' => $bankName,
                    'account_title' => $accountTitle,
                    'account_number' => $accountNumber,
                    'iban' => $iban,
                    'amount' => $amount,
                    'status' => 'pending',
                    'requested_at' => now(),
                ]);

                // Create transaction record
                CashTransaction::create([
                    'user_id' => $userId,
                    'amount' => $amount,
                    'direction' => 'debit',
                    'type' => 'bank_transfer',
                    'reference_id' => $bankTransfer->id,
                    'reference_table' => 'bank_transfers',
                    'note' => "Bank transfer request to {$bankName} - {$accountNumber}",
                ]);

                return $bankTransfer;
            } catch (\Exception $e) {
                Log::error('Bank transfer creation failed: ' . $e->getMessage(), [
                    'user_id' => $userId,
                    'amount' => $amount,
                ]);
                throw $e;
            }
        });
    }

    /**
     * Approve bank transfer
     */
    public function approveBankTransfer(int $bankTransferId): bool
    {
        return DB::transaction(function () use ($bankTransferId) {
            $bankTransfer = BankTransfer::findOrFail($bankTransferId);
            
            if ($bankTransfer->status !== 'pending') {
                throw new \Exception("Bank transfer is not pending. Current status: {$bankTransfer->status}");
            }

            $bankTransfer->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Reject bank transfer (refund amount)
     */
    public function rejectBankTransfer(int $bankTransferId, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($bankTransferId, $reason) {
            $bankTransfer = BankTransfer::findOrFail($bankTransferId);
            
            if ($bankTransfer->status !== 'pending') {
                throw new \Exception("Bank transfer is not pending. Current status: {$bankTransfer->status}");
            }

            // Refund amount to user
            $account = CashAccount::where('user_id', $bankTransfer->user_id)->lockForUpdate()->first();
            if ($account) {
                $account->balance += $bankTransfer->amount;
                $account->save();

                // Create refund transaction
                CashTransaction::create([
                    'user_id' => $bankTransfer->user_id,
                    'amount' => $bankTransfer->amount,
                    'direction' => 'credit',
                    'type' => 'admin_adjustment',
                    'reference_id' => $bankTransfer->id,
                    'reference_table' => 'bank_transfers',
                    'note' => "Bank transfer rejected refund" . ($reason ? ": {$reason}" : ""),
                ]);
            }

            $bankTransfer->update([
                'status' => 'rejected',
            ]);

            return true;
        });
    }

    /**
     * Get user balance
     */
    public function getBalance(int $userId): float
    {
        $account = CashAccount::where('user_id', $userId)->first();
        return $account ? (float) $account->balance : 0.0;
    }
}
