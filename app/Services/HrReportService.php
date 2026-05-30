<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\CashAdvance;
use App\Models\EmployeeLoan;
use App\Models\EmployeeProfile;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HrReportService
{
    public function __construct(private readonly ReportFilterService $filterService)
    {
    }

    public function resolveFilters(array $input, User $user): array
    {
        $filters = $this->filterService->normalize($input);
        $filters['branch_id'] = $this->filterService->enforceBranchScope($user, $filters['branch_id']);

        return $filters;
    }

    public function summary(array $filters): array
    {
        $employeeQuery = EmployeeProfile::query();
        $this->applyBranch($employeeQuery, $filters);

        $attendanceQuery = AttendanceLog::query();
        $this->applyBranch($attendanceQuery, $filters);
        $this->applyDateRange($attendanceQuery, 'attendance_date', $filters);

        $leaveQuery = LeaveRequest::query();
        $this->applyBranch($leaveQuery, $filters);
        $this->applyDateRange($leaveQuery, 'start_date', $filters);

        $overtimeQuery = OvertimeRequest::query();
        $this->applyBranch($overtimeQuery, $filters);
        $this->applyDateRange($overtimeQuery, 'overtime_date', $filters);

        $payrollQuery = PayrollRun::query();
        $this->applyBranch($payrollQuery, $filters);
        $this->applyDateRange($payrollQuery, 'created_at', $filters);

        $loanQuery = EmployeeLoan::query();
        $this->applyBranch($loanQuery, $filters);

        $advanceQuery = CashAdvance::query();
        $this->applyBranch($advanceQuery, $filters);

        return [
            'total_employees' => (int) (clone $employeeQuery)->count(),
            'active_employees' => (int) (clone $employeeQuery)->where('employment_status', 'active')->count(),
            'present_logs' => (int) (clone $attendanceQuery)->where('attendance_status', 'present')->count(),
            'pending_leaves' => (int) (clone $leaveQuery)->whereIn('status', ['pending_manager', 'pending_hr'])->count(),
            'pending_overtime' => (int) (clone $overtimeQuery)->whereIn('status', ['pending_manager', 'pending_hr'])->count(),
            'payroll_net_total' => (float) (clone $payrollQuery)->sum('total_net_pay'),
            'loan_balance_total' => (float) (clone $loanQuery)->where('remaining_balance', '>', 0)->sum('remaining_balance'),
            'cash_advance_balance_total' => (float) (clone $advanceQuery)->where('remaining_balance', '>', 0)->sum('remaining_balance'),
        ];
    }

    public function sections(array $filters): array
    {
        $employees = EmployeeProfile::query()
            ->with(['user', 'branch', 'position'])
            ->when($filters['status'], fn (Builder $query, $status) => $query->where('employment_status', $status))
            ->orderByDesc('employment_date')
            ->limit(30);
        $this->applyBranch($employees, $filters);

        $attendance = AttendanceLog::query()
            ->with(['user', 'branch'])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->limit(40);
        $this->applyBranch($attendance, $filters);
        $this->applyDateRange($attendance, 'attendance_date', $filters);

        $leaves = LeaveRequest::query()
            ->with(['user', 'branch'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->limit(40);
        $this->applyBranch($leaves, $filters);
        $this->applyDateRange($leaves, 'start_date', $filters);

        $payrollRuns = PayrollRun::query()
            ->with(['period', 'branch'])
            ->orderByDesc('id')
            ->limit(20);
        $this->applyBranch($payrollRuns, $filters);
        $this->applyDateRange($payrollRuns, 'created_at', $filters);

        $loanBalances = EmployeeLoan::query()
            ->with(['user', 'branch'])
            ->where('remaining_balance', '>', 0)
            ->orderByDesc('id')
            ->limit(30);
        $this->applyBranch($loanBalances, $filters);

        $cashAdvances = CashAdvance::query()
            ->with(['user', 'branch'])
            ->where('remaining_balance', '>', 0)
            ->orderByDesc('id')
            ->limit(30);
        $this->applyBranch($cashAdvances, $filters);

        return [
            'employees' => $employees->get(),
            'attendance' => $attendance->get(),
            'leaves' => $leaves->get(),
            'payroll_runs' => $payrollRuns->get(),
            'loan_balances' => $loanBalances->get(),
            'cash_advances' => $cashAdvances->get(),
        ];
    }

    public function exportRows(array $filters, int $limit = 1000): Collection
    {
        $leaveRows = LeaveRequest::query()
            ->with(['user', 'branch'])
            ->orderByDesc('start_date')
            ->limit($limit);
        $this->applyBranch($leaveRows, $filters);
        $this->applyDateRange($leaveRows, 'start_date', $filters);

        $payrollRows = PayrollRun::query()
            ->with(['period', 'branch'])
            ->orderByDesc('id')
            ->limit($limit);
        $this->applyBranch($payrollRows, $filters);
        $this->applyDateRange($payrollRows, 'created_at', $filters);

        $loanRows = EmployeeLoan::query()
            ->with(['user', 'branch'])
            ->where('remaining_balance', '>', 0)
            ->orderByDesc('id')
            ->limit($limit);
        $this->applyBranch($loanRows, $filters);

        $advanceRows = CashAdvance::query()
            ->with(['user', 'branch'])
            ->where('remaining_balance', '>', 0)
            ->orderByDesc('id')
            ->limit($limit);
        $this->applyBranch($advanceRows, $filters);

        $rows = collect();

        foreach ($leaveRows->get() as $row) {
            $rows->push([
                'section' => 'Leave',
                'reference' => '#'.$row->id,
                'employee' => $row->user?->display_name,
                'branch' => $row->branch?->branch_name ?? $row->branch?->name,
                'date' => optional($row->start_date)->format('Y-m-d'),
                'status' => (string) $row->status,
                'amount' => (string) $row->total_days,
                'extra' => (string) $row->leave_type,
            ]);
        }

        foreach ($payrollRows->get() as $row) {
            $rows->push([
                'section' => 'Payroll',
                'reference' => '#'.$row->id,
                'employee' => $row->period?->period_code,
                'branch' => $row->branch?->branch_name ?? $row->branch?->name,
                'date' => optional($row->created_at)->format('Y-m-d'),
                'status' => (string) $row->status,
                'amount' => number_format((float) $row->total_net_pay, 2, '.', ''),
                'extra' => 'Net Pay',
            ]);
        }

        foreach ($loanRows->get() as $row) {
            $rows->push([
                'section' => 'Loan',
                'reference' => (string) $row->loan_number,
                'employee' => $row->user?->display_name,
                'branch' => $row->branch?->branch_name ?? $row->branch?->name,
                'date' => optional($row->start_date)->format('Y-m-d'),
                'status' => (string) $row->status,
                'amount' => number_format((float) $row->remaining_balance, 2, '.', ''),
                'extra' => (string) $row->loan_type,
            ]);
        }

        foreach ($advanceRows->get() as $row) {
            $rows->push([
                'section' => 'Cash Advance',
                'reference' => '#'.$row->id,
                'employee' => $row->user?->display_name,
                'branch' => $row->branch?->branch_name ?? $row->branch?->name,
                'date' => optional($row->request_date)->format('Y-m-d'),
                'status' => (string) $row->status,
                'amount' => number_format((float) $row->remaining_balance, 2, '.', ''),
                'extra' => 'Remaining',
            ]);
        }

        return $rows;
    }

    private function applyBranch(Builder $query, array $filters): void
    {
        if ($filters['branch_id']) {
            $query->where('branch_id', $filters['branch_id']);
        }
    }

    private function applyDateRange(Builder $query, string $column, array $filters): void
    {
        if ($filters['date_from']) {
            $query->whereDate($column, '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate($column, '<=', $filters['date_to']);
        }
    }
}
