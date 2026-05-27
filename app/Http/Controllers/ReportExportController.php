<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ReportExport;
use App\Models\ScheduledReport;
use App\Services\AuditLogService;
use App\Services\ReportFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportExportController extends Controller
{
    public function __construct(
        private readonly ReportFilterService $filterService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->hasPermission('view_reports') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Report center access denied.');
        }

        $filters = $this->filterService->normalize($request->all());

        $exports = ReportExport::query()
            ->with(['user', 'branch'])
            ->when($user->role?->code !== config('rms.owner_role_code'), fn ($q) => $q->where('user_id', $user->id))
            ->latest('id')
            ->paginate(20);

        $schedules = ScheduledReport::query()
            ->with(['user', 'branch'])
            ->when($user->role?->code !== config('rms.owner_role_code'), fn ($q) => $q->where('user_id', $user->id))
            ->latest('id')
            ->paginate(10);

        return view('reports.index', [
            'exports' => $exports,
            'schedules' => $schedules,
            'filters' => $filters,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
        ]);
    }

    public function schedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_type' => ['required', 'string', 'max:120'],
            'schedule_frequency' => ['required', 'in:daily,weekly,monthly'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'filters' => ['nullable', 'array'],
        ]);

        if (! $request->user()->hasPermission('manage_dashboard_preferences') && $request->user()->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Scheduled report management denied.');
        }

        $branchId = $this->filterService->enforceBranchScope($request->user(), $validated['branch_id'] ?? null);

        $schedule = ScheduledReport::query()->create([
            'user_id' => $request->user()->id,
            'branch_id' => $branchId,
            'report_type' => $validated['report_type'],
            'schedule_frequency' => $validated['schedule_frequency'],
            'filters' => $validated['filters'] ?? null,
            'delivery_channel' => 'in_app',
            'status' => 'active',
            'next_run_at' => now()->addDay(),
        ]);

        $this->auditLogService->record('reports', 'scheduled_report_created', [], $schedule->toArray(), $branchId, 'Scheduled report created');

        return back()->with('status', 'Scheduled report saved.');
    }
}
