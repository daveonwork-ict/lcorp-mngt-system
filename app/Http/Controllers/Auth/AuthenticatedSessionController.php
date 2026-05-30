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
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly LoginSecurityService $loginSecurityService,
        private readonly SessionSecurityService $sessionSecurityService,
    ) {
    }

    public function create(Request $request): View
    {
        $mode = Str::lower((string) $request->query('mode', ''));

        if ($mode === 'demo') {
            return $this->createDemo();
        }

        if ($mode === 'normal') {
            return view('auth.login');
        }

        if (config('rms.login_page') === 'demo') {
            return $this->createDemo();
        }

        return view('auth.login');
    }

    public function createDemo(): View
    {
        return view('auth.demo-login', [
            'demoUsers' => $this->getDemoUsers(),
        ]);
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
            'view_attendance' => 'hr.attendance.index',
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

    private function getDemoUsers()
    {
        $defaultPassword = (string) env('RMS_DEFAULT_ROLE_PASSWORD', 'ChangeMe123!');
        $demoPassword = (string) config('rms.demo_password', 'Demo@123456');
        $employeeDemoPassword = (string) config('rms.employee_demo_password', 'Emp@123456');

        return User::query()
            ->with('role')
            ->where('is_active', true)
            ->where('username', '!=', 'superadmin')
            ->where(function ($query): void {
                $query->where('email', 'like', '%@rcstore.local')
                    ->orWhere('username', 'like', 'demo_%')
                    ->orWhere('username', 'like', 'default_%')
                    ->orWhere('username', 'like', 'employee.%')
                    ->orWhereIn('username', ['owner', 'superadmin', 'cashier', 'branchmanager', 'auditor', 'inventorystaff', 'accountingstaff', 'staffuser']);
            })
            ->orderBy('username')
            ->get()
            ->map(function (User $user) use ($defaultPassword, $demoPassword, $employeeDemoPassword): array {
                $username = (string) ($user->username ?? '');
                $email = (string) ($user->email ?? '');

                $resolvedPassword = $defaultPassword;

                if (Str::startsWith($username, 'demo_') || Str::contains($email, 'demo.')) {
                    $resolvedPassword = $demoPassword;
                }

                if (Str::startsWith($username, 'employee.') || Str::startsWith($email, 'employee.')) {
                    $resolvedPassword = $employeeDemoPassword;
                }

                return [
                    'name' => $user->display_name,
                    'username' => $username,
                    'email' => $email,
                    'role' => $user->role?->name ?? 'N/A',
                    'password' => $resolvedPassword,
                ];
            })
            ->values();
    }
}
