<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;

trait HasBranchAccess
{
    /**
     * Get user's branch ID (from owner relationship, assigned branches, or session)
     */
    protected function getUserBranchId($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user) {
            return null;
        }
        
        // First check session (for assigned users who selected branch at login)
        if (session('selected_branch_id')) {
            $sessionBranchId = session('selected_branch_id');
            // Verify user has access to this branch
            $isOwner = $user->branches && $user->branches->id == $sessionBranchId;
            $isAssigned = $user->assignedBranches()->where('branch_id', $sessionBranchId)->exists();
            
            if ($isOwner || $isAssigned) {
                return $sessionBranchId;
            }
        }
        
        // Check if user is owner of a branch
        if ($user->branches) {
            return $user->branches->id;
        }
        
        // Check if user is assigned to any branch (get first one)
        $assignedBranch = $user->assignedBranches()->first();
        if ($assignedBranch) {
            return $assignedBranch->id;
        }
        
        return null;
    }
}
