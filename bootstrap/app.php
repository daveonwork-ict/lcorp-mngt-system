<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Middleware\EnsureBranchAccess;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\TrackUserSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'branch.access' => EnsureBranchAccess::class,
            'permission' => EnsurePermission::class,
            'session.track' => TrackUserSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 403 || $request->expectsJson() || ! $request->isMethod('GET')) {
                return null;
            }

            if (! $request->routeIs('dashboard.owner') && ! $request->routeIs('dashboard.branch')) {
                return null;
            }

            $user = $request->user();
            if (! $user) {
                return null;
            }

            $fallbackRoute = $user->hasPermission('view_executive_dashboard')
                ? 'dashboard.owner'
                : ($user->hasPermission('view_branch_dashboard')
                    ? 'dashboard.branch'
                    : ($user->hasPermission('view_attendance') ? 'hr.attendance.index' : null));

            if (! $fallbackRoute || $request->routeIs($fallbackRoute)) {
                return null;
            }

            return redirect()
                ->route($fallbackRoute)
                ->with('error', 'You were redirected because your account has no access to that dashboard.');
        });
    })->create();
