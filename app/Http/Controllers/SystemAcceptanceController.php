<?php

namespace App\Http\Controllers;

use App\Services\SystemAcceptanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemAcceptanceController extends Controller
{
    public function __construct(private readonly SystemAcceptanceService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.deployment.acceptance.index', [
            'records' => $this->service->paginate($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'accepted_by' => ['nullable', 'integer', 'exists:users,id'],
            'acceptance_date' => ['nullable', 'date'],
            'criteria_payload' => ['nullable', 'array'],
            'status' => ['required', 'string', 'in:draft,ready_for_acceptance,accepted'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->service->create($validated, $request->user());

        return back()->with('status', 'Acceptance record saved.');
    }
}
