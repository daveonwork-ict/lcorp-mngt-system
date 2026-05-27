<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalInboxController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = ApprovalRequest::query()->with(['branch', 'requester', 'currentApprover']);

        if ($user?->role?->code !== config('rms.owner_role_code')) {
            $branchIds = $user?->branches()->pluck('branches.id')->all() ?: [];
            $query->where(function ($scope) use ($user, $branchIds): void {
                $scope->where('current_approver_id', $user?->id)
                    ->orWhereIn('branch_id', $branchIds ?: [-1]);
            });
        }

        $inbox = $query
            ->when($request->filled('module_name'), fn ($q) => $q->where('module_name', $request->string('module_name')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.approvals.inbox', [
            'inbox' => $inbox,
            'filters' => $request->only(['module_name', 'priority', 'status']),
        ]);
    }
}
