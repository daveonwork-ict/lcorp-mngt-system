<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowService
{
    public function __construct(
        private readonly ApprovalRoutingService $routingService,
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function submit(array $payload): ApprovalRequest
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Authentication required.');
        }

        $rules = $this->routingService->matchedRules(
            $payload['module_name'],
            $payload['transaction_type'] ?? null,
            $payload['branch_id'] ?? null,
            isset($payload['amount']) ? (float) $payload['amount'] : null,
            $user->role_id
        );

        if ($rules->isEmpty()) {
            abort(422, 'No approval rule matched this request.');
        }

        return DB::transaction(function () use ($payload, $rules, $user): ApprovalRequest {
            $firstRule = $rules->first();
            $firstApprover = $this->routingService->currentApproverForRule($firstRule, $payload['branch_id'] ?? null, $user->id);

            if (! $firstApprover) {
                abort(422, 'No approver available for first approval level.');
            }

            $approval = ApprovalRequest::query()->create([
                'approval_number' => $payload['approval_number'] ?? ('APR-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT)),
                'module_name' => $payload['module_name'],
                'reference_type' => $payload['reference_type'],
                'reference_id' => $payload['reference_id'],
                'branch_id' => $payload['branch_id'] ?? null,
                'requested_by' => $user->id,
                'requested_at' => now(),
                'current_approver_id' => $firstApprover->id,
                'approval_level' => (int) $firstRule->approval_level,
                'priority' => $payload['priority'] ?? 'normal',
                'status' => 'pending',
                'remarks' => $payload['remarks'] ?? null,
            ]);

            $this->createLog($approval, 'request_created', $payload['remarks'] ?? null);
            $this->createLog($approval, 'submitted', 'Approval request submitted');

            $this->notificationService->create(
                $firstApprover->id,
                $approval->branch_id,
                'New approval request',
                'Approval '.$approval->approval_number.' requires your action.',
                'approval',
                ['approval_request_id' => $approval->id]
            );

            if (in_array($approval->priority, ['urgent', 'critical'], true)) {
                $this->notificationService->create(
                    null,
                    $approval->branch_id,
                    'High priority approval',
                    'High-priority approval '.$approval->approval_number.' submitted.',
                    'approval',
                    ['approval_request_id' => $approval->id, 'priority' => $approval->priority]
                );
            }

            $this->auditLogService->record('approval', 'approval_request_created', [], $approval->toArray(), $approval->branch_id, 'Approval request created');

            return $approval;
        });
    }

    public function createLog(ApprovalRequest $approval, string $action, ?string $remarks = null): void
    {
        $approval->logs()->create([
            'action' => $action,
            'remarks' => $remarks,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
        ]);
    }

    public function levelRules(ApprovalRequest $approval)
    {
        return ApprovalRule::query()
            ->where('module_name', $approval->module_name)
            ->where('status', 'active')
            ->where(function ($q) use ($approval): void {
                $q->where('branch_id', $approval->branch_id)->orWhereNull('branch_id');
            })
            ->orderBy('approval_level')
            ->get();
    }
}
