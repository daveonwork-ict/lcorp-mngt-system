<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRequestService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return LeaveRequest::query()
            ->with(['user', 'branch', 'managerReviewer', 'hrReviewer'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): LeaveRequest
    {
        $data['total_days'] = $this->computeDays($data['start_date'], $data['end_date']);
        $data['status'] = $data['status'] ?? 'pending_manager';

        $request = LeaveRequest::query()->create($data);

        $this->auditLogService->record('hr_leaves', 'leave_request_created', [], $request->toArray(), $request->branch_id, 'Leave request created');

        return $request;
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
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
}
