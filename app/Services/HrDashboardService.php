<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HrDashboardService
{
    public function summary(): array
    {
        $totalEmployees = DB::table('users')->count();
        $activeEmployees = DB::table('users')
            ->where(function ($query): void {
                $query->where('status', 'active')->orWhere('is_active', true);
            })
            ->count();

        $pendingLeaveRequests = Schema::hasTable('leave_requests')
            ? DB::table('leave_requests')->whereIn('status', ['pending_manager', 'pending_hr'])->count()
            : 0;

        $pendingPayroll = Schema::hasTable('payroll_runs')
            ? DB::table('payroll_runs')->whereIn('status', ['draft', 'pending_approval'])->count()
            : 0;

        $activeLoans = Schema::hasTable('employee_loans')
            ? DB::table('employee_loans')->where('status', 'active')->count()
            : 0;

        $attendanceToday = Schema::hasTable('attendance_logs')
            ? DB::table('attendance_logs')
                ->whereDate('attendance_date', now()->toDateString())
                ->where('attendance_status', 'present')
                ->count()
            : 0;

        $absentToday = max($activeEmployees - $attendanceToday, 0);

        return [
            'metrics' => [
                ['label' => 'Total Employees', 'value' => number_format($totalEmployees), 'trend' => 'Current workforce'],
                ['label' => 'Active Employees', 'value' => number_format($activeEmployees), 'trend' => 'Employment status active'],
                ['label' => 'Pending Leave Requests', 'value' => number_format($pendingLeaveRequests), 'trend' => 'For review'],
                ['label' => 'Pending Payroll', 'value' => number_format($pendingPayroll), 'trend' => 'Awaiting approval'],
                ['label' => 'Active Loans', 'value' => number_format($activeLoans), 'trend' => 'Loan monitoring'],
                ['label' => 'Attendance Today', 'value' => number_format($attendanceToday), 'trend' => 'Present logs'],
                ['label' => 'Absent Today', 'value' => number_format($absentToday), 'trend' => 'Needs follow-up'],
            ],
            'charts' => [
                'attendance_trend' => $this->attendanceTrend(),
                'payroll_trend' => $this->payrollTrend(),
                'loan_trend' => $this->loanTrend(),
                'branch_staffing_analysis' => $this->branchStaffingAnalysis(),
            ],
            'tables' => [
                'employees_per_branch' => $this->employeesPerBranch(),
            ],
        ];
    }

    private function attendanceTrend(): array
    {
        $labels = collect(range(13, 0))
            ->map(fn (int $daysAgo): string => now()->subDays($daysAgo)->format('M d'));

        if (! Schema::hasTable('attendance_logs')) {
            return ['labels' => $labels->values(), 'values' => $labels->map(fn (): int => 0)->values()];
        }

        $valuesByDate = DB::table('attendance_logs')
            ->selectRaw('attendance_date as date_key, COUNT(*) as present_count')
            ->whereBetween('attendance_date', [now()->subDays(13)->toDateString(), now()->toDateString()])
            ->where('attendance_status', 'present')
            ->groupBy('attendance_date')
            ->pluck('present_count', 'date_key');

        $values = collect(range(13, 0))->map(function (int $daysAgo) use ($valuesByDate): int {
            $date = now()->subDays($daysAgo)->toDateString();

            return (int) ($valuesByDate[$date] ?? 0);
        });

        return ['labels' => $labels->values(), 'values' => $values->values()];
    }

    private function payrollTrend(): array
    {
        if (! Schema::hasTable('payroll_periods') || ! Schema::hasTable('payroll_runs')) {
            return ['labels' => collect(), 'values' => collect()];
        }

        $rows = DB::table('payroll_runs as r')
            ->join('payroll_periods as p', 'p.id', '=', 'r.payroll_period_id')
            ->selectRaw('p.period_code as label, SUM(r.total_net_pay) as total_net_pay')
            ->groupBy('p.period_code', 'p.period_start')
            ->orderBy('p.period_start', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        return [
            'labels' => $rows->pluck('label')->values(),
            'values' => $rows->pluck('total_net_pay')->map(fn ($value): float => (float) $value)->values(),
        ];
    }

    private function loanTrend(): array
    {
        $labels = collect(range(5, 0))
            ->map(fn (int $monthsAgo): string => now()->subMonths($monthsAgo)->format('M Y'));

        if (! Schema::hasTable('employee_loans')) {
            return ['labels' => $labels->values(), 'values' => $labels->map(fn (): int => 0)->values()];
        }

        $start = now()->subMonths(5)->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $rows = DB::table('employee_loans')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(principal_amount) as total_amount")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->pluck('total_amount', 'ym');

        $values = collect(range(5, 0))->map(function (int $monthsAgo) use ($rows): float {
            $ym = now()->subMonths($monthsAgo)->format('Y-m');

            return (float) ($rows[$ym] ?? 0);
        });

        return ['labels' => $labels->values(), 'values' => $values->values()];
    }

    private function branchStaffingAnalysis(): array
    {
        $rows = DB::table('branches as b')
            ->leftJoin('users as u', 'u.primary_branch_id', '=', 'b.id')
            ->selectRaw('COALESCE(NULLIF(b.branch_name, ""), b.name) as branch_name, COUNT(u.id) as total_staff')
            ->groupBy('b.id', 'b.branch_name', 'b.name')
            ->orderBy('branch_name')
            ->get();

        return [
            'labels' => $rows->pluck('branch_name')->map(fn ($value): string => (string) $value)->values(),
            'values' => $rows->pluck('total_staff')->map(fn ($value): int => (int) $value)->values(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function employeesPerBranch(): Collection
    {
        return DB::table('branches as b')
            ->leftJoin('users as u', 'u.primary_branch_id', '=', 'b.id')
            ->selectRaw('COALESCE(NULLIF(b.branch_name, ""), b.name) as branch_name, COUNT(u.id) as total_employees')
            ->groupBy('b.id', 'b.branch_name', 'b.name')
            ->orderBy('branch_name')
            ->get()
            ->map(fn ($row): array => [
                'branch' => $row->branch_name,
                'employees' => (int) $row->total_employees,
            ]);
    }
}
