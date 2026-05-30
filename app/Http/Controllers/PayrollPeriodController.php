<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollPeriodController extends Controller
{
    public function index(Request $request): View
    {
        $periods = PayrollPeriod::query()
            ->latest('period_end')
            ->paginate(15)
            ->withQueryString();

        return view('hr.payroll.periods.index', [
            'periods' => $periods,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_code' => ['required', 'string', 'max:80', 'unique:payroll_periods,period_code'],
            'payroll_period_type' => ['required', Rule::in(['weekly', 'semi_monthly', 'monthly'])],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['required', Rule::in(['draft', 'pending_approval', 'approved', 'released', 'cancelled'])],
        ]);

        $validated['created_by'] = $request->user()?->id;

        PayrollPeriod::query()->create($validated);

        return back()->with('status', 'Payroll period created.');
    }
}
