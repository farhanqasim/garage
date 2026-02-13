<?php

namespace App\Observers;

use App\Models\User;
use App\Models\CashAccount;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            // Automatically create a cash account for the new user
            // Check if account already exists to prevent duplicates
            if (!CashAccount::where('user_id', $user->id)->exists()) {
                CashAccount::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                ]);
                
                Log::info('Cash account created automatically for new user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create cash account for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception to prevent user creation from failing
            // Cash account can be created manually if needed
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        try {
            // Delete associated cash account when user is deleted
            // Note: Cash transactions are preserved for audit trail (no cascade delete)
            $cashAccount = CashAccount::where('user_id', $user->id)->first();
            if ($cashAccount) {
                $cashAccount->delete();
                
                Log::info('Cash account deleted for user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete cash account for user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
