<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditLogService;
use App\Services\DashboardAnalyticsService;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchDashboardController extends Controller
{
    public function __construct(
        private readonly DashboardAnalyticsService $dashboardService,
        private readonly ReportFilterService $filterService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->hasPermission('view_branch_dashboard') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Branch dashboard access denied.');
        }

        $filters = $this->filterService->normalize($request->only(['date_from', 'date_to', 'branch_id', 'category_id', 'provider_id']));
        $branchId = $this->filterService->enforceBranchScope($user, $filters['branch_id']) ?? (int) $request->session()->get('active_branch_id');

        if (! $branchId) {
            abort(422, 'Branch context is required.');
        }

        $branch = Branch::query()->findOrFail($branchId);

        $this->auditLogService->record('dashboard', 'dashboard_viewed', [], ['dashboard' => 'branch', 'filters' => $filters], $branchId, 'Branch dashboard viewed');

        return view('dashboard.branch', [
            'branch' => $branch,
            'summary' => $this->dashboardService->branch($user, $branchId, $filters),
            'filters' => $filters,
            'branches' => $user->role?->code === config('rms.owner_role_code')
                ? Branch::query()->where('is_active', true)->orderBy('branch_name')->get()
                : $user->branches()->orderBy('branch_name')->get(),
        ]);
    }
}
