<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashAdvance;
use App\Models\User;
use App\Services\CashAdvanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashAdvanceController extends Controller
{
    public function __construct(private readonly CashAdvanceService $cashAdvanceService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.cash-advances.index', [
            'records' => $this->cashAdvanceService->paginate($request->only(['branch_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'request_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
        ]);

        $this->cashAdvanceService->create($validated);

        return back()->with('status', 'Cash advance request created.');
    }

    public function approve(CashAdvance $cashAdvance): RedirectResponse
    {
        $this->cashAdvanceService->approve($cashAdvance);

        return back()->with('status', 'Cash advance approved.');
    }

    public function release(CashAdvance $cashAdvance): RedirectResponse
    {
        $this->cashAdvanceService->release($cashAdvance);

        return back()->with('status', 'Cash advance released.');
    }
}
