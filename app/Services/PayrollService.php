<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\CashAdvance;
use App\Models\EmployeeLoan;
use App\Models\EmployeeProfile;
use App\Models\LoanInstallment;
use App\Models\OvertimeRequest;
use App\Models\PagibigContributionTable;
use App\Models\Payslip;
use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\PhilhealthContributionTable;
use App\Models\SssContributionTable;
use App\Models\WithholdingTaxTable;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function generateRun(PayrollPeriod $period, ?int $branchId, User $actor): PayrollRun
    {
        return DB::transaction(function () use ($period, $branchId, $actor): PayrollRun {
            $run = PayrollRun::query()->create([
                'payroll_period_id' => $period->id,
                'branch_id' => $branchId,
                'status' => 'draft',
                'generated_by' => $actor->id,
            ]);

            $profiles = EmployeeProfile::query()
                ->with('user')
                ->where('employment_status', 'active')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->get();

            $totals = [
                'gross' => 0.0,
                'deductions' => 0.0,
                'net' => 0.0,
            ];

            foreach ($profiles as $profile) {
                if (! $profile->user_id) {
                    continue;
                }

                $item = $this->computeItem($profile, $period, $run->branch_id ?? $profile->branch_id);
                $run->items()->create($item);

                $totals['gross'] += (float) $item['gross_pay'];
                $totals['deductions'] += (float) $item['total_deductions'];
                $totals['net'] += (float) $item['net_pay'];
            }

            $run->update([
                'total_gross_pay' => round($totals['gross'], 2),
                'total_deductions' => round($totals['deductions'], 2),
                'total_net_pay' => round($totals['net'], 2),
            ]);

            $this->auditLogService->record('hr_payroll', 'payroll_generated', [], $run->toArray(), $run->branch_id, 'Payroll generated');

            return $run->fresh(['period', 'items.user']);
        });
    }

    public function submitForApproval(PayrollRun $run, User $actor): PayrollRun
    {
        $before = $run->toArray();

        $run->update([
            'status' => 'pending_approval',
            'hr_reviewed_by' => $actor->id,
            'hr_reviewed_at' => now(),
        ]);

        $this->auditLogService->record('hr_payroll', 'payroll_submitted', $before, $run->toArray(), $run->branch_id, 'Payroll submitted for approval');

        return $run;
    }

    public function approve(PayrollRun $run, User $actor): PayrollRun
    {
        $before = $run->toArray();

        if ($run->status === 'pending_approval') {
            $run->update([
                'status' => 'manager_approved',
                'manager_approved_by' => $actor->id,
                'manager_approved_at' => now(),
            ]);
        } elseif ($run->status === 'manager_approved') {
            $run->update([
                'status' => 'approved',
                'owner_approved_by' => $actor->id,
                'owner_approved_at' => now(),
            ]);
        }

        $this->auditLogService->record('hr_payroll', 'payroll_approved', $before, $run->toArray(), $run->branch_id, 'Payroll approved');

        return $run;
    }

    public function release(PayrollRun $run, User $actor): PayrollRun
    {
        $before = $run->toArray();

        if ($run->status !== 'approved') {
            abort(422, 'Payroll run must be approved before release.');
        }

        $run->update([
            'status' => 'released',
            'released_by' => $actor->id,
            'released_at' => now(),
        ]);

        $run->items()->update(['status' => 'released']);

        $this->applyReleasedDeductions($run);
        $this->generatePayslipsForRun($run, $actor);

        $this->auditLogService->record('hr_payroll', 'payroll_released', $before, $run->toArray(), $run->branch_id, 'Payroll released');

        return $run;
    }

    private function applyReleasedDeductions(PayrollRun $run): void
    {
        $run->loadMissing('items');

        foreach ($run->items as $item) {
            $loan = EmployeeLoan::query()
                ->where('user_id', $item->user_id)
                ->where('status', 'active')
                ->where('remaining_balance', '>', 0)
                ->orderBy('id')
                ->first();

            if ($loan && (float) $item->loan_deduction > 0) {
                $deductedLoanAmount = min((float) $item->loan_deduction, (float) $loan->remaining_balance);

                $loan->update([
                    'remaining_balance' => round((float) $loan->remaining_balance - $deductedLoanAmount, 2),
                    'status' => ((float) $loan->remaining_balance - $deductedLoanAmount) <= 0 ? 'paid' : $loan->status,
                ]);

                $installment = LoanInstallment::query()
                    ->where('employee_loan_id', $loan->id)
                    ->where('status', 'pending')
                    ->orderBy('due_date')
                    ->first();

                if ($installment) {
                    $installment->update([
                        'payroll_item_id' => $item->id,
                        'amount_paid' => $deductedLoanAmount,
                        'paid_at' => now(),
                        'status' => $deductedLoanAmount >= (float) $installment->amount_due ? 'paid' : 'pending',
                    ]);
                }
            }

            $advance = CashAdvance::query()
                ->where('user_id', $item->user_id)
                ->whereIn('status', ['approved', 'released'])
                ->where('remaining_balance', '>', 0)
                ->orderBy('id')
                ->first();

            if ($advance && (float) $item->cash_advance_deduction > 0) {
                $deductedAdvanceAmount = min((float) $item->cash_advance_deduction, (float) $advance->remaining_balance);

                $advance->update([
                    'remaining_balance' => round((float) $advance->remaining_balance - $deductedAdvanceAmount, 2),
                    'status' => ((float) $advance->remaining_balance - $deductedAdvanceAmount) <= 0 ? 'paid' : $advance->status,
                ]);
            }
        }
    }

    private function generatePayslipsForRun(PayrollRun $run, User $actor): void
    {
        $run->loadMissing('items');

        foreach ($run->items as $item) {
            $number = 'PS-'.now()->format('YmdHis').'-'.str_pad((string) $item->id, 5, '0', STR_PAD_LEFT);
            $path = 'hr/payslips/'.$number.'.txt';

            $content = implode(PHP_EOL, [
                'RC STORE RMS PAYSLIP',
                'Payroll Run #: '.$run->id,
                'Payroll Item #: '.$item->id,
                'Gross Pay: '.number_format((float) $item->gross_pay, 2),
                'Total Deductions: '.number_format((float) $item->total_deductions, 2),
                'Net Pay: '.number_format((float) $item->net_pay, 2),
                'Generated At: '.now()->format('Y-m-d H:i:s'),
            ]).PHP_EOL;

            \Illuminate\Support\Facades\Storage::put($path, $content);

            Payslip::query()->updateOrCreate(
                ['payroll_item_id' => $item->id],
                [
                    'payslip_number' => $number,
                    'file_path' => $path,
                    'generated_by' => $actor->id,
                    'generated_at' => now(),
                ]
            );
        }
    }

    private function computeItem(EmployeeProfile $profile, PayrollPeriod $period, ?int $branchId): array
    {
        $attendanceQuery = AttendanceLog::query()
            ->where('user_id', $profile->user_id)
            ->whereBetween('attendance_date', [$period->period_start, $period->period_end]);

        if ($branchId) {
            $attendanceQuery->where('branch_id', $branchId);
        }

        $attendance = $attendanceQuery->get();

        $daysWorked = $attendance->whereIn('attendance_status', ['present', 'late', 'overtime', 'undertime'])->count();
        $workedMinutes = $attendance->reduce(function (int $carry, AttendanceLog $log): int {
            if (! $log->time_in || ! $log->time_out) {
                return $carry;
            }

            return $carry + max($log->time_out->diffInMinutes($log->time_in), 0);
        }, 0);

        $overtimeMinutesFromAttendance = $attendance->sum('overtime_minutes');

        $approvedOvertimeHours = OvertimeRequest::query()
            ->where('user_id', $profile->user_id)
            ->whereBetween('overtime_date', [$period->period_start, $period->period_end])
            ->where('status', 'approved')
            ->sum('hours');

        $overtimeHours = round(($overtimeMinutesFromAttendance / 60) + (float) $approvedOvertimeHours, 2);

        $basicPay = $this->computeBasicPay($profile, $period->payroll_period_type, $daysWorked, $workedMinutes);
        $hourlyRate = $this->estimateHourlyRate($profile);
        $overtimePay = round($overtimeHours * $hourlyRate * (float) env('RMS_HR_OVERTIME_RATE_MULTIPLIER', 1.25), 2);

        $allowances = 0.0;
        $holidayPay = 0.0;
        $nightDifferentialPay = 0.0;
        $incentives = 0.0;

        $grossPay = round($basicPay + $overtimePay + $allowances + $holidayPay + $nightDifferentialPay + $incentives, 2);

        $sss = $this->computeSss($grossPay, $period->period_end);
        $philhealth = $this->computePhilhealth($grossPay, $period->period_end);
        $pagibig = $this->computePagibig($grossPay, $period->period_end);
        $tax = $this->computeTax($grossPay, $period->period_end, $period->payroll_period_type);
        $loanDeduction = $this->computeLoanDeduction($profile->user_id);
        $cashAdvanceDeduction = $this->computeCashAdvanceDeduction($profile->user_id);
        $otherDeduction = 0.0;

        $totalDeductions = round($sss + $philhealth + $pagibig + $tax + $loanDeduction + $cashAdvanceDeduction + $otherDeduction, 2);
        $netPay = round($grossPay - $totalDeductions, 2);

        return [
            'user_id' => $profile->user_id,
            'branch_id' => $branchId,
            'basic_pay' => $basicPay,
            'overtime_pay' => $overtimePay,
            'allowances' => $allowances,
            'holiday_pay' => $holidayPay,
            'night_differential_pay' => $nightDifferentialPay,
            'incentives' => $incentives,
            'gross_pay' => $grossPay,
            'sss_deduction' => $sss,
            'philhealth_deduction' => $philhealth,
            'pagibig_deduction' => $pagibig,
            'withholding_tax_deduction' => $tax,
            'loan_deduction' => $loanDeduction,
            'cash_advance_deduction' => $cashAdvanceDeduction,
            'other_deduction' => $otherDeduction,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'status' => 'draft',
            'computation_snapshot' => [
                'salary_type' => $profile->salary_type,
                'salary_rate' => (float) $profile->salary_rate,
                'days_worked' => $daysWorked,
                'worked_minutes' => $workedMinutes,
                'overtime_hours' => $overtimeHours,
            ],
        ];
    }

    private function computeBasicPay(EmployeeProfile $profile, string $periodType, int $daysWorked, int $workedMinutes): float
    {
        $rate = (float) $profile->salary_rate;

        return match ($profile->salary_type) {
            'daily' => round($rate * $daysWorked, 2),
            'hourly' => round(($workedMinutes / 60) * $rate, 2),
            default => round($rate * $this->periodMultiplier($periodType), 2),
        };
    }

    private function estimateHourlyRate(EmployeeProfile $profile): float
    {
        $rate = (float) $profile->salary_rate;

        return match ($profile->salary_type) {
            'hourly' => $rate,
            'daily' => $rate / 8,
            default => ($rate / 26) / 8,
        };
    }

    private function periodMultiplier(string $periodType): float
    {
        return match ($periodType) {
            'weekly' => 0.25,
            'semi_monthly' => 0.5,
            default => 1.0,
        };
    }

    private function computeSss(float $grossPay, mixed $periodEnd): float
    {
        $row = SssContributionTable::query()
            ->whereDate('effective_date', '<=', $periodEnd)
            ->where('salary_from', '<=', $grossPay)
            ->where('salary_to', '>=', $grossPay)
            ->orderByDesc('effective_date')
            ->first();

        return round((float) ($row->employee_share ?? 0), 2);
    }

    private function computePhilhealth(float $grossPay, mixed $periodEnd): float
    {
        $row = PhilhealthContributionTable::query()
            ->whereDate('effective_date', '<=', $periodEnd)
            ->where('salary_from', '<=', $grossPay)
            ->where('salary_to', '>=', $grossPay)
            ->orderByDesc('effective_date')
            ->first();

        if (! $row) {
            return 0.0;
        }

        $employeeShare = (float) $row->employee_share;

        if ($employeeShare > 0) {
            return round($employeeShare, 2);
        }

        return round(($grossPay * (float) $row->premium_rate) / 2, 2);
    }

    private function computePagibig(float $grossPay, mixed $periodEnd): float
    {
        $row = PagibigContributionTable::query()
            ->whereDate('effective_date', '<=', $periodEnd)
            ->where('salary_from', '<=', $grossPay)
            ->where('salary_to', '>=', $grossPay)
            ->orderByDesc('effective_date')
            ->first();

        if (! $row) {
            return 0.0;
        }

        $employeeShare = (float) $row->employee_share;

        if ($employeeShare > 0) {
            return round($employeeShare, 2);
        }

        return round($grossPay * (float) $row->employee_rate, 2);
    }

    private function computeTax(float $grossPay, mixed $periodEnd, string $periodType): float
    {
        $row = WithholdingTaxTable::query()
            ->whereDate('effective_date', '<=', $periodEnd)
            ->where('payroll_period_type', $periodType)
            ->where('taxable_income_from', '<=', $grossPay)
            ->where('taxable_income_to', '>=', $grossPay)
            ->orderByDesc('effective_date')
            ->first();

        if (! $row) {
            return 0.0;
        }

        $excess = max($grossPay - (float) $row->excess_over, 0);

        return round((float) $row->base_tax + ($excess * (float) $row->tax_rate), 2);
    }

    private function computeLoanDeduction(int $userId): float
    {
        $loan = EmployeeLoan::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('remaining_balance', '>', 0)
            ->orderBy('id')
            ->first();

        if (! $loan) {
            return 0.0;
        }

        return round(min((float) $loan->remaining_balance, (float) $loan->installment_amount), 2);
    }

    private function computeCashAdvanceDeduction(int $userId): float
    {
        $advance = CashAdvance::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'released'])
            ->where('remaining_balance', '>', 0)
            ->orderBy('id')
            ->first();

        if (! $advance) {
            return 0.0;
        }

        $defaultDeduction = (float) env('RMS_HR_CASH_ADVANCE_DEFAULT_DEDUCTION', 1000);

        return round(min((float) $advance->remaining_balance, $defaultDeduction), 2);
    }
}
