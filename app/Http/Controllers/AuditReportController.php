<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditReportController extends Controller
{
    public function __construct(private readonly ReportFilterService $filterService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $user->hasPermission('view_audit_reports') && ! $user->hasPermission('view_audit_logs') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Audit report access denied.');
        }

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($user, $filters['branch_id']);

        $logs = ActivityLog::query()
            ->with(['user', 'branch'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['status'], fn ($q, $status) => $q->where('action_type', $status))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('reports.audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'failedLogins' => ActivityLog::query()->where('action_type', 'login_failed')->count(),
            'permissionChanges' => ActivityLog::query()->where('action_type', 'permission_changed')->count(),
        ]);
    }
}
