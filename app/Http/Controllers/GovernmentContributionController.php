<?php

namespace App\Http\Controllers;

use App\Models\PagibigContributionTable;
use App\Models\PhilhealthContributionTable;
use App\Models\SssContributionTable;
use App\Models\WithholdingTaxTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GovernmentContributionController extends Controller
{
    public function index(): View
    {
        return view('hr.contributions.index', [
            'sssRows' => SssContributionTable::query()->latest('effective_date')->paginate(10, ['*'], 'sss_page'),
            'philhealthRows' => PhilhealthContributionTable::query()->latest('effective_date')->paginate(10, ['*'], 'philhealth_page'),
            'pagibigRows' => PagibigContributionTable::query()->latest('effective_date')->paginate(10, ['*'], 'pagibig_page'),
            'taxRows' => WithholdingTaxTable::query()->latest('effective_date')->paginate(10, ['*'], 'tax_page'),
        ]);
    }

    public function storeSss(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'effective_date' => ['required', 'date'],
            'salary_from' => ['required', 'numeric', 'min:0'],
            'salary_to' => ['required', 'numeric', 'gte:salary_from'],
            'msc' => ['required', 'numeric', 'min:0'],
            'employer_share' => ['required', 'numeric', 'min:0'],
            'employee_share' => ['required', 'numeric', 'min:0'],
        ]);

        SssContributionTable::query()->create($validated);

        return back()->with('status', 'SSS contribution row added.');
    }

    public function storePhilhealth(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'effective_date' => ['required', 'date'],
            'salary_from' => ['required', 'numeric', 'min:0'],
            'salary_to' => ['required', 'numeric', 'gte:salary_from'],
            'premium_rate' => ['required', 'numeric', 'min:0'],
            'employer_share' => ['required', 'numeric', 'min:0'],
            'employee_share' => ['required', 'numeric', 'min:0'],
        ]);

        PhilhealthContributionTable::query()->create($validated);

        return back()->with('status', 'PhilHealth contribution row added.');
    }

    public function storePagibig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'effective_date' => ['required', 'date'],
            'salary_from' => ['required', 'numeric', 'min:0'],
            'salary_to' => ['required', 'numeric', 'gte:salary_from'],
            'employee_rate' => ['required', 'numeric', 'min:0'],
            'employer_rate' => ['required', 'numeric', 'min:0'],
            'employee_share' => ['required', 'numeric', 'min:0'],
            'employer_share' => ['required', 'numeric', 'min:0'],
        ]);

        PagibigContributionTable::query()->create($validated);

        return back()->with('status', 'Pag-IBIG contribution row added.');
    }

    public function storeTax(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'effective_date' => ['required', 'date'],
            'payroll_period_type' => ['required', Rule::in(['weekly', 'semi_monthly', 'monthly'])],
            'taxable_income_from' => ['required', 'numeric', 'min:0'],
            'taxable_income_to' => ['required', 'numeric', 'gte:taxable_income_from'],
            'base_tax' => ['required', 'numeric', 'min:0'],
            'excess_over' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0'],
        ]);

        WithholdingTaxTable::query()->create($validated);

        return back()->with('status', 'Withholding tax row added.');
    }
}
