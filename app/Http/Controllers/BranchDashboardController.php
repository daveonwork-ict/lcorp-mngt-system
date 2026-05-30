<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
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
            'employeePanel' => $this->buildEmployeePanel($user->id),
            'filters' => $filters,
            'branches' => $user->role?->code === config('rms.owner_role_code')
                ? Branch::query()->where('is_active', true)->orderBy('branch_name')->get()
                : $user->branches()->orderBy('branch_name')->get(),
        ]);
    }

    private function buildEmployeePanel(int $userId): ?array
    {
        $user = auth()->user();

        if (! $user || in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true)) {
            return null;
        }

        $latestAttendance = AttendanceLog::query()
            ->with('branch')
            ->where('user_id', $userId)
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->first();

        $latestPayslip = Payslip::query()
            ->with(['payrollItem.run.period'])
            ->whereHas('payrollItem', fn ($q) => $q->where('user_id', $userId))
            ->latest('generated_at')
            ->first();

        return [
            'cards' => [
                ['label' => 'Attendance This Month', 'value' => AttendanceLog::query()->where('user_id', $userId)->whereBetween('attendance_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count(), 'url' => route('hr.attendance.index')],
                ['label' => 'Pending Leave Requests', 'value' => LeaveRequest::query()->where('user_id', $userId)->whereIn('status', ['pending_manager', 'pending_hr'])->count(), 'url' => route('hr.leaves.index')],
                ['label' => 'Pending Overtime Requests', 'value' => OvertimeRequest::query()->where('user_id', $userId)->whereIn('status', ['pending_manager', 'pending_hr'])->count(), 'url' => route('hr.overtime.index')],
                ['label' => 'Payslips Available', 'value' => Payslip::query()->whereHas('payrollItem', fn ($q) => $q->where('user_id', $userId))->count(), 'url' => route('hr.payslips.index')],
            ],
            'latest_attendance' => $latestAttendance,
            'latest_payslip' => $latestPayslip,
        ];
    }
}
