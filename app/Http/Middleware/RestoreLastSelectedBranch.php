<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestoreLastSelectedBranch
{
    /**
     * If user is logged in but session has no selected_branch_id,
     * restore from user's last_selected_branch_id (so branch persists after standby/session expiry).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if (session()->has('selected_branch_id')) {
            return $next($request);
        }

        $lastBranchId = $user->last_selected_branch_id ?? null;
        if (!$lastBranchId) {
            return $next($request);
        }

        $branch = Branch::find($lastBranchId);
        if (!$branch || $branch->status !== 'active') {
            return $next($request);
        }

        session([
            'selected_branch_id' => $branch->id,
            'selected_branch_name' => $branch->branch_name,
            'selected_branch_code' => $branch->branch_code ?? '',
        ]);

        return $next($request);
    }
}
