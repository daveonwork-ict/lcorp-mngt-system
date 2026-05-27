<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\User;
use App\Services\DailyClosingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyClosingController extends Controller
{
    public function __construct(private readonly DailyClosingService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('finance.closings.index', [
            'closings' => $this->service->paginate($request->only(['branch_id', 'status', 'date'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'cashiers' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'status', 'date']),
            'denominations' => [1000, 500, 200, 100, 50, 20, 1],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'cashier_id' => ['required', 'exists:users,id'],
            'closing_date' => ['required', 'date'],
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'variance_explanation' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,submitted'],
            'denominations' => ['nullable', 'array'],
            'denominations.*.denomination' => ['required_with:denominations', 'numeric', 'min:0.01'],
            'denominations.*.quantity' => ['required_with:denominations', 'integer', 'min:0'],
        ]);

        $this->service->upsert($validated + ['status' => $validated['status'] ?? 'submitted']);

        return back()->with('status', 'Daily closing saved.');
    }

    public function review(Request $request, DailyClosing $closing): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:reviewed,approved,rejected'],
        ]);

        $this->service->review($closing, $validated['status']);

        return back()->with('status', 'Daily closing updated.');
    }
}
