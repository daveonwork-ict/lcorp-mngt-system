<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\LoginSecurityService;
use App\Services\SessionSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly LoginSecurityService $loginSecurityService,
        private readonly SessionSecurityService $sessionSecurityService,
    ) {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        if ($user) {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $this->auditLogService->record('auth', 'login', [], ['user_id' => $user->id], $user->primary_branch_id, 'User login', $user->id);
            $this->loginSecurityService->logSuccess($user->id, $request->string('login')->toString());
            $this->sessionSecurityService->registerCurrentSession();
        }

        return redirect()->route($this->resolveLandingRoute($user));
    }

    private function resolveLandingRoute(?User $user): string
    {
        if (! $user) {
            return 'dashboard.owner';
        }

        if ($user->hasPermission('view_executive_dashboard') || $user->role?->code === config('rms.owner_role_code')) {
            return 'dashboard.owner';
        }

        if ($user->hasPermission('view_branch_dashboard')) {
            return 'dashboard.branch';
        }

        $fallbackRoutes = [
            'view_inventory' => 'inventory.index',
            'view_pos' => 'pos.index',
            'view_airtime_dashboard' => 'airtime.index',
            'view_cash_flow' => 'cash-flow.index',
            'view_reports' => 'reports.index',
            'view_approval_inbox' => 'approvals.index',
        ];

        foreach ($fallbackRoutes as $permission => $routeName) {
            if ($user->hasPermission($permission)) {
                return $routeName;
            }
        }

        return 'login';
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $this->auditLogService->record('auth', 'logout', [], ['user_id' => $user->id], $user->primary_branch_id, 'User logout', $user->id);
            $this->loginSecurityService->logLogout($user->id, $user->email ?: $user->username ?: $user->name);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
