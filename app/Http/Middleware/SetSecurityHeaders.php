<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    /**
     * Attach baseline browser security headers for all web responses.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if (! $response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', config('security.referrer_policy', 'strict-origin-when-cross-origin'));
        }

        if (! $response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', config('security.permissions_policy', 'camera=(self), geolocation=(self), microphone=()'));
        }

        if (
            $request->isSecure()
            && config('security.hsts.enabled', true)
            && ! $response->headers->has('Strict-Transport-Security')
        ) {
            $maxAge = (int) config('security.hsts.max_age', 31536000);
            $includeSubdomains = config('security.hsts.include_subdomains', true) ? '; includeSubDomains' : '';
            $preload = config('security.hsts.preload', false) ? '; preload' : '';

            $response->headers->set('Strict-Transport-Security', "max-age={$maxAge}{$includeSubdomains}{$preload}");
        }

        return $response;
    }
}
