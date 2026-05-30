<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LeaveRequestService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly BranchAccessService $branchAccessService,
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();

        return LeaveRequest::query()
            ->with(['user', 'branch', 'managerReviewer', 'hrReviewer'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true), fn ($q) => $q->where('user_id', $user->id))
            ->when($user && in_array($user->role?->code, ['branch_manager'], true), fn ($q) => $q->whereIn('branch_id', $this->branchAccessService->accessibleBranches($user)->pluck('id')->all()))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): LeaveRequest
    {
        $data = $this->resolveOwnedRecordData($data);
        $this->ensureBranchAccess((int) $data['branch_id']);

        $data['total_days'] = $this->computeDays($data['start_date'], $data['end_date']);
        $data['status'] = $data['status'] ?? 'pending_manager';

        $request = LeaveRequest::query()->create($data);

        $this->auditLogService->record('hr_leaves', 'leave_request_created', [], $request->toArray(), $request->branch_id, 'Leave request created');

        return $request;
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        $data = $this->resolveOwnedRecordData($data, $leaveRequest);
        $this->ensureBranchAccess((int) $data['branch_id']);
        $before = $leaveRequest->toArray();
        $data['total_days'] = $this->computeDays($data['start_date'], $data['end_date']);

        $leaveRequest->update($data);

        $this->auditLogService->record('hr_leaves', 'leave_request_updated', $before, $leaveRequest->toArray(), $leaveRequest->branch_id, 'Leave request updated');

        return $leaveRequest;
    }

    private function computeDays(string $start, string $end): float
    {
        return (float) Carbon::parse($start)->startOfDay()->diffInDays(Carbon::parse($end)->startOfDay()) + 1;
    }

    private function ensureBranchAccess(int $branchId): void
    {
        $user = Auth::user();

        if ($user && ! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch leave access denied.');
        }
    }

    private function resolveOwnedRecordData(array $data, ?LeaveRequest $leaveRequest = null): array
    {
        $user = Auth::user();

        if ($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true)) {
            $data['user_id'] = $user->id;
            $data['branch_id'] = $leaveRequest?->branch_id ?? $user->primary_branch_id ?? $data['branch_id'];
        }

        return $data;
    }
}
