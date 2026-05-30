<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Services\HrDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrDashboardController extends Controller
{
    public function __construct(
        private readonly HrDashboardService $hrDashboardService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission('view_hr_dashboard')) {
            abort(403, 'HR dashboard access denied.');
        }

        $summary = $this->hrDashboardService->summary();

        $this->auditLogService->record(
            'hr_dashboard',
            'dashboard_viewed',
            [],
            ['dashboard' => 'hr'],
            $user->primary_branch_id,
            'HR dashboard viewed'
        );

        return view('hr.dashboard.index', $summary);
    }
}
