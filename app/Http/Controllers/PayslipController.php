<?php

namespace App\Http\Controllers;

use App\Models\PayrollItem;
use App\Models\Payslip;
use App\Services\PayslipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayslipController extends Controller
{
    public function __construct(private readonly PayslipService $payslipService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.payslips.index', [
            'payslips' => Payslip::query()
                ->with(['payrollItem.user', 'payrollItem.run.period'])
                ->latest('generated_at')
                ->paginate(15)
                ->withQueryString(),
            'payrollItems' => PayrollItem::query()->with(['user', 'run.period'])->latest('id')->limit(200)->get(),
        ]);
    }

    public function generate(PayrollItem $item): RedirectResponse
    {
        $this->payslipService->generate($item);

        return back()->with('status', 'Payslip generated.');
    }

    public function download(Payslip $payslip)
    {
        return $this->payslipService->download($payslip);
    }
}
