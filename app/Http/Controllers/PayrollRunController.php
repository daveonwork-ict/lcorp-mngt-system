<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Services\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollRunController extends Controller
{
    public function __construct(private readonly PayrollService $payrollService)
    {
    }

    public function index(Request $request): View
    {
        $runs = PayrollRun::query()
            ->with(['period', 'branch'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('hr.payroll.runs.index', [
            'runs' => $runs,
            'periods' => PayrollPeriod::query()->latest('period_end')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payroll_period_id' => ['required', 'integer', 'exists:payroll_periods,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $period = PayrollPeriod::query()->findOrFail($validated['payroll_period_id']);

        $run = $this->payrollService->generateRun($period, $validated['branch_id'] ?? null, $request->user());

        return redirect()->route('hr.payroll.runs.show', $run)->with('status', 'Payroll run generated.');
    }

    public function show(PayrollRun $run): View
    {
        $run->load(['period', 'branch', 'items.user']);

        return view('hr.payroll.runs.show', ['run' => $run]);
    }

    public function submit(PayrollRun $run, Request $request): RedirectResponse
    {
        $this->payrollService->submitForApproval($run, $request->user());

        return back()->with('status', 'Payroll submitted for approval.');
    }

    public function approve(PayrollRun $run, Request $request): RedirectResponse
    {
        $this->payrollService->approve($run, $request->user());

        return back()->with('status', 'Payroll approval updated.');
    }

    public function release(PayrollRun $run, Request $request): RedirectResponse
    {
        $this->payrollService->release($run, $request->user());

        return back()->with('status', 'Payroll released.');
    }
}
