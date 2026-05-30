<?php

namespace App\Services;

use App\Models\PayrollItem;
use App\Models\Payslip;
use Illuminate\Support\Facades\Storage;

class PayslipService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function generate(PayrollItem $payrollItem): Payslip
    {
        $payrollItem->loadMissing(['run.period', 'user']);

        $number = 'PS-'.now()->format('YmdHis').'-'.str_pad((string) $payrollItem->id, 5, '0', STR_PAD_LEFT);

        $content = $this->buildContent($payrollItem);
        $path = 'hr/payslips/'.$number.'.txt';

        Storage::put($path, $content);

        $payslip = Payslip::query()->updateOrCreate(
            ['payroll_item_id' => $payrollItem->id],
            [
                'payslip_number' => $number,
                'file_path' => $path,
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]
        );

        $this->auditLogService->record('hr_payslips', 'payslip_generated', [], $payslip->toArray(), $payrollItem->branch_id, 'Payslip generated');

        return $payslip;
    }

    public function download(Payslip $payslip)
    {
        if (! $payslip->file_path || ! Storage::exists($payslip->file_path)) {
            abort(404, 'Payslip file not found.');
        }

        return Storage::download($payslip->file_path, $payslip->payslip_number.'.txt');
    }

    private function buildContent(PayrollItem $item): string
    {
        $user = $item->user;
        $period = $item->run?->period;

        return implode(PHP_EOL, [
            'RC STORE RMS PAYSLIP',
            'Payslip #: '.$item->id,
            'Employee: '.($user?->display_name ?? 'N/A'),
            'Period: '.($period?->period_code ?? 'N/A'),
            'Gross Pay: '.number_format((float) $item->gross_pay, 2),
            'Total Deductions: '.number_format((float) $item->total_deductions, 2),
            'Net Pay: '.number_format((float) $item->net_pay, 2),
            'Generated At: '.now()->format('Y-m-d H:i:s'),
        ]).PHP_EOL;
    }
}
