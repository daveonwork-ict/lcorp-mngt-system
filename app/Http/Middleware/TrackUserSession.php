<?php

namespace App\Http\Middleware;

use App\Services\SessionSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    public function __construct(private readonly SessionSecurityService $sessionSecurityService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $this->sessionSecurityService->heartbeat();
        }

        return $next($request);
    }
}
