<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashOpening;
use App\Models\User;
use App\Services\CashOpeningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashOpeningController extends Controller
{
    public function __construct(private readonly CashOpeningService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('finance.openings.index', [
            'openings' => $this->service->paginate($request->only(['branch_id', 'date'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'cashiers' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'date']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'cashier_id' => ['required', 'exists:users,id'],
            'opening_date' => ['required', 'date'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'opening_cash_amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->service->create($validated);

        return back()->with('status', 'Opening cash created.');
    }

    public function close(CashOpening $opening): RedirectResponse
    {
        $this->service->close($opening);

        return back()->with('status', 'Opening cash closed.');
    }
}
