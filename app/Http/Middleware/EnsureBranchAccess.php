<?php

namespace App\Http\Middleware;

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
            return $next($request);
        }

        if (! $user->branches()->where('branches.id', $activeBranchId)->exists()) {
            abort(403, 'Branch access denied.');
        }

        return $next($request);
    }
}
