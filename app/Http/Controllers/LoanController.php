<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\EmployeeLoan;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(private readonly LoanService $loanService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.loans.index', [
            'loans' => $this->loanService->paginate($request->only(['branch_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'loan_number' => ['nullable', 'string', 'max:100', 'unique:employee_loans,loan_number'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'loan_type' => ['required', Rule::in(['company', 'salary', 'emergency', 'sss', 'pagibig', 'other'])],
            'principal_amount' => ['required', 'numeric', 'min:1'],
            'interest_rate' => ['nullable', 'numeric', 'min:0'],
            'installment_amount' => ['required', 'numeric', 'min:1'],
            'term_months' => ['required', 'integer', 'min:1', 'max:240'],
            'start_date' => ['required', 'date'],
            'maturity_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $this->loanService->create($validated);

        return back()->with('status', 'Loan created.');
    }

    public function approve(EmployeeLoan $loan): RedirectResponse
    {
        $this->loanService->approve($loan);

        return back()->with('status', 'Loan approved.');
    }

    public function release(EmployeeLoan $loan): RedirectResponse
    {
        $this->loanService->release($loan);

        return back()->with('status', 'Loan released.');
    }
}
