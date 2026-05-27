<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\OfficeSupply;
use App\Models\OfficeSupplyIssuance;
use App\Models\User;
use App\Services\OfficeSupplyIssuanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeSupplyIssuanceController extends Controller
{
    public function __construct(private readonly OfficeSupplyIssuanceService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.office-supplies.issuances', [
            'issuances' => $this->service->paginate($request->only(['branch_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'supplies' => OfficeSupply::query()->where('status', 'active')->orderBy('supply_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'filters' => $request->only(['branch_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'issued_to' => ['required', 'exists:users,id'],
            'issue_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:190'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.office_supply_id' => ['required', 'exists:office_supplies,id'],
            'items.*.quantity_requested' => ['required', 'integer', 'min:1'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $this->service->create($validated);

        return back()->with('status', 'Office supply issuance request created.');
    }

    public function approve(OfficeSupplyIssuance $officeSupplyIssuance): RedirectResponse
    {
        $this->service->approve($officeSupplyIssuance);

        return back()->with('status', 'Issuance approved.');
    }

    public function reject(Request $request, OfficeSupplyIssuance $officeSupplyIssuance): RedirectResponse
    {
        $validated = $request->validate(['rejection_reason' => ['required', 'string', 'min:3']]);
        $this->service->reject($officeSupplyIssuance, $validated['rejection_reason']);

        return back()->with('status', 'Issuance rejected.');
    }

    public function issue(OfficeSupplyIssuance $officeSupplyIssuance): RedirectResponse
    {
        $this->service->issue($officeSupplyIssuance->load('items'));

        return back()->with('status', 'Office supplies released.');
    }
}
