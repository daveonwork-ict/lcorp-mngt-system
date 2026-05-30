<?php

namespace App\Services;

use App\Models\PayrollItem;
use App\Models\Payslip;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PayslipService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function generate(PayrollItem $payrollItem): Payslip
    {
        $payrollItem->loadMissing(['run.period', 'run.branch', 'user', 'branch']);

        $number = 'PS-'.now()->format('YmdHis').'-'.str_pad((string) $payrollItem->id, 5, '0', STR_PAD_LEFT);

        $content = $this->buildContent($payrollItem, $number);
        $path = 'hr/payslips/'.$number.'.doc';

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
        $payslip->loadMissing(['payrollItem.user', 'payrollItem.run.period', 'payrollItem.run.branch', 'payrollItem.branch']);

        if (! $payslip->payrollItem) {
            abort(404, 'Payslip payroll item not found.');
        }

        $path = (string) ($payslip->file_path ?? '');
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

        $docPath = 'hr/payslips/'.$payslip->payslip_number.'.doc';

        if ($extension !== 'doc' || ! $path || ! Storage::exists($path)) {
            Storage::put($docPath, $this->buildContent($payslip->payrollItem, (string) $payslip->payslip_number));

            $payslip->forceFill([
                'file_path' => $docPath,
            ])->save();
        }

        return Storage::download(
            $docPath,
            $payslip->payslip_number.'.doc',
            ['Content-Type' => 'application/msword']
        );
    }

    private function buildContent(PayrollItem $item, string $number): string
    {
        return view('hr.payslips.document', [
            'item' => $item,
            'payslipNumber' => $number,
            'generatedAt' => now(),
        ])->render();
    }
}
