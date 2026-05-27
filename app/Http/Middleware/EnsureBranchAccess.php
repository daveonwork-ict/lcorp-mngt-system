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

        if (! $user) {
            abort(401);
        }

        $activeBranchId = $request->session()->get('active_branch_id');

        if (! $activeBranchId) {
            if ($user->primary_branch_id) {
                $request->session()->put('active_branch_id', $user->primary_branch_id);
            }

            return $next($request);
        }

        if (! app(BranchAccessService::class)->canAccessBranch($user, (int) $activeBranchId)) {
            abort(403, 'Branch access denied.');
        }

        return $next($request);
    }
}
