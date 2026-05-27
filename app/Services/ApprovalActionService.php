<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\DB;

class ApprovalActionService
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
        private readonly ApprovalRoutingService $routingService,
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function approve(ApprovalRequest $approval, ?string $remarks = null): ApprovalRequest
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Authentication required.');
        }
        if ((int) $approval->current_approver_id !== (int) $user->id) {
            abort(403, 'You are not the current approver.');
        }
        if ((int) $approval->requested_by === (int) $user->id) {
            abort(403, 'Self-approval is not allowed.');
        }
        if (! in_array($approval->status, ['pending', 'under_review'], true)) {
            abort(422, 'Approval request cannot be approved in current status.');
        }

        return DB::transaction(function () use ($approval, $user, $remarks): ApprovalRequest {
            $rules = $this->workflowService->levelRules($approval);
            $nextRule = $rules->firstWhere('approval_level', '>', $approval->approval_level);

            if ($nextRule) {
                $nextApprover = $this->routingService->currentApproverForRule($nextRule, $approval->branch_id, $approval->requested_by);
                if (! $nextApprover) {
                    abort(422, 'No approver available for next level.');
                }

                $approval->update([
                    'status' => 'under_review',
                    'approval_level' => $nextRule->approval_level,
                    'current_approver_id' => $nextApprover->id,
                ]);

                $this->workflowService->createLog($approval, 'approved_level', $remarks ?: 'Approved and routed to next level');
                $this->notificationService->create($nextApprover->id, $approval->branch_id, 'Approval needs review', 'Approval '.$approval->approval_number.' moved to your queue.', 'approval', ['approval_request_id' => $approval->id]);
            } else {
                $approval->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'current_approver_id' => null,
                    'finalized_at' => now(),
                ]);

                $this->workflowService->createLog($approval, 'approved', $remarks ?: 'Final approval completed');
                $this->notificationService->create($approval->requested_by, $approval->branch_id, 'Request approved', 'Approval '.$approval->approval_number.' was approved.', 'approval', ['approval_request_id' => $approval->id]);
            }

            $this->auditLogService->record('approval', 'approval_approved', [], $approval->fresh()->toArray(), $approval->branch_id, 'Approval approved');

            return $approval->fresh();
        });
    }

    public function reject(ApprovalRequest $approval, string $reason): ApprovalRequest
    {
        $user = auth()->user();
        if (! $user || (int) $approval->current_approver_id !== (int) $user->id) {
            abort(403, 'You are not authorized to reject this request.');
        }
        if (trim($reason) === '') {
            abort(422, 'Rejection reason is required.');
        }

        $approval->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'current_approver_id' => null,
        ]);

        $this->workflowService->createLog($approval, 'rejected', $reason);
        $this->notificationService->create($approval->requested_by, $approval->branch_id, 'Request rejected', 'Approval '.$approval->approval_number.' was rejected.', 'approval', ['approval_request_id' => $approval->id]);
        $this->auditLogService->record('approval', 'approval_rejected', [], $approval->toArray(), $approval->branch_id, 'Approval rejected');

        return $approval->fresh();
    }

    public function returnForCorrection(ApprovalRequest $approval, string $reason): ApprovalRequest
    {
        $user = auth()->user();
        if (! $user || (int) $approval->current_approver_id !== (int) $user->id) {
            abort(403, 'You are not authorized to return this request.');
        }
        if (trim($reason) === '') {
            abort(422, 'Return reason is required.');
        }

        $approval->update([
            'status' => 'returned_for_correction',
            'returned_by' => $user->id,
            'returned_at' => now(),
            'return_reason' => $reason,
            'current_approver_id' => null,
        ]);

        $this->workflowService->createLog($approval, 'returned', $reason);
        $this->notificationService->create($approval->requested_by, $approval->branch_id, 'Request returned', 'Approval '.$approval->approval_number.' was returned for correction.', 'approval', ['approval_request_id' => $approval->id]);
        $this->auditLogService->record('approval', 'approval_returned', [], $approval->toArray(), $approval->branch_id, 'Approval returned for correction');

        return $approval->fresh();
    }

    public function resubmit(ApprovalRequest $approval, ?string $remarks = null): ApprovalRequest
    {
        if ((int) $approval->requested_by !== (int) auth()->id()) {
            abort(403, 'Only requester can resubmit.');
        }
        if ($approval->status !== 'returned_for_correction') {
            abort(422, 'Only returned requests can be resubmitted.');
        }

        $rules = $this->workflowService->levelRules($approval);
        $firstRule = $rules->first();
        if (! $firstRule) {
            abort(422, 'No approval rules available for resubmission.');
        }

        $approver = $this->routingService->currentApproverForRule($firstRule, $approval->branch_id, $approval->requested_by);
        if (! $approver) {
            abort(422, 'No approver found for resubmitted request.');
        }

        $approval->update([
            'status' => 'pending',
            'approval_level' => $firstRule->approval_level,
            'current_approver_id' => $approver->id,
            'return_reason' => null,
            'returned_by' => null,
            'returned_at' => null,
        ]);

        $this->workflowService->createLog($approval, 'resubmitted', $remarks ?: 'Request resubmitted');

        return $approval->fresh();
    }
}
