<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;

trait HasBranchAccess
{
    /**
     * Get user's branch ID (from owner relationship, assigned branches, or session).
     * Admin: returns session branch if set, else first assigned/available.
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
            // Admin can use any branch from session
            if ($user->role === 'admin') {
                return $sessionBranchId;
            }
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

    /**
     * Apply branch filter to query. Admin sees all branches (no filter).
     * Others: only their branch or branch_id null.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $branchIdColumn
     * @param \App\Models\User|null $user
     */
    protected function applyBranchFilter($query, $branchIdColumn = 'branch_id', $user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            $query->whereNull($branchIdColumn);
            return;
        }
        if ($user->role === 'admin') {
            return; // no filter — admin sees all branches
        }
        $bid = $this->getUserBranchId($user);
        if ($bid === null) {
            $query->whereNull($branchIdColumn);
            return;
        }
        $query->where(function ($q) use ($branchIdColumn, $bid) {
            $q->where($branchIdColumn, $bid)->orWhereNull($branchIdColumn);
        });
    }

    /**
     * Check if user can access this job's branch. Admin can access any.
     *
     * @param \App\Models\CarWashJob|object $job must have branch_id
     * @param \App\Models\User|null $user
     * @return bool
     */
    protected function canAccessJobBranch($job, $user = null)
    {
        return $this->canAccessResourceBranch($job, $user);
    }

    /**
     * Check if user can access this resource's branch (model with branch_id). Admin can access any.
     *
     * @param object $model must have branch_id
     * @param \App\Models\User|null $user
     * @return bool
     */
    protected function canAccessResourceBranch($model, $user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return false;
        }
        if ($user->role === 'admin') {
            return true;
        }
        if (empty($model->branch_id)) {
            return true;
        }
        $bid = $this->getUserBranchId($user);

        return $bid && (int) $model->branch_id === (int) $bid;
    }
}
