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
        $selfService = $this->isSelfServiceUser();

        return view('hr.payslips.index', [
            'payslips' => Payslip::query()
                ->with(['payrollItem.user', 'payrollItem.run.period'])
                ->when($selfService, fn ($q) => $q->whereHas('payrollItem', fn ($item) => $item->where('user_id', $request->user()->id)))
                ->latest('generated_at')
                ->paginate(15)
                ->withQueryString(),
            'payrollItems' => $selfService ? collect() : PayrollItem::query()->with(['user', 'run.period'])->latest('id')->limit(200)->get(),
            'selfService' => $selfService,
        ]);
    }

    public function generate(PayrollItem $item): RedirectResponse
    {
        $this->payslipService->generate($item);

        return back()->with('status', 'Payslip generated.');
    }

    public function download(Payslip $payslip)
    {
        $payslip->loadMissing('payrollItem');

        if ($this->isSelfServiceUser() && $payslip->payrollItem?->user_id !== auth()->id()) {
            abort(403, 'Payslip access denied.');
        }

        return $this->payslipService->download($payslip);
    }

    public function print(Payslip $payslip): View
    {
        $payslip->loadMissing(['payrollItem.user', 'payrollItem.run.period', 'payrollItem.run.branch', 'payrollItem.branch']);

        if ($this->isSelfServiceUser() && $payslip->payrollItem?->user_id !== auth()->id()) {
            abort(403, 'Payslip access denied.');
        }

        return view('hr.payslips.print', [
            'payslip' => $payslip,
            'item' => $payslip->payrollItem,
        ]);
    }

    private function isSelfServiceUser(): bool
    {
        $user = auth()->user();

        return (bool) $user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager', 'accounting_staff'], true);
    }
}
