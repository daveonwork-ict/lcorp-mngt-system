<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\Branch;
use App\Services\ApprovalActionService;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalRequestController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
        private readonly ApprovalActionService $actionService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.approvals.requests', [
            'requests' => ApprovalRequest::query()
                ->with(['branch', 'requester', 'currentApprover'])
                ->when($request->filled('module_name'), fn ($q) => $q->where('module_name', $request->string('module_name')))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['module_name', 'status', 'branch_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_name' => ['required', 'string', 'max:120'],
            'transaction_type' => ['nullable', 'string', 'max:120'],
            'reference_type' => ['required', 'string', 'max:120'],
            'reference_id' => ['required', 'integer', 'min:1'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['required', 'in:normal,important,urgent,critical'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->workflowService->submit($validated);

        return back()->with('status', 'Approval request submitted.');
    }

    public function show(ApprovalRequest $approvalRequest): View
    {
        $this->workflowService->createLog($approvalRequest, 'viewed', 'Approval request viewed');

        return view('admin.approvals.show', [
            'approval' => $approvalRequest->load(['branch', 'requester', 'currentApprover', 'approver', 'rejector', 'returner', 'logs.performer']),
        ]);
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate(['remarks' => ['nullable', 'string']]);
        $this->actionService->approve($approvalRequest, $validated['remarks'] ?? null);

        return back()->with('status', 'Approval processed.');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate(['rejection_reason' => ['required', 'string', 'min:3']]);
        $this->actionService->reject($approvalRequest, $validated['rejection_reason']);

        return back()->with('status', 'Approval rejected.');
    }

    public function returnForCorrection(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate(['return_reason' => ['required', 'string', 'min:3']]);
        $this->actionService->returnForCorrection($approvalRequest, $validated['return_reason']);

        return back()->with('status', 'Request returned for correction.');
    }

    public function resubmit(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate(['remarks' => ['nullable', 'string']]);
        $this->actionService->resubmit($approvalRequest, $validated['remarks'] ?? null);

        return back()->with('status', 'Approval request resubmitted.');
    }
}
