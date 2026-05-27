<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditLogService;
use App\Services\DashboardAnalyticsService;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutiveDashboardController extends Controller
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
        $filters = $this->filterService->normalize($request->only(['date_from', 'date_to', 'branch_id', 'provider_id', 'category_id']));

        if (! $user->hasPermission('view_executive_dashboard') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Executive dashboard access denied.');
        }

        $filters['branch_id'] = $this->filterService->enforceBranchScope($user, $filters['branch_id']);

        $this->auditLogService->record('dashboard', 'dashboard_viewed', [], ['dashboard' => 'executive', 'filters' => $filters], $filters['branch_id'], 'Executive dashboard viewed');

        return view('dashboard.executive', [
            'summary' => $this->dashboardService->executive($user, $filters),
            'filters' => $filters,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
        ]);
    }
}
