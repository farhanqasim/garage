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
     * Apply branch filter to query.
     * Admin: If branch selected in session, filter by that branch. Otherwise, see all branches.
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
        
        // For admin: check if a branch is selected in session
        if ($user->role === 'admin') {
            $selectedBranchId = session('selected_branch_id');
            // If admin has selected a branch, filter by that branch
            if ($selectedBranchId) {
                $query->where(function ($q) use ($branchIdColumn, $selectedBranchId) {
                    $q->where($branchIdColumn, $selectedBranchId)->orWhereNull($branchIdColumn);
                });
                return;
            }
            // If no branch selected, admin sees all branches (no filter)
            return;
        }
        
        // For non-admin users: filter by their assigned branch
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

    /**
     * Get branch name for display. Admin: selected branch or "All Branches"
     * Others: their assigned/owned branch
     *
     * @param \App\Models\User|null $user
     * @return array ['id' => int|null, 'name' => string]
     */
    protected function getBranchInfoForDisplay($user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return ['id' => null, 'name' => 'Guest'];
        }

        // For admin: prioritize selected branch from session
        if ($user->role === 'admin') {
            if (session('selected_branch_id')) {
                $selectedBranch = \App\Models\Branch::find(session('selected_branch_id'));
                if ($selectedBranch) {
                    return [
                        'id' => $selectedBranch->id,
                        'name' => $selectedBranch->branch_name
                    ];
                }
            }
            return ['id' => null, 'name' => 'All Branches'];
        }

        // For non-admin users: get their branch
        $branchId = $this->getUserBranchId($user);
        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
            if ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->branch_name
                ];
            }
        }

        return ['id' => null, 'name' => 'No Branch'];
    }
}
