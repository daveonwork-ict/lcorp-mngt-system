<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRule;
use App\Models\Branch;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalRuleController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.approvals.rules', [
            'rules' => ApprovalRule::query()->with(['branch', 'role', 'approverRole'])->latest('id')->paginate(20)->withQueryString(),
            'roles' => Role::query()->orderBy('name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['module_name', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rule_name' => ['required', 'string', 'max:190'],
            'module_name' => ['required', 'string', 'max:120'],
            'transaction_type' => ['nullable', 'string', 'max:120'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'approver_role_id' => ['required', 'exists:roles,id'],
            'approval_level' => ['required', 'integer', 'min:1', 'max:10'],
            'requires_owner_approval' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $rule = ApprovalRule::query()->create($validated + [
            'requires_owner_approval' => (bool) ($validated['requires_owner_approval'] ?? false),
        ]);

        $this->auditLogService->record('approval', 'approval_rule_created', [], $rule->toArray(), $rule->branch_id, 'Approval rule created');

        return back()->with('status', 'Approval rule created.');
    }

    public function update(Request $request, ApprovalRule $approvalRule): RedirectResponse
    {
        $validated = $request->validate([
            'rule_name' => ['required', 'string', 'max:190'],
            'module_name' => ['required', 'string', 'max:120'],
            'transaction_type' => ['nullable', 'string', 'max:120'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'approver_role_id' => ['required', 'exists:roles,id'],
            'approval_level' => ['required', 'integer', 'min:1', 'max:10'],
            'requires_owner_approval' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $before = $approvalRule->toArray();
        $approvalRule->update($validated + [
            'requires_owner_approval' => (bool) ($validated['requires_owner_approval'] ?? false),
        ]);

        $this->auditLogService->record('approval', 'approval_rule_updated', $before, $approvalRule->fresh()->toArray(), $approvalRule->branch_id, 'Approval rule updated');

        return back()->with('status', 'Approval rule updated.');
    }
}
