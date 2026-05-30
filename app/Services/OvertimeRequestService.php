<?php

namespace App\Services;

use App\Models\OvertimeRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly BranchAccessService $branchAccessService,
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();

        return OvertimeRequest::query()
            ->with(['user', 'branch', 'managerReviewer', 'hrReviewer'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true), fn ($q) => $q->where('user_id', $user->id))
            ->when($user && in_array($user->role?->code, ['branch_manager'], true), fn ($q) => $q->whereIn('branch_id', $this->branchAccessService->accessibleBranches($user)->pluck('id')->all()))
            ->orderByDesc('overtime_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): OvertimeRequest
    {
        $data = $this->resolveOwnedRecordData($data);
        $this->ensureBranchAccess((int) $data['branch_id']);

        $data['status'] = 'pending_manager';

        $request = OvertimeRequest::query()->create($data);

        $this->auditLogService->record('hr_overtime', 'overtime_request_created', [], $request->toArray(), $request->branch_id, 'Overtime request created');

        return $request;
    }

    public function update(OvertimeRequest $overtimeRequest, array $data): OvertimeRequest
    {
        $data = $this->resolveOwnedRecordData($data, $overtimeRequest);
        $this->ensureBranchAccess((int) $data['branch_id']);

        $before = $overtimeRequest->toArray();

        $overtimeRequest->update($data);

        $this->auditLogService->record('hr_overtime', 'overtime_request_updated', $before, $overtimeRequest->toArray(), $overtimeRequest->branch_id, 'Overtime request updated');

        return $overtimeRequest;
    }

    public function review(OvertimeRequest $overtimeRequest, string $decision): OvertimeRequest
    {
        $this->ensureBranchAccess((int) $overtimeRequest->branch_id);

        $user = Auth::user();
        $before = $overtimeRequest->toArray();

        if ($overtimeRequest->status === 'pending_manager') {
            $overtimeRequest->update([
                'status' => $decision === 'approve' ? 'pending_hr' : 'rejected',
                'manager_reviewer_id' => $user?->id,
                'manager_reviewed_at' => now(),
            ]);
        } elseif ($overtimeRequest->status === 'pending_hr') {
            $overtimeRequest->update([
                'status' => $decision === 'approve' ? 'approved' : 'rejected',
                'hr_reviewer_id' => $user?->id,
                'hr_reviewed_at' => now(),
            ]);
        }

        $this->auditLogService->record(
            'hr_overtime',
            'overtime_request_reviewed',
            $before,
            $overtimeRequest->toArray(),
            $overtimeRequest->branch_id,
            'Overtime request reviewed'
        );

        return $overtimeRequest;
    }

    private function ensureBranchAccess(int $branchId): void
    {
        $user = Auth::user();

        if ($user && ! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch overtime access denied.');
        }
    }

    private function resolveOwnedRecordData(array $data, ?OvertimeRequest $overtimeRequest = null): array
    {
        $user = Auth::user();

        if ($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager'], true)) {
            $data['user_id'] = $user->id;
            $data['branch_id'] = $overtimeRequest?->branch_id ?? $user->primary_branch_id ?? $data['branch_id'];
        }

        return $data;
    }
}
