<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\ExpenseApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseApprovalController extends Controller
{
    public function __construct(private readonly ExpenseApprovalService $service)
    {
    }

    public function approve(Expense $expense): RedirectResponse
    {
        $this->service->approve($expense);

        return back()->with('status', 'Expense approved.');
    }

    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3'],
        ]);

        $this->service->reject($expense, $validated['rejection_reason']);

        return back()->with('status', 'Expense rejected.');
    }
}
