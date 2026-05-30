<?php

namespace App\Http\Middleware;

use App\Services\BranchAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $branchAccessService = app(BranchAccessService::class);

        if (! $user) {
            abort(401);
        }

        $activeBranchId = $request->session()->get('active_branch_id');

        if (! $activeBranchId) {
            $fallbackBranchId = $user->primary_branch_id
                ?: $branchAccessService->accessibleBranches($user)->pluck('id')->first();

            if ($fallbackBranchId && $branchAccessService->canAccessBranch($user, (int) $fallbackBranchId)) {
                $request->session()->put('active_branch_id', (int) $fallbackBranchId);
            }

            return $next($request);
        }

        if (! $branchAccessService->canAccessBranch($user, (int) $activeBranchId)) {
            $fallbackBranchId = $user->primary_branch_id
                ?: $branchAccessService->accessibleBranches($user)->pluck('id')->first();

            if ($fallbackBranchId && $branchAccessService->canAccessBranch($user, (int) $fallbackBranchId)) {
                $request->session()->put('active_branch_id', (int) $fallbackBranchId);

                return $next($request);
            }

            $request->session()->forget('active_branch_id');

            return $next($request);
        }

        return $next($request);
    }
}
